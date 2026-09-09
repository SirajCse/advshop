<?php

namespace Botble\Language\Http\Middleware;

use Botble\Language\Facades\Language;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LocalizationRedirectFilter extends LaravelLocalizationMiddlewareBase
{
    public function handle(Request $request, Closure $next)
    {
        if ($this->shouldIgnore($request)) {
            return $next($request);
        }

        $currentLocale = Language::getCurrentLocale();
        $defaultLocale = Language::getDefaultLocale();
        $params = explode('/', $request->getPathInfo());
        array_shift($params);

        if (count($params) > 0) {
            $localeCode = $params[0];
            $locales = Language::getSupportedLocales();
            $hideDefaultLocale = Language::hideDefaultLocaleInURL();
            $redirection = false;

            // Per-locale sitemap URLs must stay reachable so search engines can
            // crawl each locale. Theme registers two sitemap routes:
            //   sitemap.xml                → main entry / multi-locale index
            //   {key}.{extension}          → sub-sitemaps (pages.xml, posts.xml…)
            // Both end up at /{locale}/<file>.xml and must NOT be redirected to
            // the locale-less variant, otherwise the default-locale sitemap
            // would 302 back to /sitemap.xml (the index), causing a loop.
            $isLocalizedSitemap = isset($params[1])
                && count($params) === 2
                && preg_match('/^[\w-]+\.(xml|xml-mobile|txt|ror-rss|ror-rdf|google-news)$/', $params[1]);

            if (! empty($locales[$localeCode])) {
                if ($localeCode === $defaultLocale && $hideDefaultLocale && ! $isLocalizedSitemap) {
                    $redirection = Language::getNonLocalizedURL();
                }
            } elseif ($currentLocale !== $defaultLocale || ! $hideDefaultLocale) {
                if (! Language::getActiveLanguage(['lang_id'])->isEmpty()) {
                    $redirection = Language::getLocalizedURL(Session::get('language'), $request->fullUrl(), [], false);
                }
            }

            if ($redirection) {
                Session::reflash();

                return new RedirectResponse($redirection, 302, ['Vary' => 'Accept-Language']);
            }
        }

        return $next($request);
    }
}
