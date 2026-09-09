<div class="row mb-3 mt-5 g-2">
    <x-core::stat-widget.item
        label="{{ trans('plugins/marketplace::marketplace.orders') }}"
        :value="$data['orders']->count()"
        icon="ti ti-shopping-cart"
        color="primary"
        :url="route('marketplace.vendor.orders.index')"
        column="col-12 col-sm-6 col-md-3"
    />

    <x-core::stat-widget.item
        label="{{ trans('plugins/marketplace::marketplace.revenues') }}"
        :value="format_price($data['revenue']['amount'])"
        icon="ti ti-cash"
        color="success"
        :url="route('marketplace.vendor.revenues.index')"
        column="col-12 col-sm-6 col-md-3"
    />

    <x-core::stat-widget.item
        label="{{ trans('plugins/marketplace::marketplace.products') }}"
        :value="$totalProducts"
        icon="ti ti-package"
        color="info"
        :url="route('marketplace.vendor.products.index')"
        column="col-12 col-sm-6 col-md-3"
    />

    <x-core::stat-widget.item
        label="{{ trans('plugins/marketplace::marketplace.earnings') }}"
        :value="format_price($data['revenue']['sub_amount'])"
        icon="ti ti-wallet"
        color="warning"
        :url="route('marketplace.vendor.revenues.index')"
        column="col-12 col-sm-6 col-md-3"
    />
</div>

