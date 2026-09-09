<?php

namespace Botble\Ecommerce\Commands;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Ecommerce\Models\Tax;
use Botble\Ecommerce\Models\TaxRule;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Seeds EU VAT rates for B2C compliance.
 *
 * Creates a "EU VAT" tax with rules for all EU member states at their standard rates.
 * After running, assign this tax to products to enable destination-based EU VAT.
 */
#[AsCommand('cms:ecommerce:seed-eu-vat', 'Seed EU VAT rates for all member states')]
class SeedEuVatRatesCommand extends Command
{
    /**
     * EU VAT standard rates as of 2024.
     *
     * @see https://europa.eu/youreurope/business/taxation/vat/vat-rates/index_en.htm
     */
    protected array $euVatRates = [
        'AT' => 20,   // Austria
        'BE' => 21,   // Belgium
        'BG' => 20,   // Bulgaria
        'HR' => 25,   // Croatia
        'CY' => 19,   // Cyprus
        'CZ' => 21,   // Czech Republic
        'DK' => 25,   // Denmark
        'EE' => 22,   // Estonia
        'FI' => 25.5, // Finland (increased from 24% in 2024)
        'FR' => 20,   // France
        'DE' => 19,   // Germany
        'GR' => 24,   // Greece
        'HU' => 27,   // Hungary (highest in EU)
        'IE' => 23,   // Ireland
        'IT' => 22,   // Italy
        'LV' => 21,   // Latvia
        'LT' => 21,   // Lithuania
        'LU' => 17,   // Luxembourg (lowest in EU)
        'MT' => 18,   // Malta
        'NL' => 21,   // Netherlands
        'PL' => 23,   // Poland
        'PT' => 23,   // Portugal
        'RO' => 19,   // Romania
        'SK' => 23,   // Slovakia
        'SI' => 22,   // Slovenia
        'ES' => 21,   // Spain
        'SE' => 25,   // Sweden
    ];

    public function handle(): int
    {
        $this->components->info('Seeding EU VAT rates...');
        $this->newLine();

        // Create or find the EU VAT tax
        $tax = Tax::query()->firstOrCreate(
            ['title' => 'EU VAT'],
            [
                'percentage' => 0, // Base rate is 0, rules define per-country rates
                'priority' => 1,
                'status' => BaseStatusEnum::PUBLISHED,
            ]
        );

        $this->components->info("Tax 'EU VAT' created/found with ID: {$tax->id}");

        // Create tax rules for each EU country
        $created = 0;
        $updated = 0;

        foreach ($this->euVatRates as $countryCode => $rate) {
            $rule = TaxRule::query()->updateOrCreate(
                [
                    'tax_id' => $tax->id,
                    'country' => $countryCode,
                    'state' => null,
                    'city' => null,
                ],
                [
                    'percentage' => $rate,
                    'priority' => 1,
                    'is_enabled' => true,
                ]
            );

            if ($rule->wasRecentlyCreated) {
                $created++;
            } else {
                $updated++;
            }
        }

        $this->newLine();
        $this->components->info("EU VAT rules: {$created} created, {$updated} updated");

        // Display the rates table
        $this->newLine();
        $this->table(
            ['Country', 'Code', 'VAT Rate'],
            collect($this->euVatRates)->map(fn ($rate, $code) => [
                $this->getCountryName($code),
                $code,
                $rate . '%',
            ])->values()->all()
        );

        $this->newLine();
        $this->components->info('Next steps:');
        $this->components->line('  1. Go to Admin → Ecommerce → Taxes');
        $this->components->line('  2. Edit "EU VAT" to verify rates');
        $this->components->line('  3. Assign "EU VAT" tax to your products');
        $this->components->line('  4. Customers will be charged VAT based on their shipping country');

        return self::SUCCESS;
    }

    protected function getCountryName(string $code): string
    {
        $names = [
            'AT' => 'Austria',
            'BE' => 'Belgium',
            'BG' => 'Bulgaria',
            'HR' => 'Croatia',
            'CY' => 'Cyprus',
            'CZ' => 'Czech Republic',
            'DK' => 'Denmark',
            'EE' => 'Estonia',
            'FI' => 'Finland',
            'FR' => 'France',
            'DE' => 'Germany',
            'GR' => 'Greece',
            'HU' => 'Hungary',
            'IE' => 'Ireland',
            'IT' => 'Italy',
            'LV' => 'Latvia',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'MT' => 'Malta',
            'NL' => 'Netherlands',
            'PL' => 'Poland',
            'PT' => 'Portugal',
            'RO' => 'Romania',
            'SK' => 'Slovakia',
            'SI' => 'Slovenia',
            'ES' => 'Spain',
            'SE' => 'Sweden',
        ];

        return $names[$code] ?? $code;
    }
}
