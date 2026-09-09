<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        $attributes = DB::table('ec_specification_attributes')
            ->whereIn('type', ['select', 'radio'])
            ->whereNotNull('options')
            ->get();

        foreach ($attributes as $attribute) {
            $options = json_decode($attribute->options, true);

            if (empty($options) || ! is_array($options)) {
                continue;
            }

            if (isset($options[0]) && is_array($options[0]) && isset($options[0]['id'])) {
                continue;
            }

            $idBasedOptions = [];
            $textToIdMap = [];

            foreach ($options as $value) {
                if (! is_string($value)) {
                    continue;
                }

                $id = bin2hex(random_bytes(4));
                $idBasedOptions[] = ['id' => $id, 'value' => $value];
                $textToIdMap[$value] = $id;
            }

            DB::table('ec_specification_attributes')
                ->where('id', $attribute->id)
                ->update(['options' => json_encode($idBasedOptions)]);

            $this->updateTranslationOptions($attribute->id, $idBasedOptions);
            $this->updateProductValues($attribute->id, $textToIdMap);
            $this->updateProductValueTranslations($attribute->id, $textToIdMap, $idBasedOptions);
        }
    }

    private function updateTranslationOptions(int $attributeId, array $idBasedOptions): void
    {
        $translations = DB::table('ec_specification_attributes_translations')
            ->where('ec_specification_attributes_id', $attributeId)
            ->whereNotNull('options')
            ->get();

        foreach ($translations as $translation) {
            $translatedOptions = json_decode($translation->options, true);

            if (empty($translatedOptions) || ! is_array($translatedOptions)) {
                continue;
            }

            if (isset($translatedOptions[0]) && is_array($translatedOptions[0]) && isset($translatedOptions[0]['id'])) {
                continue;
            }

            $converted = [];

            foreach ($translatedOptions as $i => $value) {
                if (! is_string($value)) {
                    continue;
                }

                if (isset($idBasedOptions[$i])) {
                    $converted[] = ['id' => $idBasedOptions[$i]['id'], 'value' => $value];
                }
            }

            DB::table('ec_specification_attributes_translations')
                ->where('ec_specification_attributes_id', $attributeId)
                ->where('lang_code', $translation->lang_code)
                ->update(['options' => json_encode($converted)]);
        }
    }

    private function updateProductValues(int $attributeId, array $textToIdMap): void
    {
        $pivots = DB::table('ec_product_specification_attribute')
            ->where('attribute_id', $attributeId)
            ->whereNotNull('value')
            ->get();

        foreach ($pivots as $pivot) {
            if (isset($textToIdMap[$pivot->value])) {
                DB::table('ec_product_specification_attribute')
                    ->where('product_id', $pivot->product_id)
                    ->where('attribute_id', $attributeId)
                    ->update(['value' => $textToIdMap[$pivot->value]]);
            }
        }
    }

    private function updateProductValueTranslations(
        int $attributeId,
        array $textToIdMap,
        array $idBasedOptions
    ): void {
        if (! Schema::hasTable('ec_product_specification_attribute_translations')) {
            return;
        }

        $translations = DB::table('ec_product_specification_attribute_translations')
            ->where('attribute_id', $attributeId)
            ->whereNotNull('value')
            ->get();

        $attrTranslations = DB::table('ec_specification_attributes_translations')
            ->where('ec_specification_attributes_id', $attributeId)
            ->whereNotNull('options')
            ->get()
            ->keyBy('lang_code');

        foreach ($translations as $trans) {
            if (isset($textToIdMap[$trans->value])) {
                DB::table('ec_product_specification_attribute_translations')
                    ->where('id', $trans->id)
                    ->update(['value' => $textToIdMap[$trans->value]]);

                continue;
            }

            $langTrans = $attrTranslations->get($trans->lang_code);

            if ($langTrans) {
                $langOptions = json_decode($langTrans->options, true) ?: [];

                foreach ($langOptions as $i => $opt) {
                    $optText = is_array($opt) ? ($opt['value'] ?? '') : $opt;

                    if ($optText === $trans->value && isset($idBasedOptions[$i])) {
                        DB::table('ec_product_specification_attribute_translations')
                            ->where('id', $trans->id)
                            ->update(['value' => $idBasedOptions[$i]['id']]);

                        break;
                    }
                }
            }
        }
    }

    public function down(): void
    {
        $attributes = DB::table('ec_specification_attributes')
            ->whereIn('type', ['select', 'radio'])
            ->whereNotNull('options')
            ->get();

        foreach ($attributes as $attribute) {
            $options = json_decode($attribute->options, true);

            if (empty($options) || ! is_array($options)) {
                continue;
            }

            if (! isset($options[0]) || ! is_array($options[0]) || ! isset($options[0]['id'])) {
                continue;
            }

            $idToTextMap = [];
            $flatOptions = [];

            foreach ($options as $opt) {
                $flatOptions[] = $opt['value'];
                $idToTextMap[$opt['id']] = $opt['value'];
            }

            DB::table('ec_specification_attributes')
                ->where('id', $attribute->id)
                ->update(['options' => json_encode($flatOptions)]);

            $translations = DB::table('ec_specification_attributes_translations')
                ->where('ec_specification_attributes_id', $attribute->id)
                ->whereNotNull('options')
                ->get();

            foreach ($translations as $trans) {
                $transOpts = json_decode($trans->options, true);

                if (empty($transOpts)) {
                    continue;
                }

                $flat = array_map(fn ($o) => is_array($o) ? ($o['value'] ?? '') : $o, $transOpts);

                DB::table('ec_specification_attributes_translations')
                    ->where('ec_specification_attributes_id', $attribute->id)
                    ->where('lang_code', $trans->lang_code)
                    ->update(['options' => json_encode($flat)]);
            }

            DB::table('ec_product_specification_attribute')
                ->where('attribute_id', $attribute->id)
                ->get()
                ->each(function ($pivot) use ($idToTextMap) {
                    if (isset($idToTextMap[$pivot->value])) {
                        DB::table('ec_product_specification_attribute')
                            ->where('product_id', $pivot->product_id)
                            ->where('attribute_id', $pivot->attribute_id)
                            ->update(['value' => $idToTextMap[$pivot->value]]);
                    }
                });

            if (Schema::hasTable('ec_product_specification_attribute_translations')) {
                DB::table('ec_product_specification_attribute_translations')
                    ->where('attribute_id', $attribute->id)
                    ->get()
                    ->each(function ($trans) use ($idToTextMap) {
                        if (isset($idToTextMap[$trans->value])) {
                            DB::table('ec_product_specification_attribute_translations')
                                ->where('id', $trans->id)
                                ->update(['value' => $idToTextMap[$trans->value]]);
                        }
                    });
            }
        }
    }
};
