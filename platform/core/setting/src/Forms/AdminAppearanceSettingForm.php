<?php

namespace Botble\Setting\Forms;

use Botble\Base\Facades\AdminAppearance;
use Botble\Base\Facades\AdminHelper;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Forms\FieldOptions\CodeEditorFieldOption;
use Botble\Base\Forms\FieldOptions\ColorFieldOption;
use Botble\Base\Forms\FieldOptions\GoogleFontsFieldOption;
use Botble\Base\Forms\FieldOptions\MediaImageFieldOption;
use Botble\Base\Forms\FieldOptions\MediaImagesFieldOption;
use Botble\Base\Forms\FieldOptions\NumberFieldOption;
use Botble\Base\Forms\FieldOptions\OnOffCheckboxFieldOption;
use Botble\Base\Forms\FieldOptions\RadioFieldOption;
use Botble\Base\Forms\FieldOptions\SelectFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\CodeEditorField;
use Botble\Base\Forms\Fields\ColorField;
use Botble\Base\Forms\Fields\GoogleFontsField;
use Botble\Base\Forms\Fields\MediaImageField;
use Botble\Base\Forms\Fields\MediaImagesField;
use Botble\Base\Forms\Fields\NumberField;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Forms\Fields\RadioField;
use Botble\Base\Forms\Fields\SelectField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Base\Forms\FormAbstract;
use Botble\GetStarted\Providers\GetStartedServiceProvider;
use Botble\Setting\Http\Requests\AdminAppearanceRequest;

class AdminAppearanceSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setSectionTitle(trans('core/setting::setting.admin_appearance.title'))
            ->setSectionDescription(trans('core/setting::setting.admin_appearance.description'))
            ->setValidatorClass(AdminAppearanceRequest::class)
            ->add(
                'admin_logo',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(trans('core/setting::setting.admin_appearance.form.admin_logo'))
                    ->value(setting('admin_logo'))
                    ->helperText(trans('core/setting::setting.admin_appearance.form.admin_logo_helper'))
            )
            ->add(
                'admin_logo_max_height',
                NumberField::class,
                NumberFieldOption::make()
                    ->label(trans('core/setting::setting.admin_appearance.form.admin_logo_max_height'))
                    ->helperText(trans('core/setting::setting.admin_appearance.form.admin_logo_max_height_helper', ['default' => '32px']))
                    ->value(setting('admin_logo_max_height', 32))
                    ->min(10)
                    ->max(300)
            )
            ->add(
                'admin_favicon',
                MediaImageField::class,
                MediaImageFieldOption::make()
                    ->label(trans('core/setting::setting.admin_appearance.form.admin_favicon'))
                    ->value(setting('admin_favicon'))
                    ->helperText(trans('core/setting::setting.admin_appearance.form.admin_favicon_helper'))
            )
            ->add(
                'admin_favicon_type',
                SelectField::class,
                SelectFieldOption::make()
                    ->label(trans('core/setting::setting.admin_appearance.form.admin_favicon_type'))
                    ->selected(setting('admin_favicon_type', 'image/x-icon'))
                    ->choices([
                        'image/x-icon' => 'ICO',
                        'image/png' => 'PNG',
                        'image/svg+xml' => 'SVG',
                        'image/gif' => 'GIF',
                        'image/jpeg' => 'JPEG',
                        'image/webp' => 'WebP',
                    ])
                    ->helperText(trans('core/setting::setting.admin_appearance.form.admin_favicon_type_helper'))
            )
            ->add(
                'login_screen_backgrounds[]',
                MediaImagesField::class,
                MediaImagesFieldOption::make()
                    ->label(trans('core/setting::setting.admin_appearance.form.admin_login_screen_backgrounds'))
                    ->selected(is_array(setting('login_screen_backgrounds', ''))
                        ? setting('login_screen_backgrounds', '')
                        : json_decode(setting('login_screen_backgrounds', ''), true))
                    ->helperText(trans('core/setting::setting.admin_appearance.form.admin_login_screen_backgrounds_helper'))
            )
            ->add(
                'admin_title',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('core/setting::setting.admin_appearance.form.admin_title'))
                    ->value(setting('admin_title', config('app.name')))
                    ->maxLength(120)
                    ->helperText(trans('core/setting::setting.admin_appearance.form.admin_title_helper'))
            )
            ->add(
                'admin_primary_font',
                GoogleFontsField::class,
                GoogleFontsFieldOption::make()
                    ->label(trans('core/setting::setting.admin_appearance.form.primary_font'))
                    ->selected(setting('admin_primary_font', 'Inter'))
                    ->helperText(trans('core/setting::setting.admin_appearance.form.primary_font_helper'))
            )
            ->add(
                'admin_primary_color',
                ColorField::class,
                ColorFieldOption::make()
                    ->label(trans('core/setting::setting.admin_appearance.form.primary_color'))
                    ->value(setting('admin_primary_color', '#206bc4'))
                    ->helperText(trans('core/setting::setting.admin_appearance.form.primary_color_helper'))
            )
            ->add(
                'admin_secondary_color',
                ColorField::class,
                ColorFieldOption::make()
                    ->label(trans('core/setting::setting.admin_appearance.form.secondary_color'))
                    ->value(setting('admin_secondary_color', '#6c7a91'))
                    ->helperText(trans('core/setting::setting.admin_appearance.form.secondary_color_helper'))
            )
            ->add(
                'admin_heading_color',
                ColorField::class,
                ColorFieldOption::make()
                    ->label(trans('core/setting::setting.admin_appearance.form.heading_color'))
                    ->value(setting('admin_heading_color', 'inherit'))
                    ->helperText(trans('core/setting::setting.admin_appearance.form.heading_color_helper'))
            )
            ->add(
                'admin_text_color',
                ColorField::class,
                ColorFieldOption::make()
                    ->label(trans('core/setting::setting.admin_appearance.form.text_color'))
                    ->value(setting('admin_text_color', '#182433'))
                    ->helperText(trans('core/setting::setting.admin_appearance.form.text_color_helper'))
            )
            ->add(
                'admin_link_color',
                ColorField::class,
                ColorFieldOption::make()
                    ->label(trans('core/setting::setting.admin_appearance.form.link_color'))
                    ->value(setting('admin_link_color', '#206bc4'))
                    ->helperText(trans('core/setting::setting.admin_appearance.form.link_color_helper'))
            )
            ->add(
                'admin_link_hover_color',
                ColorField::class,
                ColorFieldOption::make()
                    ->label(trans('core/setting::setting.admin_appearance.form.link_hover_color'))
                    ->value(setting('admin_link_hover_color', '#1a569d'))
                    ->helperText(trans('core/setting::setting.admin_appearance.form.link_hover_color_helper'))
            )
            ->when(! empty($locales = AdminHelper::getAdminLocales()), function (FormAbstract $form) use ($locales): void {
                $choices = ['' => trans('core/setting::setting.admin_appearance.form.admin_locale_default')] + $locales;

                $form->add(
                    AdminAppearance::getSettingKey('locale'),
                    SelectField::class,
                    SelectFieldOption::make()
                        ->label(trans('core/setting::setting.admin_appearance.form.admin_locale'))
                        ->choices($choices)
                        ->selected(AdminAppearance::getSetting('locale', ''))
                        ->searchable()
                        ->helperText(trans('core/setting::setting.admin_appearance.form.admin_locale_helper'))
                );
            })
            ->add(
                AdminAppearance::getSettingKey('locale_direction'),
                RadioField::class,
                RadioFieldOption::make()
                    ->label(trans('core/setting::setting.admin_appearance.form.admin_locale_direction'))
                    ->selected(AdminAppearance::getSetting('locale_direction', setting('admin_locale_direction', 'ltr')))
                    ->choices([
                        'ltr' => trans('core/setting::setting.locale_direction_ltr'),
                        'rtl' => trans('core/setting::setting.locale_direction_rtl'),
                    ])
                    ->helperText(trans('core/setting::setting.admin_appearance.form.admin_locale_direction_helper'))
            )
            ->add(
                'rich_editor',
                RadioField::class,
                RadioFieldOption::make()
                    ->label(trans('core/setting::setting.admin_appearance.form.rich_editor'))
                    ->selected(BaseHelper::getRichEditor())
                    ->choices(BaseHelper::availableRichEditors())
                    ->helperText(trans('core/setting::setting.admin_appearance.form.rich_editor_helper'))
            )
            ->add(
                AdminAppearance::getSettingKey('layout'),
                RadioField::class,
                RadioFieldOption::make()
                    ->label(trans('core/setting::setting.admin_appearance.layout'))
                    ->selected(AdminAppearance::getCurrentLayout())
                    ->choices(AdminAppearance::getLayouts())
                    ->helperText(trans('core/setting::setting.admin_appearance.layout_helper'))
            )
            ->add(
                AdminAppearance::getSettingKey('container_width'),
                RadioField::class,
                RadioFieldOption::make()
                    ->label(trans('core/setting::setting.admin_appearance.container_width.title'))
                    ->selected(AdminAppearance::getContainerWidth())
                    ->choices(AdminAppearance::getContainerWidths())
                    ->helperText(trans('core/setting::setting.admin_appearance.container_width.title_helper'))
            )
            ->add(
                AdminAppearance::getSettingKey('show_menu_item_icon'),
                OnOffCheckboxField::class,
                OnOffCheckboxFieldOption::make()
                    ->label(trans('core/setting::setting.admin_appearance.form.show_menu_item_icon'))
                    ->value(AdminAppearance::showMenuItemIcon())
                    ->helperText(trans('core/setting::setting.admin_appearance.form.show_menu_item_icon_helper'))
            )
            ->when(class_exists(GetStartedServiceProvider::class), function (FormAbstract $form): void {
                $form->add(
                    AdminAppearance::getSettingKey('show_get_started'),
                    OnOffCheckboxField::class,
                    OnOffCheckboxFieldOption::make()
                        ->label(trans('core/setting::setting.admin_appearance.form.show_get_started'))
                        ->value(AdminAppearance::getSetting('show_get_started', '1'))
                        ->helperText(trans('core/setting::setting.admin_appearance.form.show_get_started_helper'))
                );
            })
            ->add(
                AdminAppearance::getSettingKey('custom_css'),
                CodeEditorField::class,
                CodeEditorFieldOption::make()
                    ->label(trans('core/setting::setting.admin_appearance.form.custom_css'))
                    ->value(AdminAppearance::getSetting('custom_css'))
                    ->mode('css')
                    ->helperText(trans('core/setting::setting.admin_appearance.form.custom_css_helper'))
            )
            ->add(
                AdminAppearance::getSettingKey('custom_header_js'),
                CodeEditorField::class,
                CodeEditorFieldOption::make()
                    ->label(trans('core/setting::setting.admin_appearance.form.custom_header_js'))
                    ->helperText(trans('core/setting::setting.admin_appearance.form.custom_header_js_placeholder'))
                    ->value(AdminAppearance::getSetting('custom_header_js'))
                    ->mode('javascript')
                    ->maxLength(2500)
            )
            ->add(
                AdminAppearance::getSettingKey('custom_body_js'),
                CodeEditorField::class,
                CodeEditorFieldOption::make()
                    ->label(trans('core/setting::setting.admin_appearance.form.custom_body_js'))
                    ->helperText(trans('core/setting::setting.admin_appearance.form.custom_body_js_placeholder'))
                    ->value(AdminAppearance::getSetting('custom_body_js'))
                    ->mode('javascript')
                    ->maxLength(2500)
            )
            ->add(
                AdminAppearance::getSettingKey('custom_footer_js'),
                CodeEditorField::class,
                CodeEditorFieldOption::make()
                    ->label(trans('core/setting::setting.admin_appearance.form.custom_footer_js'))
                    ->helperText(trans('core/setting::setting.admin_appearance.form.custom_footer_js_placeholder'))
                    ->value(AdminAppearance::getSetting('custom_footer_js'))
                    ->mode('javascript')
                    ->maxLength(2500)
            );
    }
}
