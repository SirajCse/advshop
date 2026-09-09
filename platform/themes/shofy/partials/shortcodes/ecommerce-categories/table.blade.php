<section {!! $shortcode->htmlAttributes() !!} class="tp-category-area pt-60 pb-15">
    <div class="container">
        {!! Theme::partial('section-title', array_merge(compact('shortcode'), ['class' => 'mb-35'])) !!}

        <div class="tp-category-table-grid">
            @foreach ($categories as $category)
                <a href="{{ $category->url }}" title="{{ $category->name }}" class="tp-category-table-item">
                    <div class="tp-category-table-thumb">
                        {{ RvMedia::image($category->{$imageField}, $category->name, attributes: ['loading' => 'lazy']) }}
                    </div>
                    <h3 class="tp-category-table-title">{{ $category->name }}</h3>
                </a>
            @endforeach
        </div>
    </div>
</section>
