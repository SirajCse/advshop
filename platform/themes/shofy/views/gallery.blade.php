@php
    Theme::layout('full-width');
    Theme::set('pageTitle', $gallery->name);
@endphp

<section @class(['pb-100',  'pt-50' => ! theme_option('theme_breadcrumb_enabled', true)])>
    <div class="container">
        <div>
            {!! BaseHelper::clean($gallery->description) !!}
        </div>
        <div class="row row-cols-2 row-cols-md-4 g-3" id="list-photo">
            @foreach (gallery_meta_data($gallery) as $image)
                @continue(! $image)

                <div data-src="{{ RvMedia::getImageUrl($imageUrl = Arr::get($image, 'img')) }}" data-sub-html="{{ $description = BaseHelper::clean(Arr::get($image, 'description')) }}">
                    <a href="{{ $description }}">
                        {{ RvMedia::image($imageUrl, \Theme\Shofy\Helpers\MediaAltResolver::resolve($imageUrl, $description), 'medium', attributes: ['class' => 'rounded-3 w-100']) }}
                    </a>
                </div>
            @endforeach
        </div>

        {!! apply_filters(BASE_FILTER_PUBLIC_COMMENT_AREA, null, $gallery) !!}
    </div>
</section>
