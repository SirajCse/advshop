<?php

namespace Botble\Setting\Http\Controllers;

use Botble\Base\Facades\EmailHandler;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Setting\Http\Requests\EmailSendTestRequest;
use Exception;

class EmailTestController extends BaseController
{
    public function __invoke(EmailSendTestRequest $request)
    {
        try {
            $content = get_setting_email_template_content('core', 'base', 'test');
            $subject = get_setting_email_subject('core', 'base', 'test');

            if ($template = $request->input('template')) {
                [$type, $module, $templateName] = explode('.', $template);

                if ($type && $module && $templateName) {
                    $content = get_setting_email_template_content($type, $module, $templateName);
                    $subject = get_setting_email_subject($type, $module, $templateName) ?: $subject;
                }
            }

            EmailHandler::send(
                $content,
                $subject,
                $request->input('email'),
                [],
                true
            );

            return $this
                ->httpResponse()
                ->setMessage(trans('core/setting::setting.test_email_send_success'));
        } catch (Exception $exception) {
            return $this
                ->httpResponse()
                ->setError()
                ->setMessage($exception->getMessage());
        }
    }
}
