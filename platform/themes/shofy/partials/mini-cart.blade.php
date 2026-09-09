<div class="cartmini__wrapper d-flex justify-content-between flex-column">
    <div class="cartmini__top-wrapper">
        <div class="cartmini__top p-relative">
            <div class="cartmini__top-title">
                <span class="cartmini__top-title-text">{{ __('Shopping cart') }}</span>
            </div>
            <div class="cartmini__close">
                <button type="button" class="cartmini__close-btn cartmini-close-btn" aria-label="{{ __('Close cart') }}">
                    <x-core::icon name="ti ti-x" />
                </button>
            </div>
        </div>

        @if ($ajax ?? false)
            {!! Theme::partial('mini-cart.content') !!}
        @else
            <div data-bb-toggle="mini-cart-content-slot"></div>
        @endif
    </div>

    @if ($ajax ?? false)
        {!! Theme::partial('mini-cart.footer') !!}
    @else
        <div data-bb-toggle="mini-cart-footer-slot"></div>
    @endif
</div>
