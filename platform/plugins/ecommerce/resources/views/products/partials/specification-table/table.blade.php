@php
    use Botble\Ecommerce\Models\ProductSpecificationAttributeTranslation;
    use Botble\Ecommerce\Models\SpecificationTable;
    use Illuminate\Support\Facades\DB;

    $currentLangCode = ProductSpecificationAttributeTranslation::getCurrentLanguageCode($language ?? null);
    $isDefaultLanguage = ProductSpecificationAttributeTranslation::isEditingDefaultLanguage();

    $groupedAttributes = $specificationTable->getSortedAttributesForProduct($product);

    // Load translations for specification attributes
    $attributeTranslations = [];
    $productAttributeTranslations = [];

    if (!$isDefaultLanguage) {
        $attributeIds = collect($groupedAttributes)->pluck('attributes')->flatten()->pluck('id')->unique();

        // Load attribute translations (for options)
        $translations = DB::table('ec_specification_attributes_translations')
            ->whereIn('ec_specification_attributes_id', $attributeIds)
            ->where('lang_code', $currentLangCode)
            ->get()
            ->keyBy('ec_specification_attributes_id');

        foreach ($translations as $translation) {
            $attributeTranslations[$translation->ec_specification_attributes_id] = [
                'name' => $translation->name,
                'options' => json_decode($translation->options ?: '', true) ?: [],
                'default_value' => $translation->default_value,
            ];
        }

        // Load product-specific attribute translations (for values)
        if ($product->id) {
            $productTranslations = DB::table('ec_product_specification_attribute_translations')
                ->where('product_id', $product->id)
                ->whereIn('attribute_id', $attributeIds)
                ->where('lang_code', $currentLangCode)
                ->get()
                ->keyBy('attribute_id');

            foreach ($productTranslations as $translation) {
                $productAttributeTranslations[$translation->attribute_id] = $translation->value;
            }
        }
    }
@endphp

