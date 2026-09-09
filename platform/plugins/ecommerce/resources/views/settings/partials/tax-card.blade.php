@php
    use Botble\Base\Enums\BaseStatusEnum;
    use Botble\Ecommerce\Facades\EcommerceHelper;

    $isDefault = $tax->is_default;
    $isInactive = $tax->status !== BaseStatusEnum::PUBLISHED;
@endphp

<div class="col-12" data-tax-id="{{ $tax->id }}">
    <div class="card h-100 tax-card {{ $isDefault ? 'tax-card-default' : '' }} {{ $isInactive ? 'tax-card-inactive' : '' }}" style="--bb-card-border-color: rgba(98, 105, 118, 0.16);{{ $isDefault ? ' border-color: var(--tblr-primary) !important; border-width: 2px;' : '' }}">
        <div class="card-header py-3" style="min-height: auto;">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h4 class="card-title mb-0">{{ $tax->title }}</h4>
                @if($isDefault)
                    <span class="badge bg-primary-lt">
                        <x-core::icon name="ti ti-star-filled" style="width: 12px; height: 12px;" />
                        {{ trans('plugins/ecommerce::tax.default') }}
                    </span>
                @endif
                {!! $tax->status->toHtml() !!}
            </div>
            <div class="card-actions">
                <a
                    href="{{ route('tax.edit', $tax->id) }}"
                    class="btn btn-icon btn-edit-tax"
                    title="{{ trans('core/base::tables.edit') }}"
                >
                    <x-core::icon name="ti ti-edit" />
                </a>
                <button
                    type="button"
                    class="btn btn-icon text-danger btn-delete-tax"
                    data-url="{{ route('tax.destroy', $tax->id) }}"
                    title="{{ trans('core/base::tables.delete') }}"
                >
                    <x-core::icon name="ti ti-trash" />
                </button>
            </div>
        </div>
        <div class="card-body py-2 border-bottom">
            <div class="d-flex align-items-center gap-3 text-muted small flex-wrap">
                <span class="d-inline-flex align-items-center gap-1">
                    <x-core::icon name="ti ti-percentage" style="width: 14px; height: 14px;" />
                    <strong>{{ $tax->percentage }}%</strong> {{ trans('plugins/ecommerce::tax.base_rate') }}
                </span>
                <span class="d-inline-flex align-items-center gap-1">
                    <x-core::icon name="ti ti-arrows-sort" style="width: 14px; height: 14px;" />
                    {{ trans('plugins/ecommerce::tax.priority') }}: {{ $tax->priority }}
                </span>
                <span class="d-inline-flex align-items-center gap-1">
                    <x-core::icon name="ti ti-list" style="width: 14px; height: 14px;" />
                    {{ $tax->rules_count }} {{ Str::plural(trans('plugins/ecommerce::tax.rule.name'), $tax->rules_count) }}
                </span>
            </div>
        </div>

        <div class="card-body p-0 flex-grow-1">
            @if($tax->rules->isNotEmpty())
                <div class="list-group list-group-flush" style="max-height: 240px; overflow-y: auto;">
                    @foreach($tax->rules as $rule)
                        @php
                            $country = $rule->country_name;
                            if ($country === $rule->country) {
                                $country = EcommerceHelper::getCountryNameById($rule->country);
                            }
                        @endphp
                        <div class="list-group-item list-group-item-action py-2 px-3">
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <div class="text-truncate flex-grow-1">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-medium text-truncate">
                                            {{ $country ?: trans('plugins/ecommerce::tax.all_countries') }}
                                        </span>
                                        <span class="badge bg-azure-lt">{{ $rule->percentage }}%</span>
                                    </div>
                                    <div class="text-muted small text-truncate">
                                        @if($rule->state_name)
                                            {{ $rule->state_name }}@if($rule->city_name), {{ $rule->city_name }}@endif
                                        @elseif($rule->city_name)
                                            {{ $rule->city_name }}
                                        @else
                                            {{ trans('plugins/ecommerce::tax.all_regions') }}
                                        @endif
                                        @if(EcommerceHelper::isZipCodeEnabled() && $rule->zip_code)
                                            <span class="ms-1 text-nowrap">
                                                <x-core::icon name="ti ti-mail" style="width: 12px; height: 12px;" />
                                                {{ $rule->zip_code }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="btn-group btn-group-sm flex-shrink-0">
                                    <a
                                        href="{{ route('tax.rule.edit', $rule->id) }}"
                                        class="btn btn-ghost-secondary btn-icon btn-sm btn-edit-tax-rule"
                                        title="{{ trans('core/base::tables.edit') }}"
                                    >
                                        <x-core::icon name="ti ti-edit" style="width: 14px; height: 14px;" />
                                    </a>
                                    <button
                                        type="button"
                                        class="btn btn-ghost-danger btn-icon btn-sm btn-delete-tax-rule"
                                        data-url="{{ route('tax.rule.destroy', $rule->id) }}"
                                        title="{{ trans('core/base::tables.delete') }}"
                                    >
                                        <x-core::icon name="ti ti-trash" style="width: 14px; height: 14px;" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="card-footer py-2 bg-transparent">
            <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap">
                <a href="{{ route('tax.rule.create', ['tax_id' => $tax->id]) }}" class="btn btn-outline-primary btn-sm create-tax-rule-item">
                    <x-core::icon name="ti ti-plus" />
                    {{ trans('plugins/ecommerce::tax.rule.add') }}
                </a>
                @if(!$isDefault)
                    <button
                        type="button"
                        class="btn btn-ghost-primary btn-sm btn-set-default-tax"
                        data-url="{{ route('tax.set-default', $tax->id) }}"
                        title="{{ trans('plugins/ecommerce::tax.set_as_default') }}"
                    >
                        <x-core::icon name="ti ti-star" />
                        {{ trans('plugins/ecommerce::tax.set_as_default') }}
                    </button>
                @else
                    <span class="badge bg-primary-lt">
                        <x-core::icon name="ti ti-check" style="width: 12px; height: 12px;" />
                        {{ trans('plugins/ecommerce::tax.default_tax_info') }}
                    </span>
                @endif
            </div>
        </div>
    </div>
</div>