<div class="row g-2 mb-3">
    <div class="col-12 col-md-4 order-md-1">
        <x-core::card>
            <x-core::card.header>
                <x-core::card.title>
                    <x-core::icon name="ti ti-bolt" />
                    {{ trans('plugins/marketplace::marketplace.quick_actions') }}
                </x-core::card.title>
            </x-core::card.header>
            <x-core::card.body>
                <div class="row g-2">
                    <div class="col-6">
                        @if (MarketplaceHelper::isVendorDigitalProductsEnabled() && ! EcommerceHelper::isDisabledPhysicalProduct())
                            <div class="dropdown w-100">
                                <button
                                    class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center gap-1 text-nowrap dropdown-toggle"
                                    data-bs-toggle="dropdown"
                                    type="button"
                                >
                                    <x-core::icon name="ti ti-plus" />
                                    <span class="d-none d-sm-inline text-truncate">{{ trans('plugins/marketplace::marketplace.add_product') }}</span>
                                    <span class="d-inline d-sm-none text-truncate">{{ trans('plugins/marketplace::marketplace.product') }}</span>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('marketplace.vendor.products.create') }}">
                                        <x-core::icon name="ti ti-package" />
                                        {{ \Botble\Ecommerce\Enums\ProductTypeEnum::PHYSICAL()->label() }}
                                    </a>
                                    <a class="dropdown-item" href="{{ route('marketplace.vendor.products.create', ['product_type' => 'digital']) }}">
                                        <x-core::icon name="ti ti-book-download" />
                                        {{ \Botble\Ecommerce\Enums\ProductTypeEnum::DIGITAL()->label() }}
                                    </a>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('marketplace.vendor.products.create') }}" class="btn btn-outline-primary w-100 d-flex align-items-center justify-content-center gap-1 text-nowrap">
                                <x-core::icon name="ti ti-plus" />
                                <span class="d-none d-sm-inline text-truncate">{{ trans('plugins/marketplace::marketplace.add_product') }}</span>
                                <span class="d-inline d-sm-none text-truncate">{{ trans('plugins/marketplace::marketplace.product') }}</span>
                            </a>
                        @endif
                    </div>
                    <div class="col-6">
                        <a href="{{ route('marketplace.vendor.discounts.create') }}" class="btn btn-outline-info w-100 d-flex align-items-center justify-content-center gap-1 text-nowrap">
                            <x-core::icon name="ti ti-discount-2" />
                            <span class="d-none d-sm-inline text-truncate">{{ trans('plugins/marketplace::marketplace.create_discount') }}</span>
                            <span class="d-inline d-sm-none text-truncate">{{ trans('plugins/ecommerce::discount.name') }}</span>
                        </a>
                    </div>
                    <div class="col-6 mt-2">
                        <a href="{{ route('marketplace.vendor.settings') }}" class="btn btn-outline-warning w-100 d-flex align-items-center justify-content-center gap-1 text-nowrap">
                            <x-core::icon name="ti ti-settings" />
                            <span class="d-none d-sm-inline text-truncate">{{ trans('plugins/marketplace::marketplace.store_settings') }}</span>
                            <span class="d-inline d-sm-none text-truncate">{{ trans('plugins/marketplace::marketplace.settings.title') }}</span>
                        </a>
                    </div>
                    <div class="col-6 mt-2">
                        <a href="{{ route('marketplace.vendor.withdrawals.create') }}" class="btn btn-outline-success w-100 d-flex align-items-center justify-content-center gap-1 text-nowrap">
                            <x-core::icon name="ti ti-cash-banknote" />
                            <span class="d-none d-sm-inline text-truncate">{{ trans('plugins/marketplace::marketplace.withdrawals') }}</span>
                            <span class="d-inline d-sm-none text-truncate">{{ trans('plugins/marketplace::marketplace.withdraw') }}</span>
                        </a>
                    </div>
                </div>
            </x-core::card.body>
        </x-core::card>
    </div>

    <div class="col-12 col-md-8 order-md-0">
        <x-core::card>
            <x-core::card.header>
                <x-core::card.title>
                    <x-core::icon name="ti ti-building-store" />
                    {{ trans('plugins/marketplace::marketplace.store_performance') }}
                </x-core::card.title>
            </x-core::card.header>
            <x-core::card.body>
                <div class="row g-2">
                    <x-core::datagrid.item class="col-6 mb-2">
                        <x-slot:title>
                            <x-core::icon name="ti ti-shopping-cart-check" />
                            {{ trans('plugins/marketplace::marketplace.conversion_rate') }}
                        </x-slot:title>
                        {{ $totalOrders ? number_format(($totalOrders / max(1, $totalProducts)) * 100, 1) . '%' : '0%' }}
                    </x-core::datagrid.item>

                    <x-core::datagrid.item class="col-6 mb-2">
                        <x-slot:title>
                            <x-core::icon name="ti ti-receipt" />
                            {{ trans('plugins/marketplace::marketplace.avg_order_value') }}
                        </x-slot:title>
                        {{ $totalOrders ? format_price($data['revenue']['amount'] / max(1, $totalOrders)) : format_price(0) }}
                    </x-core::datagrid.item>

                    <x-core::datagrid.item class="col-6">
                        <x-slot:title>
                            <x-core::icon name="ti ti-truck-delivery" />
                            {{ trans('plugins/marketplace::marketplace.fulfillment_rate') }}
                        </x-slot:title>
                        {{ $totalOrders ? '100%' : '0%' }}
                    </x-core::datagrid.item>

                    <x-core::datagrid.item class="col-6">
                        <x-slot:title>
                            <x-core::icon name="ti ti-eye" />
                            {{ trans('plugins/marketplace::marketplace.store_visibility') }}
                        </x-slot:title>
                        <span class="badge text-bg-success text-white">{{ trans('plugins/marketplace::marketplace.active') }}</span>
                    </x-core::datagrid.item>
                </div>
            </x-core::card.body>
        </x-core::card>
    </div>
</div>

