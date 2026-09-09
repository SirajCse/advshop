<?php

namespace Botble\Media\Supports;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Resolves intrinsic image dimensions from local files and injects explicit
 * `width` and `height` attributes into rendered <img> tags. Matches the pattern
 * used by WordPress core since 5.5 — the browser's UA stylesheet derives
 * `aspect-ratio` from the attrs automatically, and the theme package ships a
 * global `img{max-width:100%;height:auto}` reset so attrs scale with CSS.
 *
 * Results are cached forever (keyed by URL path). Bust via `cache:clear` if a
 * source image is replaced with different dimensions.
 */
class ImageDimensionsInjector
{
    /**
     * In-request memo so repeated lookups for the same path don't hit the cache store repeatedly.
     *
     * @var array<string, array{int, int}|null>
     */
    protected static array $memo = [];

    /**
     * Inject width/height attributes into a raw <img> tag if missing.
     * The $attrs param is the raw string *inside* the tag (excluding `<img` and `>`).
     */
    public static function inject(string $tag, string $attrs): string
    {
        // Skip if width/height already present.
        if (preg_match('/\s(width|height)\s*=/i', $attrs)) {
            return $tag;
        }

        // Prefer the real lazy-loaded image in `data-src` over the placeholder in
        // `src`. Lazy-loading rewrites `src` to a generic placeholder (e.g. a
        // 600x400 placeholder.png) and moves the real URL to `data-src`; reading
        // `src` would reserve the placeholder's aspect ratio and shift the layout
        // when the real image is swapped in.
        if (preg_match('/\sdata-src\s*=\s*"([^"]+)"/i', $attrs, $lazyMatch)) {
            $imageUrl = $lazyMatch[1];
        } elseif (preg_match('/\ssrc\s*=\s*"([^"]+)"/i', $attrs, $srcMatch)) {
            $imageUrl = $srcMatch[1];
        } else {
            return $tag;
        }

        $dims = static::resolveFromUrl($imageUrl);
        if (! $dims) {
            return $tag;
        }

        // The `data-dims-auto` marker lets the theme reset CSS target ONLY images
        // whose dimensions we injected, so static markup like
        //   <img src="icon.png" width="24" height="24">  (on a 256x256 source file)
        // is never touched by the reset and renders at its intentional 24x24 size.
        return sprintf('<img width="%d" height="%d" data-dims-auto%s>', $dims[0], $dims[1], $attrs);
    }

    /**
     * Resolve [width, height] for a URL pointing to a local storage or public theme/plugin asset.
     * Returns null for remote URLs or unreadable files.
     *
     * @return array{int, int}|null
     */
    public static function resolveFromUrl(?string $url): ?array
    {
        if (! is_string($url) || $url === '') {
            return null;
        }

        $path = parse_url($url, PHP_URL_PATH) ?: $url;

        if (! str_contains($path, '/storage/') && ! str_contains($path, '/themes/') && ! str_contains($path, '/vendor/')) {
            return null;
        }

        if (array_key_exists($path, static::$memo)) {
            return static::$memo[$path];
        }

        $cacheKey = 'bb_img_dims:' . md5($path);

        $dims = Cache::rememberForever($cacheKey, fn () => static::readDimensionsFromPath($path));

        return static::$memo[$path] = $dims;
    }

    /**
     * Reset in-request memo (used by tests).
     */
    public static function flushMemo(): void
    {
        static::$memo = [];
    }

    /**
     * @return array{int, int}|null
     */
    protected static function readDimensionsFromPath(string $path): ?array
    {
        try {
            $full = static::resolveFilesystemPath($path);

            if (! $full || ! is_file($full)) {
                return null;
            }

            if (str_ends_with(strtolower($full), '.svg')) {
                return static::readSvgDimensions($full);
            }

            $info = @getimagesize($full);
            if ($info && $info[0] > 0 && $info[1] > 0) {
                return [(int) $info[0], (int) $info[1]];
            }
        } catch (Throwable) {
            // ignore and fall through
        }

        return null;
    }

    protected static function resolveFilesystemPath(string $path): ?string
    {
        if (str_contains($path, '/storage/')) {
            $relative = ltrim(substr($path, strpos($path, '/storage/') + strlen('/storage/')), '/');
            if (Storage::disk('public')->exists($relative)) {
                return Storage::disk('public')->path($relative);
            }

            return null;
        }

        $candidate = public_path(ltrim($path, '/'));

        return is_file($candidate) ? $candidate : null;
    }

    /**
     * @return array{int, int}|null
     */
    protected static function readSvgDimensions(string $fullPath): ?array
    {
        $head = @file_get_contents($fullPath, false, null, 0, 2048);
        if (! is_string($head) || ! preg_match('/<svg\b[^>]*>/i', $head, $svgTag)) {
            return null;
        }

        $svgAttrs = $svgTag[0];

        if (
            preg_match('/\swidth\s*=\s*"([0-9.]+)"/i', $svgAttrs, $w)
            && preg_match('/\sheight\s*=\s*"([0-9.]+)"/i', $svgAttrs, $h)
        ) {
            return [(int) round((float) $w[1]), (int) round((float) $h[1])];
        }

        if (preg_match('/\sviewBox\s*=\s*"[\d.\s-]*?\s([\d.]+)\s+([\d.]+)\s*"/i', $svgAttrs, $vb)) {
            return [(int) round((float) $vb[1]), (int) round((float) $vb[2])];
        }

        return null;
    }
}
