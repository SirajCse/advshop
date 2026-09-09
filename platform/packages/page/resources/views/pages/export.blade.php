@extends('packages/data-synchronize::export')

@section('export_extra_filters_after')
    @php
        use Botble\Base\Enums\BaseStatusEnum;

        $statuses = BaseStatusEnum::labels();
        $templates = get_page_templates();
    @endphp

    <div class="row mb-3">
        <div class="col-md-3">
            <x-core::form.text-input
                name="limit"
                type="number"
                :label="trans('packages/page::pages.export.limit')"
                :placeholder="trans('packages/page::pages.export.limit_placeholder')"
                min="1"
            />
        </div>
        <div class="col-md-3">
            <x-core::form.select
                name="status"
                :label="trans('core/base::forms.status')"
                :options="['' => trans('packages/page::pages.export.all_status')] + $statuses"
            />
        </div>
        <div class="col-md-3">
            <x-core::form.select
                name="template"
                :label="trans('packages/page::pages.export.template')"
                :options="['' => trans('packages/page::pages.export.all_templates')] + $templates"
            />
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <label
                for="start_date"
                class="form-label"
            >{{ trans('packages/page::pages.export.start_date') }}</label>

            {!! Form::datePicker('start_date', null, [
                'placeholder' => trans('packages/page::pages.export.start_date_placeholder'),
            ]) !!}
        </div>
        <div class="col-md-3">
            <label
                for="end_date"
                class="form-label"
            >{{ trans('packages/page::pages.export.end_date') }}</label>

            {!! Form::datePicker('end_date', null, [
                'placeholder' => trans('packages/page::pages.export.end_date_placeholder'),
            ]) !!}
        </div>
    </div>
@stop