<div class="table-responsive">
    <x-core::table class="table-bordered">
        <x-core::table.header>
            <x-core::table.header.cell>
                {{ trans('plugins/ecommerce::product-specification.product.specification_table.group') }}
            </x-core::table.header.cell>
            <x-core::table.header.cell>
                {{ trans('plugins/ecommerce::product-specification.product.specification_table.attribute') }}
            </x-core::table.header.cell>
            <x-core::table.header.cell>
                {{ trans('plugins/ecommerce::product-specification.product.specification_table.value') }}
            </x-core::table.header.cell>
            <x-core::table.header.cell class="text-center">
                {{ trans('plugins/ecommerce::product-specification.product.specification_table.hide') }}
            </x-core::table.header.cell>
            <x-core::table.header.cell class="text-center" style="width: 40px;">
                {{ trans('plugins/ecommerce::product-specification.product.specification_table.sorting') }}
            </x-core::table.header.cell>
        </x-core::table.header>
        <x-core::table.body>
            @foreach ($groupedAttributes as $groupData)
                @foreach ($groupData['attributes'] as $attribute)
                    @php
                        $data = SpecificationTable::getAttributeDisplayData($product, $attribute, $currentLangCode);
                        // For select/radio with ID-based options, `displayValue` resolves to the option label;
                        // edit forms (and translation lookups) need the raw pivot value (option ID).
                        $attributeValue = ($attribute->hasOptions() && $attribute->hasIdBasedOptions())
                            ? $data['defaultValue']
                            : $data['displayValue'];

                        if (!$isDefaultLanguage) {
                            if ($attribute->hasOptions()) {
                                // For select/radio: value is an option ID, no per-product translation needed
                                // Keep $attributeValue as the option ID from the default-language pivot
                            } else {
                                // For text/textarea: use translated value as before
                                if (isset($productAttributeTranslations[$attribute->id])) {
                                    $attributeValue = $productAttributeTranslations[$attribute->id];
                                } elseif (isset($attributeTranslations[$attribute->id]) && $attributeTranslations[$attribute->id]['default_value'] !== null) {
                                    $attributeValue = $attributeTranslations[$attribute->id]['default_value'];
                                }
                            }
                        }
                    @endphp

                    <x-core::table.body.row>
                        <x-core::table.body.cell>{{ $groupData['group']->name }}</x-core::table.body.cell>
                        <x-core::table.body.cell>{{ $attribute->name }}</x-core::table.body.cell>
                        <x-core::table.body.cell>
                            @if ($isDefaultLanguage)
                                @if ($attribute->type == 'text')
                                    <input class="form-control" type="text" name="specification_attributes[{{ $attribute->id }}][value]" value="{{ $attributeValue }}" placeholder="{{ trans('plugins/ecommerce::product-specification.product.specification_table.enter_value') }}">
                                @elseif ($attribute->type == 'textarea')
                                    <textarea class="form-control" name="specification_attributes[{{ $attribute->id }}][value]" placeholder="{{ trans('plugins/ecommerce::product-specification.product.specification_table.enter_value') }}">{{ $attributeValue }}</textarea>
                                @elseif ($attribute->type == 'checkbox')
                                    <input class="form-check-input" type="checkbox" name="specification_attributes[{{ $attribute->id }}][value]" value="1" @checked($attributeValue)>
                                @elseif ($attribute->type == 'select')
                                    <select class="form-select" name="specification_attributes[{{ $attribute->id }}][value]">
                                        <option value="">--</option>
                                        @if ($attribute->hasIdBasedOptions())
                                            @foreach ($attribute->getIdBasedOptions() as $opt)
                                                <option value="{{ $opt['id'] }}" @selected($opt['id'] === $attributeValue)>{{ $opt['value'] }}</option>
                                            @endforeach
                                        @else
                                            @foreach ($attribute->options ?? [] as $value)
                                                <option value="{{ $value }}" @selected($value === $attributeValue)>{{ $value }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                @elseif ($attribute->type == 'radio')
                                    @if ($attribute->hasIdBasedOptions())
                                        @foreach ($attribute->getIdBasedOptions() as $opt)
                                            <label class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="specification_attributes[{{ $attribute->id }}][value]" value="{{ $opt['id'] }}" @checked($opt['id'] === $attributeValue)>
                                                <span class="form-check-label">{{ $opt['value'] }}</span>
                                            </label>
                                        @endforeach
                                    @else
                                        @foreach ($attribute->options ?? [] as $value)
                                            <label class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="specification_attributes[{{ $attribute->id }}][value]" value="{{ $value }}" @checked($value === $attributeValue)>
                                                <span class="form-check-label">{{ $value }}</span>
                                            </label>
                                        @endforeach
                                    @endif
                                @endif
                            @else
                                @if ($attribute->type == 'text')
                                    <input class="form-control" type="text" name="specification_attributes[{{ $attribute->id }}][value]" value="{{ $attributeValue }}" placeholder="{{ trans('plugins/ecommerce::product-specification.product.specification_table.enter_translation') }}">
                                @elseif ($attribute->type == 'textarea')
                                    <textarea class="form-control" name="specification_attributes[{{ $attribute->id }}][value]" placeholder="{{ trans('plugins/ecommerce::product-specification.product.specification_table.enter_translation') }}">{{ $attributeValue }}</textarea>
                                @elseif ($attribute->type == 'checkbox')
                                    <input class="form-check-input" type="checkbox" name="specification_attributes[{{ $attribute->id }}][value]" value="1" @checked($attributeValue)>
                                @elseif ($attribute->type == 'select' || $attribute->type == 'radio')
                                    @php
                                        $resolvedLabel = null;
                                        if ($attributeValue && $attribute->hasIdBasedOptions()) {
                                            $translatedOpts = $attributeTranslations[$attribute->id]['options'] ?? [];
                                            foreach ($translatedOpts as $tOpt) {
                                                if (is_array($tOpt) && ($tOpt['id'] ?? '') === $attributeValue) {
                                                    $resolvedLabel = $tOpt['value'];
                                                    break;
                                                }
                                            }
                                            if (!$resolvedLabel) {
                                                $resolvedLabel = $attribute->getOptionValueById($attributeValue);
                                            }
                                        } elseif ($attributeValue) {
                                            $resolvedLabel = $attributeValue;
                                        }
                                    @endphp
                                    <span class="form-control-plaintext text-muted">{{ $resolvedLabel ?? '—' }}</span>
                                @endif
                            @endif
                        </x-core::table.body.cell>
                        <x-core::table.body.cell class="text-center">
                            @if ($isDefaultLanguage)
                                <input class="form-check-input" type="checkbox" name="specification_attributes[{{ $attribute->id }}][hidden]" value="1" @checked($data['isHidden'])>
                            @else
                                @if($data['isHidden'])
                                    <x-core::icon name="ti ti-check" class="text-muted" />
                                @else
                                    <x-core::icon name="ti ti-minus" class="text-muted" />
                                @endif
                            @endif
                        </x-core::table.body.cell>
                        <x-core::table.body.cell class="text-center">
                            @if ($isDefaultLanguage)
                                <input type="hidden" name="specification_attributes[{{ $attribute->id }}][order]" value="{{ $data['order'] }}">
                                <x-core::icon name="ti ti-arrows-sort" class="text-secondary" style="cursor: move;" />
                            @else
                                <span class="text-muted">{{ $data['order'] }}</span>
                            @endif
                        </x-core::table.body.cell>
                    </x-core::table.body.row>
                @endforeach
            @endforeach
        </x-core::table.body>
    </x-core::table>
</div>
