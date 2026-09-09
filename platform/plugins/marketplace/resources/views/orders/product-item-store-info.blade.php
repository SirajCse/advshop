@if ($order->store?->name)
    <li class="ws-nm">
        <span class="bull">↳</span>
        <span class="black">{{ trans('plugins/marketplace::store.store') }}</span>
        <a
            class="fw-semibold text-decoration-underline"
            href="{{ $order->store->url }}"
            target="_blank"
        >{{ $order->store->name }}</a>
    </li>
@endif
