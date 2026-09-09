<?php

namespace Botble\Marketplace\Enums;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Supports\Enum;
use Botble\Marketplace\Facades\MarketplaceHelper;
use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rule;

/**
 * @method static PayoutPaymentMethodsEnum BANK_TRANSFER()
 * @method static PayoutPaymentMethodsEnum PAYPAL()
 * @method static PayoutPaymentMethodsEnum CASH()
 */
class PayoutPaymentMethodsEnum extends Enum
{
    public const BANK_TRANSFER = 'bank_transfer';

    public const PAYPAL = 'paypal';

    public const CASH = 'cash';

    public static $langPath = 'plugins/marketplace::marketplace.payout_payment_methods';

    public function toHtml(): HtmlString|string
    {
        $color = match ($this->value) {
            self::BANK_TRANSFER => 'info',
            self::CASH => 'success',
            default => 'primary',
        };

        return BaseHelper::renderBadge($this->label(), $color);
    }

    public static function payoutMethodsEnabled(): array
    {
        return Arr::where(static::payoutMethods(), fn ($item) => $item['is_enabled']);
    }

    public static function payoutMethods(): array
    {
        $data = [
            self::BANK_TRANSFER => [
                'is_enabled' => (bool) Arr::get(MarketplaceHelper::getSetting('payout_methods'), self::BANK_TRANSFER, true),
                'key' => self::BANK_TRANSFER,
                'label' => self::BANK_TRANSFER()->label(),
                'fields' => [
                    'name' => [
                        'title' => trans('plugins/marketplace::marketplace.bank_name'),
                        'rules' => 'max:120',
                    ],
                    'code' => [
                        'title' => trans('plugins/marketplace::marketplace.bank_code_ifsc'),
                        'rules' => 'max:120',
                    ],
                    'full_name' => [
                        'title' => trans('plugins/marketplace::marketplace.account_holder_name'),
                        'rules' => 'max:120',
                    ],
                    'number' => [
                        'title' => trans('plugins/marketplace::marketplace.account_number'),
                        'rules' => 'max:50',
                    ],
                    'upi_id' => [
                        'title' => trans('plugins/marketplace::marketplace.upi_id'),
                        'rules' => 'max:120',
                        'helper_text' => trans('plugins/marketplace::marketplace.upi_id_helper'),
                    ],
                    'description' => [
                        'title' => trans('plugins/marketplace::marketplace.description'),
                        'rules' => 'max:500',
                    ],
                ],
            ],
            self::PAYPAL => [
                'is_enabled' => (bool) Arr::get(MarketplaceHelper::getSetting('payout_methods'), self::PAYPAL, true),
                'key' => self::PAYPAL,
                'label' => self::PAYPAL()->label(),
                'fields' => [
                    'paypal_id' => [
                        'title' => trans('plugins/marketplace::marketplace.paypal_id'),
                        'rules' => 'max:120',
                    ],
                ],
            ],
            self::CASH => [
                'is_enabled' => (bool) Arr::get(MarketplaceHelper::getSetting('payout_methods'), self::CASH, false),
                'key' => self::CASH,
                'label' => self::CASH()->label(),
                'fields' => [
                    'pickup_location' => [
                        'title' => trans('plugins/marketplace::marketplace.pickup_location'),
                        'rules' => 'max:500',
                        'helper_text' => trans('plugins/marketplace::marketplace.pickup_location_helper'),
                    ],
                    'contact_name' => [
                        'title' => trans('plugins/marketplace::marketplace.contact_name'),
                        'rules' => 'max:120',
                    ],
                    'contact_phone' => [
                        'title' => trans('plugins/marketplace::marketplace.contact_phone'),
                        'rules' => 'max:20',
                    ],
                ],
            ],
        ];

        return apply_filters('marketplace_payout_methods', $data);
    }

    public static function getFields(?string $channel): array
    {
        if (! $channel || ! in_array($channel, array_keys(static::payoutMethods()))) {
            $channel = self::BANK_TRANSFER;
        }

        return Arr::get(static::payoutMethods(), $channel . '.fields');
    }

    public static function getRules(?string $prefix): array
    {
        $payoutMethodsEnabled = static::payoutMethodsEnabled();
        $rules = [
            'payout_payment_method' => Rule::in(array_keys($payoutMethodsEnabled)),
        ];

        if ($prefix) {
            $prefix = rtrim($prefix, '.');
            $rules[$prefix] = 'nullable|array:' . implode(',', array_keys($payoutMethodsEnabled));
            $prefix = $prefix . '.';
        }

        foreach ($payoutMethodsEnabled as $method) {
            if (empty($method['fields'])) {
                continue;
            }
            $rules[$prefix . $method['key']] = 'array:' . implode(',', array_keys($method['fields']));
            foreach ($method['fields'] as $key => $field) {
                $rules[$prefix . $method['key'] . '.' . $key] = Arr::get($field, 'rules', 'nullable');
            }
        }

        return $rules;
    }

    public static function getAttributes(?string $prefix): array
    {
        $attributes = [];
        if ($prefix) {
            $prefix = rtrim($prefix, '.');
            $attributes[$prefix] = trans('plugins/marketplace::marketplace.payout_info');
            $prefix = $prefix . '.';
        }

        foreach (static::payoutMethodsEnabled() as $method) {
            $attributes[$prefix . $method['key']] = $method['label'];
            foreach ($method['fields'] as $key => $field) {
                $attributes[$prefix . $method['key'] . '.' . $key] = trans('plugins/marketplace::marketplace.payout_info') . ' (' . Arr::get($field, 'title') . ')';
            }
        }

        return $attributes;
    }
}
