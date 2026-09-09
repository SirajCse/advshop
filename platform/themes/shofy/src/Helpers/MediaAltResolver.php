<?php

namespace Theme\Shofy\Helpers;

use Botble\Media\Models\MediaFile;
use Throwable;

class MediaAltResolver
{
    /** Per-request memo of MediaFile->alt lookups keyed by url, to avoid repeat queries when a partial renders the same image twice. */
    private static array $cache = [];

    /**
     * Resolve an <img alt=""> string for a given media URL by reading the alt
     * text stored in the media library, falling back to the caller-supplied
     * string, then to an empty string. Looks up MediaFile by url with a
     * per-request static cache so multiple renders of the same image hit the
     * DB once.
     */
    public static function resolve(?string $url, ?string $fallback = null): string
    {
        $fallback = is_string($fallback) ? trim($fallback) : '';

        if (! is_string($url) || $url === '') {
            return $fallback;
        }

        if (! array_key_exists($url, self::$cache)) {
            try {
                $alt = MediaFile::query()->where('url', $url)->value('alt');
            } catch (Throwable) {
                $alt = null;
            }

            self::$cache[$url] = is_string($alt) ? trim($alt) : '';
        }

        $mediaAlt = self::$cache[$url];

        return $mediaAlt !== '' ? $mediaAlt : $fallback;
    }
}
