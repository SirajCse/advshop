<?php

namespace Botble\Ecommerce\Forms\Settings;

use Botble\Base\Forms\FieldOptions\AlertFieldOption;
use Botble\Base\Forms\FieldOptions\HtmlFieldOption;
use Botble\Base\Forms\FieldOptions\NumberFieldOption;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\Fields\AlertField;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Ecommerce\Http\Requests\Settings\AbandonedCartSettingRequest;
use Botble\Setting\Facades\Setting;
use Botble\Setting\Forms\SettingForm;
use Carbon\Carbon;

class AbandonedCartSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $cronjobStatus = $this->getCronjobStatus();

        $this
            ->setSectionTitle(trans('plugins/ecommerce::setting.abandoned_cart.name'))
            ->setSectionDescription(trans('plugins/ecommerce::setting.abandoned_cart.description'))
            ->setValidatorClass(AbandonedCartSettingRequest::class)
            ->add(
                'cronjob_status',
                AlertField::class,
                AlertFieldOption::make()
                    ->type($cronjobStatus['type'])
                    ->content($cronjobStatus['message'])
            )
            ->add(
                'how_it_works_section',
                HtmlField::class,
                HtmlFieldOption::make()->content($this->getHowItWorksHtml())
            )
            ->add(
                'abandoned_cart_enabled',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/ecommerce::setting.abandoned_cart.form.enable'))
                    ->helperText(trans('plugins/ecommerce::setting.abandoned_cart.form.enable_helper'))
                    ->value($enabled = get_ecommerce_setting('abandoned_cart_enabled', false))
            )
            ->addOpenCollapsible('abandoned_cart_enabled', '1', $enabled == '1')
            ->add(
                'email_section',
                HtmlField::class,
                HtmlFieldOption::make()->content('<h4 class="mb-3">' . trans('plugins/ecommerce::setting.abandoned_cart.form.email_section') . '</h4>')
            )
            ->add(
                'email_setup_warning',
                AlertField::class,
                AlertFieldOption::make()
                    ->type('info')
                    ->content(trans('plugins/ecommerce::setting.abandoned_cart.form.email_setup_warning', [
                        'url' => route('settings.email'),
                    ]))
            )
            ->add(
                'abandoned_cart_send_email',
                OnOffCheckboxField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/ecommerce::setting.abandoned_cart.form.send_email'))
                    ->helperText(trans('plugins/ecommerce::setting.abandoned_cart.form.send_email_helper'))
                    ->value($sendEmail = get_ecommerce_setting('abandoned_cart_send_email', true))
            )
            ->addOpenCollapsible('abandoned_cart_send_email', '1', $sendEmail == '1')
            ->add(
                'abandoned_cart_max_reminders',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/ecommerce::setting.abandoned_cart.form.max_reminders'))
                    ->helperText(trans('plugins/ecommerce::setting.abandoned_cart.form.max_reminders_helper'))
                    ->value(get_ecommerce_setting('abandoned_cart_max_reminders', 3))
                    ->min(1)
                    ->max(3)
            )
            ->add(
                'email_1_section',
                HtmlField::class,
                HtmlFieldOption::make()->content('<h5 class="mb-2 mt-3">' . trans('plugins/ecommerce::setting.abandoned_cart.form.email_1_title') . '</h5><p class="text-muted small mb-3">' . trans('plugins/ecommerce::setting.abandoned_cart.form.email_1_description') . '</p>')
            )
            ->add(
                'abandoned_cart_email_1_delay',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/ecommerce::setting.abandoned_cart.form.email_delay'))
                    ->helperText(trans('plugins/ecommerce::setting.abandoned_cart.form.email_1_delay_helper'))
                    ->value(get_ecommerce_setting('abandoned_cart_email_1_delay', 1))
                    ->min(1)
                    ->max(48)
            )
            ->add(
                'email_2_section',
                HtmlField::class,
                HtmlFieldOption::make()->content('<h5 class="mb-2 mt-4 pt-3 border-top">' . trans('plugins/ecommerce::setting.abandoned_cart.form.email_2_title') . '</h5><p class="text-muted small mb-3">' . trans('plugins/ecommerce::setting.abandoned_cart.form.email_2_description') . '</p>')
            )
            ->add(
                'abandoned_cart_email_2_delay',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/ecommerce::setting.abandoned_cart.form.email_delay'))
                    ->helperText(trans('plugins/ecommerce::setting.abandoned_cart.form.email_2_delay_helper'))
                    ->value(get_ecommerce_setting('abandoned_cart_email_2_delay', 24))
                    ->min(2)
                    ->max(96)
            )
            ->add(
                'email_3_section',
                HtmlField::class,
                HtmlFieldOption::make()->content('<h5 class="mb-2 mt-4 pt-3 border-top">' . trans('plugins/ecommerce::setting.abandoned_cart.form.email_3_title') . '</h5><p class="text-muted small mb-3">' . trans('plugins/ecommerce::setting.abandoned_cart.form.email_3_description') . '</p>')
            )
            ->add(
                'abandoned_cart_email_3_delay',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/ecommerce::setting.abandoned_cart.form.email_delay'))
                    ->helperText(trans('plugins/ecommerce::setting.abandoned_cart.form.email_3_delay_helper'))
                    ->value(get_ecommerce_setting('abandoned_cart_email_3_delay', 72))
                    ->min(24)
                    ->max(168)
            )
            ->addCloseCollapsible('abandoned_cart_send_email', '1')
            ->add(
                'cleanup_section',
                HtmlField::class,
                HtmlFieldOption::make()->content('<h4 class="mb-3 mt-4 pt-3 border-top">' . trans('plugins/ecommerce::setting.abandoned_cart.form.cleanup_section') . '</h4>')
            )
            ->add(
                'abandoned_cart_cleanup_days',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('plugins/ecommerce::setting.abandoned_cart.form.cleanup_days'))
                    ->helperText(trans('plugins/ecommerce::setting.abandoned_cart.form.cleanup_days_helper'))
                    ->value(get_ecommerce_setting('abandoned_cart_cleanup_days', 30))
                    ->min(7)
                    ->max(365)
            )
            ->addCloseCollapsible('abandoned_cart_enabled', '1');
    }

    protected function getCronjobStatus(): array
    {
        $lastRunAt = Setting::get('cronjob_last_run_at');

        if (! $lastRunAt) {
            return [
                'type' => 'warning',
                'message' => trans('plugins/ecommerce::setting.abandoned_cart.form.cronjob_not_setup', [
                    'url' => route('system.cronjob'),
                ]),
            ];
        }

        $lastRunAt = Carbon::parse($lastRunAt);

        if (Carbon::now()->diffInMinutes($lastRunAt) > 10) {
            return [
                'type' => 'danger',
                'message' => trans('plugins/ecommerce::setting.abandoned_cart.form.cronjob_not_running', [
                    'url' => route('system.cronjob'),
                ]),
            ];
        }

        return [
            'type' => 'success',
            'message' => trans('plugins/ecommerce::setting.abandoned_cart.form.cronjob_working', [
                'time' => $lastRunAt->diffForHumans(),
            ]),
        ];
    }

    protected function getHowItWorksHtml(): string
    {
        $steps = [
            [
                'title' => trans('plugins/ecommerce::setting.abandoned_cart.how_it_works.step1_title'),
                'description' => trans('plugins/ecommerce::setting.abandoned_cart.how_it_works.step1_description'),
            ],
            [
                'title' => trans('plugins/ecommerce::setting.abandoned_cart.how_it_works.step2_title'),
                'description' => trans('plugins/ecommerce::setting.abandoned_cart.how_it_works.step2_description'),
            ],
            [
                'title' => trans('plugins/ecommerce::setting.abandoned_cart.how_it_works.step3_title'),
                'description' => trans('plugins/ecommerce::setting.abandoned_cart.how_it_works.step3_description'),
            ],
            [
                'title' => trans('plugins/ecommerce::setting.abandoned_cart.how_it_works.step4_title'),
                'description' => trans('plugins/ecommerce::setting.abandoned_cart.how_it_works.step4_description'),
            ],
        ];

        $html = '<div class="card mb-4">';
        $html .= '<div class="card-header"><h3 class="card-title">' . trans('plugins/ecommerce::setting.abandoned_cart.how_it_works.title') . '</h3></div>';
        $html .= '<div class="card-body">';
        $html .= '<div class="row g-3">';

        foreach ($steps as $index => $step) {
            $html .= '<div class="col-md-6 col-lg-3">';
            $html .= '<div class="card card-body h-100">';
            $html .= '<div class="d-flex align-items-center mb-2">';
            $html .= '<span class="badge bg-blue-lt me-2">' . ($index + 1) . '</span>';
            $html .= '<span class="fw-semibold">' . $step['title'] . '</span>';
            $html .= '</div>';
            $html .= '<p class="text-muted mb-0 small">' . $step['description'] . '</p>';
            $html .= '</div>';
            $html .= '</div>';
        }

        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }
}
