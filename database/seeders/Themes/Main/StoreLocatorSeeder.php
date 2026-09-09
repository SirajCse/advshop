<?php

namespace Database\Seeders\Themes\Main;

use Botble\Ecommerce\Models\StoreLocator;
use Botble\Setting\Facades\Setting;
use Illuminate\Database\Seeder;

class StoreLocatorSeeder extends Seeder
{
    public function run(): void
    {
        StoreLocator::query()->truncate();

        $storeLocators = [
            [
                'name' => 'Main Store',
                'email' => 'sales@botble.com',
                'phone' => '1800979769',
                'address' => '502 New Street',
                'state' => 'Victoria',
                'city' => 'Brighton',
                'country' => 'AU',
                'zip_code' => '3186',
                'is_primary' => true,
                'is_shipping_location' => true,
            ],
            [
                'name' => 'Sydney Warehouse',
                'email' => 'sydney@botble.com',
                'phone' => '1800979770',
                'address' => '123 George Street',
                'state' => 'New South Wales',
                'city' => 'Sydney',
                'country' => 'AU',
                'zip_code' => '2000',
                'is_primary' => false,
                'is_shipping_location' => true,
            ],
            [
                'name' => 'Brisbane Warehouse',
                'email' => 'brisbane@botble.com',
                'phone' => '1800979771',
                'address' => '456 Queen Street',
                'state' => 'Queensland',
                'city' => 'Brisbane',
                'country' => 'AU',
                'zip_code' => '4000',
                'is_primary' => false,
                'is_shipping_location' => true,
            ],
        ];

        $primaryStore = null;

        foreach ($storeLocators as $data) {
            $store = StoreLocator::query()->create($data);

            if ($data['is_primary']) {
                $primaryStore = $store;
            }
        }

        if ($primaryStore) {
            Setting::delete([
                'ecommerce_store_name',
                'ecommerce_store_phone',
                'ecommerce_store_email',
                'ecommerce_store_address',
                'ecommerce_store_state',
                'ecommerce_store_city',
                'ecommerce_store_country',
                'ecommerce_store_zip_code',
            ]);

            Setting::set([
                'ecommerce_store_name' => $primaryStore->name,
                'ecommerce_store_phone' => $primaryStore->phone,
                'ecommerce_store_email' => $primaryStore->email,
                'ecommerce_store_address' => $primaryStore->address,
                'ecommerce_store_state' => $primaryStore->state,
                'ecommerce_store_city' => $primaryStore->city,
                'ecommerce_store_country' => $primaryStore->country,
                'ecommerce_store_zip_code' => $primaryStore->zip_code,
            ])->save();
        }
    }
}
