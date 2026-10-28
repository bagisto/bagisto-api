<?php

namespace Webkul\BagistoApi\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Validator;
use Webkul\BagistoApi\Dto\SubscribeToNewsletterOutput;
use Webkul\BagistoApi\Dto\UnsubscribeFromNewsletterInput;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Core\Repositories\SubscribersListRepository;

class NewsletterUnsubscriptionProcessor implements ProcessorInterface
{
    /**
     * Create a new processor instance.
     */
    public function __construct(protected SubscribersListRepository $subscribers) {}

    /**
     * Unsubscribe the subscriber holding the token, answering alike for unknown tokens.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): SubscribeToNewsletterOutput
    {
        if (! $data instanceof UnsubscribeFromNewsletterInput) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.logout.invalid-input-data'));
        }

        $token = $data->token
            ?? request()->input('token')
            ?? $context['args']['input']['token']
            ?? null;

        $validator = Validator::make(['token' => $token], ['token' => 'required|string|max:255']);

        if ($validator->fails()) {
            throw new InvalidInputException($validator->errors()->first('token'));
        }

        $this->subscribers->deleteWhere(['token' => $token]);

        $output = new SubscribeToNewsletterOutput;
        $output->success = true;
        $output->message = trans('shop::app.subscription.unsubscribe-success');

        return $output;
    }
}
