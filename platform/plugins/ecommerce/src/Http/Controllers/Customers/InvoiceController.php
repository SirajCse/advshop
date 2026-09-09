<?php

namespace Botble\Ecommerce\Http\Controllers\Customers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Facades\InvoiceHelper;
use Botble\Ecommerce\Models\Invoice;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Http\Request;

class InvoiceController extends BaseController
{
    public function __construct()
    {
        $version = EcommerceHelper::getAssetVersion();

        Theme::asset()
            ->add('customer-style', 'vendor/core/plugins/ecommerce/css/customer.css', ['bootstrap-css'], version: $version);
        Theme::asset()
            ->add('front-ecommerce-css', 'vendor/core/plugins/ecommerce/css/front-ecommerce.css', version: $version);
    }

    public function index()
    {
        SeoHelper::setTitle(__('Invoices'));

        $customerId = auth('customer')->id();

        $invoices = Invoice::query()
            ->whereHas('payment', fn ($query) => $query->where('customer_id', $customerId))
            ->with(['payment', 'items'])
            ->withCount('items')
            ->latest()
            ->paginate(10);

        Theme::breadcrumb()
            ->add(__('Invoices'), route('customer.invoices.index'));

        return Theme::scope(
            'ecommerce.customers.invoices.list',
            compact('invoices'),
            'plugins/ecommerce::themes.customers.invoices.list'
        )->render();
    }

    public function show($id)
    {
        /**
         * @var Invoice $invoice
         */
        $invoice = Invoice::query()->findOrFail($id);

        abort_unless($this->canViewInvoice($invoice), 404);

        $title = __('Invoice detail :code', ['code' => $invoice->code]);

        $this->pageTitle($title);

        SeoHelper::setTitle($title);

        return Theme::scope(
            'ecommerce.customers.invoices.detail',
            compact('invoice'),
            'plugins/ecommerce::themes.customers.invoices.detail'
        )->render();
    }

    public function getGenerateInvoice(int|string $invoiceId, Request $request)
    {
        /**
         * @var Invoice $invoice
         */
        $invoice = Invoice::query()->findOrFail($invoiceId);

        abort_unless($this->canViewInvoice($invoice), 404);

        if ($request->input('type') === 'print') {
            return InvoiceHelper::streamInvoice($invoice);
        }

        return InvoiceHelper::downloadInvoice($invoice);
    }

    protected function canViewInvoice(Invoice $invoice): bool
    {
        return auth('customer')->id() == $invoice->payment->customer_id;
    }
}
