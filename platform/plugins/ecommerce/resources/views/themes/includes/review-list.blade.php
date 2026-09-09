@php
    $currentCustomer = auth('customer')->user();
    $isVendorOfProduct = $currentCustomer
        && $currentCustomer->is_vendor
        && isset($product)
        && $currentCustomer->store
        && $product->store_id == $currentCustomer->store->id;
@endphp

@foreach ($reviews as $review)
    @continue(! $review->is_approved && auth('customer')->id() != $review->customer_id)

    @php
        $isCurrentCustomerReview = auth('customer')->check() && auth('customer')->id() == $review->customer_id;
        $canReply = $isVendorOfProduct && ! $review->reply;
        $canDeleteReply = $isVendorOfProduct && $review->reply && $review->reply->customer_id == $currentCustomer?->id;
    @endphp

    <div @class([
        'row pb-3 mb-3 review-item',
        'border-bottom' => ! $loop->last,
        'opacity-50' => ! $review->is_approved,
        'current-customer-review' => $isCurrentCustomerReview
    ])>
        <div class="col-auto">
            <img class="rounded-circle" src="{{ $review->customer_avatar_url }}" alt="{{ $review->display_name }}" width="60" height="60" style="object-fit: cover;">
        </div>
        <div class="col">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-2 review-item__header">
                <div class="fw-medium">
                    {{ $review->display_name }}
                </div>
                @if ($isCurrentCustomerReview)
                    <span class="badge bg-primary">
                        {{ trans('plugins/ecommerce::review.your_review') }}
                    </span>
                @endif
                <time class="text-muted small" datetime="{{ $review->created_at->translatedFormat('Y-m-d\TH:i:sP') }}">
                    {{ $review->created_at->diffForHumans() }}
                </time>
                @php
                    $badgeType = $review->badge_type?->getValue() ?? \Botble\Ecommerce\Enums\ReviewBadgeEnum::AUTO;
                @endphp
                @if ($badgeType === \Botble\Ecommerce\Enums\ReviewBadgeEnum::AUTO && $review->order_created_at)
                    <div class="small text-muted">{{ trans('plugins/ecommerce::review.purchased_at_time', ['time' => $review->order_created_at->diffForHumans()]) }}</div>
                @elseif ($badgeType === \Botble\Ecommerce\Enums\ReviewBadgeEnum::PURCHASED)
                    <div class="small text-muted">{{ trans('plugins/ecommerce::review.purchased_at_time', ['time' => $review->created_at->subHours(12)->diffForHumans()]) }}</div>
                @elseif ($badgeType !== \Botble\Ecommerce\Enums\ReviewBadgeEnum::AUTO && $badgeType !== \Botble\Ecommerce\Enums\ReviewBadgeEnum::NONE)
                    <div class="small text-muted">{{ $review->badge_type->label() }}</div>
                @endif
                @if (! $review->is_approved)
                    <div class="small text-warning">{{ trans('plugins/ecommerce::review.waiting_for_approval') }}</div>
                @endif

                @if ($isCurrentCustomerReview)
                    <div class="review-item__actions">
                        <a
                            href="javascript:void(0)"
                            class="text-danger delete-review-btn p-1"
                            data-review-id="{{ $review->id }}"
                            data-confirm-message="{{ trans('plugins/ecommerce::review.are_you_sure_you_want_to_delete_your_review') }}"
                            title="{{ trans('plugins/ecommerce::review.delete_your_review') }}"
                        >
                            <x-core::icon name="ti ti-trash" />
                        </a>
                    </div>
                @endif
            </div>

            <div class="mb-2 review-item__rating">
                @include(EcommerceHelper::viewPath('includes.rating-star'), ['avg' => $review->star, 'size' => 80])
            </div>

            <div class="review-item__body">
                {{ $review->comment }}
            </div>

            @if (EcommerceHelper::isCustomerReviewImageUploadEnabled() && $review->images)
                <div class="review-item__images mt-3">
                    <div class="row g-1 review-images">
                        @foreach ($review->images as $image)
                            <a href="{{ RvMedia::getImageUrl($image) }}" class="col-3 col-md-2 col-xl-1 position-relative">
                                <img src="{{ RvMedia::getImageUrl($image, 'thumb') }}" alt="{{ $review->comment }}" class="img-thumbnail">
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($review->reply)
                <div class="review-item__reply mt-3">
                    <div class="d-flex gap-3 p-3 rounded" style="background-color: #f8f9fa;">
                        <div class="flex-shrink-0">
                            <img class="rounded" src="{{ $review->reply->responder_avatar_url }}" alt="{{ $review->reply->responder_name }}" width="48" height="48" style="object-fit: cover;">
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="fw-semibold mb-1">{{ trans('plugins/ecommerce::review.seller_response') }}</div>
                                @if ($canDeleteReply)
                                    <a
                                        href="javascript:void(0)"
                                        class="text-danger vendor-delete-reply-btn"
                                        data-review-id="{{ $review->id }}"
                                        data-url="{{ route('public.reviews.reply.destroy', $review->id) }}"
                                        data-confirm-message="{{ trans('plugins/ecommerce::review.confirm_delete_reply') }}"
                                        title="{{ trans('plugins/ecommerce::review.delete_reply') }}"
                                    >
                                        <x-core::icon name="ti ti-trash" />
                                    </a>
                                @endif
                            </div>
                            <div>{{ $review->reply->message }}</div>
                        </div>
                    </div>
                </div>
            @elseif ($canReply)
                <div class="review-item__reply-form mt-2">
                    <a
                        href="javascript:void(0)"
                        class="vendor-reply-toggle-btn"
                        data-review-id="{{ $review->id }}"
                        style="color: #6b7280; font-size: 13px; text-decoration: none;"
                    >
                        <x-core::icon name="ti ti-message" style="width: 14px; height: 14px; margin-right: 4px;" />
                        {{ trans('plugins/ecommerce::review.reply') }}
                    </a>

                    <div
                        class="vendor-reply-form mt-3 d-none"
                        data-review-id="{{ $review->id }}"
                        data-url="{{ route('public.reviews.reply', $review->id) }}"
                    >
                        <div class="mb-2">
                            <textarea
                                class="form-control vendor-reply-message"
                                rows="2"
                                placeholder="{{ trans('plugins/ecommerce::review.write_your_reply') }}"
                                style="font-size: 14px; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; color: #374151;"
                            ></textarea>
                        </div>
                        <div class="d-flex" style="gap: 8px;">
                            <button type="button" class="vendor-reply-submit-btn" data-review-id="{{ $review->id }}" style="display: inline-block; width: auto; padding: 6px 16px; font-size: 13px; font-weight: 500; background-color: var(--primary-color, #0d6efd); border: none; color: #fff; border-radius: 6px; cursor: pointer;">
                                {{ trans('plugins/ecommerce::review.submit_reply') }}
                            </button>
                            <button type="button" class="vendor-reply-cancel-btn" data-review-id="{{ $review->id }}" style="display: inline-block; width: auto; padding: 6px 12px; font-size: 13px; background: transparent; border: none; color: #6b7280; cursor: pointer;">
                                {{ trans('plugins/ecommerce::review.cancel') }}
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endforeach

<div class="tp-pagination">
    {{ $reviews->links() }}
</div>
