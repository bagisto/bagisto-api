<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingSubscriberRestDto;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminMarketingSubscriber;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Core\Models\Channel;
use Webkul\Core\Models\SubscribersList;

/**
 * Subscriber detail — GET /api/admin/marketing/subscribers/{id} +
 * adminMarketingSubscriber.
 *
 * Branches: GraphQL → the AdminMarketingSubscriber Eloquent model (the `channel`
 * to-one object resolves); REST → the flat AdminMarketingSubscriberRestDto with
 * `channel` as an object `{ id, code, name }` (customerId/customerName scalars).
 */
class AdminMarketingSubscriberItemProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AdminMarketingSubscriber|AdminMarketingSubscriberRestDto
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $id = (int) basename((string) ($uriVariables['id'] ?? $context['args']['id'] ?? 0));

        if ($id <= 0) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.subscriber.not-found'));
        }

        if (! empty($context['graphql_operation_name'])) {
            $model = AdminMarketingSubscriber::with(['channel'])->find($id);

            if (! $model) {
                throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.subscriber.not-found'));
            }

            return $model;
        }

        $subscriber = SubscribersList::with(['customer'])->find($id);

        if (! $subscriber) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.subscriber.not-found'));
        }

        return $this->buildRestDto($subscriber);
    }

    public function buildRestDtoPublic(object $subscriber): AdminMarketingSubscriberRestDto
    {
        return $this->buildRestDto($subscriber);
    }

    protected function buildRestDto(object $subscriber): AdminMarketingSubscriberRestDto
    {
        /** @var SubscribersList $subscriber */
        $dto = new AdminMarketingSubscriberRestDto;

        $dto->id = (int) $subscriber->id;
        $dto->email = $subscriber->email;
        $dto->isSubscribed = (bool) $subscriber->is_subscribed;
        $dto->customerId = $subscriber->customer_id !== null ? (int) $subscriber->customer_id : null;

        if ($subscriber->customer) {
            $dto->customerName = trim((string) $subscriber->customer->first_name.' '.(string) $subscriber->customer->last_name) ?: null;
        }

        $dto->channel = $this->channelObject($subscriber->channel_id);
        $dto->createdAt = $subscriber->created_at?->toIso8601String();
        $dto->updatedAt = $subscriber->updated_at?->toIso8601String();

        return $dto;
    }

    /**
     * @return array{id:int, code:string|null, name:string|null}|null
     */
    protected function channelObject(?int $channelId): ?array
    {
        if (! $channelId) {
            return null;
        }

        try {
            $channel = Channel::find($channelId);
        } catch (\Throwable $e) {
            $channel = null;
        }

        if (! $channel) {
            return null;
        }

        return [
            'id' => (int) $channel->id,
            'code' => $channel->code,
            'name' => $channel->name,
        ];
    }
}
