<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Events\AdminNotificationEvent;
use Botble\Base\Facades\EmailHandler;
use Botble\Base\Forms\FieldOptions\HtmlFieldOption;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Supports\AdminNotificationItem;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Forms\Fronts\BecomeVendorForm;
use Botble\Marketplace\Http\Controllers\BaseController;
use Botble\Marketplace\Http\Requests\Fronts\BecomeVendorRequest;
use Botble\Marketplace\Models\Store;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Slug\Facades\SlugHelper;
use Botble\Slug\Models\Slug;
use Botble\Theme\Facades\Theme;
use Closure;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BecomeVendorController extends BaseController
{
    public function __construct()
    {
        $this->middleware(function (Request $request, Closure $next) {
            abort_unless(MarketplaceHelper::isVendorRegistrationEnabled(), 404);

            return $next($request);
        });

        $version = get_cms_version();

        Theme::asset()
            ->add('customer-style', 'vendor/core/plugins/ecommerce/css/customer.css', ['bootstrap-css'], version: $version);

        Theme::asset()
            ->container('footer')
            ->add('ecommerce-utilities-js', 'vendor/core/plugins/ecommerce/js/utilities.js', ['jquery'], version: $version)
            ->add('cropper-js', 'vendor/core/plugins/ecommerce/libraries/cropper.js', ['jquery'], version: $version)
            ->add('avatar-js', 'vendor/core/plugins/ecommerce/js/avatar.js', ['jquery'], version: $version);
    }

    public function index()
    {
        $customer = auth('customer')->user();

        SeoHelper::setTitle(trans('plugins/marketplace::marketplace.become_vendor'));

        Theme::breadcrumb()
            ->add(trans('plugins/marketplace::marketplace.become_vendor'), route('marketplace.vendor.become-vendor'));

        if ($customer->is_vendor) {
            $store = $customer->store;

            if (! $store) {
                $form = BecomeVendorForm::create();

                return Theme::scope('marketplace.become-vendor', compact('form'), MarketplaceHelper::viewPath('become-vendor', false))
                    ->render();
            }

            if (
                MarketplaceHelper::getSetting('requires_vendor_documentations_verification', true)
                && (! $store->certificate_file || ! $store->government_id_file)
            ) {
                $storeInfo = [
                    'shop_name' => $store->name,
                    'shop_url' => $store->slug,
                    'shop_phone' => $store->phone,
                ];

                $form = BecomeVendorForm::createFromArray($storeInfo)
                    ->addBefore(
                        'shop_name',
                        'missing_documentation_alert',
                        HtmlField::class,
                        HtmlFieldOption::make()
                            ->content('<div class="alert alert-warning">' . trans('plugins/marketplace::marketplace.missing_documentations') . '</div>')
                    )
                    ->setUrl(route('marketplace.vendor.become-vendor.update'))
                    ->setMethod('PUT');

                return Theme::scope('marketplace.become-vendor', compact('form'), MarketplaceHelper::viewPath('become-vendor', false))
                    ->render();
            }

            if (MarketplaceHelper::getSetting('verify_vendor', 1) && ! $customer->vendor_verified_at) {
                return Theme::scope('marketplace.approving-vendor', compact('store'), MarketplaceHelper::viewPath('approving-vendor', false))
                    ->render();
            }

            return redirect()->route('marketplace.vendor.dashboard');
        }

        $form = BecomeVendorForm::create();

        return Theme::scope('marketplace.become-vendor', compact('form'), MarketplaceHelper::viewPath('become-vendor', false))
            ->render();
    }

    public function store(BecomeVendorRequest $request)
    {
        $customer = auth('customer')->user();

        abort_if($customer->is_vendor, 404);

        $existing = SlugHelper::getSlug($request->input('shop_url'), SlugHelper::getPrefix(Store::class));

        if ($existing) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/marketplace::store.forms.shop_url_existing'));
        }

        event(new Registered($customer));

        return $this
            ->httpResponse()
            ->setData([
                'redirect_url' => route('marketplace.vendor.dashboard'),
            ])
            ->setMessage(trans('plugins/marketplace::marketplace.registered_successfully'));
    }

    public function update(BecomeVendorRequest $request)
    {
        $customer = auth('customer')->user();

        abort_if($customer->is_vendor
        && (! MarketplaceHelper::getSetting('requires_vendor_documentations_verification', true)
            || ($customer->certificate_of_incorporation && $customer->government_id)), 404);

        $store = $customer->store;

        $store->name = $request->input('shop_name');
        $store->phone = $request->input('shop_phone');

        Slug::query()->updateOrCreate([
            'reference_type' => Store::class,
            'reference_id' => $store->id,
            'key' => Str::slug($request->input('shop_url')),
            'prefix' => SlugHelper::getPrefix(Store::class),
        ]);

        $storage = Storage::disk('local');

        if (! $storage->exists("marketplace/$store->slug")) {
            $storage->makeDirectory("marketplace/$store->slug");
        }

        if ($certificateFile = $request->file('certificate_file')) {
            $certificateFilePath = $storage->putFileAs("marketplace/$store->slug", $certificateFile, 'certificate.' . $certificateFile->getClientOriginalExtension());
            $store->certificate_file = $certificateFilePath;
        }

        if ($governmentIdFile = $request->file('government_id_file')) {
            $governmentIdFilePath = $storage->putFileAs("marketplace/$store->slug", $governmentIdFile, 'government_id.' . $governmentIdFile->getClientOriginalExtension());
            $store->government_id_file = $governmentIdFilePath;
        }

        $documentsUploaded = $store->isDirty(['certificate_file', 'government_id_file']);

        $store->save();

        if ($documentsUploaded && MarketplaceHelper::getSetting('verify_vendor', 1)) {
            $customer->vendor_verified_at = null;
            $customer->save();

            $mailer = EmailHandler::setModule(MARKETPLACE_MODULE_SCREEN_NAME);

            if ($mailer->templateEnabled('verify_vendor')) {
                $mailer
                    ->setVariableValues([
                        'customer_name' => $customer->name,
                        'customer_email' => $customer->email,
                        'customer_phone' => $customer->phone,
                        'store_name' => $store->name,
                        'store_phone' => $store->phone,
                        'store_address' => $store->address,
                        'store_link' => route('marketplace.unverified-vendors.view', $customer->getKey()),
                        'store_url' => route('marketplace.unverified-vendors.view', $customer->getKey()),
                    ])
                    ->sendUsingTemplate('verify_vendor', EcommerceHelper::getAdminNotificationEmails());
            }

            event(new AdminNotificationEvent(
                AdminNotificationItem::make()
                    ->title(trans('plugins/marketplace::unverified-vendor.new_vendor_notifications.new_vendor'))
                    ->description(trans('plugins/marketplace::unverified-vendor.new_vendor_notifications.description', [
                        'customer' => $customer->name,
                    ]))
                    ->action(trans('plugins/marketplace::unverified-vendor.new_vendor_notifications.view'), route('marketplace.unverified-vendors.view', $customer->id))
                    ->permission('marketplace.unverified-vendors.edit')
            ));
        }

        return $this
            ->httpResponse()
            ->setData([
                'redirect_url' => route('marketplace.vendor.become-vendor'),
            ])
            ->setMessage(trans('plugins/marketplace::marketplace.updated_registration_info_successfully'));
    }

    public function downloadCertificate()
    {
        $customer = auth('customer')->user();

        abort_if(! $customer->is_vendor || ! $customer->store, 404);

        $storage = Storage::disk('local');

        $certificate = $customer->store->certificate_file;

        if (! $storage->exists($certificate)) {
            return BaseHttpResponse::make()
                ->setError()
                ->setMessage(trans('plugins/marketplace::marketplace.notices.file_not_found'));
        }

        return response()->file($storage->path($certificate));
    }

    public function downloadGovernmentId()
    {
        $customer = auth('customer')->user();

        abort_if(! $customer->is_vendor || ! $customer->store, 404);

        $storage = Storage::disk('local');

        $governmentId = $customer->store->government_id_file;

        if (! $storage->exists($governmentId)) {
            return BaseHttpResponse::make()
                ->setError()
                ->setMessage(trans('plugins/marketplace::marketplace.notices.file_not_found'));
        }

        return response()->file($storage->path($governmentId));
    }
}
