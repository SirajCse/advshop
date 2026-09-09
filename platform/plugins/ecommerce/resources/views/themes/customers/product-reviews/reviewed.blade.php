@if ($reviews->total() > 0)
    <div class="bb-customer-card-list">
        @foreach ($reviews as $item)
            <div class="bb-customer-card-content">
                <div class="bb-customer-card-image">
                    <img
                        src="{{ RvMedia::getImageUrl($item->product->image, 'thumb', false, RvMedia::getDefaultImage()) }}"
                        alt="{{ $item->product->name }}"
                        class="img-fluid"
                    >
                </div>
                <div class="bb-customer-card-details">
                    <div class="bb-customer-card-name">
                        <a href="{{ $item->product->url }}">{!! BaseHelper::clean($item->product->name) !!}</a>
                    </div>

                    <div class="bb-customer-card-meta">
                        <div class="d-flex align-items-center mb-2">
                            @for ($i = 1; $i <= 5; $i++)
                                <x-core::icon
                                    name="ti ti-star{{ $i <= $item->star ? '-filled' : '' }}"
                                    class="ecommerce-icon text-warning me-1"
                                />
                            @endfor
                        </div>

                        @if ($sku = $item->product->sku)
                            <span class="d-block text-muted">({{ $sku }})</span>
                        @endif

                        @if (is_plugin_active('marketplace') && $item->product->store?->id)
                            <span class="d-block">{{ trans('plugins/ecommerce::customer-dashboard.sold_by') }}: <a href="{{ $item->product->original_product->store->url }}" class="text-primary">{{ $item->product->store->name }}</a></span>
                        @endif

                        <span class="d-block text-muted">{{ trans('plugins/ecommerce::customer-dashboard.date') }}: {{ $item->created_at->translatedFormat('M d, Y h:m') }}</span>
                    </div>

                    <div class="bb-customer-card-description">
                        <p title="{{ $item->comment }}">{{ Str::limit($item->comment, 200) }}</p>
                    </div>

                    <div class="bb-customer-card-actions mt-2">
                        {!! Form::open([
                            'url' => route('public.reviews.destroy', $item->id),
                            'onSubmit' => 'return confirm("' . trans('plugins/ecommerce::customer-dashboard.do_you_really_want_to_delete_review') . '")',
                            'class' => 'd-inline'
                        ]) !!}
                            <input name="_method" type="hidden" value="DELETE">
                            <button class="btn btn-outline-danger btn-sm">{{ trans('plugins/ecommerce::customer-dashboard.delete') }}</button>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
            @if (!$loop->last)
                <hr class="my-3">
            @endif
        @endforeach
    </div>
@else
    <div class="text-center p-4">
        <div role="alert" class="alert alert-info">
            {{ trans('plugins/ecommerce::customer-dashboard.no_reviews') }}
        </div>
    </div>
@endif

<div class="pagination tp-pagination">
    {!! $reviews->links() !!}
</div>
