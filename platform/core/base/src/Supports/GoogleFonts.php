<?php

namespace Botble\Base\Supports;

use Botble\Media\Facades\RvMedia;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GoogleFonts
{
    protected Filesystem $files;

    protected string $path = 'fonts';

    protected bool $inline = true;

    protected string $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0.3 Safari/605.1.15';

    public function setDisk(): void
    {
        config(['filesystems.disks.fonts' => [
            'driver' => 'local',
            'root' => RvMedia::getUploadPath(),
            'url' => RvMedia::getUploadURL(),
            'visibility' => 'public',
            'throw' => false,
        ]]);

        $this->files = Storage::disk('fonts');
    }

    public function load(string $font, ?string $nonce = null, bool $forceDownload = false): ?Fonts
    {
        ['font' => $font, 'nonce' => $nonce] = $this->parseOptions($font, $nonce);

        $url = $font;

        try {
            if ($forceDownload) {
                return $this->fetch($url, $nonce);
            }

            $fonts = $this->loadLocal($url, $nonce);

            if (! $fonts) {
                // PERFORMANCE FIX: skip the remote fetch when it failed recently,
                // otherwise a blocked/slow outbound connection to fonts.googleapis.com
                // would hang EVERY page render (admin panel + frontend) for up to
                // the HTTP timeout instead of falling back to a direct <link> tag.
                if ($this->recentlyFailed($url)) {
                    return null;
                }

                return $this->fetch($url, $nonce);
            }

            return $fonts;
        } catch (Exception $exception) {
            $this->markAsFailed($url);

            if (App::hasDebugModeEnabled()) {
                throw $exception;
            }

            return new Fonts(googleFontsUrl: $url, nonce: $nonce);
        }
    }

    /**
     * Remote fetch failures are remembered for a few hours so a slow/unreachable
     * fonts server can never slow down page rendering on every single request.
     */
    protected function failureCacheKey(string $url): string
    {
        $generation = Cache::get('google_fonts_cache_generation', 1);

        return sprintf('google_fonts_fetch_failed_%s_%s', $generation, md5($url));
    }

    protected function recentlyFailed(string $url): bool
    {
        return (bool) Cache::get($this->failureCacheKey($url));
    }

    protected function markAsFailed(string $url): void
    {
        try {
            Cache::put($this->failureCacheKey($url), true, Carbon::now()->addHours(6));
        } catch (Exception) {
            // Cache backend unavailable: never let a perf guard break rendering.
        }
    }

    protected function loadLocal(string $url, ?string $nonce): ?Fonts
    {
        $this->setDisk();

        if (! $this->files->exists($this->path($url, 'fonts.css'))) {
            return null;
        }

        $fontCssPath = $this->path($url, 'fonts.css');

        $localizedCss = $this->files->get($fontCssPath);

        if (str_contains($localizedCss, '<!DOCTYPE html>')) {
            $this->files->delete($fontCssPath);

            return null;
        }

        if (! str_contains($localizedCss, $this->files->url('fonts'))) {
            $uploadFolder = 'storage';

            if (setting('media_customize_upload_path')) {
                $uploadFolder = trim(setting('media_upload_path'), '/');
            }

            $localizedCss = preg_replace(
                '/(http|https):\/\/.*?\/' . $uploadFolder . '\/fonts\//i',
                $this->files->url('fonts/'),
                $localizedCss
            );

            $this->files->put($fontCssPath, $localizedCss);
        }

        return new Fonts(
            googleFontsUrl: $url,
            localizedUrl: $this->files->url($fontCssPath),
            localizedCss: $localizedCss,
            nonce: $nonce,
            preferInline: $this->inline,
        );
    }

    protected function fetch(string $url, ?string $nonce): ?Fonts
    {
        // PERFORMANCE FIX: was timeout(300) — a 5 minute block during page rendering.
        // Fonts are cosmetic: fail fast (15s) and fall back to the direct stylesheet link.
        try {
            $response = Http::withHeaders(['User-Agent' => $this->userAgent])
                ->timeout(15)
                ->connectTimeout(5)
                ->withoutVerifying()
                ->get($url);
        } catch (Exception) {
            $this->markAsFailed($url);

            return null;
        }

        if ($response->failed()) {
            $this->markAsFailed($url);

            return null;
        }

        $localizedCss = $response->body();

        try {
            $extractedFonts = $this->extractFontUrls($response);
        } catch (Exception) {
            return null;
        }

        $this->setDisk();

        foreach ($extractedFonts as $fontUrl) {
            $localizedFontUrl = $this->localizeFontUrl($fontUrl);

            $storedFontPath = $this->path($url, $localizedFontUrl);

            if (! $this->files->exists($storedFontPath)) {
                // PERFORMANCE FIX: this download previously had NO timeout
                // (Laravel default 30s per font file, ~15-20 files per family).
                try {
                    $fontResponse = Http::withoutVerifying()
                        ->timeout(15)
                        ->connectTimeout(5)
                        ->get($fontUrl);
                } catch (Exception) {
                    $this->markAsFailed($url);

                    return null;
                }

                if ($fontResponse->failed() || ! $fontResponse->body()) {
                    $this->markAsFailed($url);

                    return null;
                }

                $this->files->put($storedFontPath, $fontResponse->body());
            }

            $localizedCss = str_replace(
                $fontUrl,
                $this->files->url($storedFontPath),
                $localizedCss,
            );
        }

        $this->files->put($this->path($url, 'fonts.css'), $localizedCss);

        return new Fonts(
            googleFontsUrl: $url,
            localizedUrl: $this->files->url($this->path($url, 'fonts.css')),
            localizedCss: $localizedCss,
            nonce: $nonce,
            preferInline: $this->inline,
        );
    }

    protected function extractFontUrls(string $css): array
    {
        $matches = [];
        preg_match_all('/url\((https:\/\/fonts.gstatic.com\/[^)]+)\)/', $css, $matches);

        return $matches[1];
    }

    protected function localizeFontUrl(string $path): string
    {
        [$path, $extension] = explode('.', str_replace('https://fonts.gstatic.com/', '', $path));

        return implode('.', [Str::slug($path), $extension]);
    }

    protected function path(string $url, string $path = ''): string
    {
        $segments = collect([
            $this->path,
            substr(md5($url), 0, 10),
            $path,
        ]);

        return $segments->filter()->join('/');
    }

    protected function parseOptions(string $font, ?string $nonce = null): array
    {
        return [
            'font' => $font,
            'nonce' => $nonce,
        ];
    }

    protected static ?array $fontsCache = null;

    public static function getFonts(): array
    {
        // Lazy load fonts only when needed
        if (static::$fontsCache !== null) {
            return static::$fontsCache;
        }

        $path = core_path('base/resources/data/google-fonts.json');

        try {
            if (! File::exists($path)) {
                static::$fontsCache = [];

                return [];
            }

            static::$fontsCache = File::json($path);

            return static::$fontsCache;
        } catch (Exception) {
            static::$fontsCache = [];

            return [];
        }
    }
}
