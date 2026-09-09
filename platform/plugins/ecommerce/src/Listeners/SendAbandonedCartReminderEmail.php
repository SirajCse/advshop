<?php

namespace Botble\Ecommerce\Listeners;

use Botble\Base\Facades\EmailHandler;
use Botble\Base\Supports\EmailHandler as EmailHandlerSupport;
use Botble\Ecommerce\Events\AbandonedCartReminderEvent;
use Botble\Ecommerce\Models\Product;
use Botble\Ecommerce\Services\AbandonedCartService;
use Botble\Media\Facades\RvMedia;
use Exception;
use Illuminate\Support\Facades\Log;

class SendAbandonedCartReminderEmail
{
    public function __construct(protected AbandonedCartService $service)
    {
    }

    public function handle(AbandonedCartReminderEvent $event): void
    {
        if (! get_ecommerce_setting('abandoned_cart_enabled', false)) {
            return;
        }

        if (! get_ecommerce_setting('abandoned_cart_send_email', true)) {
            return;
        }

        $abandonedCart = $event->abandonedCart;
        $sequence = $event->sequence;

        if (! $abandonedCart->email) {
            return;
        }

        if ($this->service->isOptedOut($abandonedCart)) {
            return;
        }

        $maxReminders = (int) get_ecommerce_setting('abandoned_cart_max_reminders', 3);

        if ($sequence > $maxReminders) {
            return;
        }

        $templateName = $this->getTemplateName($sequence);
        $mailer = EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME);

        if (! $mailer->templateEnabled($templateName)) {
            if (! $mailer->templateEnabled('abandoned_cart_reminder')) {
                return;
            }
            $templateName = 'abandoned_cart_reminder';
        }

        try {
            $productList = $this->buildProductListHtml($abandonedCart->cart_data);
            $cartRecoverUrl = route('public.cart', ['token' => $abandonedCart->recovery_token]);
            $unsubscribeUrl = route('public.abandoned-cart.unsubscribe', ['token' => $abandonedCart->unsubscribe_token]);

            $mailer
                ->setVariableValues([
                    'customer_name' => $abandonedCart->customer_name ?: trans('plugins/ecommerce::order.guest'),
                    'product_list' => $productList,
                    'cart_total' => format_price($abandonedCart->total_amount),
                    'cart_recover_url' => $cartRecoverUrl,
                    'unsubscribe_url' => $unsubscribeUrl,
                    'email_sequence' => $sequence,
                ])
                ->sendUsingTemplateWithLocale($templateName, $abandonedCart->email, EmailHandlerSupport::getDefaultEmailLocale());
        } catch (Exception $exception) {
            Log::error('Failed to send abandoned cart reminder email: ' . $exception->getMessage());

            throw $exception;
        }
    }

    protected function getTemplateName(int $sequence): string
    {
        return match ($sequence) {
            1 => 'abandoned_cart_reminder_1',
            2 => 'abandoned_cart_reminder_2',
            3 => 'abandoned_cart_reminder_3',
            default => 'abandoned_cart_reminder',
        };
    }

    protected function buildProductListHtml(array $cartData): string
    {
        $html = '<table style="width:100%;border-collapse:collapse;">';

        foreach ($cartData as $item) {
            $product = Product::query()->find($item['id'] ?? null);
            if (! $product) {
                continue;
            }

            $imageUrl = RvMedia::getImageUrl($product->image, 'thumb', false, RvMedia::getDefaultImage());
            $productName = $product->name ?? ($item['name'] ?? trans('plugins/ecommerce::products.product'));
            $quantity = $item['qty'] ?? 1;
            $price = $item['price'] ?? 0;

            $html .= '<tr style="border-bottom:1px solid #eee;">';
            $html .= '<td style="padding:15px;width:80px;">';
            $html .= '<img src="' . $imageUrl . '" alt="' . e($productName) . '" width="60" height="60" style="border-radius:4px;object-fit:cover;">';
            $html .= '</td>';
            $html .= '<td style="padding:15px;">';
            $html .= '<strong>' . e($productName) . '</strong><br>';
            $html .= '<span style="color:#666;">' . trans('plugins/ecommerce::products.qty') . ': ' . $quantity . '</span>';
            $html .= '</td>';
            $html .= '<td style="padding:15px;text-align:right;">';
            $html .= format_price($price);
            $html .= '</td>';
            $html .= '</tr>';
        }

        $html .= '</table>';

        return $html;
    }
}
