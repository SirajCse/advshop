<?php

namespace Botble\Ecommerce\Widgets;

use Botble\Base\Widgets\Card;
use Botble\Ecommerce\Models\Order;
use Botble\Payment\Enums\PaymentStatusEnum;
use Carbon\CarbonPeriod;

class AverageOrderValueCard extends Card
{
    public function getOptions(): array
    {
        $data = [];

        if (is_plugin_active('payment')) {
            $data = Order::query()
                ->whereDate('ec_orders.created_at', '>=', $this->startDate)
                ->whereDate('ec_orders.created_at', '<=', $this->endDate)
                ->leftJoin('payments', 'payments.order_id', '=', 'ec_orders.id')
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
                ->selectRaw('DATE(ec_orders.created_at) as date, SUM(ec_orders.amount) / COUNT(ec_orders.id) as average')
                ->groupBy('date')
                ->pluck('average')
                ->toArray();
        } else {
            $data = Order::query()
                ->whereDate('created_at', '>=', $this->startDate)
                ->whereDate('created_at', '<=', $this->endDate)
                ->where('is_finished', true)
                ->selectRaw('DATE(created_at) as date, SUM(amount) / COUNT(id) as average')
                ->groupBy('date')
                ->pluck('average')
                ->toArray();
        }

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
        $startDate = clone $this->startDate;
        $endDate = clone $this->endDate;

        $currentPeriod = CarbonPeriod::create($startDate, $endDate);
        $previousPeriod = CarbonPeriod::create(
            $startDate->subDays($currentPeriod->count()),
            $endDate->subDays($currentPeriod->count())
        );

        // Current period average
        if (is_plugin_active('payment')) {
            $currentAverage = Order::query()
                ->whereDate('ec_orders.created_at', '>=', $currentPeriod->getStartDate())
                ->whereDate('ec_orders.created_at', '<=', $currentPeriod->getEndDate())
                ->leftJoin('payments', 'payments.order_id', '=', 'ec_orders.id')
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
                ->selectRaw('SUM(ec_orders.amount) / COUNT(ec_orders.id) as average')
                ->first();
        } else {
            $currentAverage = Order::query()
                ->whereDate('created_at', '>=', $currentPeriod->getStartDate())
                ->whereDate('created_at', '<=', $currentPeriod->getEndDate())
                ->where('is_finished', true)
                ->selectRaw('SUM(amount) / COUNT(id) as average')
                ->first();
        }

        // Previous period average
        if (is_plugin_active('payment')) {
            $previousAverage = Order::query()
                ->whereDate('ec_orders.created_at', '>=', $previousPeriod->getStartDate())
                ->whereDate('ec_orders.created_at', '<=', $previousPeriod->getEndDate())
                ->leftJoin('payments', 'payments.order_id', '=', 'ec_orders.id')
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
                ->selectRaw('SUM(ec_orders.amount) / COUNT(ec_orders.id) as average')
                ->first();
        } else {
            $previousAverage = Order::query()
                ->whereDate('created_at', '>=', $previousPeriod->getStartDate())
                ->whereDate('created_at', '<=', $previousPeriod->getEndDate())
                ->where('is_finished', true)
                ->selectRaw('SUM(amount) / COUNT(id) as average')
                ->first();
        }

        $currentAverageValue = $currentAverage->average ?? 0;
        $previousAverageValue = $previousAverage->average ?? 0;

        $result = ($previousAverageValue > 0) ? (($currentAverageValue - $previousAverageValue) / $previousAverageValue) * 100 : 0;

        $result > 0 ? $this->chartColor = '#4ade80' : $this->chartColor = '#ff5b5b';

        return array_merge(parent::getViewData(), [
            'content' => view(
                'plugins/ecommerce::reports.widgets.average-order-value-card',
                [
                    'average' => $currentAverageValue,
                    'result' => $result,
                ]
            )->render(),
        ]);
    }

    public function getLabel(): string
    {
        return trans('plugins/ecommerce::reports.average_order_value');
    }
}
