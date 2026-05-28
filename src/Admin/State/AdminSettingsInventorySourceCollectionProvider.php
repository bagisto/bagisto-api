<?php

namespace Webkul\BagistoApi\Admin\State;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminSettingsInventorySource;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * Provider for GET /api/admin/settings/inventory-sources +
 * adminSettingsInventorySources GraphQL query.
 */
class AdminSettingsInventorySourceCollectionProvider extends AbstractAdminCollectionProvider
{
    protected function getSortable(): array
    {
        return ['id', 'code', 'name', 'priority', 'status'];
    }

    protected function buildQuery(array $args)
    {
        return DB::table('inventory_sources')->select(
            'id',
            'code',
            'name',
            'description',
            'contact_name',
            'contact_email',
            'contact_number',
            'contact_fax',
            'country',
            'state',
            'city',
            'street',
            'postcode',
            'priority',
            'latitude',
            'longitude',
            'status',
            'created_at',
            'updated_at',
        );
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['code'])) {
            $query->where('code', 'like', '%'.$args['code'].'%');
        }

        if (! empty($args['name'])) {
            $query->where('name', 'like', '%'.$args['name'].'%');
        }

        if (isset($args['status']) && $args['status'] !== '' && $args['status'] !== null) {
            $query->where('status', (int) $args['status']);
        }

        if (! empty($args['country'])) {
            $query->where('country', $args['country']);
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $allowed = $this->getSortable();
        if (! in_array($column, $allowed, true)) {
            $column = 'id';
        }

        $query->orderBy($column, $direction);
    }

    protected function mapRow(object $row): AdminSettingsInventorySource
    {
        $dto = new AdminSettingsInventorySource;

        $dto->id = (int) $row->id;
        $dto->code = $row->code;
        $dto->name = $row->name;
        $dto->description = $row->description;
        $dto->contactName = $row->contact_name;
        $dto->contactEmail = $row->contact_email;
        $dto->contactNumber = $row->contact_number;
        $dto->contactFax = $row->contact_fax;
        $dto->country = $row->country;
        $dto->state = $row->state;
        $dto->city = $row->city;
        $dto->street = $row->street;
        $dto->postcode = $row->postcode;
        $dto->priority = $row->priority !== null ? (int) $row->priority : null;
        $dto->latitude = $row->latitude !== null ? (float) $row->latitude : null;
        $dto->longitude = $row->longitude !== null ? (float) $row->longitude : null;
        $dto->status = $row->status !== null ? (int) $row->status : null;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }
}
