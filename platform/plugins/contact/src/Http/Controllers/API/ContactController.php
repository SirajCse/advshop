<?php

namespace Botble\Contact\Http\Controllers\API;

use Botble\Api\Http\Controllers\BaseApiController;
use Botble\Contact\Http\Requests\ContactRequest;
use Botble\Contact\Services\ContactService;
use Illuminate\Validation\ValidationException;

class ContactController extends BaseApiController
{
    public function __construct(protected ContactService $contactService)
    {
    }

    /**
     * Store a new contact message
     *
     * Rate limit: 5 requests per minute per IP address
     *
     * @group Contact
     *
     * @bodyParam name string required The sender's name. Example: John Doe
     * @bodyParam email string required The sender's email. Example: john@example.com
     * @bodyParam phone string The sender's phone number. Example: +1234567890
     * @bodyParam address string The sender's address. Example: 123 Main St
     * @bodyParam subject string The subject of the message. Example: General Inquiry
     * @bodyParam content string required The message content. Example: I would like to know more about your services.
     * @bodyParam agree_terms_and_policy boolean Accept terms and policy. Example: true
     * @bodyParam contact_custom_fields array Custom field values.
     *
     * @response 200 {
     *   "error": false,
     *   "data": null,
     *   "message": "We received your message and will contact you soon!"
     * }
     *
     * @response 422 {
     *   "error": true,
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "name": ["The name field is required."],
     *     "email": ["The email field is required."]
     *   }
     * }
     *
     * @response 429 {
     *   "message": "Too Many Attempts."
     * }
     */
    public function store(ContactRequest $request)
    {
        try {
            $result = $this->contactService->sendContact($request);

            if (! $result['success']) {
                return $this
                    ->httpResponse()
                    ->setError()
                    ->setMessage($result['message'])
                    ->toApiResponse();
            }

            return $this
                ->httpResponse()
                ->setMessage($result['message'])
                ->toApiResponse();
        } catch (ValidationException $exception) {
            throw $exception;
        }
    }
}
