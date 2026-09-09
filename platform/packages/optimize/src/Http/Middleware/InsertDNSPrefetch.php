<?php

namespace Botble\Optimize\Http\Middleware;

class InsertDNSPrefetch extends PageSpeed
{
    public function apply(string $buffer): string
    {
        preg_match_all(
            '#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#',
            $buffer,
            $match,
            PREG_OFFSET_CAPTURE
        );

        $ownHost = parse_url(config('app.url'), PHP_URL_HOST) ?: null;

        $dnsPrefetch = collect($match[0])->map(function ($item) use ($ownHost) {
            $domain = $this->replace([
                '/https:/' => '',
                '/http:/' => '',
            ], $item[0]);

            $domain = explode(
                '/',
                str_replace('//', '', $domain)
            );

            if (filter_var($domain[0], FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false) {
                return '';
            }

            if ($domain[0] === $ownHost) {
                return '';
            }

            return '<link rel="dns-prefetch" href="//' . $domain[0] . '">';
        })->filter()->unique()->implode("\n");

        $replace = [
            '#<head>(.*?)#' => '<head>' . "\n" . $dnsPrefetch,
        ];

        return $this->replace($replace, $buffer);
    }
}
