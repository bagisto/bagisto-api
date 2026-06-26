<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Dto\AdminMarketingSubscriberRestDto;
use Webkul\BagistoApi\Admin\Models\AdminMarketingSubscriber;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * GET /api/admin/marketing/subscribers + adminMarketingSubscribers GraphQL.
 *
 * Filters: email (LIKE), channel_id, is_subscribed (0/1).
 * Sort: id (default desc), email.
 *
 * Branches: GraphQL → an AdminMarketingSubscriber Eloquent row per result (the
 * `channel` to-one is detail-only, null on listing rows — no N+1); REST → the
 * flat AdminMarketingSubscriberRestDto (`channel` omitted on listing rows;
 * customerId/customerName scalars kept).
 */
class AdminMarketingSubscriberCollectionProvider extends AbstractAdminCollectionProvider
{
    protected bool $listingIsGraphQL = false;

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): \ApiPlatform\Laravel\Eloquent\Paginator
    {
        $this->listingIsGraphQL = ! empty($context['graphql_operation_name']);

        return parent::provide($operation, $uriVariables, $context);
    }

    protected function getSortable(): array
    {
        return ['id', 'email'];
    }

    protected function buildQuery(array $args)
    {
        return DB::table('subscribers_list')
            ->leftJoin('customers', 'subscribers_list.customer_id', '=', 'customers.id')
            ->select(
                'subscribers_list.id',
                'subscribers_list.email',
                'subscribers_list.channel_id',
                'subscribers_list.customer_id',
                'customers.first_name as customer_first_name',
                'customers.last_name as customer_last_name',
                'subscribers_list.is_subscribed',
                'subscribers_list.created_at',
                'subscribers_list.updated_at',
            );
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['email'])) {
            $query->where('subscribers_list.email', 'like', '%'.$args['email'].'%');
        }

        if (isset($args['channel_id']) && $args['channel_id'] !== '' && $args['channel_id'] !== null) {
            $query->where('subscribers_list.channel_id', (int) $args['channel_id']);
        }

        if (isset($args['is_subscribed']) && $args['is_subscribed'] !== '' && $args['is_subscribed'] !== null) {
            $query->where('subscribers_list.is_subscribed', (int) (bool) $args['is_subscribed']);
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $map = [
            'id'    => 'subscribers_list.id',
            'email' => 'subscribers_list.email',
        ];

        $query->orderBy($map[$column] ?? 'subscribers_list.id', $direction);
    }

    protected function mapRow(object $row): object
    {
        if ($this->listingIsGraphQL) {
            return $this->mapRowToEloquent($row);
        }

        $dto = new AdminMarketingSubscriberRestDto;
        $dto->id = (int) $row->id;
        $dto->email = $row->email;
        $dto->isSubscribed = $row->is_subscribed !== null ? (bool) $row->is_subscribed : null;
        $dto->customerId = $row->customer_id !== null ? (int) $row->customer_id : null;
        $dto->customerName = trim((string) ($row->customer_first_name ?? '').' '.(string) ($row->customer_last_name ?? '')) ?: null;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        // channel is detail-only — null on list rows.

        return $dto;
    }

    /**
     * GraphQL listing row → Eloquent AdminMarketingSubscriber. The `channel`
     * to-one is set null (detail-only — no per-row N+1). The customer_name is
     * forceFilled so the parent accessor's fast-path returns it without N+1.
     */
    protected function mapRowToEloquent(object $row): AdminMarketingSubscriber
    {
        $customerName = trim((string) ($row->customer_first_name ?? '').' '.(string) ($row->customer_last_name ?? '')) ?: null;

        $model = (new AdminMarketingSubscriber)->forceFill([
            'id'            => (int) $row->id,
            'email'         => $row->email,
            'is_subscribed' => $row->is_subscribed,
            'customer_id'   => $row->customer_id,
            'customer_name' => $customerName,
            'created_at'    => $row->created_at,
            'updated_at'    => $row->updated_at,
        ]);

        $model->setRelation('channel', null);

        return $model;
    }
}
