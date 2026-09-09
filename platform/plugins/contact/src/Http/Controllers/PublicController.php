<?php

namespace Botble\Contact\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Contact\Http\Requests\ContactRequest;
use Botble\Contact\Services\ContactService;
use Illuminate\Validation\ValidationException;

class PublicController extends BaseController
{
    public function __construct(protected ContactService $contactService)
    {
    }

    public function postSendContact(ContactRequest $request)
    {
        try {
            $result = $this->contactService->sendContact($request);

            if (! $result['success']) {
                return $this
                    ->httpResponse()
                    ->setError()
                    ->setMessage($result['message']);
            }

            return $this
                ->httpResponse()
                ->setMessage($result['message']);
        } catch (ValidationException $exception) {
            throw $exception;
        }
    }
}
