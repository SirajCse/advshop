{{--
    Standalone product-picker modal — pure CSS + vanilla JS, no Bootstrap dependency.
    Lets the user search for products, browse a paginated grid, or paste a product URL.
    Single instance per page; opened by every empty slot via data-bb-toggle="compare-open-picker".
--}}
<div
    class="compare-picker"
    data-bb-toggle="compare-picker"
    data-search-url="{{ route('public.compare.search-products') }}"
    data-add-by-url="{{ route('public.compare.add-by-url') }}"
    data-loading-text="{{ trans('plugins/ecommerce::products.compare.picker_loading') }}"
    data-empty-text="{{ trans('plugins/ecommerce::products.compare.picker_empty') }}"
    aria-hidden="true"
>
    <div class="compare-picker-backdrop" data-bb-toggle="compare-picker-close" aria-hidden="true"></div>

    <div
        class="compare-picker-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="compare-picker-title"
        tabindex="-1"
    >
        <header class="compare-picker-head">
            <h2 id="compare-picker-title" class="compare-picker-title">
                {{ trans('plugins/ecommerce::products.compare.picker_title') }}
            </h2>
            <button
                type="button"
                class="compare-picker-close"
                data-bb-toggle="compare-picker-close"
                aria-label="{{ trans('plugins/ecommerce::ecommerce.close') ?? 'Close' }}"
            >
                <x-core::icon name="ti ti-x" />
            </button>
        </header>

        <div class="compare-picker-search">
            <input
                type="search"
                class="compare-picker-search-input"
                data-bb-toggle="compare-picker-search"
                placeholder="{{ trans('plugins/ecommerce::products.compare.picker_search_placeholder') }}"
                aria-label="{{ trans('plugins/ecommerce::products.compare.picker_search_placeholder') }}"
                autocomplete="off"
            >
            <span class="compare-picker-search-icon" aria-hidden="true">
                <x-core::icon name="ti ti-search" />
            </span>
        </div>

        <div class="compare-picker-body" data-bb-toggle="compare-picker-body">
            <div class="compare-picker-grid" data-bb-toggle="compare-picker-grid"></div>

            <div class="compare-picker-status" data-bb-toggle="compare-picker-status" aria-live="polite"></div>

            <button
                type="button"
                class="compare-picker-load-more"
                data-bb-toggle="compare-picker-load-more"
                hidden
            >
                {{ trans('plugins/ecommerce::products.compare.load_more') }}
            </button>
        </div>

        <footer class="compare-picker-foot">
            <details class="compare-picker-url">
                <summary class="compare-picker-url-summary">
                    <x-core::icon name="ti ti-link" />
                    {{ trans('plugins/ecommerce::products.compare.paste_url_alt') }}
                </summary>
                <form
                    class="compare-picker-url-form mt-2"
                    data-bb-toggle="compare-picker-url-form"
                    action="{{ route('public.compare.add-by-url') }}"
                    method="POST"
                >
                    @csrf
                    <div class="input-group">
                        <input
                            type="url"
                            name="url"
                            class="form-control"
                            placeholder="{{ trans('plugins/ecommerce::products.compare.paste_url_placeholder') }}"
                            required
                            maxlength="2048"
                            autocomplete="off"
                        >
                        <button type="submit" class="btn btn-primary">
                            {{ trans('plugins/ecommerce::products.compare.add_button') }}
                        </button>
                    </div>
                </form>
            </details>
        </footer>
    </div>

    {{-- Card template; rendered into JS-managed grid clones. --}}
    <template data-bb-toggle="compare-picker-card-template">
        <article class="compare-picker-card">
            <a class="compare-picker-card-thumb" href="#" tabindex="-1">
                <img alt="" loading="lazy">
            </a>
            <h3 class="compare-picker-card-title">
                <a href="#"></a>
            </h3>
            <div class="compare-picker-card-prices">
                <span class="compare-picker-card-price"></span>
                <span class="compare-picker-card-price-old"></span>
            </div>
            <button
                type="button"
                class="compare-picker-card-select btn btn-primary"
                data-bb-toggle="compare-picker-select"
            >
                <x-core::icon name="ti ti-plus" />
                {{ trans('plugins/ecommerce::products.compare.choose_to_compare') }}
            </button>
        </article>
    </template>
</div>
