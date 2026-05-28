<?php

namespace Webkul\BagistoApi\Admin\State;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminMarketingCampaign;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * Provider for GET /api/admin/marketing/campaigns + adminMarketingCampaigns.
 *
 * Filters: name (LIKE), status (0/1), marketing_template_id, marketing_event_id,
 *          channel_id, customer_group_id.
 * Sort:    id (default desc), name.
 *
 * Listing rows omit the resolved relation labels (`marketingTemplateName` /
 * `marketingEventName` / `channelName` / `customerGroupCode`) — those are
 * detail-only.
 */
class AdminMarketingCampaignCollectionProvider extends AbstractAdminCollectionProvider
{
    protected function getSortable(): array
    {
        return ['id', 'name'];
    }

    protected function buildQuery(array $args)
    {
        return DB::table('marketing_campaigns')->select(
            'marketing_campaigns.id',
            'marketing_campaigns.name',
            'marketing_campaigns.subject',
            'marketing_campaigns.status',
            'marketing_campaigns.marketing_template_id',
            'marketing_campaigns.marketing_event_id',
            'marketing_campaigns.channel_id',
            'marketing_campaigns.customer_group_id',
            'marketing_campaigns.created_at',
            'marketing_campaigns.updated_at',
        );
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['name'])) {
            $query->where('marketing_campaigns.name', 'like', '%'.$args['name'].'%');
        }

        if (isset($args['status']) && $args['status'] !== '') {
            $query->where('marketing_campaigns.status', (int) $args['status']);
        }

        foreach (['marketing_template_id', 'marketing_event_id', 'channel_id', 'customer_group_id'] as $col) {
            if (isset($args[$col]) && $args[$col] !== '') {
                $query->where('marketing_campaigns.'.$col, (int) $args[$col]);
            }
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $columnMap = [
            'id'   => 'marketing_campaigns.id',
            'name' => 'marketing_campaigns.name',
        ];

        $query->orderBy($columnMap[$column] ?? 'marketing_campaigns.id', $direction);
    }

    protected function mapRow(object $row): AdminMarketingCampaign
    {
        $dto = new AdminMarketingCampaign;

        $dto->id = (int) $row->id;
        $dto->name = $row->name;
        $dto->subject = $row->subject;
        $dto->status = $row->status !== null ? (int) $row->status : null;
        $dto->marketingTemplateId = $row->marketing_template_id !== null ? (int) $row->marketing_template_id : null;
        $dto->marketingEventId = $row->marketing_event_id !== null ? (int) $row->marketing_event_id : null;
        $dto->channelId = $row->channel_id !== null ? (int) $row->channel_id : null;
        $dto->customerGroupId = $row->customer_group_id !== null ? (int) $row->customer_group_id : null;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }
}
