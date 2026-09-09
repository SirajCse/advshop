<?php

namespace Botble\SocialLogin\Http\Controllers;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Media\Facades\RvMedia;
use Botble\SocialLogin\Facades\SocialService;
use Botble\SocialLogin\Services\SocialLoginService;
use Carbon\Carbon;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as SocialiteUser;

class SocialLoginController extends BaseController
{
    public function __construct(protected SocialLoginService $socialLoginService)
    {
    }

    public function redirectToProvider(string $provider, Request $request)
    {
        $this->ensureProviderIsExisted($provider);

        $guard = $this->guard($request);

        if (! $guard) {
            return $this
                ->httpResponse()
                ->setError()
                ->setNextUrl(BaseHelper::getHomepageUrl());
        }

        if (BaseHelper::hasDemoModeEnabled() && $provider !== 'google') {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage(trans('plugins/social-login::social-login.demo_mode_disabled'));
        }

        $this->setProvider($provider);

        session(['social_login_guard_current' => $guard]);

        return Socialite::driver($provider)->redirect();
    }

    protected function guard(?Request $request = null)
    {
        if ($request) {
            $guard = $request->input('guard');
        } else {
            $guard = session('social_login_guard_current');
        }

        if (! $guard) {
            $guard = array_key_first(SocialService::supportedModules());
        }

        if (! $guard || ! SocialService::isSupportedModuleByKey($guard) || Auth::guard($guard)->check()) {
            return false;
        }

        return $guard;
    }

    protected function setProvider(string $provider): bool
    {
        $callbackUrl = apply_filters('social_login_callback_url', route('auth.social.callback', $provider), $provider);

        config()->set([
            'services.' . $provider => [
                'client_id' => SocialService::setting($provider . '_app_id'),
                'client_secret' => SocialService::setting($provider . '_app_secret'),
                'redirect' => $callbackUrl,
            ],
        ]);

        return true;
    }

    public function handleProviderCallback(string $provider)
    {
        $this->ensureProviderIsExisted($provider);

        $guard = $this->guard();

        if (! $guard) {
            return $this
                ->httpResponse()
                ->setError()
                ->setNextUrl(BaseHelper::getHomepageUrl())
                ->setMessage(trans('plugins/social-login::social-login.login_error'));
        }

        $this->setProvider($provider);

        $providerData = Arr::get(SocialService::supportedModules(), $guard);

        try {
            /**
             * @var SocialiteUser $oAuth
             */
            $oAuth = Socialite::driver($provider)->user();
        } catch (Exception $exception) {
            $message = $exception->getMessage();

            if (in_array($provider, ['github', 'facebook'])) {
                $message = json_encode($message);
            }

            if (! $message) {
                $message = trans('plugins/social-login::social-login.login_error');
            }

            if ($exception instanceof InvalidStateException) {
                $message = trans('plugins/social-login::social-login.invalid_state_exception');
            }

            return $this
                ->httpResponse()
                ->setError()
                ->setNextUrl($this->resolveUrl($providerData['login_url']))
                ->setMessage($message);
        }

        if (! $oAuth->getEmail()) {
            return $this
                ->httpResponse()
                ->setError()
                ->setNextUrl($this->resolveUrl($providerData['login_url']))
                ->setMessage(trans('plugins/social-login::social-login.no_email_provided'));
        }

        $avatarId = null;
        $avatarUrl = null;

        $model = new $providerData['model']();

        $account = $this->socialLoginService->findUserByEmail($oAuth->getEmail(), $model::class);

        $socialLoginUser = $this->socialLoginService->findUserByProvider($provider, $oAuth->getId());

        if ($socialLoginUser && $account && $socialLoginUser->getKey() !== $account->getKey()) {
            $this->socialLoginService->updateSocialLogin($socialLoginUser, $provider, [
                'user_id' => $account->getKey(),
                'user_type' => $account::class,
            ]);
        } elseif (! $account) {
            $beforeProcessData = apply_filters('social_login_before_creating_account', null, $oAuth, $providerData);

            if ($beforeProcessData instanceof BaseHttpResponse) {
                return $beforeProcessData;
            }

            try {
                $url = $oAuth->getAvatar();
                if ($url) {
                    $result = RvMedia::uploadFromUrl($url, 0, $model->upload_folder ?: 'accounts', 'image/png');
                    if (! $result['error']) {
                        $avatarId = $result['data']->id;
                        $avatarUrl = $result['data']->url;
                    }
                }
            } catch (Exception $exception) {
                BaseHelper::logError($exception);
            }

            $data = [
                'name' => $oAuth->getName() ?: $oAuth->getEmail(),
                'email' => $oAuth->getEmail(),
                'password' => Hash::make(Str::random(36)),
            ];

            $data = apply_filters('social_login_before_saving_account', $data, $oAuth, $providerData);

            $account = $model;

            $account->fill($data);

            if ($account->isFillable('avatar_id')) {
                $account->avatar_id = $avatarId;
            } elseif ($account->isFillable('avatar')) {
                $account->avatar = $avatarUrl;
            }

            $account->confirmed_at = Carbon::now();

            $account->save();

            event(new Registered($account));
        }

        $socialLoginData = $this->socialLoginService->createSocialLoginData([
            'provider' => $provider,
            'id' => $oAuth->getId(),
            'token' => $oAuth->token,
            'refresh_token' => $oAuth->refreshToken,
            'expires_in' => $oAuth->expiresIn,
            'name' => $oAuth->getName(),
            'email' => $oAuth->getEmail(),
            'avatar' => $oAuth->getAvatar(),
        ]);

        $this->socialLoginService->createOrUpdateSocialLogin($account, $socialLoginData);

        try {
            $url = $oAuth->getAvatar();
            $hasAvatar = $account->isFillable('avatar_id') ? $account->avatar_id : $account->avatar;

            if ($url && ! $hasAvatar) {
                $result = RvMedia::uploadFromUrl($url, 0, $model->upload_folder ?: 'accounts', 'image/png');

                if (! $result['error']) {
                    if ($account->isFillable('avatar_id')) {
                        $account->avatar_id = $result['data']->id;
                    } elseif ($account->isFillable('avatar')) {
                        $account->avatar = $result['data']->url;
                    }

                    $account->save();
                }
            }
        } catch (Exception $exception) {
            BaseHelper::logError($exception);
        }

        Auth::guard($guard)->login($account, true);

        $redirectUrl = $this->resolveUrl($providerData['redirect_url']) ?: BaseHelper::getHomepageUrl();

        if (session()->has('url.intended')) {
            $intended = session('url.intended');

            if (parse_url($intended, PHP_URL_HOST) === request()->getHost()) {
                $redirectUrl = $intended;
            }
        }

        return $this
            ->httpResponse()
            ->setNextUrl($redirectUrl)
            ->setMessage(trans('core/acl::auth.login.success'));
    }

    protected function resolveUrl(mixed $url): string
    {
        if ($url instanceof \Closure) {
            return $url();
        }

        if (is_string($url) && Route::has($url)) {
            return route($url);
        }

        return (string) $url;
    }

    protected function ensureProviderIsExisted(string $provider): void
    {
        abort_if(! in_array($provider, SocialService::getProviderKeys(), true), 404);
    }
}
