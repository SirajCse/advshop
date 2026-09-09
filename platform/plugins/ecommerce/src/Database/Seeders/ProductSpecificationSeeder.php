<?php

namespace Botble\Ecommerce\Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Ecommerce\Enums\SpecificationAttributeFieldType;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\SpecificationAttribute;
use Botble\Ecommerce\Models\SpecificationGroup;
use Botble\Ecommerce\Models\SpecificationTable;
use Botble\Language\Models\Language;
use Botble\Setting\Facades\Setting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductSpecificationSeeder extends BaseSeeder
{
    public function run(): void
    {
        SpecificationTable::query()->truncate();
        SpecificationAttribute::query()->truncate();
        SpecificationGroup::query()->truncate();
        DB::table('ec_specification_table_group')->truncate();
        DB::table('ec_specification_attributes_translations')->truncate();
        DB::table('ec_specification_groups_translations')->truncate();
        DB::table('ec_specification_tables_translations')->truncate();
        DB::table('ec_product_specification_attribute')->truncate();

        if (Schema::hasTable('ec_product_specification_attribute_translations')) {
            DB::table('ec_product_specification_attribute_translations')->truncate();
        }

        Setting::set('ecommerce_enable_product_specification', true)->save();

        $data = [
            'Dimensions' => [
                ['name' => 'Height', 'type' => SpecificationAttributeFieldType::TEXT],
                ['name' => 'Width', 'type' => SpecificationAttributeFieldType::TEXT],
                ['name' => 'Weight', 'type' => SpecificationAttributeFieldType::TEXT],
            ],
            'Performance' => [
                ['name' => 'Power', 'type' => SpecificationAttributeFieldType::TEXT],
                ['name' => 'Speed', 'type' => SpecificationAttributeFieldType::TEXT],
            ],
            'Battery' => [
                ['name' => 'Battery Life', 'type' => SpecificationAttributeFieldType::TEXT],
            ],
            'Display' => [
                ['name' => 'Screen Size', 'type' => SpecificationAttributeFieldType::TEXT],
                [
                    'name' => 'Resolution',
                    'type' => SpecificationAttributeFieldType::SELECT,
                    'options' => [
                        ['id' => 'res_1080p', 'value' => '1920x1080'],
                        ['id' => 'res_1440p', 'value' => '2560x1440'],
                        ['id' => 'res_4k', 'value' => '3840x2160'],
                    ],
                ],
                [
                    'name' => 'Panel Type',
                    'type' => SpecificationAttributeFieldType::SELECT,
                    'options' => [
                        ['id' => 'panel_ips', 'value' => 'IPS'],
                        ['id' => 'panel_va', 'value' => 'VA'],
                        ['id' => 'panel_tn', 'value' => 'TN'],
                        ['id' => 'panel_oled', 'value' => 'OLED'],
                    ],
                ],
                [
                    'name' => 'HDR Support',
                    'type' => SpecificationAttributeFieldType::RADIO,
                    'options' => [
                        ['id' => 'hdr_10', 'value' => 'HDR10'],
                        ['id' => 'hdr_10plus', 'value' => 'HDR10+'],
                        ['id' => 'hdr_dolby', 'value' => 'Dolby Vision'],
                        ['id' => 'hdr_none', 'value' => 'None'],
                    ],
                ],
            ],
        ];

        foreach ($data as $group => $attributes) {
            $group = SpecificationGroup::query()->create([
                'name' => $group,
            ]);

            foreach ($attributes as $attribute) {
                $group->specificationAttributes()->create($attribute);
            }
        }

        $specificationTable = SpecificationTable::query()
            ->create(['name' => 'General Specification']);

        $specificationTable
            ->groups()
            ->attach(SpecificationGroup::query()->whereIn('name', ['Dimensions', 'Performance'])->pluck('id'));

        $specificationTable = SpecificationTable::query()
            ->create(['name' => 'Technical Specification']);

        $specificationTable
            ->groups()
            ->attach(SpecificationGroup::query()->whereIn('name', ['Battery', 'Display'])->pluck('id'));

        $tables = SpecificationTable::query()->with('groups.specificationAttributes')->get();
        $products = Product::query()->where('is_variation', false)->get();

        $products->each(function (Model|Product $product) use ($tables): void {
            $table = $tables->random();

            $product->update([
                'specification_table_id' => $table->id,
            ]);

            $table->groups->each(function ($group) use ($product): void {
                $group->specificationAttributes->each(function ($attribute) use ($product): void {
                    $value = $this->generateAttributeValue($attribute);

                    $product->specificationAttributes()->attach($attribute->id, [
                        'value' => $value,
                        'hidden' => false,
                        'order' => 0,
                    ]);
                });
            });
        });

        $this->seedTranslations();
        $this->seedProductValueTranslations();
    }

    protected function generateAttributeValue(SpecificationAttribute $attribute)
    {
        return match ($attribute->type->getValue()) {
            SpecificationAttributeFieldType::TEXT => $this->fake()->randomFloat(2, 1, 100) . ' cm',
            SpecificationAttributeFieldType::SELECT, SpecificationAttributeFieldType::RADIO => $this->fake()->randomElement(
                array_column($attribute->options, 'id')
            ),
            SpecificationAttributeFieldType::CHECKBOX => $this->fake()->boolean(),
            default => null,
        };
    }

    protected function seedTranslations(): void
    {
        $locales = $this->getSupportedLocales();

        if (empty($locales)) {
            return;
        }

        $groupTranslations = [
            'vi' => ['Dimensions' => 'Kích thước', 'Performance' => 'Hiệu suất', 'Battery' => 'Pin', 'Display' => 'Màn hình'],
            'ar' => ['Dimensions' => 'الأبعاد', 'Performance' => 'الأداء', 'Battery' => 'البطارية', 'Display' => 'الشاشة'],
        ];

        foreach ($locales as $locale) {
            $groups = SpecificationGroup::query()->withoutGlobalScopes()->get();

            foreach ($groups as $group) {
                $translatedName = $groupTranslations[$locale][$group->name] ?? $group->name;

                DB::table('ec_specification_groups_translations')->updateOrInsert(
                    ['lang_code' => $locale, 'ec_specification_groups_id' => $group->id],
                    ['name' => $translatedName]
                );
            }
        }

        $attrTranslations = [
            'vi' => [
                'Height' => 'Chiều cao', 'Width' => 'Chiều rộng', 'Weight' => 'Cân nặng',
                'Power' => 'Công suất', 'Speed' => 'Tốc độ', 'Battery Life' => 'Thời lượng pin',
                'Screen Size' => 'Kích thước màn hình', 'Resolution' => 'Độ phân giải',
                'Panel Type' => 'Loại tấm nền', 'HDR Support' => 'Hỗ trợ HDR',
            ],
            'ar' => [
                'Height' => 'الارتفاع', 'Width' => 'العرض', 'Weight' => 'الوزن',
                'Power' => 'الطاقة', 'Speed' => 'السرعة', 'Battery Life' => 'عمر البطارية',
                'Screen Size' => 'حجم الشاشة', 'Resolution' => 'الدقة',
                'Panel Type' => 'نوع اللوحة', 'HDR Support' => 'دعم HDR',
            ],
        ];

        $optionTranslations = [
            'vi' => [
                'Panel Type' => [
                    ['id' => 'panel_ips', 'value' => 'IPS'],
                    ['id' => 'panel_va', 'value' => 'VA'],
                    ['id' => 'panel_tn', 'value' => 'TN'],
                    ['id' => 'panel_oled', 'value' => 'OLED'],
                ],
                'HDR Support' => [
                    ['id' => 'hdr_10', 'value' => 'HDR10'],
                    ['id' => 'hdr_10plus', 'value' => 'HDR10+'],
                    ['id' => 'hdr_dolby', 'value' => 'Dolby Vision'],
                    ['id' => 'hdr_none', 'value' => 'Không hỗ trợ'],
                ],
            ],
            'ar' => [
                'Panel Type' => [
                    ['id' => 'panel_ips', 'value' => 'IPS'],
                    ['id' => 'panel_va', 'value' => 'VA'],
                    ['id' => 'panel_tn', 'value' => 'TN'],
                    ['id' => 'panel_oled', 'value' => 'OLED'],
                ],
                'HDR Support' => [
                    ['id' => 'hdr_10', 'value' => 'HDR10'],
                    ['id' => 'hdr_10plus', 'value' => 'HDR10+'],
                    ['id' => 'hdr_dolby', 'value' => 'Dolby Vision'],
                    ['id' => 'hdr_none', 'value' => 'لا يوجد'],
                ],
            ],
        ];

        $attributes = SpecificationAttribute::query()->withoutGlobalScopes()->get();

        foreach ($locales as $locale) {
            foreach ($attributes as $attribute) {
                $translatedName = $attrTranslations[$locale][$attribute->name] ?? $attribute->name;
                $translatedOptions = null;

                if ($attribute->hasOptions()) {
                    $opts = $optionTranslations[$locale][$attribute->name] ?? null;

                    if ($opts) {
                        $translatedOptions = json_encode($opts);
                    }
                }

                DB::table('ec_specification_attributes_translations')->updateOrInsert(
                    ['lang_code' => $locale, 'ec_specification_attributes_id' => $attribute->id],
                    [
                        'name' => $translatedName,
                        'options' => $translatedOptions,
                        'default_value' => null,
                    ]
                );
            }
        }

        $tableTranslations = [
            'vi' => ['General Specification' => 'Thông số chung', 'Technical Specification' => 'Thông số kỹ thuật'],
            'ar' => ['General Specification' => 'المواصفات العامة', 'Technical Specification' => 'المواصفات الفنية'],
        ];

        foreach ($locales as $locale) {
            $tables = SpecificationTable::query()->withoutGlobalScopes()->get();

            foreach ($tables as $table) {
                $translatedName = $tableTranslations[$locale][$table->name] ?? $table->name;

                DB::table('ec_specification_tables_translations')->updateOrInsert(
                    ['lang_code' => $locale, 'ec_specification_tables_id' => $table->id],
                    ['name' => $translatedName, 'description' => null]
                );
            }
        }
    }

    protected function seedProductValueTranslations(): void
    {
        $locales = $this->getSupportedLocales();

        if (empty($locales) || ! Schema::hasTable('ec_product_specification_attribute_translations')) {
            return;
        }

        DB::table('ec_product_specification_attribute_translations')->truncate();

        $selectRadioIds = SpecificationAttribute::query()
            ->withoutGlobalScopes()
            ->whereIn('type', [SpecificationAttributeFieldType::SELECT, SpecificationAttributeFieldType::RADIO])
            ->pluck('id')
            ->all();

        $pivots = DB::table('ec_product_specification_attribute')
            ->whereNotIn('attribute_id', $selectRadioIds)
            ->get();

        foreach ($locales as $locale) {
            foreach ($pivots as $pivot) {
                if (! $pivot->value) {
                    continue;
                }

                DB::table('ec_product_specification_attribute_translations')->insert([
                    'product_id' => $pivot->product_id,
                    'attribute_id' => $pivot->attribute_id,
                    'lang_code' => $locale,
                    'value' => $pivot->value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    protected function getSupportedLocales(): array
    {
        if (! defined('LANGUAGE_MODULE_SCREEN_NAME')) {
            return [];
        }

        return Language::query()
            ->where('lang_is_default', false)
            ->pluck('lang_code')
            ->all();
    }
}
