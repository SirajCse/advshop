<?php

namespace Botble\Ecommerce\Cart\Exceptions;

use Botble\Base\Contracts\Exceptions\IgnoringReport;
use RuntimeException;

class CartAlreadyStoredException extends RuntimeException implements IgnoringReport
{
}
