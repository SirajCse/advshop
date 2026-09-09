<?php

namespace Botble\Contact\Http\Requests;

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Rules\EmailRule;
use Botble\Base\Rules\OnOffRule;
use Botble\Base\Rules\PhoneNumberRule;
use Botble\Contact\Enums\CustomFieldType;
use Botble\Contact\Models\CustomField;
use Botble\Support\Http\Requests\Request;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

class ContactRequest extends Request
{
    protected $errorBag = 'contact';

    protected array $mandatoryFields;

    protected array $displayFields;

    protected Collection $customFields;

    public function mandatoryFields(array $fields): static
    {
        $this->mandatoryFields = $fields;

        return $this;
    }

    public function displayFields(array $fields): static
    {
        $this->displayFields = $fields;

        return $this;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:40'],
            'email' => ['nullable', new EmailRule(), 'max:80'],
            'content' => ['required', 'string', 'max:10000'],
            'phone' => ['nullable', new PhoneNumberRule()],
            'address' => ['nullable', 'string', 'max:500'],
            'subject' => ['nullable', 'string', 'max:500'],
        ];

        if (setting('contact_form_show_terms_checkbox', true)) {
            $rules['agree_terms_and_policy'] = ['required', 'accepted:1'];
        }

        try {
            // Get display_fields and required_fields from request
            // These could be strings or arrays depending on how the form is submitted
            $displayFields = $this->input('display_fields');
            $requiredFields = $this->input('required_fields');

            $rules = $this->applyRules(
                $rules,
                $displayFields,
                $requiredFields
            );
        } catch (Throwable $exception) {
            BaseHelper::logError($exception);
        }

        $customFields = $this->getCustomFields();

        if ($customFields->isNotEmpty()) {
            $rules['contact_custom_fields'] = ['required', 'array'];
        }

        foreach ($customFields as $customField) {
            $customFieldRules = [$customField->required ? 'required' : 'nullable'];

            $fieldKey = "contact_custom_fields.$customField->id";

            $rules[$fieldKey] = match ($customField->type->getValue()) {
                CustomFieldType::TEXT, CustomFieldType::DROPDOWN, CustomFieldType::RADIO => [...$customFieldRules, 'string', 'max:255'],
                CustomFieldType::TEXTAREA => [...$customFieldRules, 'string', 'max:1000'],
                CustomFieldType::NUMBER => [...$customFieldRules, 'numeric'],
                CustomFieldType::CHECKBOX => [new OnOffRule()],
                default => $customFieldRules,
            };
        }

        return apply_filters('contact_request_rules', $rules, $this);
    }

    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'Name of the sender.',
                'example' => 'John Doe',
            ],
            'email' => [
                'description' => 'Contact email address. Required when the email field is marked mandatory.',
                'example' => 'john@example.com',
            ],
            'content' => [
                'description' => 'Message content from the sender.',
                'example' => 'I would like to know more about your services.',
            ],
            'phone' => [
                'description' => 'Phone number of the sender.',
                'example' => '+1 555-123-4567',
            ],
            'address' => [
                'description' => 'Address of the sender.',
                'example' => '123 Main St, Los Angeles, CA',
            ],
            'subject' => [
                'description' => 'Subject of the contact message.',
                'example' => 'Inquiry about product availability',
            ],
            'agree_terms_and_policy' => [
                'description' => 'Must be accepted when the terms checkbox is enabled in settings.',
                'example' => true,
            ],
            'contact_custom_fields' => [
                'description' => 'Keyed array of values for any published custom contact fields.',
                'example' => [
                    '1' => 'Extra information',
                ],
            ],
            'display_fields' => [
                'description' => 'Optional list of fields that should be displayed on the contact form.',
                'example' => ['email', 'phone', 'subject'],
            ],
            'required_fields' => [
                'description' => 'Optional list of displayed fields that should be treated as required.',
                'example' => ['email'],
            ],
        ];
    }

    public function attributes(): array
    {
        $attributes = [
            'name' => trans('plugins/contact::contact.form_name'),
            'email' => trans('plugins/contact::contact.form_email'),
            'phone' => trans('plugins/contact::contact.form_phone'),
            'content' => trans('plugins/contact::contact.form_content'),
            'subject' => trans('plugins/contact::contact.form_subject'),
            'address' => trans('plugins/contact::contact.form_address'),
            'agree_terms_and_policy' => trans('plugins/contact::contact.agree_terms_and_policy'),
            'contact_custom_fields' => trans('plugins/contact::contact.custom_fields'),
        ];

        $customFields = $this->getCustomFields();

        foreach ($customFields as $customField) {
            $attributes["contact_custom_fields.$customField->id"] = $customField->name;
        }

        return $attributes;
    }

    public function messages(): array
    {
        return [
            'agree_terms_and_policy.accepted' => trans('plugins/contact::contact.must_agree_terms'),
        ];
    }

    protected function getCustomFields(): Collection
    {
        if (isset($this->customFields)) {
            return $this->customFields;
        }

        return $this->customFields = CustomField::query()->wherePublished()->with('options')->get();
    }

    protected function filtersByDisplayFields(array $rules): array
    {
        if (empty($this->displayFields)) {
            $this->displayFields = ['email', 'address', 'phone', 'subject'];
        }

        $displayFields = [...$this->displayFields, ...$this->alwaysMandatoryFields()];

        foreach ($rules as $key => $rule) {
            if (! in_array($key, $displayFields)) {
                unset($rules[$key]);
            }
        }

        return $rules;
    }

    protected function applyMandatoryFields(array $rules): array
    {
        if (empty($this->mandatoryFields)) {
            $this->mandatoryFields = ['email'];
        }

        $mandatoryFields = [...$this->mandatoryFields, ...$this->alwaysMandatoryFields()];

        foreach ($rules as $key => $rule) {
            if (is_array($rule)) {
                foreach ($rule as $ruleKey => $ruleValue) {
                    if (in_array($ruleValue, ['required', 'nullable'])) {
                        $rule[$ruleKey] = in_array($key, $mandatoryFields) ? 'required' : 'nullable';
                    }

                    $rules[$key] = $rule;
                }
            }
        }

        foreach ($mandatoryFields as $mandatoryField) {
            if (! in_array($mandatoryField, array_keys($rules))
                && in_array($mandatoryField, $this->displayFields)) {
                $rules[$mandatoryField] = 'required';
            }
        }

        return $rules;
    }

    protected function alwaysMandatoryFields(): array
    {
        $mandatoryFields = ['name', 'content'];

        if (setting('contact_form_show_terms_checkbox', true)) {
            $mandatoryFields[] = 'agree_terms_and_policy';
        }

        return $mandatoryFields;
    }

    public function applyRules(array $rules, $displayFields = null, $mandatoryFields = null): array
    {
        // Handle both string and array inputs for fields
        if (is_array($mandatoryFields)) {
            $this->mandatoryFields(array_filter($mandatoryFields));
        } elseif (is_string($mandatoryFields) && $mandatoryFields !== '') {
            $this->mandatoryFields(array_filter(explode(',', $mandatoryFields)));
        } else {
            $this->mandatoryFields([]);
        }

        if (is_array($displayFields)) {
            $this->displayFields(array_filter($displayFields));
        } elseif (is_string($displayFields) && $displayFields !== '') {
            $this->displayFields(array_filter(explode(',', $displayFields)));
        } else {
            $this->displayFields([]);
        }

        $rules = $this->filtersByDisplayFields($rules);

        return $this->applyMandatoryFields($rules);
    }
}
