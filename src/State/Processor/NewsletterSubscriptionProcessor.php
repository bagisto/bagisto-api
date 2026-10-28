<?php

namespace Webkul\BagistoApi\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Dto\SubscribeToNewsletterInput;
use Webkul\BagistoApi\Dto\SubscribeToNewsletterOutput;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Core\Repositories\SubscribersListRepository;

class NewsletterSubscriptionProcessor implements ProcessorInterface
{
    public function __construct(protected SubscribersListRepository $subscriptionRepository) {}

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

        $validator = Validator::make(['customerEmail' => $data->customerEmail], ['customerEmail' => 'required|email']);

        if ($validator->fails()) {
            throw new InvalidInputException(implode(' ', $validator->errors()->all()));
        }

        Event::dispatch('customer.subscription.before');

        if ($this->subscriptionRepository->findOneWhere(['email' => $data->customerEmail, 'is_subscribed' => 1])) {
            throw new InvalidInputException(trans('shop::app.subscription.already'));
        }

        $subscription = $this->upsertSubscription($data->customerEmail);

        Auth::guard('sanctum')->user()?->update(['subscribed_to_news_letter' => 1]);

        Event::dispatch('customer.subscription.after', $subscription);

        return $this->output(true, __('shop::app.subscription.subscribe-success'));
    }

    /**
     * Mirror the storefront upsert: an unsubscribed row is subscribed again, never duplicated.
     */
    private function upsertSubscription(string $email): mixed
    {
        $payload = [
            'is_subscribed' => 1,
            'token' => uniqid(),
            'customer_id' => Auth::guard('sanctum')->user()?->id,
        ];

        if ($existing = $this->subscriptionRepository->findOneByField('email', $email)) {
            $existing->update($payload);

            return $existing;
        }

        return $this->subscriptionRepository->create(array_merge($payload, [
            'email' => $email,
            'channel_id' => core()->getCurrentChannel()->id,
        ]));
    }

    private function output(bool $success, string $message): SubscribeToNewsletterOutput
    {
        $output = new SubscribeToNewsletterOutput;

        $output->success = $success;
        $output->message = $message;

        return $output;
    }
}
