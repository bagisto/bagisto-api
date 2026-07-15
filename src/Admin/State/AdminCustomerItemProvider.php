<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use Illuminate\Support\Carbon;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerDetailDto;
use Webkul\BagistoApi\Admin\Models\AdminCustomer;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;

class AdminCustomerItemProvider extends AbstractAdminItemProvider
{
    protected array $context = [];

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        $this->context = $context;

        return parent::provide($operation, $uriVariables, $context);
    }

    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.customer.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return AdminCustomer::with('group')->find($id);
    }

    public function mapToDto(object $entity): object
    {
        if (! empty($this->context['graphql_operation_name'])) {
            return $entity;
        }

        return $this->toDetailDto($entity);
    }

    public function mapForProcessor(AdminCustomer $c, bool $isGraphQL): object
    {
        return $isGraphQL ? $c : $this->toDetailDto($c);
    }

    protected function toDetailDto(AdminCustomer $c): AdminCustomerDetailDto
    {
        $dto = new AdminCustomerDetailDto;
        $dto->id = (int) $c->id;
        $dto->firstName = $c->first_name;
        $dto->lastName = $c->last_name;
        $dto->name = trim((string) $c->first_name.' '.(string) $c->last_name) ?: null;
        $dto->email = $c->email;
        $dto->phone = $c->phone;
        $dto->gender = $c->gender;
        $dto->dateOfBirth = $c->date_of_birth ? Carbon::parse($c->date_of_birth)->format('Y-m-d') : null;
        $dto->channelId = $c->channel_id !== null ? (int) $c->channel_id : null;
        $dto->status = $c->status !== null ? (int) $c->status : null;
        $dto->subscribedToNewsLetter = (bool) $c->subscribed_to_news_letter;
        $dto->isVerified = $c->is_verified !== null ? (int) $c->is_verified : null;
        $dto->isSuspended = $c->is_suspended !== null ? (int) $c->is_suspended : null;
        $dto->totalAddresses = $this->totalAddresses($c);
        $dto->totalOrders = $this->totalOrders($c);
        $dto->totalAmountSpent = $this->totalAmountSpent($c);
        $dto->createdAt = $c->created_at?->toIso8601String();
        $dto->updatedAt = $c->updated_at?->toIso8601String();
        $dto->group = $this->mapGroup($c);

        return $dto;
    }

    protected function mapGroup(AdminCustomer $c): ?array
    {
        if ($c->customer_group_id === null) {
            return null;
        }

        $group = $c->group;

        if (! $group) {
            return null;
        }

        return [
            'id' => (int) $group->id,
            'code' => $group->code,
            'name' => $group->name,
        ];
    }

    protected function totalAddresses(AdminCustomer $c): int
    {
        return (int) \DB::table('addresses')
            ->where('customer_id', $c->id)
            ->where('address_type', 'customer')
            ->count();
    }

    protected function totalOrders(AdminCustomer $c): int
    {
        return (int) \DB::table('orders')->where('customer_id', $c->id)->count();
    }

    protected function totalAmountSpent(AdminCustomer $c): float
    {
        return (float) \DB::table('orders')
            ->where('customer_id', $c->id)
            ->sum('base_grand_total_invoiced');
    }
}
