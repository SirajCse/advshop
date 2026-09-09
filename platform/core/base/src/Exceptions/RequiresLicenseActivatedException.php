<?php

namespace Botble\Base\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class RequiresLicenseActivatedException extends HttpException
{
    public function __construct($message = 'Please activate your license in Admin → Settings → General before downloading plugins from the marketplace.')
    {
        parent::__construct(403, $message);
    }
}
