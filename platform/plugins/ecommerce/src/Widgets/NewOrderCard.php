<?php

namespace Botble\Ecommerce\Widgets;

use Botble\Base\Widgets\Card;
use Botble\Ecommerce\Models\Order;
use Botble\Payment\Enums\PaymentStatusEnum;
use Carbon\CarbonPeriod;

class NewOrderCard extends Card
{
    public function getOptions(): array
    {
        if (is_plugin_active('payment')) {
            $query = Order::query()
                ->whereDate('ec_orders.created_at', '>=', $this->startDate)
                ->whereDate('ec_orders.created_at', '<=', $this->endDate)
                ->leftJoin('payments', 'payments.id', '=', 'ec_orders.payment_id')
                ->where(function ($q): void {
                    $q->whereIn('payments.status', [PaymentStatusEnum::COMPLETED, PaymentStatusEnum::PENDING])
                        ->orWhereNull('ec_orders.payment_id');
                })
                ->where(function ($q): void {
                    $q->where(function ($subQ): void {
                        $subQ->whereDate('payments.created_at', '>=', $this->startDate)
                            ->whereDate('payments.created_at', '<=', $this->endDate);
                    })->orWhereNull('ec_orders.payment_id');
                });
        } else {
            $query = Order::query()
                ->whereDate('created_at', '>=', $this->startDate)
                ->whereDate('created_at', '<=', $this->endDate);
        }

        $data = $query
            ->where('ec_orders.is_finished', true)
            ->selectRaw(
                'count(ec_orders.id) as total, date_format(ec_orders.created_at, "' . $this->dateFormat . '") as period'
            )
            ->groupBy('period')
            ->pluck('total')
            ->toArray();

        return [
            'series' => [
                [
                    'data' => $data,
                ],
            ],
        ];
    }

    public function getViewData(): array
    {
        if (is_plugin_active('payment')) {
            $count = Order::query()
                ->whereDate('ec_orders.created_at', '>=', $this->startDate)
                ->whereDate('ec_orders.created_at', '<=', $this->endDate)
                ->leftJoin('payments', 'payments.id', '=', 'ec_orders.payment_id')
                ->where(function ($q): void {
                    $q->whereIn('payments.status', [PaymentStatusEnum::COMPLETED, PaymentStatusEnum::PENDING])
                        ->orWhereNull('ec_orders.payment_id');
                })
                ->where(function ($q): void {
                    $q->where(function ($subQ): void {
                        $subQ->whereDate('payments.created_at', '>=', $this->startDate)
                            ->whereDate('payments.created_at', '<=', $this->endDate);
                    })->orWhereNull('ec_orders.payment_id');
                })
                ->where('ec_orders.is_finished', true)
                ->count();
        } else {
            $count = Order::query()
                ->whereDate('created_at', '>=', $this->startDate)
                ->whereDate('created_at', '<=', $this->endDate)
                ->where('is_finished', true)
                ->count();
        }

        $startDate = clone $this->startDate;
        $endDate = clone $this->endDate;

        $currentPeriod = CarbonPeriod::create($startDate, $endDate);
        $previousPeriod = CarbonPeriod::create(
            $startDate->subDays($currentPeriod->count()),
            $endDate->subDays($currentPeriod->count())
        );

        if (is_plugin_active('payment')) {
            $currentOrders = Order::query()
                ->whereDate('ec_orders.created_at', '>=', $currentPeriod->getStartDate())
                ->whereDate('ec_orders.created_at', '<=', $currentPeriod->getEndDate())
                ->leftJoin('payments', 'payments.id', '=', 'ec_orders.payment_id')
                ->where(function ($q): void {
                    $q->whereIn('payments.status', [PaymentStatusEnum::COMPLETED, PaymentStatusEnum::PENDING])
                        ->orWhereNull('ec_orders.payment_id');
                })
                ->where(function ($q) use ($currentPeriod): void {
                    $q->where(function ($subQ) use ($currentPeriod): void {
                        $subQ->whereDate('payments.created_at', '>=', $currentPeriod->getStartDate())
                            ->whereDate('payments.created_at', '<=', $currentPeriod->getEndDate());
                    })->orWhereNull('ec_orders.payment_id');
                })
                ->where('ec_orders.is_finished', true)
                ->count();

            $previousOrders = Order::query()
                ->whereDate('ec_orders.created_at', '>=', $previousPeriod->getStartDate())
                ->whereDate('ec_orders.created_at', '<=', $previousPeriod->getEndDate())
                ->leftJoin('payments', 'payments.id', '=', 'ec_orders.payment_id')
                ->where(function ($q): void {
                    $q->whereIn('payments.status', [PaymentStatusEnum::COMPLETED, PaymentStatusEnum::PENDING])
                        ->orWhereNull('ec_orders.payment_id');
                })
                ->where(function ($q) use ($previousPeriod): void {
                    $q->where(function ($subQ) use ($previousPeriod): void {
                        $subQ->whereDate('payments.created_at', '>=', $previousPeriod->getStartDate())
                            ->whereDate('payments.created_at', '<=', $previousPeriod->getEndDate());
                    })->orWhereNull('ec_orders.payment_id');
                })
                ->where('ec_orders.is_finished', true)
                ->count();
        } else {
            $currentOrders = Order::query()
                ->whereDate('created_at', '>=', $currentPeriod->getStartDate())
                ->whereDate('created_at', '<=', $currentPeriod->getEndDate())
                ->where('is_finished', true)
                ->count();

            $previousOrders = Order::query()
                ->whereDate('created_at', '>=', $previousPeriod->getStartDate())
                ->whereDate('created_at', '<=', $previousPeriod->getEndDate())
                ->where('is_finished', true)
                ->count();
        }

        $result = $currentOrders - $previousOrders;

        $result > 0 ? $this->chartColor = '#4ade80' : $this->chartColor = '#ff5b5b';

        return array_merge(parent::getViewData(), [
            'content' => view(
                'plugins/ecommerce::reports.widgets.new-order-card',
                compact('count', 'result')
            )->render(),
        ]);
    }
}
