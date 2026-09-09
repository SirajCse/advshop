<?php

namespace Botble\PayPal\Services\Core;

use PayPalCheckoutSdk\Core\PayPalEnvironment;
use PayPalCheckoutSdk\Core\PayPalHttpClient as BasePayPalHttpClient;

class PayPalHttpClient extends BasePayPalHttpClient
{
    public $curlCls;

    public function __construct(PayPalEnvironment $environment)
    {
        parent::__construct($environment);
    }
}
