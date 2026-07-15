<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerListDto;
use Webkul\BagistoApi\Admin\Models\AdminCustomer;
use Webkul\BagistoApi\Admin\Models\AdminCustomerGroupRef;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

class AdminCustomerCollectionProvider extends AbstractAdminCollectionProvider
{
    protected bool $isGraphQL = false;

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {
        $this->isGraphQL = ! empty($context['graphql_operation_name']);

        return parent::provide($operation, $uriVariables, $context);
    }

    protected function getSortable(): array
    {
        return ['id', 'email', 'first_name'];
    }

    protected function buildQuery(array $args)
    {
        return DB::table('customers')
            ->leftJoin('customer_groups', 'customers.customer_group_id', '=', 'customer_groups.id')
            ->select(
                'customers.id',
                'customers.first_name',
                'customers.last_name',
                'customers.email',
                'customers.phone',
                'customers.gender',
                'customers.date_of_birth',
                'customers.customer_group_id',
                'customer_groups.code as customer_group_code',
                'customer_groups.name as customer_group_name',
                'customer_groups.is_user_defined as customer_group_is_user_defined',
                'customers.channel_id',
                'customers.status',
                'customers.subscribed_to_news_letter',
                'customers.is_verified',
                'customers.is_suspended',
                'customers.created_at',
                'customers.updated_at',
            );
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['name'])) {
            $name = $args['name'];
            $query->where(function ($q) use ($name) {
                $q->where('customers.first_name', 'like', '%'.$name.'%')
                    ->orWhere('customers.last_name', 'like', '%'.$name.'%')
                    ->orWhere(DB::raw('CONCAT('.DB::getTablePrefix()."customers.first_name, ' ', ".DB::getTablePrefix().'customers.last_name)'), 'like', '%'.$name.'%');
            });
        }

        if (! empty($args['email'])) {
            $query->where('customers.email', 'like', '%'.$args['email'].'%');
        }

        if (! empty($args['phone'])) {
            $query->where('customers.phone', 'like', '%'.$args['phone'].'%');
        }

        if (isset($args['customer_group_id']) && $args['customer_group_id'] !== '' && $args['customer_group_id'] !== null) {
            $query->where('customers.customer_group_id', (int) $args['customer_group_id']);
        }

        if (isset($args['status']) && $args['status'] !== '' && $args['status'] !== null) {
            $query->where('customers.status', (int) $args['status']);
        }

        if (isset($args['channel_id']) && $args['channel_id'] !== '' && $args['channel_id'] !== null) {
            $query->where('customers.channel_id', (int) $args['channel_id']);
        }

        if (! empty($args['date_of_birth_from'])) {
            $query->where('customers.date_of_birth', '>=', $args['date_of_birth_from']);
        }
        if (! empty($args['date_of_birth_to'])) {
            $query->where('customers.date_of_birth', '<=', $args['date_of_birth_to']);
        }

        if (! empty($args['created_at_from'])) {
            $query->where('customers.created_at', '>=', $args['created_at_from']);
        }
        if (! empty($args['created_at_to'])) {
            $query->where('customers.created_at', '<=', $args['created_at_to']);
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $map = [
            'id' => 'customers.id',
            'email' => 'customers.email',
            'first_name' => 'customers.first_name',
        ];

        $query->orderBy($map[$column] ?? 'customers.id', $direction);
    }

    protected function mapRow(object $row): object
    {
        return $this->isGraphQL ? $this->toEloquent($row) : $this->toListDto($row);
    }

    protected function toEloquent(object $row): AdminCustomer
    {
        $model = (new AdminCustomer)->forceFill([
            'id' => (int) $row->id,
            'first_name' => $row->first_name,
            'last_name' => $row->last_name,
            'email' => $row->email,
            'phone' => $row->phone,
            'gender' => $row->gender,
            'date_of_birth' => $row->date_of_birth,
            'customer_group_id' => $row->customer_group_id !== null ? (int) $row->customer_group_id : null,
            'channel_id' => $row->channel_id !== null ? (int) $row->channel_id : null,
            'status' => $row->status !== null ? (int) $row->status : null,
            'subscribed_to_news_letter' => (bool) $row->subscribed_to_news_letter,
            'is_verified' => $row->is_verified !== null ? (int) $row->is_verified : null,
            'is_suspended' => $row->is_suspended !== null ? (int) $row->is_suspended : null,
            'created_at' => $row->created_at,
            'updated_at' => $row->updated_at,
        ]);

        if ($row->customer_group_id !== null) {
            $model->setRelation('group', (new AdminCustomerGroupRef)->forceFill([
                'id' => (int) $row->customer_group_id,
                'code' => $row->customer_group_code,
                'name' => $row->customer_group_name,
                'is_user_defined' => $row->customer_group_is_user_defined,
            ]));
        } else {
            $model->setRelation('group', null);
        }

        return $model;
    }

    protected function toListDto(object $row): AdminCustomerListDto
    {
        $dto = new AdminCustomerListDto;
        $dto->id = (int) $row->id;
        $dto->firstName = $row->first_name;
        $dto->lastName = $row->last_name;
        $dto->name = trim((string) $row->first_name.' '.(string) $row->last_name) ?: null;
        $dto->email = $row->email;
        $dto->phone = $row->phone;
        $dto->gender = $row->gender;
        $dto->dateOfBirth = $row->date_of_birth;
        $dto->channelId = $row->channel_id !== null ? (int) $row->channel_id : null;
        $dto->status = $row->status !== null ? (int) $row->status : null;
        $dto->subscribedToNewsLetter = (bool) $row->subscribed_to_news_letter;
        $dto->isVerified = $row->is_verified !== null ? (int) $row->is_verified : null;
        $dto->isSuspended = $row->is_suspended !== null ? (int) $row->is_suspended : null;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;
        $dto->group = $this->mapGroup($row);

        return $dto;
    }

    protected function mapGroup(object $row): ?array
    {
        if ($row->customer_group_id === null) {
            return null;
        }

        return [
            'id' => (int) $row->customer_group_id,
            'code' => $row->customer_group_code,
            'name' => $row->customer_group_name,
        ];
    }
}
