@php
    Gallery::registerAssets();
@endphp

<section class="section page-intro pt-100 pb-100 bg-cover">
    <div
        class="bg-overlay"
        style="opacity: 0.7"
    ></div>
    <div class="container">
        <h3 class="page-intro__title">{{ trans('plugins/gallery::gallery.galleries') }}</h3>
        {!! Theme::breadcrumb()->render() !!}
    </div>
</section>
<section class="section pt-50 pb-100">
    <div class="container">
        <div class="page-content">
            <article class="post post--single">
                <div class="post__content">
                    @if (isset($galleries) && $galleries->isNotEmpty())
                        <div class="gallery-wrap">
                            @foreach ($galleries as $gallery)
                                <div class="gallery-item">
                                    <div class="img-wrap">
                                        <a href="{{ $gallery->url }}">
                                            {{ RvMedia::image($gallery->image, $gallery->name, 'medium') }}
                                        </a>
                                    </div>
                                    <div class="gallery-detail">
                                        <div class="gallery-title">
                                            <a href="{{ $gallery->url }}">{{ $gallery->name }}</a>
                                        </div>
                                        @if (trim($gallery->user->name))
                                            <div class="gallery-author">
                                                {{ trans('plugins/gallery::gallery.by_name', ['name' => $gallery->user->name]) }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </article>
        </div>
    </div>
</section>
