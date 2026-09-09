<?php

namespace Botble\Ecommerce\Listeners;

use Botble\Base\Facades\EmailHandler;
use Botble\Base\Supports\EmailHandler as EmailHandlerSupport;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Customer;
use Illuminate\Auth\Events\Registered;

class SendMailsAfterCustomerRegistered
{
    public function handle(Registered $event): void
    {
        $customer = $event->user;

        if (! $customer instanceof Customer) {
            return;
        }

        if (EcommerceHelper::isEnableEmailVerification()) {
            $customer->sendEmailVerificationNotification();
        } elseif (! is_plugin_active('marketplace') || ! $customer->is_vendor) {
            EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME)
                ->setVariableValues([
                    'customer_name' => $customer->name,
                ])
                ->sendUsingTemplateWithLocale('welcome', $customer->email, EmailHandlerSupport::getDefaultEmailLocale());
        }
    }
}
