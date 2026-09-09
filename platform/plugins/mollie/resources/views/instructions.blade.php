<ol>
    <li>
        <p>
            <a
                href="https://www.mollie.com/dashboard/signup"
                target="_blank"
            >
                {{ trans('plugins/mollie::mollie.register_account', ['name' => 'Mollie']) }}
            </a>
        </p>
    </li>
    <li>
        <p>
            {{ trans('plugins/mollie::mollie.after_registration', ['name' => 'Mollie']) }}
        </p>
    </li>
    <li>
        <p>{{ trans('plugins/mollie::mollie.enter_api_key') }}</p>
    </li>
    <li>
        <p><strong>{{ trans('plugins/mollie::mollie.webhook_configuration') }}</strong></p>
        <p>{{ trans('plugins/mollie::mollie.webhook_url_instruction') }}</p>
        <code style="background: #f5f5f5; padding: 5px; display: block; margin: 10px 0;">
            {{ url('mollie/payment/webhook/{token}') }}
        </code>
        <p><small>{{ trans('plugins/mollie::mollie.webhook_note') }}</small></p>
    </li>
    <li>
        <p><strong>{{ trans('plugins/mollie::mollie.security_optional') }}</strong></p>
        <p>{{ trans('plugins/mollie::mollie.security_instruction') }}</p>
    </li>
</ol>
