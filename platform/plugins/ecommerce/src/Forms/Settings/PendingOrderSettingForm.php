<?php

namespace Botble\Ecommerce\Forms\Settings;

use Botble\Base\Forms\FieldOptions\AlertFieldOption;
use Botble\Base\Forms\FieldOptions\NumberFieldOption;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\Fields\AlertField;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Ecommerce\Http\Requests\Settings\PendingOrderSettingRequest;
use Botble\Setting\Facades\Setting;
use Botble\Setting\Forms\SettingForm;
use Carbon\Carbon;

class PendingOrderSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $cronjobStatus = $this->getCronjobStatus();

        $this
            ->setSectionTitle(trans('plugins/ecommerce::setting.pending_orders.name'))
            ->setSectionDescription(trans('plugins/ecommerce::setting.pending_orders.description'))
            ->setValidatorClass(PendingOrderSettingRequest::class)
            ->add(
                'cronjob_status',
                AlertField::class,
                AlertFieldOption::make()
                    ->type($cronjobStatus['type'])
                    ->content($cronjobStatus['message'])
            )
            ->add(
                'auto_cancel_pending_orders_enabled',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/ecommerce::setting.pending_orders.form.enable'))
                    ->helperText(trans('plugins/ecommerce::setting.pending_orders.form.enable_helper'))
                    ->value($enabled = get_ecommerce_setting('auto_cancel_pending_orders_enabled', false))
            )
            ->addOpenCollapsible('auto_cancel_pending_orders_enabled', '1', $enabled == '1')
            ->add(
                'auto_cancel_pending_orders_threshold_minutes',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/ecommerce::setting.pending_orders.form.threshold_minutes'))
                    ->helperText(trans('plugins/ecommerce::setting.pending_orders.form.threshold_minutes_helper'))
                    ->value(get_ecommerce_setting('auto_cancel_pending_orders_threshold_minutes', 30))
                    ->min(5)
                    ->max(1440)
            )
            ->addCloseCollapsible('auto_cancel_pending_orders_enabled', '1');
    }

    protected function getCronjobStatus(): array
    {
        $lastRunAt = Setting::get('cronjob_last_run_at');

        if (! $lastRunAt) {
            return [
                'type' => 'warning',
                'message' => trans('plugins/ecommerce::setting.pending_orders.form.cronjob_not_setup', [
                    'url' => route('system.cronjob'),
                ]),
            ];
        }

        $lastRunAt = Carbon::parse($lastRunAt);

        if (Carbon::now()->diffInMinutes($lastRunAt) > 10) {
            return [
                'type' => 'danger',
                'message' => trans('plugins/ecommerce::setting.pending_orders.form.cronjob_not_running', [
                    'url' => route('system.cronjob'),
                ]),
            ];
        }

        return [
            'type' => 'success',
            'message' => trans('plugins/ecommerce::setting.pending_orders.form.cronjob_working', [
                'time' => $lastRunAt->diffForHumans(),
            ]),
        ];
    }
}