@if (!$totalProducts)
    <div class="row g-2 mb-3">
        <div class="col-12">
            <svg
                style="display: none;"
                xmlns="http://www.w3.org/2000/svg"
            >
                <symbol
                    id="check-circle-fill"
                    fill="currentColor"
                    viewBox="0 0 16 16"
                >
                    <path
                        d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"
                    />
                </symbol>
            </svg>
            <div
                class="alert alert-success d-block"
                role="alert"
            >
                <h4 class="alert-heading">
                    <svg
                        class="bi flex-shrink-0 me-2"
                        role="img"
                        aria-label="Info:"
                        width="24"
                        height="24"
                    >
                        <use xlink:href="#check-circle-fill" />
                    </svg>
                    {{ trans('plugins/marketplace::marketplace.congratulations_vendor', ['site_title' => Theme::getSiteTitle()]) }}
                </h4>
                <p>{{ trans('plugins/marketplace::marketplace.attract_customers_message') }}</p>
                <hr>
                @if (MarketplaceHelper::isVendorDigitalProductsEnabled() && ! EcommerceHelper::isDisabledPhysicalProduct())
                    <p class="mb-2">{{ trans('plugins/marketplace::marketplace.choose_product_type_to_create') }}</p>
                    <div class="d-flex gap-2 flex-wrap">
                        <a href="{{ route('marketplace.vendor.products.create') }}" class="btn btn-primary">
                            <x-core::icon name="ti ti-package" />
                            {{ \Botble\Ecommerce\Enums\ProductTypeEnum::PHYSICAL()->label() }}
                        </a>
                        <a href="{{ route('marketplace.vendor.products.create', ['product_type' => 'digital']) }}" class="btn btn-info">
                            <x-core::icon name="ti ti-book-download" />
                            {{ \Botble\Ecommerce\Enums\ProductTypeEnum::DIGITAL()->label() }}
                        </a>
                    </div>
                @else
                    <p class="mb-0">{!! BaseHelper::clean(trans('plugins/marketplace::marketplace.create_new_product_here', ['url' => route('marketplace.vendor.products.create')])) !!}</p>
                @endif
            </div>
        </div>
    </div>
@elseif (!$totalOrders)
    <div class="row g-2 mb-3">
        <div class="col-12">
            <svg
                style="display: none;"
                xmlns="http://www.w3.org/2000/svg"
            >
                <symbol
                    id="info-fill"
                    fill="currentColor"
                    viewBox="0 0 16 16"
                >
                    <path
                        d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"
                    />
                </symbol>
            </svg>
            <div
                class="alert alert-info d-block"
                role="alert"
            >
                <h4 class="alert-heading">
                    <svg
                        class="bi flex-shrink-0 me-2"
                        role="img"
                        aria-label="Info:"
                        width="24"
                        height="24"
                    >
                        <use xlink:href="#info-fill" />
                    </svg>
                    {{ trans('plugins/marketplace::marketplace.no_orders_yet_message', ['total' => $totalProducts]) }}
                </h4>
                <hr>
                <p class="mb-0">{!! BaseHelper::clean(trans('plugins/marketplace::marketplace.view_your_store_here', ['url' => $user->store->url])) !!}</p>
            </div>
        </div>
    </div>
