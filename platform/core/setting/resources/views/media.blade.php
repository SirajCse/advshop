@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    {!! $form->renderForm() !!}
@stop

@push('footer')
    <x-core::modal
        id="generate-thumbnails-modal"
        :title="trans('core/setting::setting.generate_thumbnails')"
        type="warning"
        :has-form="true"
        :form-action="route('settings.media.generate-thumbnails')"
        :data-total-files="0"
        :data-chunk-limit="RvMedia::getConfig('generate_thumbnails_chunk_limit')"
        data-bs-backdrop="static"
        data-bs-keyboard="false"
    >
        <p>{{ trans('core/setting::setting.generate_thumbnails_description') }}</p>

        <x-core::form.checkbox
            :label="trans('core/setting::setting.generate_thumbnails_override')"
            :helper_text="trans('core/setting::setting.generate_thumbnails_override_helper')"
            name="override_existing"
            :checked="false"
            id="override_existing"
        />

        <x-core::form.text-input
            :label="trans('core/setting::setting.generate_thumbnails_batch_size')"
            :helper_text="trans('core/setting::setting.generate_thumbnails_batch_size_helper')"
            name="batch_size"
            type="number"
            :value="RvMedia::getConfig('generate_thumbnails_chunk_limit')"
            min="10"
            max="1000"
            step="10"
            id="generate_thumbnails_batch_size"
        />

        <x-core::form.text-input
            :label="trans('core/setting::setting.generate_thumbnails_start_offset')"
            :helper_text="trans('core/setting::setting.generate_thumbnails_start_offset_helper')"
            name="start_offset"
            type="number"
            value="0"
            min="0"
            step="1"
            id="generate_thumbnails_start_offset"
        />

        <div id="generate-thumbnails-progress" class="d-none mt-3">
            <div class="d-flex justify-content-between mb-1">
                <small class="text-muted" id="generate-thumbnails-progress-text"></small>
                <small class="text-muted" id="generate-thumbnails-progress-percent"></small>
            </div>
            <div class="progress">
                <div
                    class="progress-bar"
                    id="generate-thumbnails-progress-bar"
                    role="progressbar"
                    style="width: 0%"
                ></div>
            </div>
            <div id="generate-thumbnails-error-log" class="mt-2 d-none">
                <small class="text-danger" id="generate-thumbnails-error-text"></small>
            </div>
        </div>

        <x-slot:footer>
            <button
                type="button"
                class="btn btn-warning"
                id="generate-thumbnails-button"
            >{{ trans('core/setting::setting.generate') }}</button>
            <button
                type="button"
                class="btn btn-primary"
                data-bs-dismiss="modal"
            >{{ trans('core/base::base.close') }}</button>
        </x-slot:footer>
    </x-core::modal>
@endpush
