<?php

namespace ArchiElite\Announcement\Forms;

use ArchiElite\Announcement\Http\Requests\AnnouncementRequest;
use ArchiElite\Announcement\Models\Announcement;
use Botble\Base\Forms\FieldOptions\DatePickerFieldOption;
use Botble\Base\Forms\Fields\DatePickerField;
use Botble\Base\Forms\FormAbstract;

class AnnouncementForm extends FormAbstract
{
    public function buildForm(): void
    {
        $this
            ->model(Announcement::class)
            ->setValidatorClass(AnnouncementRequest::class)
            ->withCustomFields()
            ->add('name', 'text', [
                'label' => trans('core/base::forms.name'),
                'required' => true,
                'attr' => [
                    'placeholder' => trans('plugins/announcement::announcements.name_placeholder'),
                    'data-counter' => 255,
                ],
                'help_block' => [
                    'text' => trans('plugins/announcement::announcements.name_help'),
                ],
            ])
            ->add('content', 'editor', [
                'label' => trans('core/base::forms.content'),
                'required' => true,
                'attr' => [
                    'rows' => 2,
                    'without-buttons' => true,
                    'placeholder' => trans('plugins/announcement::announcements.content_placeholder'),
                ],
                'help_block' => [
                    'text' => trans('plugins/announcement::announcements.content_help'),
                ],
            ])
            ->add('openRow1', 'html', [
                'html' => '<div class="row">',
            ])
            ->add(
                'start_date',
                DatePickerField::class,
                DatePickerFieldOption::make()
                    ->label(trans('plugins/announcement::announcements.start_date'))
                    ->placeholder(trans('plugins/announcement::announcements.start_date_placeholder'))
                    ->helperText(trans('plugins/announcement::announcements.start_date_help'))
                    ->wrapperAttributes(['class' => 'col-md-6 mb-3'])
                    ->withTimePicker()
            )
            ->add(
                'end_date',
                DatePickerField::class,
                DatePickerFieldOption::make()
                    ->label(trans('plugins/announcement::announcements.end_date'))
                    ->placeholder(trans('plugins/announcement::announcements.end_date_placeholder'))
                    ->helperText(trans('plugins/announcement::announcements.end_date_help'))
                    ->wrapperAttributes(['class' => 'col-md-6 mb-3'])
                    ->withTimePicker()
            )
            ->add('closeRow1', 'html', [
                'html' => '</div>',
            ])
            ->add('has_action', 'onOff', [
                'label' => trans('plugins/announcement::announcements.has_action'),
                'attr' => [
                    'data-bb-toggle' => 'collapse',
                    'data-bb-target' => '.has-action',
                ],
                'help_block' => [
                    'text' => trans('plugins/announcement::announcements.has_action_help'),
                ],
            ])
            ->add('openRow2', 'html', [
                'html' => sprintf('<div class="row has-action" style="%s">', $this->getModel()->has_action ? '' : 'display: none;'),
            ])
            ->add('action_label', 'text', [
                'label' => trans('plugins/announcement::announcements.action_label'),
                'attr' => [
                    'placeholder' => trans('plugins/announcement::announcements.action_label_placeholder'),
                ],
                'wrapper' => [
                    'class' => 'col-md-6 mb-3',
                ],
                'help_block' => [
                    'text' => trans('plugins/announcement::announcements.action_label_help'),
                ],
            ])
            ->add('action_url', 'text', [
                'label' => trans('plugins/announcement::announcements.action_url'),
                'attr' => [
                    'placeholder' => trans('plugins/announcement::announcements.action_url_placeholder'),
                ],
                'wrapper' => [
                    'class' => 'col-md-6 mb-3',
                ],
                'help_block' => [
                    'text' => trans('plugins/announcement::announcements.action_url_help'),
                ],
            ])
            ->add('action_open_new_tab', 'onOff', [
                'label' => trans('plugins/announcement::announcements.action_open_new_tab'),
                'wrapper' => [
                    'class' => 'col-md-12 mb-3',
                ],
                'help_block' => [
                    'text' => trans('plugins/announcement::announcements.action_open_new_tab_help'),
                ],
            ])
            ->add('closeRow2', 'html', [
                'html' => '</div>',
            ])
            ->add('is_active', 'onOff', [
                'label' => trans('plugins/announcement::announcements.is_active'),
                'label_attr' => ['class' => 'control-label'],
                'default_value' => true,
                'help_block' => [
                    'text' => trans('plugins/announcement::announcements.is_active_help'),
                ],
            ])
            ->setBreakFieldPoint('is_active');
    }
}
