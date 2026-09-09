@if ($labels->isNotEmpty())
    @php
        $requestLabels = EcommerceHelper::parseFilterParams(request(), 'labels');
    @endphp
    <div class="bb-product-filter">
        <h4 class="bb-product-filter-title">{{ trans('plugins/ecommerce::products.form.labels') }}</h4>

        <div class="bb-product-filter-content">
            <ul class="bb-product-filter-items filter-checkbox">
                @foreach ($labels as $label)
                    <li class="bb-product-filter-item">
                        <input id="attribute-label-{{ $label->id }}" type="checkbox" name="labels[]" value="{{ $label->id }}" @checked(in_array($label->id, $requestLabels)) />
                        <label for="attribute-label-{{ $label->id }}">{{ $label->name }}</label>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
