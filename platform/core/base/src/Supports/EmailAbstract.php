<?php

namespace Botble\Base\Supports;

use Botble\Base\Facades\EmailHandler;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class EmailAbstract extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $content;

    public $subject;

    public array $data;

    public function __construct(?string $content, ?string $subject, array $data = [])
    {
        $this->content = $content ?? '';
        $this->subject = $subject;
        $this->data = $data;
    }

    public function build(): EmailAbstract
    {
        $fromAddress = setting('email_from_address', config('mail.from.address'));

        $fromName = setting('email_from_name', config('mail.from.name'));

        if (isset($this->data['from'])) {
            if (is_array($this->data['from'])) {
                $fromAddress = Arr::first(array_keys($this->data['from']));
                $fromName = Arr::first($this->data['from']);
            } else {
                $fromAddress = $this->data['from'];
            }
        }

        $content = trim($this->content);

        if ($content !== '') {
            $inlineCss = new CssToInlineStyles();
            $content = $inlineCss->convert($content, EmailHandler::getCssContent());
        }

        $email = $this
            ->from($fromAddress, $fromName)
            ->subject($this->subject)
            ->html($content);

        $attachments = Arr::get($this->data, 'attachments');
        if (! empty($attachments)) {
            if (! is_array($attachments)) {
                $attachments = [$attachments];
            }
            foreach ($attachments as $file) {
                if (is_array($file)) {
                    if (isset($file['data'])) {
                        $email->attachData(
                            $file['data'],
                            $file['name'] ?? 'attachment',
                            ['mime' => $file['mime'] ?? 'application/octet-stream']
                        );
                    } elseif (isset($file['file'])) {
                        $options = [];
                        if (! empty($file['name'])) {
                            $options['as'] = $file['name'];
                        }
                        if (! empty($file['mime'])) {
                            $options['mime'] = $file['mime'];
                        }
                        $email->attach($file['file'], $options);
                    }
                } else {
                    $email->attach($file);
                }
            }
        }

        if (isset($this->data['cc'])) {
            $email = $this->cc($this->data['cc']);
        }

        if (isset($this->data['bcc'])) {
            $email = $this->bcc($this->data['bcc']);
        }

        if (isset($this->data['replyTo'])) {
            $email = $this->replyTo($this->data['replyTo']);
        }

        return $email;
    }
}
