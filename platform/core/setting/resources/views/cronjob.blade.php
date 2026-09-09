@extends(BaseHelper::getAdminMasterLayoutTemplate())

@section('content')
    <x-core-setting::section
        :title="trans('core/setting::cronjob.name')"
        :description="trans('core/setting::cronjob.description')"
    >
        {{-- Status Alert --}}
        @if (!$lastRunAt)
            <x-core::alert
                type="warning"
                :title="trans('core/setting::cronjob.is_not_ready')"
                icon="ti ti-alert-triangle"
            >
                <x-slot:subtitle>
                    {{ trans('core/setting::cronjob.is_not_ready_description') }}
                </x-slot:subtitle>
            </x-core::alert>
        @elseif(Carbon\Carbon::now()->diffInMinutes($lastRunAt) <= 10)
            <x-core::alert
                type="success"
                :title="trans('core/setting::cronjob.is_working')"
                icon="ti ti-circle-check"
            >
                <x-slot:subtitle>
                    {!! BaseHelper::clean(
                        trans('core/setting::cronjob.last_checked', [
                            'time' => "<span class='fw-semibold'>{$lastRunAt->diffForHumans()}</span>",
                        ]),
                    ) !!}
                </x-slot:subtitle>
            </x-core::alert>
        @else
            <x-core::alert
                type="danger"
                :title="trans('core/setting::cronjob.is_not_working')"
                icon="ti ti-alert-circle"
            >
                <x-slot:subtitle>
                    {{ trans('core/setting::cronjob.is_not_working_description') }}
                </x-slot:subtitle>
            </x-core::alert>
        @endif

        {{-- What is Cronjob --}}
        <x-core::card class="mt-4">
            <x-core::card.header>
                <x-core::card.title>{{ trans('core/setting::cronjob.what_is.title') }}</x-core::card.title>
            </x-core::card.header>
            <div class="card-body">
                <p class="mb-3">{{ trans('core/setting::cronjob.what_is.description') }}</p>
                <div class="text-muted">
                    <strong>{{ trans('core/setting::cronjob.what_is.examples') }}:</strong>
                    {{ trans('core/setting::cronjob.what_is.features') }}
                </div>
            </div>
        </x-core::card>

        {{-- Command to Copy --}}
        <x-core::card class="mt-3">
            <x-core::card.header>
                <x-core::card.title>{{ trans('core/setting::cronjob.command.title') }}</x-core::card.title>
            </x-core::card.header>
            <div class="card-body">
                <p class="text-muted mb-3">{{ trans('core/setting::cronjob.command.description') }}</p>
                <div class="input-group">
                    <input
                        type="text"
                        id="command"
                        class="form-control font-monospace"
                        value="{{ $command }}"
                        readonly
                    >
                    <button
                        class="btn btn-primary"
                        data-bb-toggle="clipboard"
                        data-clipboard-target="#command"
                        data-clipboard-message="{{ trans('core/setting::cronjob.setup.copied') }}"
                    >
                        <x-core::icon name="ti ti-copy" />
                        {{ trans('core/setting::cronjob.copy_button') }}
                    </button>
                </div>
            </div>
        </x-core::card>

        {{-- Setup Instructions --}}
        <x-core::card class="mt-3">
            <x-core::card.header>
                <x-core::card.title>{{ trans('core/setting::cronjob.setup.name') }}</x-core::card.title>
            </x-core::card.header>
            <div class="card-body">
                <p class="text-muted mb-3">{{ trans('core/setting::cronjob.setup.choose_hosting') }}</p>

                {{-- Tabs for different hosting types --}}
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#cpanel-tab" type="button">
                            cPanel
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#plesk-tab" type="button">
                            Plesk
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#directadmin-tab" type="button">
                            DirectAdmin
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#ssh-tab" type="button">
                            SSH/Terminal
                        </button>
                    </li>
                </ul>

                <div class="tab-content mt-3">
                    {{-- cPanel Instructions --}}
                    <div class="tab-pane fade show active" id="cpanel-tab" role="tabpanel">
                        <div class="list-group list-group-flush">
                            @foreach (['step1', 'step2', 'step3', 'step4', 'step5'] as $index => $step)
                                <div class="list-group-item">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <span class="badge bg-blue-lt">{{ $index + 1 }}</span>
                                        </div>
                                        <div class="col">
                                            {!! BaseHelper::clean(trans("core/setting::cronjob.cpanel.{$step}")) !!}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Plesk Instructions --}}
                    <div class="tab-pane fade" id="plesk-tab" role="tabpanel">
                        <div class="list-group list-group-flush">
                            @foreach (['step1', 'step2', 'step3', 'step4', 'step5'] as $index => $step)
                                <div class="list-group-item">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <span class="badge bg-blue-lt">{{ $index + 1 }}</span>
                                        </div>
                                        <div class="col">
                                            {!! BaseHelper::clean(trans("core/setting::cronjob.plesk.{$step}")) !!}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- DirectAdmin Instructions --}}
                    <div class="tab-pane fade" id="directadmin-tab" role="tabpanel">
                        <div class="list-group list-group-flush">
                            @foreach (['step1', 'step2', 'step3', 'step4', 'step5'] as $index => $step)
                                <div class="list-group-item">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <span class="badge bg-blue-lt">{{ $index + 1 }}</span>
                                        </div>
                                        <div class="col">
                                            {!! BaseHelper::clean(trans("core/setting::cronjob.directadmin.{$step}")) !!}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- SSH Instructions --}}
                    <div class="tab-pane fade" id="ssh-tab" role="tabpanel">
                        <div class="list-group list-group-flush">
                            @foreach (['step1', 'step2', 'step3', 'step4', 'step5'] as $index => $step)
                                <div class="list-group-item">
                                    <div class="row align-items-center">
                                        <div class="col-auto">
                                            <span class="badge bg-blue-lt">{{ $index + 1 }}</span>
                                        </div>
                                        <div class="col">
                                            {!! BaseHelper::clean(trans("core/setting::cronjob.ssh.{$step}")) !!}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Help section --}}
                <div class="border-top mt-4 pt-3">
                    <p class="text-muted mb-0">
                        <x-core::icon name="ti ti-help" class="me-1" />
                        {!! BaseHelper::clean(trans('core/setting::cronjob.need_help')) !!}
                    </p>
                </div>
            </div>
        </x-core::card>
    </x-core-setting::section>
@stop
