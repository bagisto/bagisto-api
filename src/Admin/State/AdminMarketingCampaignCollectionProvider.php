<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingCampaignRestDto;
use Webkul\BagistoApi\Admin\Models\AdminMarketingCampaign;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * Provider for GET /api/admin/marketing/campaigns + adminMarketingCampaigns.
 *
 * Filters: name (LIKE), status (0/1), marketing_template_id, marketing_event_id,
 *          channel_id, customer_group_id.
 * Sort:    id (default desc), name.
 *
 * Branches: GraphQL → an AdminMarketingCampaign Eloquent row per result (the four
 * to-one objects are detail-only, null on listing rows — no N+1); REST → the flat
 * AdminMarketingCampaignRestDto (the four objects omitted on listing rows).
 */
class AdminMarketingCampaignCollectionProvider extends AbstractAdminCollectionProvider
{
    protected bool $listingIsGraphQL = false;

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): \ApiPlatform\Laravel\Eloquent\Paginator
    {
        $this->listingIsGraphQL = ! empty($context['graphql_operation_name']);

        return parent::provide($operation, $uriVariables, $context);
    }

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
            'marketing_campaigns.type',
            'marketing_campaigns.mail_to',
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

    protected function mapRow(object $row): object
    {
        if ($this->listingIsGraphQL) {
            return $this->mapRowToEloquent($row);
        }

        $dto = new AdminMarketingCampaignRestDto;
        $dto->id = (int) $row->id;
        $dto->name = $row->name;
        $dto->subject = $row->subject;
        $dto->status = $row->status !== null ? (int) $row->status : null;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        // channel / customer_group / marketing_template / marketing_event are
        // detail-only — null on list rows.

        return $dto;
    }

    /**
     * GraphQL listing row → Eloquent AdminMarketingCampaign. The four to-one
     * objects are set null (detail-only — no per-row N+1). The FK ids are NOT
     * forceFilled (they're consumed by the relations, not exposed as scalars).
     */
    protected function mapRowToEloquent(object $row): AdminMarketingCampaign
    {
        $model = (new AdminMarketingCampaign)->forceFill([
            'id'         => (int) $row->id,
            'name'       => $row->name,
            'subject'    => $row->subject,
            'status'     => $row->status,
            'type'       => $row->type,
            'mail_to'    => $row->mail_to,
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ]);

        $model->setRelation('channel', null);
        $model->setRelation('customer_group', null);
        $model->setRelation('marketing_template', null);

        return $model;
    }
}
