@php
    use Botble\Ecommerce\Enums\ProductTypeEnum;
    use Botble\Ecommerce\Facades\EcommerceHelper;
    use Botble\Marketplace\Facades\MarketplaceHelper;
@endphp

@if (
    EcommerceHelper::isDisabledPhysicalProduct()
    || (MarketplaceHelper::isVendorDigitalProductsEnabled()
        && (
            (! $product && request()->input('product_type') == ProductTypeEnum::DIGITAL)
            || ($product && $product->isTypeDigital())
        ))
)
    @if (EcommerceHelper::isEnabledLicenseCodesForDigitalProducts())
        <x-core::form.on-off.checkbox
            :label="trans('plugins/ecommerce::products.digital_attachments.generate_license_code_after_purchasing_product')"
            name="generate_license_code"
            :checked="old('generate_license_code', $product ? $product->generate_license_code : 0)"
            data-bb-toggle="collapse"
            data-bb-target="#license-code-options"
        />

        <div class="collapse @if(old('generate_license_code', $product ? $product->generate_license_code : 0)) show @endif" id="license-code-options">
            <x-core::form-group class="mt-3">
                <x-core::form.label for="license_code_type" :value="trans('plugins/ecommerce::products.license_codes.type.title')" />
                <x-core::form.select name="license_code_type" id="license_code_type">
                    <option value="auto_generate" @if(old('license_code_type', $product ? $product->license_code_type : 'auto_generate') === 'auto_generate') selected @endif>
                        {{ trans('plugins/ecommerce::products.license_codes.type.auto_generate') }}
                    </option>
                    <option value="pick_from_list" @if(old('license_code_type', $product ? $product->license_code_type : 'auto_generate') === 'pick_from_list') selected @endif>
                        {{ trans('plugins/ecommerce::products.license_codes.type.pick_from_list') }}
                    </option>
                </x-core::form.select>
                <x-core::form.helper-text>
                    {{ trans('plugins/ecommerce::products.license_codes.type.description') }}
                </x-core::form.helper-text>
            </x-core::form-group>
        </div>

        <x-core::form-group class="product-license-codes-management mb-5" id="license-codes-management" @style(['display: none' => !($product && $product->generate_license_code && $product->license_code_type === 'pick_from_list')])>
            <x-core::form.label for="license_codes" class="mb-3">
                {{ trans('plugins/ecommerce::products.license_codes.title') }}

                <x-slot:description>
                    <div class="btn-list mt-3 mb-3">
                        <x-core::button type="button" class="license-code-add-btn" size="sm" icon="ti ti-plus">
                            {{ trans('plugins/ecommerce::products.license_codes.add') }}
                        </x-core::button>

                        <x-core::button type="button" class="license-code-generate-btn" size="sm" icon="ti ti-refresh">
                            {{ trans('plugins/ecommerce::products.license_codes.generate') }}
                        </x-core::button>
                    </div>
                </x-slot:description>
            </x-core::form.label>

            <div class="clearfix"></div>

            <div class="table-responsive">
                <x-core::table>
                    <x-core::table.header>
                        <x-core::table.header.cell>
                            {{ trans('plugins/ecommerce::products.license_codes.code') }}
                        </x-core::table.header.cell>
                        <x-core::table.header.cell>
                            {{ trans('plugins/ecommerce::products.license_codes.status') }}
                        </x-core::table.header.cell>
                        <x-core::table.header.cell />
                    </x-core::table.header>

                    <x-core::table.body id="license-codes-table-body">
                        @if($product)
                            @foreach ($product->licenseCodes->filter(fn($code) => $code->isAvailable()) as $licenseCode)
                                <x-core::table.body.row data-license-code-id="{{ $licenseCode->id }}">
                                    <x-core::table.body.cell>
                                        <input type="text"
                                               name="license_codes[{{ $licenseCode->id }}][code]"
                                               value="{{ $licenseCode->license_code }}"
                                               class="form-control license-code-input">
                                    </x-core::table.body.cell>
                                    <x-core::table.body.cell>
                                        {!! $licenseCode->status->toHtml() !!}
                                    </x-core::table.body.cell>
                                    <x-core::table.body.cell>
                                        <x-core::button type="button"
                                                        class="license-code-delete-btn"
                                                        size="sm"
                                                        color="danger"
                                                        icon="ti ti-trash"
                                                        data-license-code-id="{{ $licenseCode->id }}">
                                            {{ trans('core/base::tables.delete') }}
                                        </x-core::button>
                                    </x-core::table.body.cell>
                                </x-core::table.body.row>
                            @endforeach
                        @endif
                    </x-core::table.body>
                </x-core::table>
            </div>
        </x-core::form-group>
    @endif

    <x-core::form-group class="product-type-digital-management">
        <x-core::form.label for="product_file" class="mb-3">
            {{ trans('plugins/ecommerce::products.digital_attachments.title') }}

            <x-slot:description>
                <div class="btn-list">
                    <x-core::button type="button" class="digital_attachments_btn" size="sm" icon="ti ti-paperclip">
                        {{ trans('plugins/ecommerce::products.digital_attachments.add') }}
                    </x-core::button>

                    <x-core::button type="button" class="digital_attachments_external_btn" size="sm" icon="ti ti-link">
                        {{ trans('plugins/ecommerce::products.digital_attachments.add_external_link') }}
                    </x-core::button>
                </div>
            </x-slot:description>
        </x-core::form.label>

        <x-core::table>
            <x-core::table.header>
                <x-core::table.header.cell />
                <x-core::table.header.cell>
                    {{ trans('plugins/ecommerce::products.digital_attachments.file_name') }}
                </x-core::table.header.cell>
                <x-core::table.header.cell>
                    {{ trans('plugins/ecommerce::products.digital_attachments.file_size') }}
                </x-core::table.header.cell>
                <x-core::table.header.cell>
                    {{ trans('core/base::tables.created_at') }}
                </x-core::table.header.cell>
                <x-core::table.header.cell />
            </x-core::table.header>

            <x-core::table.body>
                @if($product)
                    @foreach ($product->productFiles as $file)
                        <x-core::table.body.row>
                            <x-core::table.body.cell>
                                <x-core::form.checkbox
                                    name="product_files[{{ $file->id }}]"
                                    class="digital-attachment-checkbox"
                                    :checked="true"
                                    :single="true"
                                />
                            </x-core::table.body.cell>
                            <x-core::table.body.cell>
                                @if ($file->is_external_link)
                                    <a href="{{ $file->url }}" target="_blank">
                                        <x-core::icon name="ti ti-link" />
                                        {{ $file->basename ? Str::limit($file->basename, 50) : $file->url }}
                                    </a>
                                @else
                                    <x-core::icon name="ti ti-paperclip" />
                                    {{ Str::limit($file->basename, 50) }}
                                @endif
                            </x-core::table.body.cell>
                            <x-core::table.body.cell>
                                {{ $file->file_size ? BaseHelper::humanFileSize($file->file_size) : '-' }}
                            </x-core::table.body.cell>
                            <x-core::table.body.cell>
                                {{ BaseHelper::formatDate($file->created_at) }}
                            </x-core::table.body.cell>
                            <x-core::table.body.cell />
                        </x-core::table.body.row>
                    @endforeach
                @endif
            </x-core::table.body>
        </x-core::table>

        <div class="digital_attachments_input">
            <input
                name="product_files_input[]"
                data-id="{{ Str::random(10) }}"
                type="file"
            >
        </div>
    </x-core::form-group>

    @if($product)
        <x-core::form.checkbox
            name="notify_attachment_updated"
            :label="trans('plugins/ecommerce::products.digital_attachments.notify_attachment_updated')"
            :checked="old('notify_attachment_updated', $product->notify_attachment_updated)"
            :value="true"
        />
    @endif

    @pushOnce('footer')
        @include('plugins/ecommerce::products.partials.digital-product-file-template')
        @if (EcommerceHelper::isEnabledLicenseCodesForDigitalProducts())
            @include('plugins/ecommerce::products.partials.license-code-template', ['isVariation' => false])
        @endif
    @endpushOnce
@endif
