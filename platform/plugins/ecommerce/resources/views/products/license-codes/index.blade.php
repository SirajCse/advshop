@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <div
        class="license-codes-container"
        data-routes="{{ json_encode([
            'store' => route('products.license-codes.store', $product->id),
            'update' => route('products.license-codes.update', [$product->id, '__ID__']),
            'destroy' => route('products.license-codes.destroy', [$product->id, '__ID__']),
            'bulkGenerate' => route('products.license-codes.bulk-generate', $product->id),
            'bulkDelete' => route('products.license-codes.bulk-delete', $product->id),
        ]) }}"
        data-confirm-delete="{{ trans('core/base::tables.confirm_delete') }}"
        data-confirm-bulk-delete="{{ trans('plugins/ecommerce::products.license_codes.bulk_delete.confirm') }}"
    >
    <div class="row">
        <div class="col-md-12">
            <x-core::card>
                <x-core::card.header>
                    <x-core::card.title>
                        {{ trans('plugins/ecommerce::products.license_codes.title') }} - {{ $product->name }}
                        @if($product->is_variation)
                            <small class="text-muted">({{ trans('plugins/ecommerce::products.license_codes.variation_label') }})</small>
                        @endif
                    </x-core::card.title>
                    <div class="card-actions">
                        <a href="{{ route('products.edit', $product->id) }}" class="btn btn-ghost-secondary">
                            <x-core::icon name="ti ti-arrow-left" />
                            {{ trans('plugins/ecommerce::products.license_codes.back') }}
                        </a>
                        <div class="btn-group">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add-license-code-modal">
                                <x-core::icon name="ti ti-plus" />
                                {{ trans('plugins/ecommerce::products.license_codes.add') }}
                            </button>
                            <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="visually-hidden">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#bulk-generate-modal">
                                        <x-core::icon name="ti ti-wand" />
                                        {{ trans('plugins/ecommerce::products.license_codes.generate') }}
                                    </button>
                                </li>
                                <li>
                                    <a href="{{ route('tools.data-synchronize.import.product-license-codes.index') }}" class="dropdown-item">
                                        <x-core::icon name="ti ti-upload" />
                                        {{ trans('plugins/ecommerce::products.license_codes.import.button') }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </x-core::card.header>

                <x-core::card.body>
                    @if($showOutOfStockWarning)
                        <x-core::alert type="danger" class="mb-3">
                            <strong>{{ trans('plugins/ecommerce::products.license_codes.out_of_stock_title') }}</strong><br>
                            {{ trans('plugins/ecommerce::products.license_codes.out_of_stock_message') }}
                        </x-core::alert>
                    @elseif($showLowStockWarning)
                        <x-core::alert type="warning" class="mb-3">
                            <strong>{{ trans('plugins/ecommerce::products.license_codes.low_stock_title') }}</strong><br>
                            {{ trans('plugins/ecommerce::products.license_codes.low_stock_message', ['count' => $availableCount]) }}
                        </x-core::alert>
                    @endif

                    @if($licenseCodes->count() > 0)
                        <div class="mb-3 bulk-actions-wrapper" style="display: none;">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted">
                                    <span class="selected-count fw-bold">0</span> {{ trans('plugins/ecommerce::products.license_codes.bulk_delete.selected') }}
                                </span>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-bulk-delete">
                                    <x-core::icon name="ti ti-trash" />
                                    {{ trans('plugins/ecommerce::products.license_codes.bulk_delete.button') }}
                                </button>
                            </div>
                        </div>

                        <x-core::table>
                            <x-core::table.header>
                                <x-core::table.header.cell class="text-center" style="width: 40px;">
                                    <input type="checkbox" class="form-check-input select-all-checkbox" />
                                </x-core::table.header.cell>
                                <x-core::table.header.cell>
                                    {{ trans('plugins/ecommerce::products.license_codes.code') }}
                                </x-core::table.header.cell>
                                <x-core::table.header.cell>
                                    {{ trans('plugins/ecommerce::products.license_codes.status') }}
                                </x-core::table.header.cell>
                                <x-core::table.header.cell>
                                    {{ trans('plugins/ecommerce::products.license_codes.assigned_at') }}
                                </x-core::table.header.cell>
                                <x-core::table.header.cell>
                                    {{ trans('core/base::tables.created_at') }}
                                </x-core::table.header.cell>
                                <x-core::table.header.cell>
                                    {{ trans('core/base::tables.operations') }}
                                </x-core::table.header.cell>
                            </x-core::table.header>

                            <x-core::table.body>
                                @foreach($licenseCodes as $licenseCode)
                                    <x-core::table.body.row>
                                        <x-core::table.body.cell class="text-center">
                                            @if($licenseCode->isAvailable())
                                                <input type="checkbox" class="form-check-input license-code-checkbox" data-id="{{ $licenseCode->id }}" />
                                            @endif
                                        </x-core::table.body.cell>
                                        <x-core::table.body.cell>
                                            <code>{{ $licenseCode->license_code }}</code>
                                        </x-core::table.body.cell>
                                        <x-core::table.body.cell>
                                            {!! $licenseCode->status->toHtml() !!}
                                        </x-core::table.body.cell>
                                        <x-core::table.body.cell>
                                            @if($licenseCode->assigned_at && $licenseCode->assignedOrderProduct && $licenseCode->assignedOrderProduct->order)
                                                <div>
                                                    {{ BaseHelper::formatDate($licenseCode->assigned_at) }}
                                                    <br>
                                                    <a href="{{ route('orders.edit', $licenseCode->assignedOrderProduct->order->id) }}"
                                                       class="text-primary"
                                                       target="_blank">
                                                        <x-core::icon name="ti ti-external-link" />
                                                        {{ trans('plugins/ecommerce::order.view_order') }} {{ $licenseCode->assignedOrderProduct->order->code }}
                                                    </a>
                                                </div>
                                            @else
                                                {{ $licenseCode->assigned_at ? BaseHelper::formatDate($licenseCode->assigned_at) : '-' }}
                                            @endif
                                        </x-core::table.body.cell>
                                        <x-core::table.body.cell>
                                            {{ BaseHelper::formatDate($licenseCode->created_at) }}
                                        </x-core::table.body.cell>
                                        <x-core::table.body.cell>
                                            @if($licenseCode->isAvailable())
                                                <button type="button"
                                                        class="btn btn-sm btn-warning edit-license-code-btn"
                                                        data-license-code-id="{{ $licenseCode->id }}"
                                                        data-license-code="{{ $licenseCode->license_code }}">
                                                    <x-core::icon name="ti ti-edit" />
                                                    {{ trans('core/base::tables.edit') }}
                                                </button>
                                                <button type="button"
                                                        class="btn btn-sm btn-danger delete-license-code-btn"
                                                        data-license-code-id="{{ $licenseCode->id }}">
                                                    <x-core::icon name="ti ti-trash" />
                                                    {{ trans('core/base::tables.delete') }}
                                                </button>
                                            @else
                                                <span class="text-muted">{{ trans('plugins/ecommerce::products.license_codes.used_code_no_actions') }}</span>
                                            @endif
                                        </x-core::table.body.cell>
                                    </x-core::table.body.row>
                                @endforeach
                            </x-core::table.body>
                        </x-core::table>

                        <div class="mt-3">
                            {!! $licenseCodes->links() !!}
                        </div>
                    @else
                        @if($product->license_code_type === 'pick_from_list')
                            <x-core::alert type="warning">
                                <strong>{{ trans('plugins/ecommerce::products.license_codes.no_codes_warning_title') }}</strong><br>
                                {{ trans('plugins/ecommerce::products.license_codes.no_codes_warning_message') }}
                            </x-core::alert>
                        @else
                            <x-core::alert type="info">
                                {{ trans('plugins/ecommerce::products.license_codes.no_codes_auto_generate') }}
                            </x-core::alert>
                        @endif
                    @endif
                </x-core::card.body>
            </x-core::card>
        </div>
    </div>

    @include('plugins/ecommerce::products.license-codes.modals')
    </div>
@endsection

@push('footer')
    {{ Html::script('vendor/core/plugins/ecommerce/js/product-license-codes.js') }}
@endpush
