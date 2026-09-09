<?php

namespace Botble\Contact\Listeners;

use Botble\Base\Facades\EmailHandler;
use Botble\Contact\Events\SentContactEvent;
use Botble\Contact\Models\Contact;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Arr;

class SendContactEmailListener implements ShouldQueue
{
    public function handle(SentContactEvent $event): void
    {
        $contact = $event->data;

        if (! $contact instanceof Contact) {
            return;
        }

        $locale = $event->locale;

        $receiverEmails = $this->getReceiverEmails();
        $customFields = $contact->custom_fields ?? [];

        $args = [];

        if ($contact->name && $contact->email) {
            $args = ['replyTo' => [$contact->name => $contact->email]];
        }

        $emailHandler = EmailHandler::setModule(CONTACT_MODULE_SCREEN_NAME)
            ->setVariableValues([
                'contact_name' => $contact->name,
                'contact_subject' => $contact->subject,
                'contact_email' => $contact->email,
                'contact_phone' => $contact->phone,
                'contact_address' => $contact->address,
                'contact_content' => $contact->content,
                'contact_custom_fields' => $customFields,
            ]);

        $emailHandler->sendUsingTemplate('notice', $receiverEmails ?: null, $args);

        $args = ['replyTo' => is_array($receiverEmails) ? Arr::first($receiverEmails) : $receiverEmails];

        $emailHandler->sendUsingTemplateWithLocale('sender-confirmation', $contact->email, $locale, $args);
    }

    protected function getReceiverEmails(): string|array|null
    {
        $receiverEmails = null;

        if ($receiverEmailsSetting = setting('receiver_emails', '')) {
            $receiverEmails = trim($receiverEmailsSetting);
        }

        if ($receiverEmails) {
            $receiverEmails = collect(json_decode($receiverEmails, true))
                ->pluck('value')
                ->all();
        }

        if (is_array($receiverEmails)) {
            $receiverEmails = array_filter($receiverEmails);

            if (count($receiverEmails) === 1) {
                $receiverEmails = Arr::first($receiverEmails);
            }
        }

        return $receiverEmails;
    }
}
