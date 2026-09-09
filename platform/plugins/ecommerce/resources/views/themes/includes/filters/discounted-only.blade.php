<div class="bb-product-filter">
    <div class="bb-product-filter-attribute-item">
        <h4 class="bb-product-filter-title">{{ trans('plugins/ecommerce::ecommerce.on_sale') }}</h4>
        <div class="bb-product-filter-content">
            <ul class="bb-product-filter-items filter-checkbox">
                <li class="bb-product-filter-item">
                    <input
                        id="discounted_only"
                        name="discounted_only"
                        type="checkbox"
                        value="1"
                        @if (request()->input('discounted_only') == 1) checked @endif
                        data-bb-toggle="product-form-filter-item"
                    >
                    <label for="discounted_only" style="line-height: 20px;">{{ trans('plugins/ecommerce::products.show_only_discounted_products') }}</label>
                </li>
            </ul>
        </div>
    </div>
</div>
