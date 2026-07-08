<?php

namespace Webkul\BagistoApi\Admin\State;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminCustomerGdprRequest;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * GET /api/admin/customers/gdpr-requests + adminCustomerGdprRequests GraphQL.
 *
 * DataGrid parity with Webkul\Admin\DataGrids\Customers\GDPRDataGrid.
 * Filters: status, type, customer_id, email, customer_name, created_at range.
 * Sort: id (default desc), status, type, created_at.
 */
class AdminCustomerGdprCollectionProvider extends AbstractAdminCollectionProvider
{
    protected function getSortable(): array
    {
        return ['id', 'status', 'type', 'created_at'];
    }

    protected function buildQuery(array $args)
    {
        $tablePrefix = DB::getTablePrefix();

        return DB::table('gdpr_data_request as gdpr')
            ->leftJoin('customers', 'gdpr.customer_id', '=', 'customers.id')
            ->select(
                'gdpr.id',
                'gdpr.customer_id',
                DB::raw("CONCAT(COALESCE({$tablePrefix}customers.first_name, ''), ' ', COALESCE({$tablePrefix}customers.last_name, '')) as customer_name"),
                'gdpr.email',
                'gdpr.type',
                'gdpr.status',
                'gdpr.message',
                'gdpr.revoked_at',
                'gdpr.created_at',
                'gdpr.updated_at',
            );
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['status'])) {
            $query->where('gdpr.status', (string) $args['status']);
        }

        if (! empty($args['type'])) {
            $query->where('gdpr.type', (string) $args['type']);
        }

        if (isset($args['customer_id']) && $args['customer_id'] !== '' && $args['customer_id'] !== null) {
            $query->where('gdpr.customer_id', (int) $args['customer_id']);
        }

        if (! empty($args['email'])) {
            $query->where('gdpr.email', 'like', '%'.$args['email'].'%');
        }

        if (! empty($args['customer_name'])) {
            $tablePrefix = DB::getTablePrefix();
            $name = $args['customer_name'];
            $query->where(DB::raw("CONCAT({$tablePrefix}customers.first_name, ' ', {$tablePrefix}customers.last_name)"), 'like', '%'.$name.'%');
        }

        if (! empty($args['created_at_from'])) {
            $query->where('gdpr.created_at', '>=', $args['created_at_from']);
        }
        if (! empty($args['created_at_to'])) {
            $query->where('gdpr.created_at', '<=', $args['created_at_to']);
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $map = [
            'id' => 'gdpr.id',
            'status' => 'gdpr.status',
            'type' => 'gdpr.type',
            'created_at' => 'gdpr.created_at',
        ];

        $query->orderBy($map[$column] ?? 'gdpr.id', $direction);
    }

    protected function mapRow(object $row): AdminCustomerGdprRequest
    {
        $dto = new AdminCustomerGdprRequest;
        $dto->id = (int) $row->id;
        $dto->customerId = $row->customer_id !== null ? (int) $row->customer_id : null;
        $dto->customerName = trim((string) ($row->customer_name ?? '')) !== '' ? trim((string) $row->customer_name) : null;
        $dto->email = $row->email;
        $dto->type = $row->type;
        $dto->status = $row->status;
        $dto->message = $row->message;
        $dto->revokedAt = $row->revoked_at ? Carbon::parse($row->revoked_at)->toIso8601String() : null;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }
}
