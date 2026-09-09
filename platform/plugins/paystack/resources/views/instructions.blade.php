<ol>
    <li>
        <p>
            <a
                href="https://paystack.com"
                target="_blank"
            >
                {{ trans('plugins/paystack::paystack.register_account', ['name' => 'Paystack']) }}
            </a>
        </p>
    </li>
    <li>
        <p>
            {{ trans('plugins/paystack::paystack.after_registration', ['name' => 'Paystack']) }}
        </p>
    </li>
    <li>
        <p>
            {{ trans('plugins/paystack::paystack.enter_keys') }}
        </p>
    </li>
    <li>
        <p>
            {!! trans('plugins/paystack::paystack.callback_url_instruction') !!}
        </p>
        <p>
            <code>{{ route('paystack.payment.callback') }}</code>
        </p>
    </li>
    <li>
        <p>
            {!! trans('plugins/paystack::paystack.webhook_url_instruction') !!}
        </p>
        <p>
            <code>{{ route('paystack.webhook') }}</code>
        </p>
    </li>
</ol>
