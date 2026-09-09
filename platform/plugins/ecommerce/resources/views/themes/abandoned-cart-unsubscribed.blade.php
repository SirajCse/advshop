<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-body py-5">
                        @if($success)
                            <div class="mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-success"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            </div>
                            <h2 class="card-title mb-3">{{ __('Successfully Unsubscribed') }}</h2>
                            <p class="card-text text-muted mb-4">
                                {{ __('You have been unsubscribed from abandoned cart reminder emails. You will no longer receive these notifications.') }}
                            </p>
                        @else
                            <div class="mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-danger"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                            </div>
                            <h2 class="card-title mb-3">{{ __('Link Expired or Invalid') }}</h2>
                            <p class="card-text text-muted mb-4">
                                {{ __('The unsubscribe link you used is either invalid or has already been used. If you continue to receive unwanted emails, please contact our support team.') }}
                            </p>
                        @endif
                        <a href="{{ route('public.index') }}" class="btn btn-primary">
                            {{ __('Return to Homepage') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
