<?php

namespace Botble\Ecommerce\Http\Controllers\Fronts;

use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\DeletedContentEvent;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Http\Requests\Fronts\ReviewRequest;
use Botble\Ecommerce\Http\Requests\ReviewReplyRequest;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Models\Review;
use Botble\Ecommerce\Traits\CheckReviewConditionTrait;
use Botble\Media\Facades\RvMedia;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Slug\Facades\SlugHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ReviewController extends BaseController
{
    use CheckReviewConditionTrait;

    public function store(ReviewRequest $request)
    {
        abort_unless(EcommerceHelper::isReviewEnabled(), 404);

        $productId = $request->input('product_id');
        $check = $this->checkReviewCondition($productId);

        if (Arr::get($check, 'error')) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(Arr::get($check, 'message', __('Oops! Something Went Wrong.')));
        }

        $results = [];
        if (EcommerceHelper::isCustomerReviewImageUploadEnabled() && $request->hasFile('images')) {
            $images = (array) $request->file('images', []);
            foreach ($images as $image) {
                $result = RvMedia::handleUpload($image, 0, 'reviews');
                if ($result['error']) {
                    return $this
                        ->httpResponse()
                        ->setError()
                        ->setMessage($result['message']);
                }

                $results[] = $result;
            }
        }

        $review = Review::query()->create([
            ...$request->validated(),
            'customer_id' => auth('customer')->id(),
            'images' => $results ? collect($results)->pluck('data.url')->values()->all() : null,
            'status' => get_ecommerce_setting('review_need_to_be_approved', false) ? BaseStatusEnum::PENDING : BaseStatusEnum::PUBLISHED,
        ]);

        event(new CreatedContentEvent('review', $request, $review));

        return $this
            ->httpResponse()
            ->setMessage(__('Added review successfully!'));
    }

    public function destroy(int|string $id, Request $request)
    {
        abort_unless(EcommerceHelper::isReviewEnabled(), 404);

        $review = Review::query()->findOrFail($id);

        if (auth()->check() || (auth('customer')->check() && auth('customer')->id() == $review->customer_id)) {
            event(new DeletedContentEvent('review', $request, $review));

            $review->delete();

            return $this
                ->httpResponse()
                ->setMessage(__('Deleted review successfully!'));
        }

        abort(401);
    }

    public function getProductReview(string $key)
    {
        abort_unless(EcommerceHelper::isReviewEnabled(), 404);

        $slug = SlugHelper::getSlug($key, SlugHelper::getPrefix(Product::class));

        abort_unless($slug, 404);

        $condition = [
            'ec_products.id' => $slug->reference_id,
        ];

        $product = get_products([
                'condition' => $condition,
                'take' => 1,
            ]);

        abort_unless($product, 404);

        $check = $this->checkReviewCondition($product->id);
        if (Arr::get($check, 'error')) {
            return $this
                ->httpResponse()
                ->setNextUrl($product->url)
                ->setError()
                ->setMessage(Arr::get($check, 'message', __('Oops! Something Went Wrong.')));
        }

        Theme::asset()
            ->add('ecommerce-review-css', 'vendor/core/plugins/ecommerce/css/review.css');
        Theme::asset()->container('footer')
            ->add('ecommerce-review-js', 'vendor/core/plugins/ecommerce/js/review.js', ['jquery']);

        SeoHelper::setTitle(__('Review product ":product"', ['product' => $product->name]))->setDescription($product->description);

        Theme::breadcrumb()
            ->add(__('Products'), route('public.products'))
            ->add($product->name, $product->url)
            ->add(__('Review'));

        do_action(BASE_ACTION_PUBLIC_RENDER_SINGLE, PRODUCT_MODULE_SCREEN_NAME, $product);

        return Theme::scope('ecommerce.product-review', compact('product'), 'plugins/ecommerce::themes.includes.reviews')
            ->render();
    }

    public function ajaxReviews(int|string $id, Request $request)
    {
        /**
         * @var Product $product
         */
        $product = Product::query()
            ->wherePublished()
            ->where([
                'id' => $id,
                'is_variation' => false,
            ])
            ->with(['variations'])
            ->firstOrFail();

        $star = $request->integer('star');
        $perPage = $request->integer('per_page', 10);
        $search = (string) BaseHelper::stringify($request->input('search'));
        $sortBy = BaseHelper::stringify($request->input('sort_by'));
        $sortBy = $sortBy && in_array($sortBy, ['newest', 'oldest', 'highest_rating', 'lowest_rating'])
            ? $sortBy
            : 'newest';

        $reviews = EcommerceHelper::getProductReviews($product, $star, $perPage, $search, $sortBy);

        if ($star) {
            $message = __(':total review(s) ":star star" for ":product"', [
                'total' => $reviews->total(),
                'product' => $product->name,
                'star' => $star,
            ]);
        } else {
            $message = __(':total review(s) for ":product"', [
                'total' => $reviews->total(),
                'product' => $product->name,
            ]);
        }

        return $this
            ->httpResponse()
            ->setData(
                Theme::scope(
                    'ecommerce.includes.review-list',
                    compact('reviews', 'product'),
                    'plugins/ecommerce::themes.includes.review-list'
                )->getContent() ?: ' '
            )
            ->setMessage($message, false)
            ->toApiResponse();
    }

    public function storeReply(Review $review, ReviewReplyRequest $request)
    {
        abort_unless(EcommerceHelper::isReviewEnabled(), 404);

        $customer = auth('customer')->user();

        if (! $customer || ! $customer->is_vendor) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/ecommerce::review.must_be_vendor_to_reply'));
        }

        $store = $customer->store;

        if (! $store) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/ecommerce::review.must_have_store_to_reply'));
        }

        $product = $review->product;

        if (! $product || $product->store_id !== $store->id) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/ecommerce::review.can_only_reply_to_own_products'));
        }

        if ($review->reply()->exists()) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/ecommerce::review.already_replied'));
        }

        $review->reply()->create([
            'customer_id' => $customer->id,
            'message' => $request->input('message'),
        ]);

        return $this
            ->httpResponse()
            ->setMessage(trans('plugins/ecommerce::review.replied_success_message'));
    }

    public function destroyReply(Review $review)
    {
        abort_unless(EcommerceHelper::isReviewEnabled(), 404);

        $customer = auth('customer')->user();

        if (! $customer || ! $customer->is_vendor) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/ecommerce::review.must_be_vendor_to_delete_reply'));
        }

        $reply = $review->reply;

        if (! $reply || $reply->customer_id !== $customer->id) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/ecommerce::review.can_only_delete_own_replies'));
        }

        $reply->delete();

        return $this
            ->httpResponse()
            ->setMessage(trans('plugins/ecommerce::review.deleted_reply_success'));
    }
}
