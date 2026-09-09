<?php

namespace Botble\PayPal\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Payment\Supports\PaymentHelper;
use Botble\PayPal\Http\Requests\PayPalPaymentCallbackRequest;
use Botble\PayPal\Services\Gateways\PayPalPaymentService;

class PayPalController extends BaseController
{
    public function getCallback(
        PayPalPaymentCallbackRequest $request,
        PayPalPaymentService $payPalPaymentService,
        BaseHttpResponse $response
    ) {
        $status = $payPalPaymentService->getPaymentStatus($request);

        if (! $status) {
            return $response
                ->setError()
                ->setNextUrl(PaymentHelper::getCancelURL())
                ->withInput()
                ->setMessage(trans('plugins/paypal::paypal.payment_failed'));
        }

        $payPalPaymentService->afterMakePayment($request->input());

        return $response
            ->setNextUrl(PaymentHelper::getRedirectURL())
            ->setMessage(trans('plugins/payment::payment.checkout_success'));
    }
}
