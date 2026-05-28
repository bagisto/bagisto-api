<?php

namespace Webkul\BagistoApi\Admin\State;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminMarketingSubscriber;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * GET /api/admin/marketing/subscribers + adminMarketingSubscribers GraphQL.
 *
 * Filters: email (LIKE), channel_id, is_subscribed (0/1).
 * Sort: id (default desc), email.
 */
class AdminMarketingSubscriberCollectionProvider extends AbstractAdminCollectionProvider
{
    protected function getSortable(): array
    {
        return ['id', 'email'];
    }

    protected function buildQuery(array $args)
    {
        return DB::table('subscribers_list')
            ->leftJoin('channels', 'subscribers_list.channel_id', '=', 'channels.id')
            ->leftJoin('customers', 'subscribers_list.customer_id', '=', 'customers.id')
            ->select(
                'subscribers_list.id',
                'subscribers_list.email',
                'subscribers_list.channel_id',
                'channels.code as channel_code',
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

    protected function mapRow(object $row): AdminMarketingSubscriber
    {
        $dto = new AdminMarketingSubscriber;
        $dto->id = (int) $row->id;
        $dto->email = $row->email;
        $dto->channelId = $row->channel_id !== null ? (int) $row->channel_id : null;
        $dto->channelName = $row->channel_code;
        $dto->customerId = $row->customer_id !== null ? (int) $row->customer_id : null;
        $dto->customerName = trim((string) ($row->customer_first_name ?? '').' '.(string) ($row->customer_last_name ?? '')) ?: null;
        $dto->isSubscribed = $row->is_subscribed !== null ? (bool) $row->is_subscribed : null;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }
}
