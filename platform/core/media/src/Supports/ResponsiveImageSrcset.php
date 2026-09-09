<?php

namespace Botble\Media\Supports;

use Botble\Media\Facades\RvMedia;

/**
 * Builds `srcset` + `sizes` attributes for an <img> tag, matching the pattern
 * WordPress core uses in `wp_calculate_image_srcset()` / `wp_image_add_srcset_and_sizes()`:
 *
 *  - Width-descriptor srcset (`url 565w, url 540w, ...`) — flexible with `sizes`,
 *    works for fluid and fixed layouts.
 *  - Candidates drawn from the same registered aspect ratio as the requested size
 *    (mixing 150x150 into a 565x375 srcset would upset the browser's picker).
 *  - Default `sizes="(max-width: {W}px) 100vw, {W}px"` where W is the base size width.
 *  - Filter hooks `media_image_srcset` + `media_image_sizes` for theme/plugin override.
 *
 * Consumers call `RvMedia::image($url, $alt, $size, ...)` as usual; the builder is
 * invoked from inside `RvMedia::image()` before the attributes reach Html::image().
 */
class ResponsiveImageSrcset
{
    /**
     * Aspect-ratio tolerance when filtering candidate sizes. 1% covers the rounding noise
     * in common thumb dimensions (e.g. 270x180 → 1.5, 540x360 → 1.5).
     */
    protected const ASPECT_TOLERANCE = 0.01;

    /**
     * Build a srcset string from the given original image URL and the name of the size
     * currently requested (which must be registered via `RvMedia::addSize()`).
     *
     * Returns null when there's nothing useful to emit (fewer than 2 candidates,
     * unregistered base size, or external URL).
     */
    public static function build(?string $originalUrl, ?string $baseSizeName): ?string
    {
        if (! $originalUrl || ! $baseSizeName) {
            return null;
        }

        if (str_starts_with($originalUrl, 'http://') || str_starts_with($originalUrl, 'https://') || str_starts_with($originalUrl, 'data:')) {
            return null;
        }

        $baseDims = static::resolveSizeDims($baseSizeName);
        if (! $baseDims) {
            return null;
        }

        $baseAspect = $baseDims[0] / $baseDims[1];

        $candidates = [];
        foreach (RvMedia::getSizes() as $name => $dimString) {
            $dims = static::parseDimString($dimString);
            if (! $dims) {
                continue;
            }

            [$w, $h] = $dims;
            if ($w <= 0 || $h <= 0) {
                continue;
            }

            if (abs(($w / $h) - $baseAspect) > static::ASPECT_TOLERANCE) {
                continue;
            }

            $variantUrl = RvMedia::getImageUrl($originalUrl, $name);
            if (! $variantUrl) {
                continue;
            }

            $candidates[$w] = sprintf('%s %dw', $variantUrl, $w);
        }

        if (count($candidates) < 2) {
            return null;
        }

        ksort($candidates);

        $srcset = implode(', ', $candidates);

        return apply_filters('media_image_srcset', $srcset, $originalUrl, $baseSizeName);
    }

    /**
     * Default `sizes` attribute: `(max-width: Wpx) 100vw, Wpx`. Themes override via
     * the `media_image_sizes` filter. Returns null if we can't resolve the base size.
     */
    public static function sizes(?string $baseSizeName): ?string
    {
        $dims = static::resolveSizeDims($baseSizeName);
        if (! $dims) {
            return null;
        }

        $sizes = sprintf('(max-width: %dpx) 100vw, %dpx', $dims[0], $dims[0]);

        return apply_filters('media_image_sizes', $sizes, $baseSizeName, $dims);
    }

    /**
     * @return array{int, int}|null
     */
    protected static function resolveSizeDims(?string $sizeName): ?array
    {
        if (! $sizeName) {
            return null;
        }

        $sizes = RvMedia::getSizes();
        if (! isset($sizes[$sizeName])) {
            return null;
        }

        return static::parseDimString($sizes[$sizeName]);
    }

    /**
     * @return array{int, int}|null
     */
    protected static function parseDimString(string $dimString): ?array
    {
        if (! preg_match('/^(\d+)x(\d+)$/', $dimString, $m)) {
            return null;
        }

        return [(int) $m[1], (int) $m[2]];
    }
}
