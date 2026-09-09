<div class="col-xl-4 col-lg-3 col-md-4 col-sm-6">
    <div class="tp-footer-widget footer-col-1 mb-50">
        <div class="tp-footer-widget-content">
            <div class="tp-footer-logo">
                @if ($logo = $config['logo'] ?: Theme::getLogo())
                <a href="{{ BaseHelper::getHomepageUrl() }}">
                    {{ RvMedia::image($logo, Theme::getSiteTitle(), attributes: $attributes) }}
                </a>
                @endif
            </div>
            <div class="tp-footer-desc">
                {!! BaseHelper::clean(nl2br($config['about'])) !!}
            </div>
            @if($config['show_social_links'] && $socialLinks = Theme::getSocialLinks())
                <div class="tp-footer-social">
                    @foreach($socialLinks as $socialLink)
                        @continue(! $socialLink->getUrl() || ! $socialLink->getIconHtml())

                        <a {!! $socialLink->getAttributes() !!}>{{ $socialLink->getIconHtml() }}</a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
