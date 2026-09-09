@php
    $title = theme_option('newsletter_popup_title');
    $desktopImage = theme_option('newsletter_popup_image');
    $tabletImage = theme_option('newsletter_popup_tablet_image') ?: $desktopImage;
    $mobileImage = theme_option('newsletter_popup_mobile_image') ?: $tabletImage;
    $hasImage = $desktopImage || $tabletImage || $mobileImage;
@endphp

<link
    rel="stylesheet"
    href="{{ asset('vendor/core/plugins/newsletter/css/newsletter.css') }}?v=1.4.0"
>

<div @class(['modal-dialog', 'modal-lg' => $hasImage])>
    <div @class([
        'modal-content border-0',
        'd-flex flex-md-col flex-lg-row' => $hasImage,
    ])>
        @if ($hasImage)
            <div class="col-md-6 newsletter-popup-bg">
                <picture>
                    @if ($desktopImage)
                        <source
                            srcset="{{ RvMedia::getImageUrl($desktopImage, null, false, RvMedia::getDefaultImage()) }}"
                            media="(min-width: 1200px)"
                        >
                    @endif
                    @if ($tabletImage)
                        <source
                            srcset="{{ RvMedia::getImageUrl($tabletImage, null, false, RvMedia::getDefaultImage()) }}"
                            media="(min-width: 768px)"
                        >
                    @endif
                    @if ($mobileImage)
                        <source
                            srcset="{{ RvMedia::getImageUrl($mobileImage, null, false, RvMedia::getDefaultImage()) }}"
                            media="(max-width: 767px)"
                        >
                    @endif
                    {!! RvMedia::image($mobileImage ?: $tabletImage ?: $desktopImage, $title, attributes: ['loading' => 'eager', 'class' => 'newsletter-popup-image']) !!}
                </picture>
            </div>
        @endif

        <button
            type="button"
            class="btn-close position-absolute"
            data-bs-dismiss="modal"
            data-dismiss="modal"
            aria-label="Close"
        ></button>

        <div class="newsletter-popup-content">
            <div class="modal-header flex-column align-items-start border-0 p-0">
                @if ($subtitle = theme_option('newsletter_popup_subtitle'))
                    <span class="modal-subtitle">{!! BaseHelper::clean($subtitle) !!}</span>
                @endif

                @if ($title)
                    <h5
                        class="modal-title fs-2"
                        id="newsletterPopupModalLabel"
                    >{!! BaseHelper::clean($title) !!}</h5>
                @endif

                @if ($description = theme_option('newsletter_popup_description'))
                    <p class="modal-text text-muted">{!! BaseHelper::clean($description) !!}</p>
                @endif
            </div>
            <div class="modal-body p-0">
                {!! $newsletterForm->setFormOption('class', 'bb-newsletter-popup-form')->renderForm() !!}
            </div>
        </div>
    </div>
</div>
