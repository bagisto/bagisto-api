<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Models\AdminMarketingCampaign;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\Marketing\Models\Campaign;

class AdminMarketingCampaignItemProvider extends AbstractAdminItemProvider
{
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.marketing.campaign.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return Campaign::with(['email_template', 'event', 'channel', 'customer_group'])->find($id);
    }

    protected function mapToDto(object $campaign): AdminMarketingCampaign
    {
        /** @var Campaign $campaign */
        $dto = new AdminMarketingCampaign;

        $dto->id = (int) $campaign->id;
        $dto->name = $campaign->name;
        $dto->subject = $campaign->subject;
        $dto->status = $campaign->status !== null ? (int) $campaign->status : null;
        $dto->marketingTemplateId = $campaign->marketing_template_id !== null ? (int) $campaign->marketing_template_id : null;
        $dto->marketingEventId = $campaign->marketing_event_id !== null ? (int) $campaign->marketing_event_id : null;
        $dto->channelId = $campaign->channel_id !== null ? (int) $campaign->channel_id : null;
        $dto->customerGroupId = $campaign->customer_group_id !== null ? (int) $campaign->customer_group_id : null;
        $dto->marketingTemplateName = $campaign->email_template?->name;
        $dto->marketingEventName = $campaign->event?->name;
        $dto->channelName = $campaign->channel?->name ?? $campaign->channel?->code;
        $dto->customerGroupCode = $campaign->customer_group?->code;
        $dto->createdAt = $campaign->created_at?->toIso8601String();
        $dto->updatedAt = $campaign->updated_at?->toIso8601String();

        return $dto;
    }

    /** Public alias used by the processor to reuse mapping after a write. */
    public function mapToDtoPublic(object $campaign): AdminMarketingCampaign
    {
        return $this->mapToDto($campaign);
    }
}
