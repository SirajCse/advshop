<div {!! $shortcode->htmlAttributes() !!} class="tp-instagram-area pb-70">
    <div class="container">
        <div class="row align-items-center mb-40">
            <div class="col-xl-4 col-md-6">
                {!! Theme::partial('section-title', compact('shortcode')) !!}
            </div>
            <div class="col-xl-8 col-md-6">
                @if(($buttonLabel = $shortcode->button_label) && ($buttonUrl = $shortcode->button_url ?: route('public.galleries')))
                    <div class="tp-blog-more-wrapper d-flex justify-content-md-end">
                        <div class="tp-blog-more text-md-end">
                            <a href="{{ $buttonUrl }}" class="tp-btn tp-btn-2 tp-btn-blue">
                                {!! BaseHelper::clean($buttonLabel) !!}
                                <svg width="17" height="14" viewBox="0 0 17 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M16 6.99976L1 6.99976" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M9.9502 0.975414L16.0002 6.99941L9.9502 13.0244" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </a>
                            <span class="tp-blog-more-border"></span>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="tp-gallery-slider swiper-container">
            <div class="swiper-wrapper">
                @foreach($galleries as $gallery)
                    <div class="swiper-slide">
                        <div class="tp-instagram-item p-relative z-index-1 fix mb-30 w-img">
                            {{ RvMedia::image($gallery->image, $gallery->name, 'medium') }}
                            <div class="tp-instagram-icon">
                                <a href="{{ $gallery->url }}">
                                    {{ $gallery->name }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
