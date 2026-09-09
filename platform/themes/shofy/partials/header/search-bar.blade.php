<section class="tp-search-area">
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <div class="tp-search-form">
                    <div class="mb-20 text-center tp-search-close">
                        <button class="tp-search-close-btn" aria-label="{{ __('Close search') }}"></button>
                    </div>
                    @if(is_plugin_active('ecommerce'))
                        {!! Theme::partial('header.search-bar-ecommerce') !!}
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
