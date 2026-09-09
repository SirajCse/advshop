<?php

namespace Botble\Shortcode\Http\Controllers;

use Botble\Base\Facades\Html;
use Botble\Base\Forms\FormAbstract;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Shortcode\Events\ShortcodeAdminConfigRendering;
use Botble\Shortcode\Facades\Shortcode;
use Botble\Shortcode\Forms\ShortcodeForm;
use Botble\Shortcode\Http\Requests\GetShortcodeDataRequest;
use Botble\Shortcode\Http\Requests\RenderBlockUiRequest;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class ShortcodeController extends BaseController
{
    public function ajaxGetAdminConfig(?string $key, GetShortcodeDataRequest $request)
    {
        ShortcodeAdminConfigRendering::dispatch();

        $registered = shortcode()->getAll();

        $key = $key ?: $request->input('key');

        $data = Arr::get($registered, $key . '.admin_config');

        $attributes = [];
        $content = null;

        if ($code = $request->input('code')) {
            $compiler = shortcode()->getCompiler();

            $processedCode = $this->protectHtmlInAttributes($code);
            $attributes = $compiler->getAttributes(html_entity_decode($processedCode));
            $content = $compiler->getContent();
            $attributes = $this->restoreHtmlInAttributes($attributes);
        } else {
            $attributes = $request->except(['_token', 'key', 'code']);
            if (isset($attributes['content'])) {
                $content = $attributes['content'];
                unset($attributes['content']);
            }
        }

        if ($data instanceof Closure || is_callable($data)) {
            $data = call_user_func($data, $attributes, $content);

            if ($modifier = Arr::get($registered, $key . '.admin_config_modifier')) {
                $data = call_user_func($modifier, $data, $attributes, $content);
            }

            // If it's a ShortcodeForm, add cache warning and caching options
            if ($data instanceof ShortcodeForm) {
                $data->withCacheWarning($key)->withCaching();
                $data = $data->renderForm();
            } elseif ($data instanceof FormAbstract) {
                $data = $data->renderForm();
            }
        }

        $data = apply_filters(SHORTCODE_REGISTER_CONTENT_IN_ADMIN, $data, $key, $attributes);

        if (! $data) {
            $data = Html::tag('code', Shortcode::generateShortcode($key, $attributes))->toHtml();
        }

        return $this
            ->httpResponse()
            ->setData($data);
    }

    public function ajaxRenderUiBlock(RenderBlockUiRequest $request)
    {
        $name = $request->input('name');

        if (! in_array($name, array_keys(Shortcode::getAll()))) {
            return $this
                ->httpResponse()
                ->setData(null);
        }

        $attributes = $request->input('attributes', []);
        $shortcodeId = $request->input('shortcodeId');

        if ($shortcodeId) {
            $attributes['data-vb-id'] = $shortcodeId;
            $request->merge(['shortcodeId' => $shortcodeId]);
        }

        $locale = app()->getLocale();
        $authorized = auth()->check() ? 'auth' : 'anon';
        $appUrl = url('/');
        $extraCacheKeys = apply_filters('shortcode_cache_key_parts', [], $name);
        $cacheKey = 'shortcode_' . md5($name . $appUrl . serialize($attributes) . $locale . $authorized . serialize($extraCacheKeys));

        if (! setting('shortcode_cache_enabled', false)) {
            $code = Shortcode::generateShortcode($name, $attributes);
            $content = Shortcode::compile($code, true)->toHtml();

            return $this->httpResponse()->setData($content);
        }

        $enableCaching = Arr::get($attributes, 'enable_caching');

        if ($enableCaching === 'no' || $shortcodeId || request()->input('visual_builder')) {
            $code = Shortcode::generateShortcode($name, $attributes);
            $content = Shortcode::compile($code, true)->toHtml();

            return $this->httpResponse()->setData($content);
        }

        $cacheTtl = (int) setting('shortcode_cache_ttl', 1800);
        $cacheDuration = Carbon::now()->addSeconds($cacheTtl);

        $content = Cache::remember($cacheKey, $cacheDuration, function () use ($name, $attributes) {
            $code = Shortcode::generateShortcode($name, $attributes);

            return Shortcode::compile($code, true)->toHtml();
        });

        return $this
            ->httpResponse()
            ->setData($content);
    }

    protected function protectHtmlInAttributes(string $code): string
    {
        return preg_replace_callback(
            '/(\w+)="([^"]*)"/',
            function ($matches) {
                $name = $matches[1];
                $value = $matches[2];

                $value = preg_replace('/<br[^>]*\/?>/i', '{{BR}}', $value);
                $value = str_replace('&nbsp;', '{{NBSP}}', $value);

                return $name . '="' . $value . '"';
            },
            $code
        );
    }

    protected function restoreHtmlInAttributes(array $attributes): array
    {
        foreach ($attributes as $key => $value) {
            if (is_string($value)) {
                $attributes[$key] = str_replace(
                    ['{{BR}}', '{{NBSP}}', '{{NEWLINE}}'],
                    ['<br>', '&nbsp;', "\n"],
                    $value
                );
            }
        }

        return $attributes;
    }
}
