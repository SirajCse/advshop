@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <x-core-setting::section
        :title="trans('plugins/ecommerce::tax.name')"
        :description="trans('plugins/ecommerce::tax.description')"
        :card="false"
        style="max-width: 1200px;"
    >
        <div class="d-flex align-items-center justify-content-between gap-3 mb-3 flex-wrap">
            <button type="button" class="btn btn-primary btn-create-tax" data-href="{{ route('tax.create') }}">
                <x-core::icon name="ti ti-plus" />
                {{ trans('plugins/ecommerce::tax.create') }}
            </button>

            <button
                class="btn btn-ghost-secondary btn-sm"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#tax-help-section"
                aria-expanded="false"
                aria-controls="tax-help-section"
            >
                <x-core::icon name="ti ti-help-circle" />
                {{ trans('plugins/ecommerce::tax.how_it_works') }}
            </button>
        </div>

        <div class="collapse mb-3" id="tax-help-section">
            <div class="card" style="--bb-card-border-color: rgba(98, 105, 118, 0.16);">
                <div class="card-body py-3">
                    <div class="markdown">
                        <ul class="mb-0 ps-3">
                            <li class="mb-1">{!! BaseHelper::clean(trans('plugins/ecommerce::tax.instruction_tax')) !!}</li>
                            <li class="mb-1">{!! BaseHelper::clean(trans('plugins/ecommerce::tax.instruction_rules')) !!}</li>
                            <li class="mb-1">{!! BaseHelper::clean(trans('plugins/ecommerce::tax.instruction_default')) !!}</li>
                            <li>{!! BaseHelper::clean(trans('plugins/ecommerce::tax.instruction_priority')) !!}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        @if($taxes->isEmpty())
            <div class="card" style="--bb-card-border-color: rgba(98, 105, 118, 0.16);">
                <div class="card-body">
                    <div class="empty">
                        <div class="empty-icon">
                            <x-core::icon name="ti ti-receipt-tax" />
                        </div>
                        <p class="empty-title">{{ trans('plugins/ecommerce::tax.no_taxes') }}</p>
                    </div>
                </div>
            </div>
        @else
            <div class="row g-3" id="tax-cards-container">
                @foreach($taxes as $tax)
                    @include('plugins/ecommerce::settings.partials.tax-card', ['tax' => $tax])
                @endforeach
            </div>
        @endif
    </x-core-setting::section>

    @include('plugins/ecommerce::taxes.form-modal')
    @include('plugins/ecommerce::taxes.rules.form-modal')
@endsection
