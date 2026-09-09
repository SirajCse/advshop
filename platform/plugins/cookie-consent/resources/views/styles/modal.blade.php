<style>
    .site-notice {
        position: fixed;
        inset: 0;
        padding: 1rem;
        z-index: 99999;
        display: none;
        align-items: center;
        justify-content: center;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .site-notice.site-notice--visible {
        display: flex;
    }

    .site-notice .site-notice-body {
        margin: 0 auto;
        max-width: 480px;
        width: 100%;
        padding: 28px;
        border-radius: 12px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    .site-notice .site-notice__inner {
        display: flex;
        flex-direction: column;
        align-items: stretch;
        gap: 1.25rem;
    }

    .site-notice .site-notice__message {
        margin: 0;
        line-height: 1.6;
        font-size: 14px;
    }

    .site-notice .site-notice__message a {
        color: inherit;
        text-decoration: underline;
    }

    .site-notice .site-notice__message a:hover {
        text-decoration: none;
    }

    .site-notice .site-notice__actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    [dir="rtl"] .site-notice .site-notice__actions {
        justify-content: flex-start;
    }

    .site-notice .site-notice__actions button {
        padding: 10px 18px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        font-size: 13px;
        font-weight: 600;
        min-width: 110px;
        text-align: center;
    }

    .site-notice .site-notice__actions button:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .site-notice .site-notice__categories {
        display: none;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
    }

    .site-notice .site-notice__categories .cookie-category {
        margin-bottom: 0.75rem;
        padding: 0.75rem;
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 6px;
    }

    .site-notice .site-notice__categories .cookie-category__label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
        cursor: pointer;
    }

    .site-notice .site-notice__categories .cookie-category__label input[type="checkbox"] {
        margin: 0;
        padding: 0;
        border: none;
        border-radius: 0;
        box-shadow: none;
        font-size: initial;
        height: initial;
        width: auto;
    }

    .site-notice .site-notice__categories .cookie-category__label input[type="checkbox"]:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .site-notice .site-notice__categories .cookie-category__name {
        font-weight: bold;
    }

    .site-notice .site-notice__categories .cookie-category__description {
        margin: 0;
        font-size: 0.9em;
        opacity: 0.8;
    }

    .site-notice .site-notice__categories .site-notice__save {
        margin-top: 1rem;
        padding: 0.75rem 0 0;
        text-align: right;
    }

    [dir="rtl"] .site-notice .site-notice__categories .site-notice__save {
        text-align: left;
    }

    .site-notice .site-notice__categories .site-notice__save .site-notice__save-button {
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        font-size: 13px;
        min-width: 110px;
    }

    @media (max-width: 767px) {
        .site-notice .site-notice-body {
            padding: 20px;
        }

        .site-notice .site-notice__actions {
            justify-content: stretch;
        }

        .site-notice .site-notice__actions button {
            flex: 1;
            min-width: 0;
            padding: 8px 12px;
            font-size: 12px;
        }
    }
</style>

<div
    class="js-site-notice site-notice"
    dir="{{ BaseHelper::siteLanguageDirection() }}"
    data-nosnippet
>
    <div
        class="site-notice-body"
        style="background-color: {{ theme_option('cookie_consent_background_color', '#000') }}; color: {{ theme_option('cookie_consent_text_color', '#fff') }};"
    >
        <div class="site-notice__inner">
            <div class="site-notice__message">
                {!! BaseHelper::clean(
                    theme_option('cookie_consent_message', trans('plugins/cookie-consent::cookie-consent.message')),
                ) !!}
                @if (
                    ($learnMoreUrl = theme_option('cookie_consent_learn_more_url')) &&
                        ($learnMoreText = theme_option('cookie_consent_learn_more_text')))
                    <a
                        href="{{ Str::startsWith($learnMoreUrl, ['http://', 'https://']) ? $learnMoreUrl : BaseHelper::getHomepageUrl() . '/' . ltrim($learnMoreUrl, '/') }}">{{ $learnMoreText }}</a>
                @endif
            </div>

            <div class="site-notice__actions">
                @if (theme_option('cookie_consent_show_reject_button', 'no') == 'yes')
                    <button
                        class="js-site-notice-reject site-notice__reject"
                        style="background-color: {{ theme_option('cookie_consent_text_color', '#fff') }}; color: {{ theme_option('cookie_consent_background_color', '#000') }}; border: 1px solid {{ theme_option('cookie_consent_text_color', '#fff') }};"
                    >
                        {{ trans('plugins/cookie-consent::cookie-consent.reject_text') }}
                    </button>
                @endif
                @if (
                    !empty($cookieConsentConfig['cookie_categories']) &&
                        theme_option('cookie_consent_show_customize_button', 'no') == 'yes')
                    <button
                        class="js-site-notice-customize site-notice__customize"
                        style="background-color: {{ theme_option('cookie_consent_text_color', '#fff') }}; color: {{ theme_option('cookie_consent_background_color', '#000') }}; border: 1px solid {{ theme_option('cookie_consent_text_color', '#fff') }};"
                    >
                        {{ trans('plugins/cookie-consent::cookie-consent.customize_text') }}
                    </button>
                @endif
                <button
                    class="js-site-notice-agree site-notice__agree"
                    style="background-color: {{ theme_option('cookie_consent_background_color', '#000') }}; color: {{ theme_option('cookie_consent_text_color', '#fff') }}; border: 1px solid {{ theme_option('cookie_consent_text_color', '#fff') }};"
                >
                    {{ theme_option('cookie_consent_button_text', trans('plugins/cookie-consent::cookie-consent.button_text')) }}
                </button>
            </div>
        </div>

        @if (!empty($cookieConsentConfig['cookie_categories']))
            <div class="site-notice__categories">
                @foreach ($cookieConsentConfig['cookie_categories'] as $key => $category)
                    <div class="cookie-category">
                        <label class="cookie-category__label">
                            <input
                                type="checkbox"
                                name="cookie_category[]"
                                value="{{ $key }}"
                                class="js-cookie-category"
                                @if ($category['required']) checked disabled @endif
                            >
                            <span
                                class="cookie-category__name">{{ trans('plugins/cookie-consent::cookie-consent.cookie_categories.' . $key . '.name') }}</span>
                        </label>
                        <p class="cookie-category__description">
                            {{ trans('plugins/cookie-consent::cookie-consent.cookie_categories.' . $key . '.description') }}
                        </p>
                    </div>
                @endforeach

                <div class="site-notice__save">
                    <button
                        class="js-site-notice-save site-notice__save-button"
                        style="background-color: {{ theme_option('cookie_consent_background_color', '#000') }}; color: {{ theme_option('cookie_consent_text_color', '#fff') }}; border: 1px solid {{ theme_option('cookie_consent_text_color', '#fff') }};"
                    >
                        {{ trans('plugins/cookie-consent::cookie-consent.save_text') }}
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>

@include('plugins/cookie-consent::partials.scripts')
