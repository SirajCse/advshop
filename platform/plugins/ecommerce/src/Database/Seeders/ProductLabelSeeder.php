<?php

namespace Botble\Ecommerce\Database\Seeders;

use Botble\Ecommerce\Models\ProductLabel;
use Illuminate\Database\Seeder;

class ProductLabelSeeder extends Seeder
{
    public function run(): void
    {
        ProductLabel::query()->truncate();

        $productCollections = [
            [
                'name' => 'Hot',
                'color' => '#d71e2d',
                'text_color' => '#ffffff',
            ],
            [
                'name' => 'New',
                'color' => '#02856e',
                'text_color' => '#ffffff',
            ],
            [
                'name' => 'Sale',
                'color' => '#fe9931',
                'text_color' => '#ffffff',
            ],
        ];

        foreach ($productCollections as $item) {
            ProductLabel::query()->create($item);
        }
    }
}
