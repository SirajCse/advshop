<section {!! $shortcode->htmlAttributes() !!} class="tp-brand-area pt-30 pb-30">
    <div class="container">
        <div class="row">
            <div class="col-xl-12">
                <div class="tp-brand-slider p-relative">
                    <div
                        class="tp-brand-slider-active swiper-container"
                        data-loop="{{ $shortcode->is_loop == 'yes' }}"
                        data-autoplay="{{ $shortcode->is_autoplay == 'yes' }}"
                        data-autoplay-speed="{{ in_array($shortcode->autoplay_speed, [2000, 3000, 4000, 5000, 6000, 7000, 8000, 9000, 10000]) ? $shortcode->autoplay_speed : 5000 }}"
                    >
                        <div class="swiper-wrapper">
                            @switch($shortcode->type)
                                @case('custom')
                                    @foreach($tabs as $tab)
                                        @continue(! $tab['image'])

                                        <div class="tp-brand-item swiper-slide text-center">
                                            <a href="{{ $tab['url'] }}">
                                                {{ RvMedia::image($tab['image'], $tab['name']) }}
                                            </a>
                                        </div>
                                    @endforeach

                                    @break

                                @case('brands')
                                    @foreach($brands as $brand)
                                        <div class="tp-brand-item swiper-slide text-center">
                                            <a href="{{ $brand->url }}">
                                                {{ RvMedia::image($brand->logo, $brand->name) }}
                                            </a>
                                        </div>
                                    @endforeach

                                    @break
                            @endswitch
                        </div>
                    </div>
                    <div class="tp-brand-slider-arrow">
                        <button class="tp-brand-slider-button-prev" aria-label="{{ __('Previous') }}">
                            <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M7 1L1 7L7 13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                        <button class="tp-brand-slider-button-next" aria-label="{{ __('Next') }}">
                            <svg width="8" height="14" viewBox="0 0 8 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M1 1L7 7L1 13" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
