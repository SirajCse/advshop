<div class="d-flex gap-3 mb-3">
    {{ RvMedia::image($store->logo_url, $store->name, 'thumb', attributes: ['class' => 'rounded-pill', 'style' => 'width: 70px; height: 70px']) }}
    <div>
        <h6>
            <a href="{{ $store->url }}">
                {{ $store->name }} {!! $store->badge !!}
            </a>
        </h6>
        @if (EcommerceHelper::isReviewEnabled() && (!EcommerceHelper::hideRatingWhenNoReviews() || $store->reviews()->count() > 0))
            <div class="d-flex align-items-center gap-2">
                @include(EcommerceHelper::viewPath('includes.rating-star'), ['avg' => $store->reviews()->avg('star')])
                <span class="small text-muted">
                    @if (($reviewsCount = $store->reviews()->count()) === 1)
                        ({{ __('1 Review') }})
                    @else
                        ({{ __(':count Reviews', ['count' => number_format($reviewsCount)]) }})
                    @endif
                </span>
            </div>
        @endif

        <time class="small text-muted" datetime="{{ $store->created_at->toDateString() }}">
            {{ __('Joined :date', ['date' => Theme::formatDate($store->created_at)]) }}
        </time>
    </div>
</div>

@include(MarketplaceHelper::viewPath('includes.vacation-notice'), ['store' => $store])

<ul class="d-flex flex-column gap-2 list-unstyled mb-3">
    @if (! MarketplaceHelper::hideStoreAddress() && $store->full_address)
        <li class="d-flex align-items-start gap-2">
            <x-core::icon name="ti ti-map-pin" class="flex-shrink-0 mt-1" />
            <div>
                <strong>{{ __('Address:') }}</strong>
                {{ $store->full_address }}
            </div>
        </li>
    @endif

    @if (! MarketplaceHelper::hideStorePhoneNumber() && $store->phone)
        <li class="d-flex align-items-center gap-2">
            <x-core::icon name="ti ti-headphones" class="flex-shrink-0" />
            <div>
                <strong>{{ __('Phone:') }}</strong>
                <a href="tel:{{ $store->phone }}">{{ $store->phone }}</a>
            </div>
        </li>
    @endif

    @if (! MarketplaceHelper::hideStoreEmail() && $store->email)
        <li class="d-flex align-items-center gap-2">
            <x-core::icon name="ti ti-mail" class="flex-shrink-0" />
            <div>
                <strong>{{ __('Email:') }}</strong>
                <a href="mailto:{{ $store->email }}">{{ $store->email }}</a>
            </div>
        </li>
    @endif
</ul>

<p>
    {!! BaseHelper::clean($store->description ?: Str::words($store->content, 50)) !!}
</p>
