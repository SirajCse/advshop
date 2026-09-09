<?php

namespace Botble\Ecommerce\Notifications;

use Botble\Base\Facades\EmailHandler;
use Botble\Base\Supports\EmailHandler as EmailHandlerSupport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected string $emailLocale;

    public function __construct(public string $token)
    {
        $this->emailLocale = EmailHandlerSupport::getDefaultEmailLocale();
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $previousLocale = app()->getLocale();
        app()->setLocale($this->emailLocale);

        try {
            $emailHandler = EmailHandler::setModule(ECOMMERCE_MODULE_SCREEN_NAME)
                ->setType('plugins')
                ->setTemplate('password-reminder')
                ->addTemplateSettings(ECOMMERCE_MODULE_SCREEN_NAME, config('plugins.ecommerce.email', []))
                ->setVariableValues([
                    'reset_link' => route('customer.password.reset.update', ['token' => $this->token, 'email' => request()->input('email')]),
                    'customer_name' => $notifiable->name,
                ]);

            return (new MailMessage())
                ->view(['html' => new HtmlString($emailHandler->getContent())])
                ->subject($emailHandler->getSubject());
        } finally {
            app()->setLocale($previousLocale);
        }
    }
}
