<?php

namespace Botble\Page\Providers;

use Botble\Base\Facades\Html;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Rules\OnOffRule;
use Botble\Base\Supports\RepositoryHelper;
use Botble\Base\Supports\ServiceProvider;
use Botble\Dashboard\Events\RenderingDashboardWidgets;
use Botble\Dashboard\Supports\DashboardWidgetInstance;
use Botble\Media\Facades\RvMedia;
use Botble\Menu\Events\RenderingMenuOptions;
use Botble\Menu\Facades\Menu;
use Botble\Page\Models\Page;
use Botble\Page\Services\PageService;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Setting\Forms\AdminAppearanceSettingForm;
use Botble\Setting\Http\Requests\AdminAppearanceRequest;
use Botble\Shortcode\Compilers\ShortcodeCompiler;
use Botble\Slug\Models\Slug;
use Botble\Table\Columns\Column;
use Botble\Table\Columns\NameColumn;
use Botble\Theme\Events\RenderingThemeOptionSettings;
use Botble\Theme\Facades\Theme;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use ReflectionClass;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Menu::addMenuOptionModel(Page::class);

        $this->app['events']->listen(RenderingMenuOptions::class, function (): void {
            add_action(MENU_ACTION_SIDEBAR_OPTIONS, [$this, 'registerMenuOptions'], 10);
        });

        $this->app['events']->listen(RenderingDashboardWidgets::class, function (): void {
            add_filter(DASHBOARD_FILTER_ADMIN_LIST, [$this, 'addPageStatsWidget'], 15, 2);
        });

        add_filter(BASE_FILTER_PUBLIC_SINGLE_DATA, [$this, 'handleSingleView'], 1);

        $this->app['events']->listen(RenderingThemeOptionSettings::class, function (): void {
            $pages = Page::query()
                ->wherePublished();

            $pages = RepositoryHelper::applyBeforeExecuteQuery($pages, new Page())
                ->pluck('name', 'id')
                ->all();

            theme_option()
                ->when($pages, function () use ($pages): void {
                    theme_option()
                        ->setSection([
                            'title' => trans('packages/page::pages.theme_options.title'),
                            'id' => 'opt-text-subsection-page',
                            'subsection' => true,
                            'icon' => 'ti ti-book',
                            'shared' => true,
                            'fields' => [
                                [
                                    'id' => 'homepage_id',
                                    'type' => 'customSelect',
                                    'label' => trans('packages/page::pages.theme_options.your_home_page_display'),
                                    'attributes' => [
                                        'name' => 'homepage_id',
                                        'list' => [0 => trans('core/base::forms.select_placeholder')] + $pages,
                                        'value' => '',
                                        'options' => [
                                            'class' => 'form-control',
                                        ],
                                    ],
                                ],
                            ],
                        ]);
                });
        });

        $this->app['events']->listen(RouteMatched::class, function (): void {
            if (defined('THEME_FRONT_HEADER')) {
                add_action(BASE_ACTION_PUBLIC_RENDER_SINGLE, function ($screen, $page): void {
                    add_filter(THEME_FRONT_HEADER, function (?string $html) use ($page): string|null {
                        if ($page::class != Page::class) {
                            return $html;
                        }

                        $schema = [
                            '@context' => 'https://schema.org',
                            '@type' => 'Organization',
                            'name' => rescue(fn () => SeoHelper::openGraph()->getProperty('site_name')),
                            'url' => $page->url,
                            'logo' => [
                                '@type' => 'ImageObject',
                                'url' => RvMedia::getImageUrl(Theme::getLogo()),
                            ],
                        ];

                        return $html . Html::tag('script', json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), ['type' => 'application/ld+json'])
                                ->toHtml();
                    }, 2);
                }, 2, 2);
            }

            add_filter(PAGE_FILTER_FRONT_PAGE_CONTENT, fn (?string $html) => (string) $html, 1, 2);

            add_filter('table_name_column_data', [$this, 'appendPageName'], 2, 3);

            $this->registerVisualBuilderButton();

            $this->registerVisualBuilderDataAttributes();

            $this->injectVisualBuilderIframeScript();
        });

        if (! config('core.base.general.disable_front_theme')) {
            AdminAppearanceSettingForm::extend(function (AdminAppearanceSettingForm $form): void {
                $form
                    ->addAfter('rich_editor', 'enable_page_visual_builder', OnOffCheckboxField::class, [
                        'label' => trans('packages/page::pages.settings.enable_page_visual_builder'),
                        'value' => setting('enable_page_visual_builder', true),
                        'help_block' => [
                            'text' => trans('packages/page::pages.settings.enable_page_visual_builder_helper'),
                        ],
                    ]);
            }, 120);

            add_filter('core_request_rules', function ($rules, Request $request) {
                if ($request instanceof AdminAppearanceRequest) {
                    $rules = [
                        ...$rules,
                        'enable_page_visual_builder' => new OnOffRule(),
                    ];
                }

                return $rules;
            }, 120, 2);
        }
    }

    protected function registerVisualBuilderButton(): void
    {
        add_filter(BASE_FILTER_FORM_EDITOR_BUTTONS, function (?string $buttons, array $attributes, string $id) {
            if ($id !== 'content') {
                return $buttons;
            }

            $routeName = request()->route()?->getName();
            if (! str_starts_with((string) $routeName, 'pages.')) {
                return $buttons;
            }

            if (! setting('enable_page_visual_builder', true) || config('core.base.general.disable_front_theme')) {
                return $buttons;
            }

            $page = request()->route('page');
            if (! $page instanceof Page || ! $page->getKey()) {
                return $buttons;
            }

            $buttons = (string) $buttons;

            $visualBuilderUrl = route('pages.visual-builder', $page);

            if ($refLang = request()->input('ref_lang')) {
                $visualBuilderUrl .= '?' . http_build_query(['ref_lang' => $refLang]);
            }

            $buttons .= view('packages/page::forms.partials.visual-builder-button', [
                'url' => $visualBuilderUrl,
                'label' => trans('packages/page::pages.visual_builder_button'),
            ])->render();

            return $buttons;
        }, 120, 3);
    }

    protected function registerVisualBuilderDataAttributes(): void
    {
        add_filter(
            'shortcode_content_compiled',
            function (?string $html, string $name, $callback, ShortcodeCompiler $compiler) {
                if (! request()->has('visual_builder') && ! request()->has('shortcodeId')) {
                    return $html;
                }

                if (empty($html)) {
                    return $html;
                }

                $shortcodeId = null;

                try {
                    $reflection = new ReflectionClass($compiler);
                    $matchesProperty = $reflection->getProperty('matches');
                    $matches = $matchesProperty->getValue($compiler);

                    if (! empty($matches[3])) {
                        $attributesString = $matches[3];
                        if (preg_match('/data-vb-id\s*=\s*["\']([^"\']+)["\']/', $attributesString, $idMatch)) {
                            $shortcodeId = $idMatch[1];
                        }
                    }
                } catch (Exception) {
                }

                if (empty($shortcodeId)) {
                    static $shortcodeCounter = 0;
                    $shortcodeId = 'sc_' . time() . '_' . $shortcodeCounter++;
                }

                return $this->injectDataAttributes($html, [
                    'data-shortcode-id' => $shortcodeId,
                    'data-shortcode-name' => $name,
                ]);
            },
            9998,
            4
        );
    }

    protected function injectDataAttributes(string $html, array $attributes): string
    {
        $attributeString = '';
        foreach ($attributes as $key => $value) {
            $attributeString .= sprintf(' %s="%s"', $key, htmlspecialchars($value, ENT_QUOTES));
        }

        $pattern = '/^(<[a-z][a-z0-9]*)([\s>])/i';

        if (preg_match($pattern, $html)) {
            $html = preg_replace($pattern, '$1' . $attributeString . '$2', $html, 1);
        } else {
            $html = '<div' . $attributeString . '>' . $html . '</div>';
        }

        return $html;
    }

    protected function injectVisualBuilderIframeScript(): void
    {
        add_filter(THEME_FRONT_FOOTER, function (?string $html) {
            if (! request()->has('visual_builder')) {
                return $html;
            }

            $script = view('packages/page::visual-builder.iframe-script')->render();

            return $html . $script;
        }, 999);
    }

    public function appendPageName(string $value, Model $model, Column $column)
    {
        if ($column instanceof NameColumn && $model instanceof Page) {
            $value = apply_filters(PAGE_FILTER_PAGE_NAME_IN_ADMIN_LIST, $value, $model);
        }

        return $value;
    }

    public function registerMenuOptions(): void
    {
        if (Auth::guard()->user()->hasPermission('pages.index')) {
            Menu::registerMenuOptions(Page::class, trans('packages/page::pages.menu'));
        }
    }

    public function addPageStatsWidget(array $widgets, Collection $widgetSettings): array
    {
        $pages = fn () => Page::query()->wherePublished()->count();

        return (new DashboardWidgetInstance())
            ->setType('stats')
            ->setPermission('pages.index')
            ->setTitle(trans('packages/page::pages.pages'))
            ->setKey('widget_total_pages')
            ->setIcon('ti ti-files')
            ->setColor('yellow')
            ->setStatsTotal($pages)
            ->setRoute(route('pages.index'))
            ->setColumn('col-12 col-md-6 col-lg-3')
            ->init($widgets, $widgetSettings);
    }

    public function handleSingleView(Slug|array $slug): Slug|array
    {
        return (new PageService())->handleFrontRoutes($slug);
    }
}
