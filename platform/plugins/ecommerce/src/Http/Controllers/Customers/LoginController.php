<?php

namespace Botble\Ecommerce\Http\Controllers\Customers;

use Botble\ACL\Traits\AuthenticatesUsers;
use Botble\ACL\Traits\LogoutGuardTrait;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Enums\CustomerStatusEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Forms\Fronts\Auth\LoginForm;
use Botble\Ecommerce\Http\Requests\LoginRequest;
use Botble\Ecommerce\Models\Customer;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends BaseController
{
    use AuthenticatesUsers, LogoutGuardTrait {
        AuthenticatesUsers::attemptLogin as baseAttemptLogin;
    }

    public string $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('customer.guest', ['except' => 'logout']);
    }

    public function showLoginForm()
    {
        $title = __('Login');
        SeoHelper::setTitle(theme_option('ecommerce_login_seo_title') ?: $title)
            ->setDescription(theme_option('ecommerce_login_seo_description'));

        SeoHelper::meta()->addMeta('robots', 'noindex, nofollow');

        Theme::breadcrumb()->add($title, route('customer.login'));

        $currentHost = request()->getHost();

        if (request()->has('redirect') && request()->get('redirect')) {
            $redirect = request()->get('redirect');

            if (parse_url($redirect, PHP_URL_HOST) === $currentHost || ! parse_url($redirect, PHP_URL_HOST)) {
                session(['url.intended' => $redirect]);
            }
        } elseif (! session()->has('url.intended') && ! in_array(url()->previous(), [route('customer.login'), route('customer.register')])) {
            $previous = url()->previous();

            if (parse_url($previous, PHP_URL_HOST) === $currentHost) {
                session(['url.intended' => $previous]);
            }
        }

        return Theme::scope(
            'ecommerce.customers.login',
            ['form' => LoginForm::create()],
            'plugins/ecommerce::themes.customers.login'
        )->render();
    }

    protected function guard()
    {
        return auth('customer');
    }

    public function login(LoginRequest $request)
    {
        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to log in and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        $this->sendFailedLoginResponse();
    }

    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        $email = $request->input($this->username());
        Cookie::queue('customer_remember_email', $email, 525600);

        $customer = $this->guard()->user();

        $response = apply_filters('customer_login_response', null, $customer, $request);

        if ($response) {
            return $response;
        }

        return $this->authenticated($request, $customer)
            ?: redirect()->intended($this->redirectPath());
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();

        $this->loggedOut($request);

        return redirect()->to(BaseHelper::getHomepageUrl());
    }

    protected function attemptLogin(LoginRequest $request)
    {
        $credentials = $this->credentials($request);

        if ($this->guard()->validate($credentials)) {
            $customer = $this->guard()->getLastAttempted();
        } else {
            $customer = $this->findCustomerByPhoneWithoutCountryCode($credentials);
        }

        if (! $customer) {
            return false;
        }

        if (EcommerceHelper::isEnableEmailVerification() && empty($customer->confirmed_at)) {
            throw ValidationException::withMessages([
                'confirmation' => [
                    __(
                        'The given email address has not been confirmed. <a href=":resend_link">Resend confirmation link.</a>',
                        [
                            'resend_link' => route('customer.resend_confirmation', ['email' => $customer->email]),
                        ]
                    ),
                ],
            ]);
        }

        if ($customer->status->getValue() !== CustomerStatusEnum::ACTIVATED) {
            throw ValidationException::withMessages([
                'email' => [
                    __('Your account has been locked, please contact the administrator.'),
                ],
            ]);
        }

        return $this->guard()->login($customer, $request->filled('remember'));
    }

    protected function findCustomerByPhoneWithoutCountryCode(array $credentials): ?Customer
    {
        $loginOption = EcommerceHelper::getLoginOption();

        if (! in_array($loginOption, ['phone', 'email_or_phone'])) {
            return null;
        }

        $phone = $credentials['phone'] ?? null;

        if (! $phone) {
            return null;
        }

        $password = $credentials['password'] ?? null;

        $customer = Customer::query()
            ->wherePhone($phone)
            ->first();

        if ($customer && Hash::check($password, $customer->password)) {
            return $customer;
        }

        return null;
    }

    public function credentials(LoginRequest $request): array
    {
        $usernameKey = match (EcommerceHelper::getLoginOption()) {
            'phone' => 'phone',
            'email_or_phone' => $request->isEmail($request->input($this->username())) ? 'email' : 'phone',
            default => 'email',
        };

        return [
            $usernameKey => $request->input($this->username()),
            'password' => $request->input('password'),
        ];
    }
}
