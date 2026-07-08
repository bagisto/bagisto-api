<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use Webkul\BagistoApi\Admin\Models\AdminEuWithdrawal;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;
use Webkul\BagistoApi\Admin\State\Concerns\BuildsAdminEuWithdrawal;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;

class AdminEuWithdrawalCollectionProvider extends AbstractAdminCollectionProvider
{
    use BuildsAdminEuWithdrawal;
    use ChecksAdminPermission;

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): \ApiPlatform\Laravel\Eloquent\Paginator
    {
        $this->authorizedAdmin('sales.eu_withdrawals', 'bagistoapi::app.admin.eu-withdrawal.no-permission');

        return parent::provide($operation, $uriVariables, $context);
    }

    protected function getSortable(): array
    {
        return ['id', 'received_at', 'status'];
    }

    protected function buildQuery(array $args)
    {
        return $this->baseWithdrawalQuery();
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['order_increment_id'])) {
            $query->where('o.increment_id', 'like', '%'.$args['order_increment_id'].'%');
        }

        if (! empty($args['customer_email'])) {
            $query->where('w.customer_email', 'like', '%'.$args['customer_email'].'%');
        }

        if (! empty($args['status'])) {
            $query->where('w.status', $args['status']);
        }

        if (! empty($args['channel_code'])) {
            $query->where('c.code', $args['channel_code']);
        }

        if (! empty($args['received_at_from'])) {
            $query->whereDate('w.received_at', '>=', $args['received_at_from']);
        }

        if (! empty($args['received_at_to'])) {
            $query->whereDate('w.received_at', '<=', $args['received_at_to']);
        }

        if (! empty($args['confirmation_sent_at_from'])) {
            $query->whereDate('w.confirmation_sent_at', '>=', $args['confirmation_sent_at_from']);
        }

        if (! empty($args['confirmation_sent_at_to'])) {
            $query->whereDate('w.confirmation_sent_at', '<=', $args['confirmation_sent_at_to']);
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $map = ['id' => 'w.id', 'received_at' => 'w.received_at', 'status' => 'w.status'];

        $query->orderBy($map[$column] ?? 'w.id', $direction);
    }

    protected function mapRow(object $row): AdminEuWithdrawal
    {
        return $this->mapWithdrawalRow($row);
    }
}
