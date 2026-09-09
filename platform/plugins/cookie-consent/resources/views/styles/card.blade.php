@php
    $cardTitle = theme_option('cookie_consent_title', trans('plugins/cookie-consent::cookie-consent.title'));
    $cardMessage = theme_option('cookie_consent_message', trans('plugins/cookie-consent::cookie-consent.message'));
    $learnMoreUrl = theme_option('cookie_consent_learn_more_url');
    $learnMoreText = theme_option('cookie_consent_learn_more_text');
    $hasCategories = ! empty($cookieConsentConfig['cookie_categories']);
    $primaryColor = theme_option('primary_color', '#f97316');
    $primaryColorHover = theme_option('primary_color_hover', '#d66313');

    // Pick the accept-all button text color (white vs near-black) that has the
    // highest WCAG contrast against the primary color, so the CTA stays legible
    // whatever brand color the store sets. White on a mid-tone brand color (e.g.
    // the default orange) only reaches ~2.9:1 and fails the AA 4.5:1 threshold.
    $accentTextColor = '#ffffff';

    if (preg_match('/^#?([a-fA-F0-9]{3}|[a-fA-F0-9]{6})$/', $primaryColor)) {
        $hex = ltrim($primaryColor, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $toLinear = function ($channel) {
            $channel /= 255;

            return $channel <= 0.03928 ? $channel / 12.92 : pow(($channel + 0.055) / 1.055, 2.4);
        };

        $luminance = 0.2126 * $toLinear(hexdec(substr($hex, 0, 2)))
            + 0.7152 * $toLinear(hexdec(substr($hex, 2, 2)))
            + 0.0722 * $toLinear(hexdec(substr($hex, 4, 2)));

        $contrastWhite = (1.0 + 0.05) / ($luminance + 0.05);
        $contrastBlack = ($luminance + 0.05) / 0.05;

        $accentTextColor = $contrastWhite >= $contrastBlack ? '#ffffff' : '#18181b';
    }
@endphp

<style>
    .site-notice {
        position: fixed;
        right: 1rem;
        bottom: 1rem;
        left: 1rem;
        z-index: 99999;
        display: none;
    }

    @media (min-width: 640px) {
        .site-notice {
            left: auto;
            width: 22rem;
        }
    }

    [dir="rtl"] .site-notice {
        right: 1rem;
        left: 1rem;
    }

    @media (min-width: 640px) {
        [dir="rtl"] .site-notice {
            right: auto;
            left: 1rem;
            width: 22rem;
        }
    }

    .site-notice.site-notice--visible {
        display: block;
        animation: site-notice-pop 0.35s ease;
    }

    @keyframes site-notice-pop {
        from {
            opacity: 0;
            transform: translateY(12px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .site-notice .site-notice-body {
        overflow: hidden;
        background-color: #ffffff;
        border: 1px solid rgba(228, 228, 231, 0.9);
        border-radius: 16px;
        box-shadow: 0 20px 25px -5px rgba(24, 24, 27, 0.1), 0 8px 10px -6px rgba(24, 24, 27, 0.1);
    }

    .site-notice .site-notice__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 16px;
        border-bottom: 1px solid #f4f4f5;
        background: linear-gradient(to bottom right, #fafafa, #ffffff);
    }

    .site-notice .site-notice__header-left {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .site-notice .site-notice__icon {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 12px;
        background-color: color-mix(in srgb, var(--cc-primary, #f97316) 12%, #ffffff);
        color: var(--cc-primary, #f97316);
    }

    .site-notice .site-notice__icon svg {
        width: 20px;
        height: 20px;
    }

    .site-notice .site-notice__title {
        margin: 0;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.375;
        color: #18181b;
    }

    .site-notice .site-notice__close {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        padding: 0;
        margin: 0;
        border: none;
        background: transparent;
        color: #a1a1aa;
        cursor: pointer;
        border-radius: 8px;
        transition: color 0.2s ease, background-color 0.2s ease;
    }

    .site-notice .site-notice__close:hover {
        color: #3f3f46;
        background-color: #f4f4f5;
    }

    .site-notice .site-notice__close svg {
        width: 16px;
        height: 16px;
    }

    .site-notice .site-notice__content {
        padding: 16px;
    }

    .site-notice .site-notice__message {
        margin: 0;
        font-size: 13px;
        line-height: 1.6;
        color: #52525b;
    }

    .site-notice .site-notice__message a {
        color: var(--cc-primary, #f97316);
        text-decoration: underline;
    }

    .site-notice .site-notice__message a:hover {
        text-decoration: none;
    }

    .site-notice .site-notice__actions {
        margin-top: 16px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .site-notice .site-notice__actions button {
        width: 100%;
        padding: 8px 14px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 12px;
        line-height: 1.2;
        text-align: center;
        transition: all 0.2s ease;
    }

    .site-notice .site-notice__accept-all {
        background-color: var(--cc-primary, #f97316);
        color: var(--cc-accent-text, #ffffff);
        border: 1px solid var(--cc-primary, #f97316);
        font-weight: 600;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08);
    }

    .site-notice .site-notice__accept-all:hover {
        background-color: var(--cc-primary-hover, #d66313);
        border-color: var(--cc-primary-hover, #d66313);
    }

    .site-notice .site-notice__secondary {
        background-color: #ffffff;
        color: #3f3f46;
        border: 1px solid #e4e4e7;
        font-weight: 500;
    }

    .site-notice .site-notice__secondary:hover {
        background-color: #fafafa;
        border-color: #d4d4d8;
    }

    /* Customize preferences modal */
    .cookie-consent-modal {
        position: fixed;
        inset: 0;
        z-index: 100000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        background-color: rgba(24, 24, 27, 0.5);
    }

    .cookie-consent-modal.is-open {
        display: flex;
    }

    .cookie-consent-modal__dialog {
        width: 100%;
        max-width: 440px;
        max-height: 85vh;
        display: flex;
        flex-direction: column;
        background-color: #ffffff;
        border-radius: 16px;
        box-shadow: 0 25px 50px -12px rgba(24, 24, 27, 0.35);
        overflow: hidden;
        animation: site-notice-pop 0.3s ease;
    }

    .cookie-consent-modal__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 20px;
        border-bottom: 1px solid #f4f4f5;
    }

    .cookie-consent-modal__title {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
        color: #18181b;
    }

    .cookie-consent-modal__body {
        padding: 16px 20px;
        overflow-y: auto;
    }

    .cookie-consent-modal__intro {
        margin: 0 0 16px;
        font-size: 13px;
        line-height: 1.6;
        color: #52525b;
    }

    .cookie-consent-modal__item {
        display: block;
        padding: 12px 0;
        border-top: 1px solid #f4f4f5;
    }

    .cookie-consent-modal__item-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 4px;
    }

    .cookie-consent-modal__item-name {
        font-size: 14px;
        font-weight: 600;
        color: #18181b;
    }

    .cookie-consent-modal__item-desc {
        margin: 0;
        font-size: 12px;
        line-height: 1.55;
        color: #71717a;
    }

    .cookie-consent-modal__badge {
        flex: 0 0 auto;
        font-size: 11px;
        font-weight: 600;
        color: var(--cc-primary, #f97316);
    }

    .cookie-consent-modal__switch {
        position: relative;
        display: inline-block;
        flex: 0 0 auto;
        width: 40px;
        height: 22px;
        cursor: pointer;
    }

    .cookie-consent-modal__switch input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }

    .cookie-consent-modal__slider {
        position: absolute;
        inset: 0;
        background-color: #d4d4d8;
        border-radius: 999px;
        transition: background-color 0.2s ease;
    }

    .cookie-consent-modal__slider::before {
        content: '';
        position: absolute;
        top: 3px;
        left: 3px;
        width: 16px;
        height: 16px;
        background-color: #ffffff;
        border-radius: 50%;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        transition: transform 0.2s ease;
    }

    .cookie-consent-modal__switch input:checked + .cookie-consent-modal__slider {
        background-color: var(--cc-primary, #f97316);
    }

    .cookie-consent-modal__switch input:checked + .cookie-consent-modal__slider::before {
        transform: translateX(18px);
    }

    .cookie-consent-modal__footer {
        display: flex;
        flex-direction: column;
        gap: 8px;
        padding: 16px 20px;
        border-top: 1px solid #f4f4f5;
    }

    .cookie-consent-modal__footer button {
        width: 100%;
        padding: 8px 14px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 12px;
        line-height: 1.2;
        text-align: center;
        transition: all 0.2s ease;
    }

    @media (max-width: 639px) {
        .site-notice {
            right: 1rem;
            left: 1rem;
        }
    }
</style>

<div
    class="js-site-notice site-notice"
    dir="{{ BaseHelper::siteLanguageDirection() }}"
    role="dialog"
    aria-labelledby="js-site-notice-title"
    aria-live="polite"
    style="--cc-primary: {{ $primaryColor }}; --cc-primary-hover: {{ $primaryColorHover }}; --cc-accent-text: {{ $accentTextColor }};"
    data-nosnippet
>
    <div class="site-notice-body">
        <div class="site-notice__header">
            <div class="site-notice__header-left">
                <span class="site-notice__icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                </span>
                <p class="site-notice__title" id="js-site-notice-title">{{ $cardTitle }}</p>
            </div>
            <button
                type="button"
                class="js-site-notice-essential site-notice__close"
                aria-label="{{ trans('plugins/cookie-consent::cookie-consent.essential_only_text') }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 6 6 18M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="site-notice__content">
            <p class="site-notice__message">
                {!! BaseHelper::clean($cardMessage) !!}
                @if ($learnMoreUrl && $learnMoreText)
                    <a href="{{ Str::startsWith($learnMoreUrl, ['http://', 'https://']) ? $learnMoreUrl : BaseHelper::getHomepageUrl() . '/' . ltrim($learnMoreUrl, '/') }}">{{ $learnMoreText }}</a>
                @endif
            </p>

            <div class="site-notice__actions">
                <button type="button" class="js-site-notice-accept-all site-notice__accept-all">
                    {{ trans('plugins/cookie-consent::cookie-consent.accept_all_text') }}
                </button>
                @if ($hasCategories)
                    <button type="button" class="js-cookie-consent-customize site-notice__secondary">
                        {{ trans('plugins/cookie-consent::cookie-consent.customize_text') }}
                    </button>
                @endif
                <button type="button" class="js-site-notice-essential site-notice__secondary">
                    {{ trans('plugins/cookie-consent::cookie-consent.essential_only_text') }}
                </button>
            </div>
        </div>
    </div>

    @if ($hasCategories)
        {{-- Category toggles live here. Visible inside the modal; also power accept-all / essential-only. --}}
        <div class="js-cookie-consent-modal cookie-consent-modal" role="dialog" aria-modal="true" aria-labelledby="js-cookie-consent-modal-title">
            <div class="cookie-consent-modal__dialog">
                <div class="cookie-consent-modal__header">
                    <p class="cookie-consent-modal__title" id="js-cookie-consent-modal-title">{{ trans('plugins/cookie-consent::cookie-consent.customize_text') }}</p>
                    <button
                        type="button"
                        class="js-cookie-consent-modal-close site-notice__close"
                        aria-label="{{ trans('core/base::base.close') }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 6 6 18M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="cookie-consent-modal__body">
                    <p class="cookie-consent-modal__intro">{!! BaseHelper::clean($cardMessage) !!}</p>

                    @foreach ($cookieConsentConfig['cookie_categories'] as $key => $category)
                        <label class="cookie-consent-modal__item">
                            <span class="cookie-consent-modal__item-head">
                                <span class="cookie-consent-modal__item-name">
                                    {{ trans('plugins/cookie-consent::cookie-consent.cookie_categories.' . $key . '.name') }}
                                </span>
                                @if ($category['required'])
                                    <span class="cookie-consent-modal__badge">
                                        {{ trans('plugins/cookie-consent::cookie-consent.always_active') }}
                                    </span>
                                    <input type="checkbox" class="js-cookie-category" value="{{ $key }}" checked disabled hidden>
                                @else
                                    <span class="cookie-consent-modal__switch">
                                        <input type="checkbox" class="js-cookie-category" value="{{ $key }}">
                                        <span class="cookie-consent-modal__slider"></span>
                                    </span>
                                @endif
                            </span>
                            <span class="cookie-consent-modal__item-desc">
                                {{ trans('plugins/cookie-consent::cookie-consent.cookie_categories.' . $key . '.description') }}
                            </span>
                        </label>
                    @endforeach
                </div>

                <div class="cookie-consent-modal__footer">
                    <button type="button" class="js-site-notice-accept-all site-notice__accept-all">
                        {{ trans('plugins/cookie-consent::cookie-consent.accept_all_text') }}
                    </button>
                    <button type="button" class="js-site-notice-save site-notice__secondary">
                        {{ trans('plugins/cookie-consent::cookie-consent.save_text') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

@include('plugins/cookie-consent::partials.scripts')

@if ($hasCategories)
    <script>
        (function() {
            const banner = document.querySelector('.js-site-notice');
            const modal = banner ? banner.querySelector('.js-cookie-consent-modal') : null;

            if (!banner || !modal) {
                return;
            }

            banner.addEventListener('click', function(event) {
                if (event.target.closest('.js-cookie-consent-customize')) {
                    modal.classList.add('is-open');
                } else if (event.target.closest('.js-cookie-consent-modal-close') || event.target === modal) {
                    modal.classList.remove('is-open');
                }
            });
        })();
    </script>
@endif
