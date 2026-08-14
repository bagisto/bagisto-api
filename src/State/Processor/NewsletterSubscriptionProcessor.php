<?php

namespace Webkul\BagistoApi\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Dto\SubscribeToNewsletterInput;
use Webkul\BagistoApi\Dto\SubscribeToNewsletterOutput;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Core\Models\SubscribersListProxy;

class NewsletterSubscriptionProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if (
            $operation->getName() !== 'create'
            && ! $operation instanceof Post
        ) {
            return $this->output(false, __('bagistoapi::app.graphql.logout.invalid-operation'));
        }

        if (! ($data instanceof SubscribeToNewsletterInput)) {
            return $this->output(false, __('bagistoapi::app.graphql.logout.invalid-input-data'));
        }

        if (empty($data->customerEmail)) {
            $body = request()->all();
            $input = $context['args']['input'] ?? [];
            $data->customerEmail = $body['customerEmail']
                ?? $body['customer_email']
                ?? $input['customerEmail']
                ?? $input['customer_email']
                ?? null;
        }

        $validator = Validator::make(['customerEmail' => $data->customerEmail], ['customerEmail' => 'required|email|unique:subscribers_list,email']);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $errorMessage = implode(' ', $errors);

            throw new InvalidInputException($errorMessage);
        }

        $customer = Auth::guard('sanctum')->user();

        try {
            SubscribersListProxy::create([
                'email' => $data->customerEmail,
                'channel_id' => $data?->channelId ?? core()->getCurrentChannel()->id,
                'is_subscribed' => 1,
                'token' => uniqid(),
                'customer_id' => $customer?->id,
            ]);

            return $this->output(true, __('shop::app.subscription.subscribe-success'));
        } catch (\Exception $e) {
            report($e);

            return $this->output(false, __('bagistoapi::app.graphql.newsletter.error-during-subscription'));
        }
    }

    private function output(bool $success, string $message): SubscribeToNewsletterOutput
    {
        $output = new SubscribeToNewsletterOutput;

        $output->success = $success;
        $output->message = $message;

        return $output;
    }
}
