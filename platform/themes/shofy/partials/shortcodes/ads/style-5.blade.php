<div {!! $shortcode->htmlAttributes() !!} class="tp-product-banner-area pt-30 pb-30">
    <div class="container">
        @foreach($ads as $ad)
            {!! Theme::partial('shortcodes.ads.includes.item', ['item' => $ad]) !!}
        @endforeach
    </div>
</div>
