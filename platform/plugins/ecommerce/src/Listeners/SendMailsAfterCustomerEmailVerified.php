<?php

namespace Botble\Ecommerce\Listeners;

use Botble\Base\Facades\EmailHandler;
use Botble\Base\Supports\EmailHandler as EmailHandlerSupport;
use Botble\Ecommerce\Events\CustomerEmailVerified;

class SendMailsAfterCustomerEmailVerified
{
    public function handle(CustomerEmailVerified $event): void
    {
        $customer = $event->customer;

        if (! is_plugin_active('marketplace') || ! $customer->is_vendor) {
            EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME)
                ->setVariableValues([
                    'customer_name' => $customer->name,
                ])
                ->sendUsingTemplateWithLocale('welcome', $customer->email, EmailHandlerSupport::getDefaultEmailLocale());
        }
    }
}
