@if ($order->store?->name)
    <div class="hr my-1"></div>

    <div class="p-3">
        <h4 class="mb-2">{{ trans('plugins/marketplace::store.store') }}</h4>
        <a href="{{ $order->store->url }}" target="_blank">{{ $order->store->name }}</a>
    </div>
@endif
