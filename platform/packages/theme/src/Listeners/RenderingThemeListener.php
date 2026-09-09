<?php

namespace Botble\Theme\Listeners;

use Botble\Base\Facades\Html;
use Botble\Shortcode\Compilers\ShortcodeCompiler;
use Botble\Theme\Facades\AdminBar;
use Botble\Theme\Facades\Theme;
use Botble\Theme\Supports\ThemeSupport;
use Illuminate\Support\Facades\Auth;
use Throwable;

class RenderingThemeListener
{
    public function handle(): void
    {
        $this->registerAdminBar();
        $this->registerShortcodeGuideline();
    }

    protected function registerAdminBar(): void
    {
        add_filter(THEME_FRONT_FOOTER, function (?string $html): ?string {
            try {
                if (! Auth::guard()->check() || ! AdminBar::isDisplay() || ! (int) setting('show_admin_bar', 1)) {
                    return $html;
                }

                return $html . Html::style('vendor/core/packages/theme/css/admin-bar.css') . AdminBar::render();
            } catch (Throwable) {
                return $html;
            }
        }, 14);
    }

    protected function registerShortcodeGuideline(): void
    {
        add_filter(
            'shortcode_content_compiled',
            function (?string $html, string $name, $callback, ShortcodeCompiler $compiler) {
                $editLink = $compiler->getEditLink();

                if (request()->expectsJson() || request()->ajax()) {
                    return $html;
                }

                if (! $editLink || ! setting('show_theme_guideline_link', false) || request()->input('visual_builder')) {
                    return $html;
                }

                Theme::asset()
                    ->usePath(false)
                    ->add('theme-guideline-css', asset('vendor/core/packages/theme/css/guideline.css'));

                $link = view('packages/theme::guideline-link', [
                    'html' => $html,
                    'editLink' => $editLink . '?shortcode=' . $compiler->getName(),
                    'editLabel' => trans('packages/theme::theme.shortcode_labels.edit_this_shortcode'),
                ])->render();

                return ThemeSupport::insertBlockAfterTopHtmlTags($link, $html);
            },
            9999,
            4
        );
    }
}
