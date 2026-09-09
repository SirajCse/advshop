<?php

namespace Botble\Marketplace\Http\Controllers\Fronts;

use Botble\Base\Facades\Assets;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Models\Product;
use Botble\Marketplace\Enums\RevenueTypeEnum;
use Botble\Marketplace\Enums\WithdrawalStatusEnum;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Botble\Marketplace\Models\Revenue;
use Botble\Marketplace\Models\Withdrawal;
use Botble\Media\Chunks\Exceptions\UploadMissingFileException;
use Botble\Media\Chunks\Handler\DropZoneUploadHandler;
use Botble\Media\Chunks\Receiver\FileReceiver;
use Botble\Media\Facades\RvMedia;
use Botble\Theme\Facades\Theme;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DashboardController extends BaseController
{
    public function __construct()
    {
        $version = get_cms_version();

        Theme::asset()
            ->add('customer-style', 'vendor/core/plugins/ecommerce/css/customer.css', ['bootstrap-css'], version: $version);

        Theme::asset()
            ->container('footer')
            ->add('ecommerce-utilities-js', 'vendor/core/plugins/ecommerce/js/utilities.js', ['jquery'], version: $version)
            ->add('cropper-js', 'vendor/core/plugins/ecommerce/libraries/cropper.js', ['jquery'], version: $version)
            ->add('avatar-js', 'vendor/core/plugins/ecommerce/js/avatar.js', ['jquery'], version: $version);
    }

    public function index(Request $request)
    {
        $this->pageTitle(trans('plugins/marketplace::marketplace.dashboard'));

        Assets::addScriptsDirectly([
                'vendor/core/plugins/ecommerce/libraries/daterangepicker/daterangepicker.js',
                'vendor/core/plugins/ecommerce/libraries/apexcharts-bundle/dist/apexcharts.min.js',
                'vendor/core/plugins/ecommerce/js/report.js',
            ])
            ->addStylesDirectly([
                'vendor/core/plugins/ecommerce/libraries/daterangepicker/daterangepicker.css',
                'vendor/core/plugins/ecommerce/libraries/apexcharts-bundle/dist/apexcharts.css',
                'vendor/core/plugins/ecommerce/css/report.css',
            ])
            ->addScripts(['moment']);

        Assets::usingVueJS();

        [$startDate, $endDate, $predefinedRange] = EcommerceHelper::getDateRangeInReport($request);

        // Default the vendor dashboard to the current month (1st up to today) when no range is selected.
        if (! $request->filled('date_from') && ! $request->filled('date_to') && ! $request->filled('predefined_range')) {
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now();
            $predefinedRange = trans('plugins/ecommerce::reports.ranges.this_month');
        }

        $user = auth('customer')->user();
        $store = $user->store;
        $data = compact('startDate', 'endDate', 'predefinedRange');

        $revenue = Revenue::query()
            ->selectRaw(
                'SUM(CASE WHEN type IS NULL OR type = ? THEN sub_amount WHEN type = ? THEN sub_amount * -1 ELSE 0 END) as sub_amount,
                SUM(CASE WHEN type IS NULL OR type = ? THEN amount WHEN type = ? THEN amount * -1 ELSE 0 END) as amount,
                SUM(fee) as fee',
                [RevenueTypeEnum::ADD_AMOUNT, RevenueTypeEnum::SUBTRACT_AMOUNT, RevenueTypeEnum::ADD_AMOUNT, RevenueTypeEnum::SUBTRACT_AMOUNT]
            )
            ->where('customer_id', $user->getKey())
            ->where(function ($query) use ($startDate, $endDate): void {
                $query->whereDate('created_at', '>=', $startDate)
                    ->whereDate('created_at', '<=', $endDate);
            })
            ->groupBy('customer_id')
            ->first();

        $withdrawal = Withdrawal::query()
            ->select([
                DB::raw('SUM(mp_customer_withdrawals.amount) as amount'),
                DB::raw('SUM(mp_customer_withdrawals.fee)'),
            ])
            ->where('mp_customer_withdrawals.customer_id', $user->getKey())
            ->whereIn('mp_customer_withdrawals.status', [
                WithdrawalStatusEnum::COMPLETED,
                WithdrawalStatusEnum::PENDING,
                WithdrawalStatusEnum::PROCESSING,
            ])
            ->where(function ($query) use ($startDate, $endDate): void {
                $query->whereDate('mp_customer_withdrawals.created_at', '>=', $startDate)
                    ->whereDate('mp_customer_withdrawals.created_at', '<=', $endDate);
            })
            ->groupBy('mp_customer_withdrawals.customer_id')
            ->first();

        $revenues = collect([
            'amount' => $revenue ? $revenue->amount : 0,
            'fee' => ($revenue ? $revenue->fee : 0) + ($withdrawal ? $withdrawal->fee : 0),
            'sub_amount' => $revenue ? $revenue->sub_amount : 0,
            'withdrawal' => $withdrawal ? $withdrawal->amount : 0,
        ]);

        $data['revenue'] = $revenues;

        $data['orders'] = Order::query()
            ->select([
                'id',
                'status',
                'user_id',
                'created_at',
                'amount',
                'tax_amount',
                'shipping_amount',
                'payment_id',
            ])
            ->with(['user'])
            ->where([
                'is_finished' => 1,
                'store_id' => $store->id,
            ])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)->latest()
            ->limit(10)
            ->get();

        $data['products'] = Product::query()
            ->select([
                'id',
                'name',
                'order',
                'created_at',
                'status',
                'sku',
                'images',
                'price',
                'sale_price',
                'sale_type',
                'start_date',
                'end_date',
                'quantity',
                'with_storehouse_management',
            ])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->where([
                'is_variation' => false,
                'store_id' => $store->id,
            ])
            ->wherePublished()
            ->limit(10)
            ->get();

        // Aggregate sold quantities by PARENT product id. ec_order_product.product_id
        // points at the variation row for products with variations, so we resolve
        // each row back to its parent via ec_product_variations.
        $soldSubquery = DB::table('ec_order_product')
            ->join('ec_orders', 'ec_orders.id', '=', 'ec_order_product.order_id')
            ->leftJoin('ec_product_variations', 'ec_product_variations.product_id', '=', 'ec_order_product.product_id')
            ->where('ec_orders.is_finished', 1)
            ->where('ec_orders.store_id', $store->id)
            ->whereDate('ec_orders.created_at', '>=', $startDate)
            ->whereDate('ec_orders.created_at', '<=', $endDate)
            ->selectRaw(
                'COALESCE(ec_product_variations.configurable_product_id, ec_order_product.product_id) AS parent_id,
                SUM(ec_order_product.qty) AS total_sold'
            )
            ->groupBy('parent_id');

        $data['topSellingProducts'] = Product::query()
            ->select([
                'ec_products.id',
                'ec_products.name',
                'ec_products.order',
                'ec_products.created_at',
                'ec_products.status',
                'ec_products.sku',
                'ec_products.images',
                'ec_products.price',
                'ec_products.sale_price',
                'ec_products.sale_type',
                'ec_products.start_date',
                'ec_products.end_date',
                'ec_products.quantity',
                'ec_products.with_storehouse_management',
                'sold.total_sold',
            ])
            ->joinSub($soldSubquery, 'sold', 'sold.parent_id', '=', 'ec_products.id')
            ->where('ec_products.is_variation', false)
            ->where('ec_products.store_id', $store->id)
            ->wherePublished('ec_products.status')
            ->orderByDesc('sold.total_sold')
            ->limit(10)
            ->get();

        $totalProducts = $store->products()->count();
        $totalOrders = Order::query()
            ->where('is_finished', 1)
            ->where('store_id', $store->id)
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->count();
        $compact = compact('user', 'store', 'data', 'totalProducts', 'totalOrders');

        if ($request->ajax()) {
            return $this
                ->httpResponse()
                ->setData([
                    'html' => MarketplaceHelper::view('vendor-dashboard.partials.dashboard-content', $compact)->render(),
                ]);
        }

        return MarketplaceHelper::view('vendor-dashboard.index', $compact);
    }

    public function postUpload(Request $request)
    {
        $customer = auth('customer')->user();

        $uploadFolder = $customer->store?->upload_folder ?: $customer->upload_folder;

        if (! RvMedia::isChunkUploadEnabled()) {
            $validator = Validator::make($request->all(), [
                'file.0' => ['required', 'image', 'mimes:jpg,jpeg,png,webp'],
            ]);

            if ($validator->fails()) {
                return $this
                    ->httpResponse()
                    ->setError()
                    ->setMessage($validator->getMessageBag()->first());
            }

            $result = RvMedia::handleUpload(Arr::first($request->file('file')), 0, $uploadFolder);

            if ($result['error']) {
                return $this
                    ->httpResponse()
                    ->setError()
                    ->setMessage($result['message']);
            }

            return $this
                ->httpResponse()
                ->setData($result['data']);
        }

        try {
            // Create the file receiver
            $receiver = new FileReceiver('file', $request, DropZoneUploadHandler::class);
            // Check if the upload is success, throw exception or return response you need
            if ($receiver->isUploaded() === false) {
                throw new UploadMissingFileException();
            }
            // Receive the file
            $save = $receiver->receive();
            // Check if the upload has finished (in chunk mode it will send smaller files)
            if ($save->isFinished()) {
                $result = RvMedia::handleUpload($save->getFile(), 0, $uploadFolder);

                if (! $result['error']) {
                    return $this
                        ->httpResponse()
                        ->setData($result['data']);
                }

                return $this
                    ->httpResponse()
                    ->setError()
                    ->setMessage($result['message']);
            }
            // We are in chunk mode, lets send the current progress
            $handler = $save->handler();

            return response()->json([
                'done' => $handler->getPercentageDone(),
                'status' => true,
            ]);
        } catch (Exception $exception) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }

    public function postUploadFromEditor(Request $request)
    {
        $customer = auth('customer')->user();

        $uploadFolder = $customer->store?->upload_folder ?: $customer->upload_folder;

        return RvMedia::uploadFromEditor($request, 0, $uploadFolder);
    }
}
