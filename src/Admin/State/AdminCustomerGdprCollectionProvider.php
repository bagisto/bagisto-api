<?php

namespace Webkul\BagistoApi\Admin\State;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminCustomerGdprRequest;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;
use Webkul\BagistoApi\Support\Sql;

class AdminCustomerGdprCollectionProvider extends AbstractAdminCollectionProvider
{
    /**
     * Get sortable.
     */
    protected function getSortable(): array
    {
        return ['id', 'status', 'type', 'created_at'];
    }

    /**
     * Build query.
     */
    protected function buildQuery(array $args)
    {

        return DB::table('gdpr_data_request as gdpr')
            ->leftJoin('customers', 'gdpr.customer_id', '=', 'customers.id')
            ->select(
                'gdpr.id',
                'gdpr.customer_id',
                DB::raw(Sql::fullName('customers.first_name', 'customers.last_name').' as customer_name'),
                'gdpr.email',
                'gdpr.type',
                'gdpr.status',
                'gdpr.message',
                'gdpr.revoked_at',
                'gdpr.created_at',
                'gdpr.updated_at',
            );
    }

    /**
     * Apply filters.
     */
    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['status'])) {
            $query->where('gdpr.status', (string) $args['status']);
        }

        if (! empty($args['type'])) {
            $query->where('gdpr.type', (string) $args['type']);
        }

        if (
            isset($args['customer_id'])
            && $args['customer_id'] !== ''
            && $args['customer_id'] !== null
        ) {
            $query->where('gdpr.customer_id', (int) $args['customer_id']);
        }

        if (! empty($args['email'])) {
            $query->where('gdpr.email', db_grammar()->caseInsensitiveLike(), '%'.$args['email'].'%');
        }

        if (! empty($args['customer_name'])) {
            $name = $args['customer_name'];
            $query->where(DB::raw(Sql::fullName('customers.first_name', 'customers.last_name')), db_grammar()->caseInsensitiveLike(), '%'.$name.'%');
        }

        if (! empty($args['created_at_from'])) {
            $query->where('gdpr.created_at', '>=', $args['created_at_from']);
        }
        if (! empty($args['created_at_to'])) {
            $query->where('gdpr.created_at', '<=', $args['created_at_to']);
        }
    }

    /**
     * Apply sort.
     */
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

    /**
     * Map row.
     */
    protected function mapRow(object $row): AdminCustomerGdprRequest
    {
        $dto = new AdminCustomerGdprRequest;
        $dto->id = (int) $row->id;
        $dto->customerId = $row->customer_id !== null ? (int) $row->customer_id : null;
        $dto->customerName = trim((string) ($row->customer_name ?? '')) !== '' ? trim((string) $row->customer_name) : null;
        $dto->email = $row->email;
        $dto->type = $row->type;
        $dto->typeLabel = AdminCustomerGdprRequest::labelForType($row->type);
        $dto->status = $row->status;
        $dto->message = $row->message;
        $dto->revokedAt = $row->revoked_at ? Carbon::parse($row->revoked_at)->toIso8601String() : null;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }
}
