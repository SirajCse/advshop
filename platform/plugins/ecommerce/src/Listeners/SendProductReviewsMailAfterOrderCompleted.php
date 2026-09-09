<?php

namespace Botble\Ecommerce\Listeners;

use Botble\Base\Facades\EmailHandler;
use Botble\Base\Supports\EmailHandler as EmailHandlerSupport;
use Botble\Ecommerce\Events\OrderCompletedEvent;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Order;
use Botble\Ecommerce\Repositories\Interfaces\ProductInterface;

class SendProductReviewsMailAfterOrderCompleted
{
    public function handle(OrderCompletedEvent $event): void
    {
        $mailer = EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME);

        if (EcommerceHelper::isReviewEnabled() && $mailer->templateEnabled('review_products')) {
            $order = $event->order;

            if ($order instanceof Order && ($customer = $order->user) && $customer->id) {
                $products = app(ProductInterface::class)->productsNeedToReviewByCustomer($customer->id, 12, [$order->id]);

                if ($products->count() && $products->loadMissing(['slugable'])) {
                    $locale = $order->getOrderMetadata('customer_locale') ?: EmailHandlerSupport::getDefaultEmailLocale();

                    $mailer
                        ->setVariableValues([
                            'customer_name' => $customer->name,
                            'product_review_list' => view('plugins/ecommerce::emails.partials.product-review-list', compact('products'))->render(),
                            'order_id' => $order->code,
                        ])
                        ->sendUsingTemplateWithLocale('review_products', $customer->email, $locale);
                }
            }
        }
    }
}
