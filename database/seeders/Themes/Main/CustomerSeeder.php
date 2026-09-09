<?php

namespace Database\Seeders\Themes\Main;

use Botble\Ecommerce\Models\Address;
use Botble\Ecommerce\Models\Customer;
use Botble\Theme\Database\Seeders\ThemeSeeder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends ThemeSeeder
{
    public function run(): void
    {
        $this->uploadFiles('customers');

        Customer::query()->truncate();
        Address::query()->truncate();

        $names = $this->getNames();
        $phones = $this->getPhones();
        $countries = $this->getCountries();
        $states = $this->getStates();
        $cities = $this->getCities();
        $addresses = $this->getAddresses();
        $zipCodes = $this->getZipCodes();

        $customers = [
            'customer@botble.com',
            'vendor@botble.com',
        ];

        $now = $this->now();

        foreach ($customers as $index => $item) {
            $customer = Customer::query()->forceCreate([
                'name' => $names[$index] ?? Arr::random($names),
                'email' => $item,
                'password' => Hash::make('12345678'),
                'phone' => $phones[$index] ?? Arr::random($phones),
                'avatar' => $this->filePath(sprintf('customers/%s.jpg', rand(1, 10))),
                'dob' => $this->now()->subYears(rand(20, 50))->subDays(rand(1, 30)),
                'confirmed_at' => $now,
            ]);

            Address::query()->create([
                'name' => $customer->name,
                'phone' => Arr::random($phones),
                'email' => $customer->email,
                'country' => Arr::random($countries),
                'state' => Arr::random($states),
                'city' => Arr::random($cities),
                'address' => Arr::random($addresses),
                'zip_code' => Arr::random($zipCodes),
                'customer_id' => $customer->getKey(),
                'is_default' => true,
            ]);

            Address::query()->create([
                'name' => $customer->name,
                'phone' => Arr::random($phones),
                'email' => $customer->email,
                'country' => Arr::random($countries),
                'state' => Arr::random($states),
                'city' => Arr::random($cities),
                'address' => Arr::random($addresses),
                'zip_code' => Arr::random($zipCodes),
                'customer_id' => $customer->getKey(),
                'is_default' => false,
            ]);
        }

        for ($i = 0; $i < 8; $i++) {
            $customer = Customer::query()->forceCreate([
                'name' => Arr::random($names),
                'email' => sprintf('customer%d@example.com', $i + 1),
                'password' => Hash::make('12345678'),
                'phone' => Arr::random($phones),
                'avatar' => $this->filePath(sprintf('customers/%d.jpg', $i + 1)),
                'dob' => $this->now()->subYears(rand(20, 50))->subDays(rand(1, 30)),
                'confirmed_at' => $now,
            ]);

            Address::query()->create([
                'name' => $customer->name,
                'phone' => Arr::random($phones),
                'email' => $customer->email,
                'country' => Arr::random($countries),
                'state' => Arr::random($states),
                'city' => Arr::random($cities),
                'address' => Arr::random($addresses),
                'zip_code' => Arr::random($zipCodes),
                'customer_id' => $customer->getKey(),
                'is_default' => true,
            ]);
        }
    }

    protected function getNames(): array
    {
        return [
            'John Smith',
            'Emma Wilson',
            'Michael Brown',
            'Sarah Davis',
            'James Johnson',
            'Emily Taylor',
            'Robert Anderson',
            'Jennifer Martinez',
            'David Thompson',
            'Lisa Garcia',
        ];
    }

    protected function getPhones(): array
    {
        return [
            '+1-555-0101',
            '+1-555-0102',
            '+1-555-0103',
            '+1-555-0104',
            '+1-555-0105',
            '+1-555-0106',
            '+1-555-0107',
            '+1-555-0108',
            '+1-555-0109',
            '+1-555-0110',
        ];
    }

    protected function getCountries(): array
    {
        return ['US', 'GB', 'CA', 'AU', 'DE'];
    }

    protected function getStates(): array
    {
        return [
            'California',
            'New York',
            'Texas',
            'Florida',
            'Illinois',
            'Pennsylvania',
            'Ohio',
            'Georgia',
            'Michigan',
            'Arizona',
        ];
    }

    protected function getCities(): array
    {
        return [
            'Los Angeles',
            'New York',
            'Houston',
            'Miami',
            'Chicago',
            'Phoenix',
            'San Diego',
            'Dallas',
            'Austin',
            'Denver',
        ];
    }

    protected function getAddresses(): array
    {
        return [
            '123 Main Street',
            '456 Oak Avenue',
            '789 Pine Road',
            '321 Maple Drive',
            '654 Cedar Lane',
            '987 Birch Boulevard',
            '147 Elm Court',
            '258 Walnut Way',
            '369 Cherry Circle',
            '741 Spruce Street',
        ];
    }

    protected function getZipCodes(): array
    {
        return [
            '10001',
            '90210',
            '60601',
            '33101',
            '77001',
            '85001',
            '48201',
            '30301',
            '19101',
            '80201',
        ];
    }
}
