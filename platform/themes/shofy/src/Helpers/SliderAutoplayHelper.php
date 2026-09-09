<?php

namespace Theme\Shofy\Helpers;

use Botble\Shortcode\Compilers\Shortcode;

class SliderAutoplayHelper
{
    public static function getDataAttributes(Shortcode $shortcode): string
    {
        $attributes = [
            'data-autoplay' => ($shortcode->is_autoplay === 'yes') ? 'true' : 'false',
            'data-autoplay-speed' => $shortcode->autoplay_speed ?: 3000,
            'data-loop' => ($shortcode->is_loop === 'no') ? 'false' : 'true',
        ];

        return implode(' ', array_map(
            fn ($key, $value) => sprintf('%s="%s"', $key, $value),
            array_keys($attributes),
            $attributes
        ));
    }

    public static function getJavaScriptConfig(): string
    {
        return '
            const isAutoplay = $element.data(\'autoplay\') === true || $element.data(\'autoplay\') === \'true\'
            const autoplaySpeed = parseInt($element.data(\'autoplay-speed\')) || 3000
            const isLoop = $element.data(\'loop\') !== false && $element.data(\'loop\') !== \'false\'
        ';
    }

    public static function getSwiperAutoplayConfig(): string
    {
        return '
            if (isAutoplay) {
                swiperConfig.autoplay = {
                    delay: autoplaySpeed,
                    disableOnInteraction: false,
                }
            }
        ';
    }

    public static function isAutoplayEnabled(Shortcode $shortcode): bool
    {
        return $shortcode->is_autoplay === 'yes';
    }

    public static function isLoopEnabled(Shortcode $shortcode): bool
    {
        return $shortcode->is_loop !== 'no';
    }

    public static function getAutoplaySpeed(Shortcode $shortcode): int
    {
        return (int) ($shortcode->autoplay_speed ?: 3000);
    }

    public static function getAutoplaySpeedChoices(): array
    {
        return array_combine(
            [2000, 3000, 4000, 5000, 6000, 7000, 8000, 9000, 10000],
            [2000, 3000, 4000, 5000, 6000, 7000, 8000, 9000, 10000]
        );
    }

    public static function getLoopChoices(): array
    {
        return [
            'yes' => __('Continuously'),
            'no' => __('Stop on the last slide'),
        ];
    }

    public static function getAutoplayChoices(): array
    {
        return [
            'no' => __('No'),
            'yes' => __('Yes'),
        ];
    }
}