@else
    <div class="row g-2 mb-3">
        <div class="col-12 col-md-8">
            <x-core::card class="mb-3">
                <x-core::card.header>
                    <div>
                        <x-core::card.title>
                            <x-core::icon name="ti ti-chart-line" />
                            {{ trans('plugins/marketplace::marketplace.sales_reports') }}
                        </x-core::card.title>
                        <x-core::card.subtitle>
                            <a href="{{ route('marketplace.vendor.revenues.index') }}">
                                {{ trans('plugins/marketplace::marketplace.revenues_in_label', ['label' => $data['predefinedRange']]) }}
                                <x-core::icon name="ti ti-arrow-right" />
                            </a>
                        </x-core::card.subtitle>
                    </div>
                </x-core::card.header>
                <x-core::table.body>
                    <div id="sales-report-chart">
                        <sales-reports-chart
                            url="{{ route('marketplace.vendor.chart.month') }}"
                            date_from='{{ $data['startDate']->format('Y-m-d') }}'
                            date_to='{{ $data['endDate']->format('Y-m-d') }}'
                        ></sales-reports-chart>
                    </div>
                </x-core::table.body>
            </x-core::card>
        </div>

        <div class="col-12 col-md-4">
            <x-core::card>
                <x-core::card.header>
                    <div>
                        <x-core::card.title>
                            <x-core::icon name="ti ti-coin" />
                            {{ trans('plugins/marketplace::marketplace.earnings') }}
                        </x-core::card.title>
                        <x-core::card.subtitle>{{ trans('plugins/marketplace::marketplace.earnings_in_label', ['label' => $data['predefinedRange']]) }}</x-core::card.subtitle>
                    </div>
                </x-core::card.header>
                <x-core::card.body>
                    <div id="revenue-chart">
                        <revenue-chart
                            :data="{{ json_encode([
                                    ['label' => trans('plugins/marketplace::marketplace.revenues'), 'value' => $data['revenue']['amount'], 'color' => '#80bc00'],
                                    ['label' => trans('plugins/marketplace::marketplace.fees'), 'value' => $data['revenue']['fee'], 'color' => '#fcb800'],
                                    ['label' => trans('plugins/marketplace::marketplace.withdrawals'), 'value' => $data['revenue']['withdrawal'], 'color' => '#fc6b00'],
                                ]) }}"
                        ></revenue-chart>
                    </div>

                    <div class="row mt-4 g-2">
                        <x-core::datagrid.item class="col-6 mb-2">
                            <x-slot:title>
                                <x-core::icon name="ti ti-wallet"></x-core::icon>
                                {{ trans('plugins/marketplace::marketplace.earnings') }}
                            </x-slot:title>
                            {{ format_price($data['revenue']['sub_amount']) }}
                        </x-core::datagrid.item>

                        <x-core::datagrid.item class="col-6 mb-2">
                            <x-slot:title>
                                {{ trans('plugins/marketplace::marketplace.revenues') }}
                            </x-slot:title>
                            {{ format_price($data['revenue']['sub_amount'] - $data['revenue']['fee']) }}
                        </x-core::datagrid.item>

                        <x-core::datagrid.item class="col-6">
                            <x-slot:title>
                                    <span
                                        data-bs-toggle="tooltip"
                                        data-bs-original-title="{{ trans('plugins/marketplace::marketplace.includes_order_statuses') }}"
                                    >
                                        {{ trans('plugins/marketplace::marketplace.withdrawals') }}
                                    </span>
                            </x-slot:title>
                            {{ format_price($data['revenue']['withdrawal']) }}
                        </x-core::datagrid.item>

                        <x-core::datagrid.item class="col-6">
                            <x-slot:title>
                                {{ trans('plugins/marketplace::marketplace.fees') }}
                            </x-slot:title>
                            {{ format_price($data['revenue']['fee']) }}
                        </x-core::datagrid.item>
                    </div>
                </x-core::card.body>
            </x-core::card>
        </div>
    </div>
@endif

