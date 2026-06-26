<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCampaignRestDto;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminMarketingCampaign;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Marketing\Models\Campaign;

/**
 * Campaign detail — GET /api/admin/marketing/campaigns/{id} +
 * adminMarketingCampaign.
 *
 * Branches: GraphQL → the AdminMarketingCampaign Eloquent model (the four to-one
 * objects resolve); REST → the flat AdminMarketingCampaignRestDto with channel /
 * customer_group / marketing_template / marketing_event as objects.
 */
class AdminMarketingCampaignItemProvider implements ProviderInterface
{
    protected const GRAPHQL_RELATIONS = ['channel', 'customer_group', 'marketing_template'];

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AdminMarketingCampaign|AdminMarketingCampaignRestDto
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $id = (int) basename((string) ($uriVariables['id'] ?? $context['args']['id'] ?? 0));

        if ($id <= 0) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.campaign.not-found'));
        }

        if (! empty($context['graphql_operation_name'])) {
            $model = AdminMarketingCampaign::with(self::GRAPHQL_RELATIONS)->find($id);

            if (! $model) {
                throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.campaign.not-found'));
            }

            return $model;
        }

        $campaign = Campaign::with(['email_template', 'event', 'channel', 'customer_group'])->find($id);

        if (! $campaign) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.marketing.campaign.not-found'));
        }

        return $this->buildRestDto($campaign);
    }

    public function buildRestDtoPublic(object $campaign): AdminMarketingCampaignRestDto
    {
        return $this->buildRestDto($campaign);
    }

    protected function buildRestDto(object $campaign): AdminMarketingCampaignRestDto
    {
        /** @var Campaign $campaign */
        $dto = new AdminMarketingCampaignRestDto;

        $dto->id = (int) $campaign->id;
        $dto->name = $campaign->name;
        $dto->subject = $campaign->subject;
        $dto->status = $campaign->status !== null ? (int) $campaign->status : null;
        $dto->channel = $this->channelObject($campaign->channel);
        $dto->customerGroup = $this->customerGroupObject($campaign->customer_group);
        $dto->marketingTemplate = $this->templateObject($campaign->email_template);
        $dto->marketingEvent = $this->eventObject($campaign->event);
        $dto->createdAt = $campaign->created_at?->toIso8601String();
        $dto->updatedAt = $campaign->updated_at?->toIso8601String();

        return $dto;
    }

    /**
     * @return array{id:int, code:string|null, name:string|null}|null
     */
    protected function channelObject(?object $channel): ?array
    {
        if (! $channel) {
            return null;
        }

        return [
            'id'   => (int) $channel->id,
            'code' => $channel->code,
            'name' => $channel->name ?? $channel->code,
        ];
    }

    /**
     * @return array{id:int, code:string|null, name:string|null}|null
     */
    protected function customerGroupObject(?object $group): ?array
    {
        if (! $group) {
            return null;
        }

        return [
            'id'   => (int) $group->id,
            'code' => $group->code,
            'name' => $group->name,
        ];
    }

    /**
     * @return array{id:int, name:string|null, status:string|null}|null
     */
    protected function templateObject(?object $template): ?array
    {
        if (! $template) {
            return null;
        }

        return [
            'id'     => (int) $template->id,
            'name'   => $template->name,
            'status' => $template->status,
        ];
    }

    /**
     * @return array{id:int, name:string|null, date:string|null}|null
     */
    protected function eventObject(?object $event): ?array
    {
        if (! $event) {
            return null;
        }

        $date = $event->date;
        if ($date instanceof \DateTimeInterface) {
            $date = $date->format('Y-m-d');
        }

        return [
            'id'   => (int) $event->id,
            'name' => $event->name,
            'date' => $date !== null ? (string) $date : null,
        ];
    }
}
