<?php

namespace Botble\Ecommerce\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\Cart;
use Botble\Ecommerce\Tables\CustomerCartTable;
use Illuminate\Http\Request;

class CustomerCartController extends BaseController
{
    public function index(CustomerCartTable $table)
    {
        $this->pageTitle(trans('plugins/ecommerce::cart.customer_carts'));

        return $table->renderTable();
    }

    public function destroy(Request $request, string $identifier, string $instance)
    {
        Cart::query()
            ->where('identifier', $identifier)
            ->where('instance', $instance)
            ->delete();

        return $this
            ->httpResponse()
            ->setMessage(trans('core/base::notices.delete_success_message'));
    }
}
