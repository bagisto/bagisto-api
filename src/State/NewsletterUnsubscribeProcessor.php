<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Dto\NewsletterUnsubscribeInput;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Models\NewsletterUnsubscribe;
use Webkul\Core\Repositories\SubscribersListRepository;

/**
 * Mirrors the storefront SubscriptionController::destroy.
 */
class NewsletterUnsubscribeProcessor implements ProcessorInterface
{
    public function __construct(protected SubscribersListRepository $subscriptionRepository) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): NewsletterUnsubscribe
    {
        $token = $data instanceof NewsletterUnsubscribeInput ? $data->token : null;
        $token ??= $context['args']['input']['token'] ?? request()->input('token');

        if (! is_string($token) || trim($token) === '') {
            throw new InvalidInputException(__('bagistoapi::app.graphql.newsletter.token-required'), 422);
        }

        $this->subscriptionRepository->deleteWhere(['token' => $token]);

        $result = new NewsletterUnsubscribe;
        $result->success = true;
        $result->message = __('bagistoapi::app.graphql.newsletter.unsubscribed');

        return $result;
    }
}
