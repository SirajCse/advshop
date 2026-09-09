<section class="section--blog">
    <div class="section__content">
        <section class="section--auth">
            <div class="form__header">
                <h3>{{ trans('plugins/ecommerce::products.download_product_with_external_links', ['name' => $orderProduct->product_name]) }}
                </h3>
                <p>{{ trans('plugins/ecommerce::ecommerce.you_can_now_download_it_by_clicking_the_links_belo') }}</p>
            </div>
            <ol class="list-group list-group-numbered list-group-flush">
                @foreach ($externalProductFiles as $productFile)
                    <li class="list-group-item">
                        <a
                            href="{{ $productFile->url }}"
                            target="_blank"
                        >{{ $productFile->file_name ?: $productFile->url }}</a>
                    </li>
                @endforeach
            </ol>
        </section>
    </div>
</section>
