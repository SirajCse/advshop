<?php

namespace Botble\Marketplace\Http\Controllers\API;

use Botble\Api\Http\Controllers\BaseApiController;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\EmailHandler;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Http\Resources\API\AvailableProductResource;
use Botble\Ecommerce\Services\Products\GetProductService;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Http\Requests\API\ContactStoreRequest;
use Botble\Marketplace\Http\Resources\API\StoreDetailResource;
use Botble\Marketplace\Http\Resources\API\StoreResource;
use Botble\Marketplace\Models\Message;
use Botble\Marketplace\Models\Store;
use Botble\Slug\Facades\SlugHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreController extends BaseApiController
{
    /**
     * List published stores with pagination
     *
     * @group Marketplace Stores
     * @queryParam q string Search by store name. No-example
     * @queryParam per_page int Items per page (default: 12). No-example
     * @queryParam page int Page number. No-example
     */
    public function index(Request $request)
    {
        $search = BaseHelper::stringify(BaseHelper::clean($request->input('q')));

        $query = Store::query()
            ->wherePublished()
            ->with(['slugable']);

        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%");
        }

        if (EcommerceHelper::isReviewEnabled()) {
            $query->with(['reviews' => function ($q): void {
                $q->where([
                    'ec_products.status' => BaseStatusEnum::PUBLISHED,
                    'ec_reviews.status' => BaseStatusEnum::PUBLISHED,
                ]);
            }]);
        }

        $query->withCount([
            'products' => function ($q): void {
                $q->where('is_variation', 0)->wherePublished();
            },
        ]);

        $stores = $query->latest()->paginate($request->integer('per_page', 12));

        return $this
            ->httpResponse()
            ->setData(StoreResource::collection($stores))
            ->toApiResponse();
    }

    /**
     * Get store details by slug
     *
     * @group Marketplace Stores
     * @urlParam slug string required Store slug. Example: my-store
     * @queryParam per_page int Products per page (default: 12). No-example
     */
    public function show(string $slug, Request $request, GetProductService $productService)
    {
        $slugRecord = SlugHelper::getSlug($slug, SlugHelper::getPrefix(Store::class));

        abort_unless($slugRecord, 404);

        $store = Store::query()
            ->wherePublished()
            ->with(['slugable', 'metadata'])
            ->where('id', $slugRecord->reference_id)
            ->firstOrFail();

        $products = $productService->getProduct(
            $request,
            null,
            null,
            EcommerceHelper::withProductEagerLoadingRelations(),
            [],
            ['is_variation' => 0, 'store_id' => $store->getKey()]
        );

        return $this
            ->httpResponse()
            ->setData(new StoreDetailResource($store))
            ->setAdditional([
                'products' => AvailableProductResource::collection($products),
                'products_count' => $products->total(),
            ])
            ->toApiResponse();
    }

    /**
     * Send message to store
     *
     * @group Marketplace Stores
     * @urlParam id int|string required Store ID (integer or UUID). Example: 1
     * @bodyParam content string required Message content (max 1000). Example: Hello, I have a question.
     * @bodyParam name string Name (required if not logged in). Example: John Doe
     * @bodyParam email string Email (required if not logged in). Example: john@example.com
     */
    public function contact(int|string $id, ContactStoreRequest $request): JsonResponse
    {
        abort_unless(MarketplaceHelper::isEnabledMessagingSystem(), 404);

        $store = Store::query()
            ->wherePublished()
            ->findOrFail($id);

        $customer = auth('customer')->user();

        if ($customer && $customer->store?->getKey() == $store->getKey()) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/marketplace::message.cannot_send_to_own_store'))
                ->toApiResponse();
        }

        $emailVariables = [
            'store_name' => $store->name,
            'store_phone' => $store->phone,
            'store_address' => $store->full_address,
            'store_url' => $store->url,
            'customer_message' => $request->input('content'),
            'customer_name' => $customer?->name ?? $request->input('name'),
            'customer_email' => $customer?->email ?? $request->input('email'),
        ];

        Message::query()->create([
            'store_id' => $store->getKey(),
            'customer_id' => $customer?->getKey(),
            'name' => $emailVariables['customer_name'],
            'email' => $emailVariables['customer_email'],
            'content' => $request->input('content'),
        ]);

        EmailHandler::setModule(MARKETPLACE_MODULE_SCREEN_NAME)
            ->setVariableValues($emailVariables)
            ->sendUsingTemplate('contact-store', $store->email);

        return $this
            ->httpResponse()
            ->setMessage(trans('plugins/marketplace::message.send_message_successfully'))
            ->toApiResponse();
    }
}
