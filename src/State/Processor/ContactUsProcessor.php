<?php

namespace Webkul\BagistoApi\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Dto\ContactUsInput;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Shop\Mail\ContactUs;

/**
 * Processor for Contact Us form submissions
 *
 * Validates input and queues the contact email to the store admin.
 */
class ContactUsProcessor implements ProcessorInterface
{
    /**
     * Process the Contact Us form submission
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): object
    {
        if (! ($data instanceof ContactUsInput)) {
            return (object) [
                'success' => false,
                'message' => __('bagistoapi::app.graphql.contact-us.invalid-input'),
            ];
        }

        $validator = Validator::make([
            'name' => $data->name,
            'email' => $data->email,
            'contact' => $data->contact,
            'message' => $data->message,
        ], [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'contact' => 'nullable|string|max:50',
            'message' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new InvalidInputException(implode(' ', $validator->errors()->all()));
        }

        try {
            Mail::queue(new ContactUs([
                'name' => $data->name,
                'email' => $data->email,
                'contact' => $data->contact ?? '',
                'message' => $data->message,
            ]));

            return (object) [
                'success' => true,
                'message' => __('bagistoapi::app.graphql.contact-us.success'),
            ];
        } catch (\Exception $e) {
            report($e);

            return (object) [
                'success' => false,
                'message' => __('bagistoapi::app.graphql.contact-us.failed'),
            ];
        }
    }
}
