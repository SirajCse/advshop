<?php

namespace Botble\Marketplace\Http\Controllers;

use Botble\Base\Facades\Assets;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Marketplace\Models\Vendor;
use Botble\Marketplace\Tables\VendorTable;
use Illuminate\Support\Facades\Storage;

class VendorController extends BaseController
{
    public function index(VendorTable $table)
    {
        $this->pageTitle(trans('plugins/marketplace::marketplace.vendors'));

        return $table->renderTable();
    }

    public function view($id)
    {
        /**
         * @var Vendor $vendor
         */
        $vendor = Vendor::query()
            ->with(['store', 'orders', 'addresses', 'wishlist', 'reviews', 'revenues', 'withdrawals'])
            ->findOrFail($id);

        $this->pageTitle(trans('plugins/marketplace::marketplace.view_vendor', ['name' => $vendor->name]));

        Assets::addScriptsDirectly('vendor/core/plugins/ecommerce/js/customer.js');

        $orderStats = $vendor
            ->orders()
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(CASE WHEN is_finished = 1 THEN 1 ELSE 0 END) as completed_orders,
                SUM(CASE WHEN is_finished = 1 THEN amount ELSE 0 END) as total_spent
            ')
            ->first();

        $totalSpent = $orderStats->total_spent ?? 0;
        $totalOrders = $orderStats->total_orders ?? 0;
        $completedOrders = $orderStats->completed_orders ?? 0;

        $totalProducts = $vendor
            ->completedOrders()
            ->withCount('products')
            ->get()
            ->sum('products_count');

        $store = $vendor->store;
        $totalRevenue = $vendor->total_revenue;
        $totalEarnings = $vendor->total_earnings;
        $totalWithdrawals = $vendor->completed_withdrawals;
        $pendingWithdrawals = $vendor->pending_withdrawals;
        $balance = $vendor->balance;
        $storeProducts = $vendor->products_count;
        $storeOrders = $vendor->orders_count;

        return view('plugins/marketplace::vendors.view', compact(
            'vendor',
            'totalSpent',
            'totalOrders',
            'completedOrders',
            'totalProducts',
            'store',
            'totalRevenue',
            'totalEarnings',
            'totalWithdrawals',
            'pendingWithdrawals',
            'balance',
            'storeProducts',
            'storeOrders'
        ));
    }

    public function downloadCertificate(int|string $id)
    {
        $vendor = Vendor::query()->findOrFail($id);

        $storage = Storage::disk('local');

        if (! $storage->exists($vendor->store->certificate_file)) {
            return BaseHttpResponse::make()
                ->setError()
                ->setMessage(trans('plugins/marketplace::marketplace.file_not_found'));
        }

        return response()->file($storage->path($vendor->store->certificate_file));
    }

    public function downloadGovernmentId(int|string $id)
    {
        $vendor = Vendor::query()->findOrFail($id);

        $storage = Storage::disk('local');

        if (! $storage->exists($vendor->store->government_id_file)) {
            return BaseHttpResponse::make()
                ->setError()
                ->setMessage(trans('plugins/marketplace::marketplace.file_not_found'));
        }

        return response()->file($storage->path($vendor->store->government_id_file));
    }
}