<div class="row g-2">
    @if ($totalOrders)
        <div class="col-12 col-md-8">
            <x-core::card class="mb-3">
                <x-core::card.header>
                    <x-core::card.title>
                        <x-core::icon name="ti ti-shopping-cart" />
                        {{ trans('plugins/marketplace::marketplace.recent_orders') }}
                    </x-core::card.title>
                </x-core::card.header>

                <div class="table-responsive">
                    <x-core::table class="table-striped">
                        <x-core::table.header>
                            <x-core::table.header.cell>{{ trans('plugins/ecommerce::order.order') }}</x-core::table.header.cell>
                            <x-core::table.header.cell class="d-none d-md-table-cell">{{ trans('plugins/marketplace::marketplace.tables.date') }}</x-core::table.header.cell>
                            <x-core::table.header.cell class="d-none d-md-table-cell">{{ trans('plugins/ecommerce::order.customer_label') }}</x-core::table.header.cell>
                            <x-core::table.header.cell>{{ trans('core/base::tables.status') }}</x-core::table.header.cell>
                            <x-core::table.header.cell>{{ trans('plugins/ecommerce::order.total') }}</x-core::table.header.cell>
                        </x-core::table.header>
                        <x-core::table.body>
                            @forelse ($data['orders'] as $order)
                                <x-core::table.body.row>
                                    <x-core::table.body.cell>
                                        <a href="{{ route('marketplace.vendor.orders.edit', $order->id) }}" class="fw-semibold text-decoration-none">
                                            {{ get_order_code($order->id) }}
                                        </a>
                                        <div class="d-md-none small text-muted">
                                            {{ $order->created_at->translatedFormat('M d, Y') }}
                                        </div>
                                    </x-core::table.body.cell>
                                    <x-core::table.body.cell class="d-none d-md-table-cell">
                                        {{ $order->created_at->translatedFormat('M d, Y') }}
                                    </x-core::table.body.cell>
                                    <x-core::table.body.cell class="d-none d-md-table-cell">
                                        <div class="d-flex flex-column">
                                            <span>{{ $order->user->name ?: $order->address->name }}</span>
                                            @if (is_plugin_active('payment'))
                                                <small class="text-muted">{!! BaseHelper::clean($order->payment->status->toHtml()) !!}</small>
                                            @endif
                                        </div>
                                    </x-core::table.body.cell>
                                    <x-core::table.body.cell>
                                        {!! BaseHelper::clean($order->status->toHtml()) !!}
                                    </x-core::table.body.cell>
                                    <x-core::table.body.cell>
                                        <span class="fw-semibold">{{ format_price($order->amount) }}</span>
                                    </x-core::table.body.cell>
                                </x-core::table.body.row>
                            @empty
                                <x-core::table.body.row>
                                    <x-core::table.body.cell class="text-center" colspan="5">
                                        {{ trans('plugins/marketplace::marketplace.no_orders') }}
                                    </x-core::table.body.cell>
                                </x-core::table.body.row>
                            @endforelse
                        </x-core::table.body>
                    </x-core::table>
                </div>

                <x-core::card.footer>
                    <a href="{{ route('marketplace.vendor.orders.index') }}" class="d-flex align-items-center gap-1">
                        {{ trans('plugins/marketplace::marketplace.view_full_orders') }}
                        <x-core::icon name="ti ti-chevron-right" />
                    </a>
                </x-core::card.footer>
            </x-core::card>
        </div>

        <div class="col-12 col-md-4">
            <x-core::card class="mb-3">
                <x-core::card.header>
                    <x-core::card.title>
                        <x-core::icon name="ti ti-star" />
                        {{ trans('plugins/marketplace::marketplace.customer_reviews') }}
                    </x-core::card.title>
                </x-core::card.header>
                <x-core::card.body>
                    @if (is_plugin_active('ecommerce'))
                        @php
                            $reviews = \Botble\Ecommerce\Models\Review::query()
                                ->whereHas('product', function($query) use ($user) {
                                    $query->where('store_id', $user->store->id);
                                })
                                ->with(['product', 'user'])
                                ->latest()
                                ->limit(5)
                                ->get();
                        @endphp

                        @if ($reviews->count())
                            @foreach($reviews as $review)
                                <div class="d-flex mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                    <div class="flex-shrink-0 me-2 me-md-3">
                                        <img src="{{ $review->user->avatar_url }}" class="rounded-circle" width="40" alt="{{ $review->user->name }}">
                                    </div>
                                    <div class="flex-grow-1 min-width-0 overflow-hidden">
                                        <div class="d-flex align-items-center mb-1 flex-wrap">
                                            <h6 class="mb-0 me-2 text-truncate">{{ $review->user->name }}</h6>
                                            <div class="text-warning">
                                                @for ($i = 0; $i < 5; $i++)
                                                    @if ($i < $review->star)
                                                        <x-core::icon name="ti ti-star-filled" />
                                                    @else
                                                        <x-core::icon name="ti ti-star" />
                                                    @endif
                                                @endfor
                                            </div>
                                        </div>
                                        <p class="text-muted small mb-1 text-truncate">{{ Str::limit($review->comment, 60) }}</p>
                                        <div class="d-flex align-items-center flex-wrap">
                                            <small class="text-muted me-2">{{ $review->created_at->diffForHumans() }}</small>
                                            <a href="{{ route('marketplace.vendor.products.edit', $review->product->id) }}" class="small text-decoration-none text-truncate">
                                                {{ Str::limit($review->product->name, 15) }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-3">
                                <x-core::icon name="ti ti-message-circle-2" style="font-size: 3rem; opacity: 0.5" class="mb-2" />
                                <p class="text-muted">{{ trans('plugins/marketplace::marketplace.no_reviews_yet') }}</p>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-3">
                            <p class="text-muted">{{ trans('plugins/marketplace::marketplace.reviews_require_ecommerce') }}</p>
                        </div>
                    @endif
                </x-core::card.body>
                @if (is_plugin_active('ecommerce') && $reviews->count())
                    <x-core::card.footer>
                        <a href="{{ route('marketplace.vendor.products.index') }}" class="d-flex align-items-center gap-1">
                            {{ trans('plugins/marketplace::marketplace.view_all_products') }}
                            <x-core::icon name="ti ti-chevron-right" />
                        </a>
                    </x-core::card.footer>
                @endif
            </x-core::card>
        </div>
    @endif

    @if ($totalProducts)
        <div class="col-12 col-md-8">
            <x-core::card>
                <x-core::card.header>
                    <x-core::card.title>
                        <x-core::icon name="ti ti-trending-up" />
                        {{ trans('plugins/marketplace::marketplace.top_selling_products') }}
                    </x-core::card.title>
                </x-core::card.header>

                <div class="table-responsive">
                    <x-core::table class="table-striped">
                        <x-core::table.header>
                            <x-core::table.header.cell>{{ trans('core/base::tables.name') }}</x-core::table.header.cell>
                            <x-core::table.header.cell>{{ trans('plugins/ecommerce::products.price') }}</x-core::table.header.cell>
                            <x-core::table.header.cell class="d-none d-md-table-cell">{{ trans('plugins/ecommerce::products.quantity') }}</x-core::table.header.cell>
                            <x-core::table.header.cell class="d-none d-md-table-cell">{{ trans('core/base::tables.status') }}</x-core::table.header.cell>
                        </x-core::table.header>
                        <x-core::table.body>
                            @forelse ($data['topSellingProducts'] as $product)
                                <x-core::table.body.row>
                                    <x-core::table.body.cell>
                                        <div class="d-flex align-items-center">
                                            <div class="me-2">
                                                <img src="{{ RvMedia::getImageUrl($product->image ?: $product->images[0] ?? null, 'thumb', false, RvMedia::getDefaultImage()) }}"
                                                     width="40"
                                                     alt="{{ $product->name }}"
                                                     class="img-thumbnail">
                                            </div>
                                            <div class="min-width-0">
                                                <a href="{{ route('marketplace.vendor.products.edit', $product->id) }}" class="text-decoration-none d-block text-truncate">
                                                    {{ Str::limit($product->name, 25) }}
                                                </a>
                                                <div class="text-muted small">ID: {{ $product->id }}</div>
                                            </div>
                                        </div>
                                    </x-core::table.body.cell>
                                    <x-core::table.body.cell>
                                        {!! BaseHelper::clean($product->price_in_table) !!}
                                    </x-core::table.body.cell>
                                    <x-core::table.body.cell class="d-none d-md-table-cell">
                                        <span class="badge text-bg-success text-success-fg">{{ (int) $product->total_sold }}</span>
                                    </x-core::table.body.cell>
                                    <x-core::table.body.cell class="d-none d-md-table-cell">
                                        {!! BaseHelper::clean($product->status->toHtml()) !!}
                                    </x-core::table.body.cell>
                                </x-core::table.body.row>
                            @empty
                                <x-core::table.body.row>
                                    <x-core::table.body.cell class="text-center" colspan="4">
                                        {{ trans('plugins/marketplace::marketplace.no_products') }}
                                    </x-core::table.body.cell>
                                </x-core::table.body.row>
                            @endforelse
                        </x-core::table.body>
                    </x-core::table>
                </div>

                <x-core::card.footer>
                    <a href="{{ route('marketplace.vendor.products.index') }}" class="d-flex align-items-center gap-1">
                        {{ trans('plugins/marketplace::marketplace.view_full_products') }}
                        <x-core::icon name="ti ti-chevron-right" />
                    </a>
                </x-core::card.footer>
            </x-core::card>
        </div>

        <div class="col-12 col-md-4">
            <x-core::card>
                <x-core::card.header>
                    <x-core::card.title>
                        <x-core::icon name="ti ti-box-seam" />
                        {{ trans('plugins/marketplace::marketplace.inventory_status') }}
                    </x-core::card.title>
                </x-core::card.header>
                <x-core::card.body>
                    <div class="inventory-status">
                        @php
                            $lowStockThreshold = \Botble\Marketplace\Facades\MarketplaceHelper::lowStockThreshold();

                            $lowStockProducts = collect($data['products'])->filter(function($product) use ($lowStockThreshold) {
                                return $product->with_storehouse_management && $product->quantity > 0 && $product->quantity <= $lowStockThreshold;
                            })->count();

                            $outOfStockProducts = collect($data['products'])->filter(function($product) {
                                return $product->with_storehouse_management && $product->quantity <= 0;
                            })->count();

                            $inStockProducts = $totalProducts - $lowStockProducts - $outOfStockProducts;
                        @endphp

                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <span class="badge text-bg-success text-white p-2">
                                    <x-core::icon name="ti ti-check" />
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-0">{{ $inStockProducts }}</h5>
                                <span class="text-muted">{{ trans('plugins/marketplace::marketplace.in_stock') }}</span>
                            </div>
                            <div>
                                <a href="{{ route('marketplace.vendor.products.index') }}" class="btn btn-sm btn-outline-success">
                                    {{ trans('core/base::tables.view') }}
                                </a>
                            </div>
                        </div>

                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <span class="badge text-bg-warning text-white p-2">
                                    <x-core::icon name="ti ti-alert-triangle" />
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-0">{{ $lowStockProducts }}</h5>
                                <span class="text-muted">{{ trans('plugins/marketplace::marketplace.low_stock') }}</span>
                            </div>
                            <div>
                                <a href="{{ route('marketplace.vendor.products.index', ['filter_table_id' => 'table-marketplace-vendor-products', 'class' => 'Botble\Ecommerce\Models\Product', 'filter_columns' => ['quantity' => '1-' . $lowStockThreshold]]) }}" class="btn btn-sm btn-outline-warning">
                                    {{ trans('core/base::tables.view') }}
                                </a>
                            </div>
                        </div>

                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <span class="badge text-bg-danger text-white p-2">
                                    <x-core::icon name="ti ti-x" />
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <h5 class="mb-0">{{ $outOfStockProducts }}</h5>
                                <span class="text-muted">{{ trans('plugins/marketplace::marketplace.out_of_stock') }}</span>
                            </div>
                            <div>
                                <a href="{{ route('marketplace.vendor.products.index', ['filter_table_id' => 'table-marketplace-vendor-products', 'class' => 'Botble\Ecommerce\Models\Product', 'filter_columns' => ['quantity' => '0']]) }}" class="btn btn-sm btn-outline-danger">
                                    {{ trans('core/base::tables.view') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </x-core::card.body>
                <x-core::card.footer>
                    @if (MarketplaceHelper::isVendorDigitalProductsEnabled() && ! EcommerceHelper::isDisabledPhysicalProduct())
                        <div class="dropdown">
                            <a href="#" class="d-flex align-items-center gap-1 dropdown-toggle" data-bs-toggle="dropdown">
                                <x-core::icon name="ti ti-plus" />
                                {{ trans('plugins/marketplace::marketplace.add_new_product') }}
                            </a>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ route('marketplace.vendor.products.create') }}">
                                    <x-core::icon name="ti ti-package" />
                                    {{ \Botble\Ecommerce\Enums\ProductTypeEnum::PHYSICAL()->label() }}
                                </a>
                                <a class="dropdown-item" href="{{ route('marketplace.vendor.products.create', ['product_type' => 'digital']) }}">
                                    <x-core::icon name="ti ti-book-download" />
                                    {{ \Botble\Ecommerce\Enums\ProductTypeEnum::DIGITAL()->label() }}
                                </a>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('marketplace.vendor.products.create') }}" class="d-flex align-items-center gap-1">
                            <x-core::icon name="ti ti-plus" />
                            {{ trans('plugins/marketplace::marketplace.add_new_product') }}
                        </a>
                    @endif
                </x-core::card.footer>
            </x-core::card>
        </div>
    @endif
</div>
