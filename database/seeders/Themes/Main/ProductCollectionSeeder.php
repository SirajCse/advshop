<?php

namespace Database\Seeders\Themes\Main;

use Botble\Ecommerce\Models\ProductCollection;
use Botble\Slug\Facades\SlugHelper;
use Botble\Slug\Models\Slug;
use Botble\Theme\Database\Seeders\ThemeSeeder;
use Illuminate\Support\Str;

class ProductCollectionSeeder extends ThemeSeeder
{
    public function run(): void
    {
        ProductCollection::query()->truncate();

        // Also clean up any existing slugs for product collections
        Slug::query()
            ->where('reference_type', ProductCollection::class)
            ->delete();

        foreach ($this->getData() as $item) {
            $collection = ProductCollection::query()->create([
                'name' => $item,
                'slug' => Str::slug($item),
            ]);

            // Create slug entry using SlugHelper
            SlugHelper::createSlug($collection, $collection->slug);
        }
    }

    protected function getData(): array
    {
        return [
            'Weekly Gadget Spotlight',
            'Electronics Trendsetters',
            'Digital Workspace Gear',
            'Cutting-Edge Tech Showcase',
        ];
    }
}
