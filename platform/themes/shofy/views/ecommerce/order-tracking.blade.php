@php
    Theme::layout('full-width');
@endphp

<section class="tp-order-area pb-160 @if (! theme_option('theme_breadcrumb_enabled', true)) pt-100 @else pt-60 @endif">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="tp-order-inner">
                    <div class="tp-order-info-wrapper">
                        {!! $form->renderForm() !!}
                    </div>
                </div>

                <br>
                @include(EcommerceHelper::viewPath('includes.order-tracking-detail'))
            </div>
        </div>
    </div>
</section>
