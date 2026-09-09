<section {!! $shortcode->htmlAttributes() !!} class="tp-stores-area">
    <div class="container">
        @if($shortcode->title || $shortcode->subtitle)
            <div class="mb-40">
                {!! Theme::partial('section-title', compact('shortcode')) !!}
            </div>
        @endif

        <div class="row g-4 mb-40">
            @foreach ($stores as $store)
                @php
                    $coverImage = $store->getMetaData('background', true);
                @endphp

                <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6">
                    @include('plugins/marketplace::themes.includes.store-item')
                </div>
            @endforeach
        </div>
    </div>
</section>
