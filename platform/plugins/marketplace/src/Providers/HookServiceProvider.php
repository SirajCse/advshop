<?php

namespace Botble\Marketplace\Providers;

use Botble\ACL\Models\User;
use Botble\Base\Contracts\BaseModel;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\AdminHelper;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;
use Botble\Base\Forms\FieldOptions\HtmlFieldOption;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\FieldOptions\PhoneNumberFieldOption;
use Botble\Base\Forms\FieldOptions\RadioFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\OnOffField;
use Botble\Base\Forms\Fields\PhoneNumberField;
use Botble\Base\Forms\Fields\RadioField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Base\Rules\MediaImageRule;
use Botble\Ecommerce\Enums\CustomerStatusEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Forms\CustomerForm;
use Botble\Ecommerce\Forms\Fronts\Auth\RegisterForm;
use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\Discount;
use Botble\Ecommerce\Models\Invoice;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\OrderProduct;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\Shipment;
use Botble\Ecommerce\Tables\CustomerTable;
use Botble\Ecommerce\Tables\OrderIncompleteTable;
use Botble\Ecommerce\Tables\ProductTable;
use Botble\Language\Facades\Language;
use Botble\LanguageAdvanced\Supports\LanguageAdvancedManager;
use Botble\Marketplace\Enums\RevenueTypeEnum;
use Botble\Marketplace\Enums\WithdrawalStatusEnum;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Forms\ContactStoreForm;
use Botble\Marketplace\Http\Requests\Fronts\ContactStoreRequest;
use Botble\Marketplace\Models\Revenue;
use Botble\Marketplace\Models\Store;
use Botble\Marketplace\Models\VendorInfo;
use Botble\Marketplace\Models\Withdrawal;
use Botble\Marketplace\Services\CartValidateSameStore;
use Botble\Marketplace\Services\GeneratePayoutInvoiceService;
use Botble\Media\Facades\RvMedia;
use Botble\Slug\Facades\SlugHelper;
use Botble\Table\Abstracts\TableAbstract;
use Botble\Table\CollectionDataTable;
use Botble\Table\Columns\Column;
use Botble\Table\EloquentDataTable;
use Botble\Theme\Events\RenderingThemeOptionSettings;
use Botble\Theme\Facades\Theme;
use Botble\Theme\FormFrontManager;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\File as ValidationFile;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Throwable;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->booted(function (): void {
            FormFrontManager::register(ContactStoreForm::class, ContactStoreRequest::class);

            add_filter('ecommerce_order_product_relations', [$this, 'addStoreRelations'], 120);
            add_filter('ecommerce_order_product_name', [$this, 'appendStoreToProductName'], 120, 3);
            add_filter('ecommerce_order_validate_products', [$this, 'validateDifferentStores'], 120, 2);
            add_filter('ecommerce_order_shipping_origin_address', [$this, 'setStoreAsOriginAddress'], 120, 3);
            add_filter('ecommerce_order_detail_extra_info', [$this, 'addStoreInfoToOrderDetail'], 120, 2);
            add_filter('ecommerce_order_product_item_extra_info_after', [$this, 'addStoreInfoToOrderProductItem'], 120, 3);
            add_filter('ecommerce_order_table_filters', [$this, 'addStoreFilterToOrderTable'], 120, 2);

            add_filter(BASE_FILTER_AFTER_FORM_CREATED, [$this, 'registerAdditionalData'], 128, 2);

            add_action(BASE_ACTION_AFTER_CREATE_CONTENT, [$this, 'saveAdditionalData'], 128, 3);

            add_action(BASE_ACTION_AFTER_UPDATE_CONTENT, [$this, 'saveAdditionalData'], 128, 3);

            add_filter(BASE_FILTER_GET_LIST_DATA, [$this, 'addColumnToEcommerceTable'], 153, 3);
            add_filter(BASE_FILTER_TABLE_HEADINGS, [$this, 'addHeadingToEcommerceTable'], 153, 3);
            add_filter(BASE_FILTER_TABLE_QUERY, [$this, 'modifyQueryInCustomerTable'], 153, 2);

            add_filter('base_filter_table_filters', function (array $filters, TableAbstract $table) {
                if ($table instanceof CustomerTable) {
                    $filters['is_vendor'] = [
                        'title' => trans('plugins/marketplace::store.forms.is_vendor'),
                        'type' => 'select',
                        'choices' => [1 => trans('core/base::base.yes'), 0 => trans('core/base::base.no')],
                        'validate' => 'required|in:1,0',
                    ];
                }

                if ($table instanceof ProductTable) {
                    $filters['store_id'] = [
                        'title' => trans('plugins/marketplace::store.forms.store'),
                        'type' => 'select-search',
                        'validate' => 'required|string',
                        'callback' => fn () => cache()->remember(
                            'marketplace_stores_for_filter',
                            Carbon::now()->addMinutes(15),
                            fn () => Store::query()->pluck('name', 'id')->all()
                        ),
                    ];
                }

                return $filters;
            }, 120, 2);

            add_filter(BASE_FILTER_APPEND_MENU_NAME, [$this, 'getUnverifiedVendors'], 130, 2);
            add_filter(BASE_FILTER_MENU_ITEMS_COUNT, [$this, 'getMenuItemCount'], 121);

            $this->app['events']->listen(RenderingThemeOptionSettings::class, function (): void {
                add_action(RENDERING_THEME_OPTIONS_PAGE, [$this, 'addThemeOptions'], 55);
            });

            add_filter(BASE_FILTER_BEFORE_RENDER_FORM, function (FormAbstract $form) {
                if (is_plugin_active('language') && is_plugin_active('language-advanced')) {
                    $model = $form->getModel();

                    if (
                        $model instanceof BaseModel &&
                        Language::getCurrentAdminLocaleCode() != Language::getDefaultLocaleCode() &&
                        $model->getKey() &&
                        LanguageAdvancedManager::isSupported($model) &&
                        Route::current() &&
                        in_array('vendor', Route::current()->middleware()) &&
                        auth('customer')->check() &&
                        auth('customer')->user()->is_vendor
                    ) {
                        $refLang = null;

                        if (Language::getCurrentAdminLocaleCode() != Language::getDefaultLocaleCode()) {
                            $refLang = '?ref_lang=' . Language::getCurrentAdminLocaleCode();
                        }

                        $form->setUrl(route('marketplace.vendor.language-advanced.save', $model->getKey()) . $refLang);
                    }
                }

                return $form;
            }, 1234);

            FormAbstract::beforeRendering(function (): void {
                add_action(BASE_ACTION_TOP_FORM_CONTENT_NOTIFICATION, [$this, 'createdByVendorNotification'], 45, 2);
                add_action(BASE_ACTION_TOP_FORM_CONTENT_NOTIFICATION, [$this, 'withdrawalVendorNotification'], 47, 2);

                add_filter('marketplace_vendor_dashboard_language_switcher', fn () => '', 120);

                add_filter('route_actions', function (array $routes): array {
                    if (Route::currentRouteName() !== 'marketplace.vendor.settings') {
                        return $routes;
                    }

                    return [
                        'create' => 'marketplace.vendor.settings',
                        'edit' => 'marketplace.vendor.settings',
                    ];
                }, 120);
            });

            add_filter(ACTION_BEFORE_POST_ORDER_REFUND_ECOMMERCE, [$this, 'beforeOrderRefund'], 120, 3);
            add_filter(ACTION_AFTER_POST_ORDER_REFUNDED_ECOMMERCE, [$this, 'afterOrderRefunded'], 120, 3);

            if (MarketplaceHelper::isVendorRegistrationEnabled()) {
                add_filter('ecommerce_customer_registration_form_validation_rules', function (array $rules): array {
                    return $rules + [
                        'shop_name' => [
                            'nullable',
                            'required_if:is_vendor,1',
                            'string',
                            'min:2',
                        ],
                        'shop_phone' => [
                            'nullable',
                            'required_if:is_vendor,1',
                        ] + explode('|', BaseHelper::getPhoneValidationRule()),
                        'shop_url' => [
                            'nullable',
                            'required_if:is_vendor,1',
                            'string',
                            'min:2',
                        ],
                    ];
                }, 45, 2);

                add_filter('ecommerce_customer_registration_form_validation_attributes', function (array $attributes): array {
                    return $attributes + [
                        'shop_name' => trans('plugins/marketplace::store.shop_name'),
                        'shop_phone' => trans('plugins/marketplace::store.shop_phone'),
                        'shop_url' => trans('plugins/marketplace::store.shop_url'),
                    ];
                }, 45);

                add_filter('ecommerce_customer_registration_form_validation_messages', function (array $attributes): array {
                    return $attributes + [
                        'shop_name.required_if' => trans('plugins/marketplace::store.shop_name_required'),
                        'shop_phone.required_if' => trans('plugins/marketplace::store.shop_phone_required'),
                        'shop_url.required_if' => trans('plugins/marketplace::store.shop_url_required'),
                    ];
                }, 45);

                add_action('customer_register_validation', function ($request): void {
                    if (is_plugin_active('marketplace') && $request->input('is_vendor') == 1) {
                        $existing = SlugHelper::getSlug($request->input('shop_url'), SlugHelper::getPrefix(Store::class));

                        if ($existing) {
                            throw ValidationException::withMessages([
                                'shop_url' => trans('plugins/marketplace::marketplace.shop_url_exists'),
                            ]);
                        }
                    }
                }, 45, 2);
            }

            add_filter('ecommerce_import_product_row_data', [$this, 'setStoreToRow'], 45);

            add_filter('ecommerce_shipping_label_data', function (array $data, Shipment $shipment): array {
                $store = $shipment->order->store;

                if (! $store || ! $store->id) {
                    return $data;
                }

                return [
                    ...$data,
                    'sender' => [
                        ...$data['sender'],
                        'name' => $store->name,
                        'logo' => $store->logo ? RvMedia::getRealPath($store->logo) : $data['sender']['logo'],
                        'phone' => $store->phone,
                        'email' => $store->email,
                        'address' => $store->address,
                        'full_address' => $store->full_address,
                        'city' => $store->city_name,
                        'state' => $store->state_name,
                        'country' => $store->country_name,
                        'zip_code' => $store->zip_code,
                    ],
                ];
            }, 999, 2);

            add_filter('ecommerce_invoice_variables', function (array $variables, Invoice $invoice): array {
                if (! $invoice->reference) {
                    return $variables;
                }

                if (MarketplaceHelper::getSetting('hide_store_info_in_invoice', false)) {
                    return $variables;
                }

                $store = $invoice->reference->store;

                if (! $store || ! $store->id) {
                    return $variables;
                }

                if ($store->logo) {
                    $variables['logo_full_path'] = RvMedia::getRealPath($store->logo);
                    $variables['company_logo_full_path'] = RvMedia::getRealPath($store->logo);
                }

                if ($store->name) {
                    $variables['site_title'] = $store->name;
                }

                return array_merge($variables, [
                    'company_name' => $store->name,
                    'company_address' => $store->address,
                    'company_phone' => $store->phone,
                    'company_email' => $store->email,
                    'company_tax_id' => $store->tax_id,
                    'store' => $store->toArray(),
                ]);
            }, 45, 2);

            add_filter('ecommerce_product_eager_loading_relations', function (array $with) {
                return array_merge($with, ['store', 'store.slugable']);
            }, 120);

            add_filter('ecommerce_customer_form_end', function (?string $html, CustomerForm $form) {
                if (! $form->getModel()->id) {
                    return $html;
                }

                $isActivated = $form->getModel()->status == CustomerStatusEnum::ACTIVATED();

                return $html . view('plugins/marketplace::customers.control-modal', ['isActivated' => $isActivated, 'model' => $form->getModel()])->render();
            }, 999, 2);
        });

        if (is_plugin_active('marketplace') && MarketplaceHelper::isVendorRegistrationEnabled() && MarketplaceHelper::getSetting('show_vendor_registration_form_at_registration_page', true)) {
            RegisterForm::extend(function (RegisterForm $form): void {
                Theme::asset()
                    ->container('footer')
                    ->add('marketplace-register', 'vendor/core/plugins/marketplace/js/customer-register.js', ['jquery']);

                $form
                    ->addAfter(
                        'password_confirmation',
                        'is_vendor',
                        RadioField::class,
                        RadioFieldOption::make()
                            ->label(trans('plugins/marketplace::marketplace.register_as'))
                            ->choices([0 => trans('plugins/marketplace::marketplace.i_am_customer'), 1 => trans('plugins/marketplace::marketplace.i_am_vendor')])
                            ->defaultValue(0)
                    )
                    ->addAfter(
                        'is_vendor',
                        'openVendorWrapper',
                        HtmlField::class,
                        ['html' => sprintf('<div data-bb-toggle="vendor-info" style="%s">', old('is_vendor') ? '' : 'display: none;')]
                    )
                    ->addAfter(
                        'openVendorWrapper',
                        'shop_name',
                        TextField::class,
                        TextFieldOption::make()
                            ->label(trans('plugins/marketplace::store.shop_name'))
                            ->placeholder(trans('plugins/marketplace::store.ex_my_shop'))
                    )
                    ->addAfter(
                        'shop_name',
                        'shop_url',
                        TextField::class,
                        TextFieldOption::make()
                            ->label(trans('plugins/marketplace::store.shop_url'))
                            ->placeholder(trans('plugins/marketplace::store.store_url'))
                            ->attributes([
                                'data-url' => route('public.ajax.check-store-url'),
                                'style' => 'direction: ltr; text-align: left;',
                            ])
                            ->wrapperAttributes(['class' => 'shop-url-wrapper mb-3 position-relative'])
                            ->prepend(
                                sprintf(
                                    '<span class="position-absolute top-0 end-0 shop-url-status"></span><div class="input-group"><span class="input-group-text">%s</span>',
                                    route('public.store', ['slug' => '/'])
                                )
                            )
                            ->append('</div>')
                            ->helperText(trans('plugins/marketplace::store.forms.shop_url_helper'))
                            ->required(),
                    )
                    ->addAfter(
                        'shop_url',
                        'shop_phone',
                        PhoneNumberField::class,
                        PhoneNumberFieldOption::make()
                            ->label(trans('plugins/marketplace::store.forms.phone'))
                            ->placeholder(trans('plugins/marketplace::store.ex_phone'))
                            ->withCountryCodeSelection()
                    )
                    ->addAfter('shop_phone', 'closeVendorWrapper', HtmlField::class, ['html' => '</div>']);
            });
        }

        add_filter('language_advanced_before_save', function (array $data, ?Model $model, Request $request) {
            if (! $model instanceof Store) {
                return $data;
            }

            $request->validate([
                'cover_image_input' => ['nullable', new MediaImageRule()],
            ]);

            if ($request->hasFile('cover_image_input')) {
                $result = RvMedia::handleUpload($request->file('cover_image_input'), 0, 'stores');

                if (! $result['error']) {
                    $data['cover_image'] = $result['data']->url;
                }
            }

            return $data;
        }, 45, 3);

        add_filter('ecommerce_invoice_templates', function (array $templates): array {
            $generateWithdrawalInvoiceService = new GeneratePayoutInvoiceService();

            return [
                ...$templates,
                'payout' => [
                    'label' => trans('plugins/marketplace::withdrawal.invoice.invoice_template_label'),
                    'content' => fn () => $generateWithdrawalInvoiceService->getContent(),
                    'variables' => fn () => $generateWithdrawalInvoiceService->getVariables(),
                    'customized_path' => $generateWithdrawalInvoiceService->getCustomizedTemplatePath(),
                    'preview' => fn () => $generateWithdrawalInvoiceService->preview(),
                ],
            ];
        }, 999);

        add_filter('checkout_discount_item_before', function (?string $html, Discount $discount) {
            if (! $discount->store_id) {
                return $html;
            }

            return $html . view('plugins/marketplace::checkout.discount-item-store', compact('discount'))->render();
        }, 999, 2);

        add_action('ecommerce_before_add_to_cart', function (Product $originalProduct): void {
            (new CartValidateSameStore())->handle(
                $originalProduct,
                trans('plugins/marketplace::marketplace.only_add_products_from_same_store')
            );
        }, 999);

        add_action('ecommerce_post_checkout', function (): void {
            (new CartValidateSameStore())->handle(
                errorMessage: trans('plugins/marketplace::marketplace.checkout_only_one_store')
            );
        }, 999);

        add_filter('core_media_extra_validation', function (array $validation, UploadedFile $fileUpload) {
            if (AdminHelper::isInAdmin(true)) {
                return $validation;
            }

            if (! AdminHelper::isInAdmin() || ! auth('customer')->check() || ! auth('customer')->user()->is_vendor) {
                return $validation;
            }

            if ($allowedFileTypes = MarketplaceHelper::mediaMimeTypesAllowed()) {
                $validator = Validator::make(['uploaded_file' => $fileUpload], [
                    'uploaded_file' => ['required', ValidationFile::types($allowedFileTypes)],
                ]);

                if ($validator->fails()) {
                    throw ValidationException::withMessages([
                        'uploaded_file' => $validator->getMessageBag()->first(),
                    ]);
                }
            }

            return $validation;
        }, 999, 2);

        add_filter('ecommerce_order_placed_webhook_data', function (array $data, Order $order): array {
            $store = $order->store;

            if ($store && $store->id) {
                $data['store'] = [
                    'id' => $store->id,
                    'name' => $store->name,
                    'url' => $store->url,
                    'phone' => $store->phone,
                    'email' => $store->email,
                    'address' => $store->address,
                    'full_address' => $store->full_address,
                    'city' => $store->city_name,
                    'state' => $store->state_name,
                    'country' => $store->country_name,
                    'zip_code' => $store->zip_code,
                    'logo' => $store->logo ? RvMedia::getImageUrl($store->logo) : null,
                ];
            }

            return $data;
        }, 999, 2);

        // Vendor vacation mode: block purchases and show a notice while a store is on vacation.
        add_action('ecommerce_before_add_to_cart', [$this, 'blockAddToCartForVacationStore'], 45);
        add_filter('ecommerce_order_validate_products', [$this, 'validateStoresNotOnVacation'], 125, 2);
        add_filter(ECOMMERCE_PRODUCT_DETAIL_EXTRA_HTML, [$this, 'addVacationNoticeToProductDetail'], 45, 2);
    }

    public function blockAddToCartForVacationStore(Product $product): void
    {
        $store = $product->original_product->store ?? $product->store;

        if ($store && $store->id && $store->isOnVacation()) {
            throw new Exception(
                $store->vacation_message
                    ?: trans('plugins/marketplace::store.forms.vacation_default_notice', ['store' => $store->name])
            );
        }
    }

    public function validateStoresNotOnVacation(array $result, $stores): array
    {
        // Respect an error already raised by an earlier validator (e.g. different-vendor check).
        if (! empty($result['isError'])) {
            return $result;
        }

        foreach ($stores as $store) {
            if ($store && $store->id && $store->isOnVacation()) {
                return [
                    'isError' => true,
                    'message' => $store->vacation_message
                        ?: trans('plugins/marketplace::store.forms.vacation_default_notice', ['store' => $store->name]),
                ];
            }
        }

        return $result;
    }

    public function addVacationNoticeToProductDetail(?string $html, Product $product): ?string
    {
        $store = $product->original_product->store ?? $product->store;

        if (! $store || ! $store->id || ! $store->isOnVacation()) {
            return $html;
        }

        return $html . view(MarketplaceHelper::viewPath('includes.vacation-notice'), compact('store'))->render();
    }

    public function beforeOrderRefund(BaseHttpResponse $response, Order $order, Request $request): BaseHttpResponse
    {
        $refundAmount = $request->input('refund_amount');
        if ($refundAmount) {
            $store = $order->store;
            if ($store && $store->id) {
                $vendor = $store->customer;
                if ($vendor && $vendor->id) {

                    if (
                        Revenue::query()
                        ->where(['order_id' => $order->getKey(), 'customer_id' => $vendor->id])
                        ->doesntExist()
                    ) {
                        return $response;
                    }

                    $vendorInfo = $vendor->vendorInfo;
                    if ($vendorInfo->balance < $refundAmount) {
                        $response
                            ->setError()
                            ->setMessage(
                                trans('plugins/marketplace::order.refund.insufficient_balance', [
                                    'balance' => format_price($vendorInfo->balance),
                                ])
                            );
                    }
                }
            }
        }

        return $response;
    }

    public function afterOrderRefunded(BaseHttpResponse $response, Order $order, Request $request): BaseHttpResponse
    {
        $refundAmount = $request->input('refund_amount');
        if ($refundAmount) {
            $store = $order->store;
            if ($store && $store->id) {
                $vendor = $store->customer;
                if ($vendor && $vendor->id) {

                    $originalRevenue = Revenue::query()
                        ->where([
                            'order_id' => $order->getKey(),
                            'customer_id' => $vendor->id,
                            'type' => RevenueTypeEnum::ADD_AMOUNT,
                        ])
                        ->first();

                    if (! $originalRevenue) {
                        return $response;
                    }

                    $vendorInfo = $vendor->vendorInfo;

                    // Calculate vendor's proportional share of the refund
                    // The vendor only received amount after tax/shipping/fee deductions,
                    // so the refund should only subtract the vendor's proportional share.
                    $orderAmount = $order->amount;
                    if ($orderAmount > 0 && $originalRevenue->amount > 0) {
                        $vendorRefundAmount = $refundAmount * ($originalRevenue->amount / $orderAmount);
                        $vendorRefundAmount = round($vendorRefundAmount, 2);
                    } else {
                        $vendorRefundAmount = 0;
                    }

                    if ($vendor->balance >= $vendorRefundAmount) {
                        $vendorInfo->total_revenue -= $vendorRefundAmount;
                        $vendorInfo->balance -= $vendorRefundAmount;

                        $data = [
                            'fee' => 0,
                            'currency' => get_application_currency()->title,
                            'current_balance' => $vendor->balance,
                            'customer_id' => $vendor->getKey(),
                            'order_id' => $order->getKey(),
                            'user_id' => Auth::id(),
                            'type' => RevenueTypeEnum::SUBTRACT_AMOUNT,
                            'description' => trans('plugins/marketplace::order.refund.description', [
                                'order' => $order->code,
                            ]),
                            'amount' => $vendorRefundAmount,
                            'sub_amount' => $refundAmount,
                        ];

                        try {
                            DB::beginTransaction();

                            Revenue::query()->create($data);

                            $vendorInfo->save();

                            DB::commit();
                        } catch (Throwable|Exception $th) {
                            DB::rollBack();

                            return $response
                                ->setError()
                                ->setMessage($th->getMessage());
                        }
                    } else {
                        $response
                            ->setError()
                            ->setMessage(
                                trans('plugins/marketplace::order.refund.insufficient_balance', [
                                    'balance' => format_price($vendorInfo->balance),
                                ])
                            );
                    }
                }
            }
        }

        return $response;
    }

    public function addThemeOptions(): void
    {
        theme_option()
            ->setSection([
                'title' => trans('plugins/marketplace::marketplace.theme_options.name'),
                'id' => 'opt-text-subsection-marketplace',
                'subsection' => true,
                'icon' => 'ti ti-shopping-bag',
                'fields' => [
                    [
                        'id' => 'logo_vendor_dashboard',
                        'type' => 'mediaImage',
                        'label' => trans('plugins/marketplace::marketplace.theme_options.logo_vendor_dashboard'),
                        'shared' => true,
                        'attributes' => [
                            'name' => 'logo_vendor_dashboard',
                            'value' => null,
                            'attributes' => [
                                'allow_thumb' => false,
                            ],
                        ],
                    ],
                    [
                        'id' => 'marketplace_stores_seo_title',
                        'type' => 'text',
                        'label' => trans('plugins/marketplace::marketplace.stores_seo_title'),
                        'attributes' => [
                            'name' => 'marketplace_stores_seo_title',
                            'value' => trans('plugins/marketplace::marketplace.stores'),
                            'options' => [
                                'class' => 'form-control',
                            ],
                        ],
                    ],
                    [
                        'id' => 'marketplace_stores_seo_description',
                        'type' => 'textarea',
                        'label' => trans('plugins/marketplace::marketplace.stores_seo_description'),
                        'attributes' => [
                            'name' => 'marketplace_stores_seo_description',
                            'value' => null,
                            'options' => [
                                'class' => 'form-control',
                                'rows' => 3,
                            ],
                        ],
                        'helper' => trans('plugins/marketplace::marketplace.leave_empty_for_default'),
                    ],
                ],
            ]);
    }

    public function registerAdditionalData(FormAbstract $form, array|Model|string|null $data): FormAbstract
    {
        if ($data instanceof Product && request()->segment(1) === BaseHelper::getAdminPrefix()) {
            $stores = cache()->remember(
                'marketplace_stores_for_filter',
                Carbon::now()->addMinutes(15),
                fn () => Store::query()->pluck('name', 'id')->all()
            );

            $form
                ->when($stores, function (FormAbstract $form) use ($stores): void {
                    $form
                        ->addAfter(
                            'status',
                            'store_id',
                            SelectField::class,
                            SelectFieldOption::make()
                                ->label(trans('plugins/marketplace::store.forms.store'))
                                ->choices($stores)
                                ->searchable()
                                ->emptyValue(trans('plugins/marketplace::store.forms.select_store'))
                                ->allowClear()
                        );
                });
        } elseif ($form instanceof CustomerForm) {
            if ($data->is_vendor && $form->has('status')) {
                $isActivated = $form->getModel()->status == CustomerStatusEnum::ACTIVATED();
                $blockReason = $form->getModel()->block_reason;

                $form
                    ->modify(
                        'status',
                        HtmlField::class,
                        HtmlFieldOption::make()
                            ->label(trans('plugins/marketplace::store.control.title'))
                            ->content(view('plugins/marketplace::customers.control', compact('isActivated', 'blockReason'))->render())
                    );
            }

            $form->addAfter(
                'email',
                'is_vendor',
                OnOffField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/marketplace::store.forms.is_vendor'))
                    ->defaultValue(false)
                    ->colspan(2)
            );
        }

        return $form;
    }

    public function saveAdditionalData(string $type, Request $request, Model|string|null $object): bool
    {
        if (! is_in_admin()) {
            return false;
        }

        if (in_array($type, [STORE_MODULE_SCREEN_NAME, (new Store())->getTable()])) {
            $customer = $object->customer;
            if ($customer && $customer->id) {
                if ($object->status->getValue() == BaseStatusEnum::PUBLISHED) {
                    $customer->status = CustomerStatusEnum::ACTIVATED;
                } else {
                    $customer->status = CustomerStatusEnum::LOCKED;
                }

                $customer->save();
            }
        } elseif (
            $type == PRODUCT_MODULE_SCREEN_NAME &&
            $request->has('store_id') &&
            request()->segment(1) !== config('plugins.marketplace.general.vendor_panel_dir', 'vendor')
        ) {
            $object->store_id = $request->input('store_id');
            $object->save();
        } elseif (in_array($type, [CUSTOMER_MODULE_SCREEN_NAME, (new Customer())->getTable()])
            && in_array(
                Route::currentRouteName(),
                ['customers.create', 'customers.store', 'customers.edit', 'customers.update']
            )
        ) {
            if ($request->has('is_vendor')) {
                $object->is_vendor = $request->input('is_vendor');
            }

            if ($object->is_vendor && ! $object->vendorInfo->id) {
                VendorInfo::query()->create(['customer_id' => $object->id]);
            }

            if ($object->is_vendor) {
                $store = $object->store;

                if (! $store->name) {
                    $store->name = $object->name;
                }

                if (! $store->phone) {
                    $store->phone = $object->phone;
                }

                if (! $store->logo) {
                    $store->logo = $object->avatar;
                }

                if ($object->status->getValue() == CustomerStatusEnum::ACTIVATED) {
                    $store->status = BaseStatusEnum::PUBLISHED;
                } else {
                    $store->status = BaseStatusEnum::DRAFT;
                }

                if (AdminHelper::isInAdmin() && auth()->check()) {
                    $object->vendor_verified_at = Carbon::now();
                }

                $store->save();

                if (! $store->slug) {
                    SlugHelper::createSlug($store);
                }
            }

            $object->save();
        }

        return true;
    }

    public function addColumnToEcommerceTable(EloquentDataTable|CollectionDataTable $data, Model|string|null $model, TableAbstract $table)
    {
        if (! $model || ! is_in_admin(true)) {
            return $data;
        }

        if ($model::class === Customer::class && Route::is('marketplace.vendors.index')) {
            return $data->addColumn('store_name', function ($item) {
                if (! $item->store->name) {
                    return '&mdash;';
                }

                return Html::link(route('marketplace.store.edit', $item->store?->id), $item->store->name);
            });
        }

        $data = match ($model::class) {
            Customer::class => $data->addColumn('is_vendor', function ($item) {
                if (! $item->is_vendor) {
                    return trans('core/base::base.no');
                }

                return Html::tag('span', trans('core/base::base.yes'), ['class' => 'text-success']);
            }),
            Discount::class => $data
                ->addColumn('store_id', function ($item) {
                    $store = $item->original_product && $item->original_product->store->name ? $item->original_product->store : $item->store;

                    if (! $store->name) {
                        return '&mdash;';
                    }

                    return Html::link($store->url, $store->name, ['target' => '_blank']);
                })
                ->filter(function ($query) use ($table, $model) {
                    if ($keyword = request()->input('search.value')) {
                        $keyword = '%' . $keyword . '%';

                        return $query
                            ->whereHas('store', function ($subQuery) use ($keyword) {
                                return $subQuery->where('name', 'LIKE', $keyword);
                            })
                            ->orWhere('code', 'LIKE', $keyword);
                    }

                    return $query;
                }),
            Order::class => $data
                ->addColumn('store_id', function ($item) {
                    $store = $item->original_product && $item->original_product->store->name ? $item->original_product->store : $item->store;

                    if (! $store->name) {
                        return '&mdash;';
                    }

                    return Html::link($store->url, $store->name, ['target' => '_blank']);
                })
                ->filter(function ($query) use ($table, $model) {
                    if ($keyword = request()->input('search.value')) {
                        $keyword = '%' . $keyword . '%';

                        return $query
                            ->where(function ($query) use ($keyword): void {
                                $query
                                    ->whereHas('store', function ($subQuery) use ($keyword) {
                                        return $subQuery->where('name', 'LIKE', $keyword);
                                    })
                                    ->orWhere(function ($subQuery) use ($keyword): void {
                                        $subQuery->searchByKeyword(trim($keyword, '%'));
                                    });
                            })
                            ->where('is_finished', ! $table instanceof OrderIncompleteTable);
                    }

                    return $query;
                }),
            default => $data,
        };

        if ($table instanceof ProductTable) {
            $data
                ->addColumn('store_id', function ($item) {
                    $store = $item->original_product && $item->original_product->store->name ? $item->original_product->store : $item->store;

                    if (! $store->name) {
                        return '&mdash;';
                    }

                    return Html::link($store->url, $store->name, ['target' => '_blank']);
                })
                ->filter(function ($query) {
                    $keyword = request()->input('search.value');
                    if ($keyword) {
                        $keyword = '%' . $keyword . '%';

                        return $query->where(function ($query) use ($keyword): void {
                            $query
                                ->searchByKeyword(trim($keyword, '%'))
                                ->orWhere(function ($subQuery) use ($keyword): void {
                                    $subQuery
                                        ->where('is_variation', 0)
                                        ->whereHas('store', function ($storeQuery) use ($keyword) {
                                            return $storeQuery->where('name', 'LIKE', $keyword);
                                        });
                                });
                        });
                    }

                    return $query;
                });
        }

        return $data;
    }

    public function addHeadingToEcommerceTable(array $headings, Model|string|null $model, TableAbstract $table): array
    {
        if (! $model || ! is_in_admin(true) || Route::is('marketplace.vendors.index')) {
            return $headings;
        }

        return match (true) {
            $model::class === Customer::class
                => array_merge($headings, [
                    Column::make('is_vendor')
                        ->title(trans('plugins/marketplace::store.forms.is_vendor'))
                        ->alignCenter()
                        ->width(100),
                ]),
            in_array($model::class, [Order::class, Discount::class])
            || ($model::class === Product::class && $table instanceof ProductTable)
                => array_merge($headings, [
                    Column::make('store_id')
                        ->title(trans('plugins/marketplace::store.forms.store'))
                        ->alignLeft()
                        ->orderable(false)
                        ->searchable(false),
                ]),
            default => $headings,
        };
    }

    public function modifyQueryInCustomerTable(Builder|EloquentBuilder|null $query, TableAbstract $table): Builder|EloquentBuilder|null
    {
        $model = null;

        if ($query instanceof Builder || $query instanceof EloquentBuilder) {
            $model = $query->getModel();
        }

        return match (true) {
            $model::class === Customer::class
                => $query->addSelect('is_vendor'),
            in_array($model::class, [Order::class, Discount::class])
            || ($model::class === Product::class && $table instanceof ProductTable)
                => $query->addSelect($model->getTable() . '.store_id')->with(
                    ['store']
                ),
            default => $query,
        };
    }

    public function getUnverifiedVendors(string|int|null $number, string $menuId): int|string|null
    {
        switch ($menuId) {
            case 'cms-plugins-marketplace-unverified-vendor':
                if (! Auth::user()->hasPermission('marketplace.unverified-vendor.index')) {
                    return $number;
                }

                if (! MarketplaceHelper::getSetting('verify_vendor', 1)) {
                    return $number;
                }

                return view('core/base::partials.navbar.badge-count', ['class' => 'unverified-vendors'])->render();

            case 'cms-plugins-withdrawal':
                if (! Auth::user()->hasPermission('marketplace.withdrawal.index')) {
                    return $number;
                }

                return view('core/base::partials.navbar.badge-count', ['class' => 'pending-withdrawals'])->render();

            case 'cms-plugins-marketplace':
                if (
                    ! Auth::user()->hasAnyPermission([
                        'marketplace.withdrawal.index',
                        'marketplace.unverified-vendor.index',
                    ])
                ) {
                    return $number;
                }

                return view('core/base::partials.navbar.badge-count', ['class' => 'marketplace-notifications-count'])->render();

            case 'cms-plugins-ecommerce-product':
                if (! Auth::user()->hasPermission('products.index')) {
                    return $number;
                }

                return view('core/base::partials.navbar.badge-count', ['class' => 'pending-products'])->render();
        }

        return $number;
    }

    public function getMenuItemCount(array $data = []): array
    {
        if (! Auth::check()) {
            return $data;
        }

        /**
         * @var User $user
         */
        $user = Auth::user();

        $cacheKey = 'marketplace_menu_counts_' . Auth::id();

        $counts = cache()->remember($cacheKey, Carbon::now()->addMinutes(5), function () use ($user): array {
            $counts = [];

            if ($user->hasPermission('marketplace.unverified-vendor.index') &&
                MarketplaceHelper::getSetting('verify_vendor', 1)
            ) {
                $counts['unverified_vendors'] = Customer::query()
                    ->where('is_vendor', true)
                    ->whereNull('vendor_verified_at')
                    ->count();
            }

            if ($user->hasPermission('marketplace.withdrawal.index')) {
                $counts['pending_withdrawals'] = Withdrawal::query()
                    ->where('status', 'IN', [WithdrawalStatusEnum::PENDING, WithdrawalStatusEnum::PROCESSING])
                    ->count();
            }

            if ($user->hasPermission('products.index')) {
                $counts['pending_products'] = Product::query()
                    ->where('status', BaseStatusEnum::PENDING)
                    ->where('created_by_type', Customer::class)
                    ->where('created_by_id', '!=', 0)
                    ->where('is_variation', 0)
                    ->count();

                $counts['pending_orders'] = Order::query()
                    ->wherePublished()
                    ->where('is_finished', 1)
                    ->count();
            }

            return $counts;
        });

        $countUnverifiedVendors = $counts['unverified_vendors'] ?? 0;
        $countPendingWithdrawals = $counts['pending_withdrawals'] ?? 0;
        $countPendingProducts = $counts['pending_products'] ?? 0;
        $pendingOrders = $counts['pending_orders'] ?? 0;

        if ($user->hasPermission('marketplace.unverified-vendor.index') &&
            MarketplaceHelper::getSetting('verify_vendor', 1)
        ) {
            $data[] = [
                'key' => 'unverified-vendors',
                'value' => $countUnverifiedVendors,
            ];
        }

        if ($user->hasPermission('marketplace.withdrawal.index')) {
            $data[] = [
                'key' => 'pending-withdrawals',
                'value' => $countPendingWithdrawals,
            ];
        }

        if ($user->hasAnyPermission(['marketplace.withdrawal.index', 'marketplace.unverified-vendor.index'])) {
            $data[] = [
                'key' => 'marketplace-notifications-count',
                'value' => $countUnverifiedVendors + $countPendingWithdrawals,
            ];
        }

        if ($user->hasPermission('products.index')) {
            $data[] = [
                'key' => 'pending-products',
                'value' => $countPendingProducts,
            ];

            $data[] = [
                'key' => 'ecommerce-count',
                'value' => $pendingOrders + $countPendingProducts,
            ];
        }

        return $data;
    }

    public function createdByVendorNotification(Request $request, Model|string|null $data = null): bool
    {
        if (! MarketplaceHelper::getSetting('enable_product_approval', 1)) {
            return false;
        }

        if (! $data instanceof Product || ! in_array(Route::currentRouteName(), ['products.create', 'products.edit'])) {
            return false;
        }

        if ($data->created_by_id &&
            $data->created_by_type == Customer::class &&
            Auth::user()->hasPermission('products.edit')
        ) {
            $isApproved = $data->status == BaseStatusEnum::PUBLISHED;
            if (! $isApproved) {
                Assets::addScriptsDirectly(['vendor/core/plugins/marketplace/js/marketplace-product.js']);
            }

            echo view('plugins/marketplace::partials.notification', ['product' => $data, 'isApproved' => $isApproved])
                ->render();

            return true;
        }

        return false;
    }

    public function withdrawalVendorNotification(Request $request, Model|string|null $data = null): bool
    {
        if (! $data instanceof Withdrawal || Route::currentRouteName() != 'marketplace.withdrawal.edit') {
            return false;
        }

        if (! $data->customer->store || ! $data->customer->store?->id) {
            return false;
        }

        echo view('plugins/marketplace::withdrawals.store-info', ['store' => $data->customer->store])->render();

        return true;
    }

    public function setStoreToRow(array $row): array
    {
        $row['store_id'] = 0;

        if (! empty($row['vendor'])) {
            $row['vendor'] = trim($row['vendor']);

            if (is_numeric($row['vendor'])) {
                $store = Store::query()->find($row['vendor']);
            } else {
                $store = Store::query()->where('name', $row['vendor'])->first();
            }

            $row['store_id'] = $store ? $store->id : 0;
        }

        return $row;
    }

    public function addStoreRelations(array $with): array
    {
        return array_merge($with, ['store', 'variationInfo.configurableProduct.store']);
    }

    public function appendStoreToProductName(string $productName, $product, $stores)
    {
        $store = $product->original_product->store;
        if ($store && $store->id) {
            $productName .= ' (' . $store->name . ')';
            $stores->push($store);
        }

        return $productName;
    }

    public function validateDifferentStores(array $result, $stores): array
    {
        if ($stores->count() && count(array_unique(array_filter($stores->pluck('id')->all()))) > 1) {
            return [
                'isError' => true,
                'message' => trans('plugins/marketplace::order.products_are_from_different_vendors'),
            ];
        }

        return $result;
    }

    public function setStoreAsOriginAddress(array $origin, $stores, array $addressKeys): array
    {
        if ($stores->count() && ($store = $stores->first()) && $store->id) {
            $origin = Arr::only($store->toArray(), $addressKeys);
            if (! EcommerceHelper::isUsingInMultipleCountries()) {
                $origin['country'] = EcommerceHelper::getFirstCountryId();
            }
        }

        return $origin;
    }

    public function addStoreInfoToOrderDetail(string $html, Order $order): string
    {
        return $html . view('plugins/marketplace::orders.store-info', compact('order'))->render();
    }

    public function addStoreInfoToOrderProductItem(string $html, OrderProduct $orderProduct, Order $order): string
    {
        return $html . view('plugins/marketplace::orders.product-item-store-info', compact('order'))->render();
    }

    public function addStoreFilterToOrderTable(array $filters, $table): array
    {
        $filters['store_id'] = [
            'title' => trans('plugins/marketplace::store.forms.store'),
            'type' => 'select-search',
            'choices' => [-1 => Theme::getSiteTitle()] + DB::table('mp_stores')->pluck('name', 'id')->all(),
        ];

        return $filters;
    }
}
