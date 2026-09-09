<?php

namespace Botble\Contact\Services;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Models\BaseModel;
use Botble\Contact\Enums\CustomFieldType;
use Botble\Contact\Events\SentContactEvent;
use Botble\Contact\Forms\Fronts\ContactForm;
use Botble\Contact\Http\Requests\ContactRequest;
use Botble\Contact\Models\CustomField;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ContactService
{
    public function validateBlacklistDomain(string $email): ?string
    {
        $blacklistDomains = setting('blacklist_email_domains');

        if (! $blacklistDomains) {
            return null;
        }

        $emailDomain = Str::after(strtolower($email), '@');
        $blacklistDomains = collect(json_decode($blacklistDomains, true))->pluck('value')->all();

        if (in_array($emailDomain, $blacklistDomains)) {
            return trans('plugins/contact::contact.email_blacklisted');
        }

        return null;
    }

    public function validateBlacklistKeywords(string $content): ?string
    {
        $blacklistWords = trim(setting('blacklist_keywords', ''));

        if (! $blacklistWords) {
            return null;
        }

        $content = strtolower($content);

        $badWords = collect(json_decode($blacklistWords, true))
            ->filter(function ($item) use ($content) {
                $matches = [];
                $pattern = '/\b' . preg_quote($item['value'], '/') . '\b/iu';

                return preg_match($pattern, $content, $matches, PREG_UNMATCHED_AS_NULL);
            })
            ->pluck('value')
            ->all();

        if (! empty($badWords)) {
            return trans('plugins/contact::contact.message_contains_blacklist_words', ['words' => implode(', ', $badWords)]);
        }

        return null;
    }

    public function processContactForm(ContactRequest $request): array
    {
        do_action('form_extra_fields_validate', $request, ContactForm::class);

        $form = ContactForm::create();

        $form->saving(function (ContactForm $form): void {
            $data = $form->getRequestData();

            if (Arr::has($data, 'contact_custom_fields')) {
                $customFields = CustomField::query()
                    ->wherePublished()
                    ->with('options')
                    ->get();

                $data['custom_fields'] = collect($data['contact_custom_fields'])
                    ->mapWithKeys(function ($item, $id) use ($customFields) {
                        $field = $customFields->firstWhere('id', $id);
                        $options = $field->options->firstWhere('value', $item);

                        if (! $field) {
                            return [];
                        }

                        $value = match ($field->type->getValue()) {
                            CustomFieldType::CHECKBOX => $item ? trans('plugins/contact::contact.yes') : trans('plugins/contact::contact.no'),
                            CustomFieldType::RADIO, CustomFieldType::DROPDOWN => $options?->label,
                            default => $item,
                        };

                        return [$field->name => $value];
                    })->all();
            }

            $contact = $form->getModel();

            if (! $contact instanceof BaseModel) {
                return;
            }

            $contact->fill($data)->save();

            event(new SentContactEvent($contact));
        }, true);

        return [
            'success' => true,
            'message' => trans('plugins/contact::contact.send_message_success'),
        ];
    }

    public function sendContact(ContactRequest $request): array
    {
        if ($error = $this->validateBlacklistDomain($request->input('email'))) {
            return [
                'success' => false,
                'message' => $error,
            ];
        }

        if ($error = $this->validateBlacklistKeywords($request->input('content'))) {
            return [
                'success' => false,
                'message' => $error,
            ];
        }

        try {
            return $this->processContactForm($request);
        } catch (Exception $exception) {
            BaseHelper::logError($exception);

            return [
                'success' => false,
                'message' => trans('plugins/contact::contact.cant_send_message'),
            ];
        }
    }
}
