<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Models\AdminMarketingSubscriber;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\Core\Models\SubscribersList;

class AdminMarketingSubscriberItemProvider extends AbstractAdminItemProvider
{
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.marketing.subscriber.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return SubscribersList::with(['customer'])->find($id);
    }

    public function mapToDto(object $entity): object
    {
        return $this->doMap($entity);
    }

    protected function doMap(SubscribersList $s): AdminMarketingSubscriber
    {
        $dto = new AdminMarketingSubscriber;
        $dto->id = (int) $s->id;
        $dto->email = $s->email;
        $dto->channelId = $s->channel_id !== null ? (int) $s->channel_id : null;
        try {
            $dto->channelName = $s->channel_id ? optional(\Webkul\Core\Models\Channel::find($s->channel_id))->code : null;
        } catch (\Throwable $e) {
            $dto->channelName = null;
        }
        $dto->customerId = $s->customer_id !== null ? (int) $s->customer_id : null;
        if ($s->customer) {
            $dto->customerName = trim((string) $s->customer->first_name.' '.(string) $s->customer->last_name) ?: null;
        }
        $dto->isSubscribed = (bool) $s->is_subscribed;
        $dto->createdAt = $s->created_at?->toIso8601String();
        $dto->updatedAt = $s->updated_at?->toIso8601String();

        return $dto;
    }
}
