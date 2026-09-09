@php
    $credentials = setting('analytics_service_account_credentials');
    $propertyId = setting('analytics_property_id');
    $hasCredentials = !empty($credentials) && !empty($propertyId);
    $clientEmail = null;
    $projectId = null;

    if ($credentials) {
        try {
            $decoded = json_decode($credentials, true);
            $clientEmail = $decoded['client_email'] ?? null;
            $projectId = $decoded['project_id'] ?? null;
        } catch (\Exception $e) {}
    }
@endphp

<div class="google-analytics-settings-info mt-4">
    <div class="row g-4">
        {{-- Left Column: Status & Testing --}}
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <x-core::icon name="ti ti-chart-bar" class="me-2" />
                        {{ trans('plugins/analytics::analytics.settings.status') }}
                    </h4>
                </div>
                <div class="card-body">
                    {{-- Configuration Status --}}
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-4">
                            <div class="border rounded p-3 text-center h-100">
                                <div class="text-muted small mb-1">{{ trans('plugins/analytics::analytics.settings.property_id_status') }}</div>
                                <div class="h5 mb-0 {{ $propertyId ? 'text-success' : 'text-warning' }}">
                                    @if($propertyId)
                                        <x-core::icon name="ti ti-check" class="me-1" />
                                        {{ trans('plugins/analytics::analytics.settings.configured') }}
                                    @else
                                        <x-core::icon name="ti ti-alert-triangle" class="me-1" />
                                        {{ trans('plugins/analytics::analytics.settings.not_configured') }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="border rounded p-3 text-center h-100">
                                <div class="text-muted small mb-1">{{ trans('plugins/analytics::analytics.settings.credentials_status') }}</div>
                                <div class="h5 mb-0 {{ $clientEmail ? 'text-success' : 'text-warning' }}">
                                    @if($clientEmail)
                                        <x-core::icon name="ti ti-check" class="me-1" />
                                        {{ trans('plugins/analytics::analytics.settings.configured') }}
                                    @else
                                        <x-core::icon name="ti ti-alert-triangle" class="me-1" />
                                        {{ trans('plugins/analytics::analytics.settings.not_configured') }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="border rounded p-3 text-center h-100">
                                <div class="text-muted small mb-1">{{ trans('plugins/analytics::analytics.settings.dashboard_widgets_status') }}</div>
                                <div class="h5 mb-0 {{ setting('analytics_dashboard_widgets') ? 'text-success' : 'text-muted' }}">
                                    @if(setting('analytics_dashboard_widgets'))
                                        <x-core::icon name="ti ti-check" class="me-1" />
                                        {{ trans('plugins/analytics::analytics.settings.enabled') }}
                                    @else
                                        <x-core::icon name="ti ti-x" class="me-1" />
                                        {{ trans('plugins/analytics::analytics.settings.disabled') }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Test Connection --}}
                    <div class="d-flex align-items-center gap-2 mb-4 pb-4 border-bottom">
                        <button type="button" class="btn btn-outline-info" id="test-analytics-connection" {{ !$hasCredentials ? 'disabled' : '' }}>
                            <x-core::icon name="ti ti-plug" class="me-1" />
                            {{ trans('plugins/analytics::analytics.settings.test_connection') }}
                        </button>
                        <span id="analytics-connection-result"></span>
                    </div>

                    {{-- Current Property Info --}}
                    @if($propertyId)
                        <div class="mb-3">
                            <label class="form-label small text-muted mb-1">{{ trans('plugins/analytics::analytics.settings.current_property_id') }}</label>
                            <div class="d-flex align-items-center gap-2">
                                <code class="flex-grow-1 p-2 bg-light rounded small">{{ $propertyId }}</code>
                                <x-core::copy :copyable-state="$propertyId" />
                            </div>
                        </div>
                    @endif

                    @if($clientEmail)
                        <div class="mb-3">
                            <label class="form-label small text-muted mb-1">{{ trans('plugins/analytics::analytics.settings.service_account_email') }}</label>
                            <div class="d-flex align-items-center gap-2">
                                <code class="flex-grow-1 p-2 bg-light rounded small text-break">{{ $clientEmail }}</code>
                                <x-core::copy :copyable-state="$clientEmail" />
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right Column: Setup Instructions --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-primary-lt">
                    <h4 class="card-title mb-0">
                        <x-core::icon name="ti ti-settings" class="me-2" />
                        {{ trans('plugins/analytics::analytics.settings.setup_instructions') }}
                    </h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <p class="small text-muted mb-2">{{ trans('plugins/analytics::analytics.settings.setup_steps') }}:</p>
                        <ol class="small ps-3 mb-0">
                            <li class="mb-1">{{ trans('plugins/analytics::analytics.settings.step_1') }}</li>
                            <li class="mb-1">{{ trans('plugins/analytics::analytics.settings.step_2') }}</li>
                            <li class="mb-1">{{ trans('plugins/analytics::analytics.settings.step_3') }}</li>
                            <li class="mb-1">{{ trans('plugins/analytics::analytics.settings.step_4') }}</li>
                            <li>{{ trans('plugins/analytics::analytics.settings.step_5') }}</li>
                        </ol>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="https://console.cloud.google.com/apis/credentials" target="_blank" class="btn btn-primary btn-sm">
                            <x-core::icon name="ti ti-external-link" class="me-1" />
                            {{ trans('plugins/analytics::analytics.settings.open_cloud_console') }}
                        </a>
                        <a href="https://analytics.google.com/analytics/web/" target="_blank" class="btn btn-outline-secondary btn-sm">
                            <x-core::icon name="ti ti-chart-line" class="me-1" />
                            {{ trans('plugins/analytics::analytics.settings.open_analytics') }}
                        </a>
                        @if($projectId)
                            <a href="https://console.cloud.google.com/apis/api/analyticsdata.googleapis.com/overview?project={{ $projectId }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                                <x-core::icon name="ti ti-api" class="me-1" />
                                {{ trans('plugins/analytics::analytics.settings.enable_analytics_api') }}
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="small text-muted">
                        <x-core::icon name="ti ti-info-circle" class="me-1" />
                        {{ trans('plugins/analytics::analytics.settings.api_info') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    function initAnalyticsTests() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const testConnectionBtn = document.getElementById('test-analytics-connection');

        if (testConnectionBtn && !testConnectionBtn.dataset.initialized) {
            testConnectionBtn.dataset.initialized = 'true';
            testConnectionBtn.addEventListener('click', function() {
                const btn = this;
                const result = document.getElementById('analytics-connection-result');
                btn.disabled = true;
                result.innerHTML = '<span class="text-muted">{{ trans('plugins/analytics::analytics.settings.testing') }}</span>';

                fetch('{{ route("analytics.settings.test-connection") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    result.innerHTML = data.success
                        ? '<span class="text-success"><i class="ti ti-check"></i> ' + data.message + '</span>'
                        : '<span class="text-danger"><i class="ti ti-x"></i> ' + data.message + '</span>';
                })
                .catch(e => result.innerHTML = '<span class="text-danger">Error: ' + e.message + '</span>')
                .finally(() => btn.disabled = false);
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAnalyticsTests);
    } else {
        initAnalyticsTests();
    }
})();
</script>
