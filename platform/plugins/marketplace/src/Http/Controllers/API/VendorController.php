<?php

namespace Botble\Marketplace\Http\Controllers\API;

use Botble\Api\Http\Controllers\BaseApiController;
use Botble\Ecommerce\Models\Customer;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Http\Requests\API\BecomeVendorRequest;
use Botble\Marketplace\Http\Requests\API\RegisterVendorRequest;
use Botble\Marketplace\Models\Store;
use Botble\Slug\Facades\SlugHelper;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class VendorController extends BaseApiController
{
    /**
     * Register new customer as vendor
     *
     * @group Marketplace Vendors
     * @bodyParam first_name string Customer first name. Example: John
     * @bodyParam last_name string Customer last name. Example: Doe
     * @bodyParam name string Customer full name (alternative to first_name/last_name). Example: John Doe
     * @bodyParam email string required Customer email. Example: john@example.com
     * @bodyParam password string required Password (min 6). Example: secret123
     * @bodyParam password_confirmation string required Password confirmation. Example: secret123
     * @bodyParam phone string Customer phone. Example: +1234567890
     * @bodyParam shop_name string required Store name. Example: John's Shop
     * @bodyParam shop_phone string required Store phone. Example: +1234567890
     * @bodyParam shop_url string required Store URL slug. Example: johns-shop
     * @unauthenticated
     */
    public function register(RegisterVendorRequest $request): JsonResponse
    {
        abort_unless(MarketplaceHelper::isVendorRegistrationEnabled(), 404);

        $existing = SlugHelper::getSlug(
            $request->input('shop_url'),
            SlugHelper::getPrefix(Store::class)
        );

        if ($existing) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/marketplace::store.forms.shop_url_existing'))
                ->toApiResponse();
        }

        $request->merge(['password' => Hash::make($request->input('password'))]);

        if (! $request->has('name') || ! $request->input('name')) {
            $request->merge(['name' => trim($request->input('first_name') . ' ' . $request->input('last_name'))]);
        }

        $customer = Customer::query()->create($request->only([
            'first_name',
            'last_name',
            'name',
            'email',
            'phone',
            'password',
        ]));

        $request->merge(['is_vendor' => true]);

        event(new Registered($customer));

        $token = $customer->createToken('api-token')->plainTextToken;

        $customer->refresh();

        return $this
            ->httpResponse()
            ->setData([
                'token' => $token,
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'is_vendor' => $customer->is_vendor,
                ],
            ])
            ->setMessage(trans('plugins/marketplace::marketplace.registered_successfully'))
            ->toApiResponse();
    }

    /**
     * Upgrade existing customer to vendor
     *
     * @group Marketplace Vendors
     * @bodyParam shop_name string required Store name. Example: John's Shop
     * @bodyParam shop_phone string required Store phone. Example: +1234567890
     * @bodyParam shop_url string required Store URL slug. Example: johns-shop
     * @authenticated
     */
    public function becomeVendor(BecomeVendorRequest $request): JsonResponse
    {
        abort_unless(MarketplaceHelper::isVendorRegistrationEnabled(), 404);

        $customer = auth('sanctum')->user();

        if (! $customer instanceof Customer) {
            return $this
                ->httpResponse()
                ->setError()
                ->setCode(401)
                ->setMessage(trans('plugins/ecommerce::api.unauthorized'))
                ->toApiResponse();
        }

        if ($customer->is_vendor) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/marketplace::marketplace.already_vendor'))
                ->toApiResponse();
        }

        $existing = SlugHelper::getSlug(
            $request->input('shop_url'),
            SlugHelper::getPrefix(Store::class)
        );

        if ($existing) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/marketplace::store.forms.shop_url_existing'))
                ->toApiResponse();
        }

        $request->merge(['is_vendor' => true]);

        event(new Registered($customer));

        $customer->refresh();

        return $this
            ->httpResponse()
            ->setData([
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'is_vendor' => $customer->is_vendor,
                    'vendor_verified_at' => $customer->vendor_verified_at,
                ],
                'store' => $customer->store ? [
                    'id' => $customer->store->id,
                    'name' => $customer->store->name,
                    'slug' => $customer->store->slugable?->key,
                ] : null,
            ])
            ->setMessage(trans('plugins/marketplace::marketplace.registered_successfully'))
            ->toApiResponse();
    }
}
