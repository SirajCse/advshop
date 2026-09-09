<x-core::form.on-off.checkbox
    :label="trans('plugins/newsletter::newsletter.settings.enable_newsletter_contacts_list_api')"
    name="enable_newsletter_contacts_list_api"
    :checked="setting('enable_newsletter_contacts_list_api', false)"
    :helper-text="trans('plugins/newsletter::newsletter.settings.enable_newsletter_contacts_list_api_helper')"
    data-bb-toggle="collapse"
    data-bb-target="#newsletter-settings"
    class="mb-0"
    :wrapper="false"
/>

<x-core::form.fieldset
    data-bb-value="1"
    class="mt-3"
    id="newsletter-settings"
    @style(['display: none;' => !setting('enable_newsletter_contacts_list_api', false)])
>
    <x-core::alert
        type="info"
        class="mb-3"
    >
        <p class="mb-2"><strong>Mailchimp Integration</strong></p>
        <p class="mb-0">{!! BaseHelper::clean(trans('plugins/newsletter::newsletter.settings.mailchimp_api_key_instruction')) !!}</p>
    </x-core::alert>

    <x-core::form.text-input
        name="newsletter_mailchimp_api_key"
        data-counter="120"
        :label="trans('plugins/newsletter::newsletter.settings.mailchimp_api_key')"
        :value="setting('newsletter_mailchimp_api_key')"
        :placeholder="trans('plugins/newsletter::newsletter.settings.mailchimp_api_key')"
        :helper-text="trans('plugins/newsletter::newsletter.settings.mailchimp_api_key_helper')"
    />

    @if (empty($mailchimpContactList))
        <x-core::form.text-input
            name="newsletter_mailchimp_list_id"
            data-counter="120"
            :label="trans('plugins/newsletter::newsletter.settings.mailchimp_list_id')"
            :value="setting('newsletter_mailchimp_list_id')"
            :placeholder="trans('plugins/newsletter::newsletter.settings.mailchimp_list_id')"
            :helper-text="trans('plugins/newsletter::newsletter.settings.mailchimp_list_id_helper')"
        />
    @else
        <x-core-setting::select
            name="newsletter_mailchimp_list_id"
            :label="trans('plugins/newsletter::newsletter.settings.mailchimp_list')"
            :options="$mailchimpContactList"
            :value="setting('newsletter_mailchimp_list_id')"
            :helper-text="trans('plugins/newsletter::newsletter.settings.mailchimp_list_helper')"
        />
    @endif

    <hr class="my-4">

    <x-core::alert
        type="info"
        class="mb-3"
    >
        <p class="mb-2"><strong>SendGrid Integration</strong></p>
        <p class="mb-0">{!! BaseHelper::clean(trans('plugins/newsletter::newsletter.settings.sendgrid_api_key_instruction')) !!}</p>
    </x-core::alert>

    <x-core::form.text-input
        name="newsletter_sendgrid_api_key"
        data-counter="120"
        :label="trans('plugins/newsletter::newsletter.settings.sendgrid_api_key')"
        :value="setting('newsletter_sendgrid_api_key')"
        :placeholder="trans('plugins/newsletter::newsletter.settings.sendgrid_api_key')"
        :helper-text="trans('plugins/newsletter::newsletter.settings.sendgrid_api_key_helper')"
    />

    @if (empty($sendGridContactList))
        <x-core::form.text-input
            name="newsletter_sendgrid_list_id"
            data-counter="120"
            :label="trans('plugins/newsletter::newsletter.settings.sendgrid_list_id')"
            :value="setting('newsletter_sendgrid_list_id')"
            :placeholder="trans('plugins/newsletter::newsletter.settings.sendgrid_list_id')"
            :helper-text="trans('plugins/newsletter::newsletter.settings.sendgrid_list_id_helper')"
        />
    @else
        <x-core::form.select
            name="newsletter_sendgrid_list_id"
            :label="trans('plugins/newsletter::newsletter.settings.sendgrid_list')"
            :options="$sendGridContactList"
            :value="setting('newsletter_sendgrid_list_id')"
            :helper-text="trans('plugins/newsletter::newsletter.settings.sendgrid_list_helper')"
        />
    @endif
</x-core::form.fieldset>
