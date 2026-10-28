<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use Illuminate\Support\Carbon;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerDetailDto;
use Webkul\BagistoApi\Admin\Models\AdminCustomer;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;

class AdminCustomerItemProvider extends AbstractAdminItemProvider
{
    /**
     * The context.
     */
    protected array $context = [];

    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        $this->context = $context;

        return parent::provide($operation, $uriVariables, $context);
    }

    /**
     * Map to dto.
     */
    public function mapToDto(object $entity): object
    {
        if (! empty($this->context['graphql_operation_name'])) {
            return $entity;
        }

        return $this->toDetailDto($entity);
    }

    /**
     * Map for processor.
     */
    public function mapForProcessor(AdminCustomer $customer, bool $isGraphQL): object
    {
        return $isGraphQL ? $customer : $this->toDetailDto($customer);
    }

    /**
     * Get not found lang key.
     */
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.customer.not-found';
    }

    /**
     * Find entity.
     */
    protected function findEntity(int $id): ?object
    {
        return AdminCustomer::with('group')->find($id);
    }

    /**
     * To detail dto.
     */
    protected function toDetailDto(AdminCustomer $customer): AdminCustomerDetailDto
    {
        $dto = new AdminCustomerDetailDto;
        $dto->id = (int) $customer->id;
        $dto->firstName = $customer->first_name;
        $dto->lastName = $customer->last_name;
        $dto->name = trim((string) $customer->first_name.' '.(string) $customer->last_name) ?: null;
        $dto->email = $customer->email;
        $dto->phone = $customer->phone;
        $dto->gender = $customer->gender;
        $dto->dateOfBirth = $customer->date_of_birth ? Carbon::parse($customer->date_of_birth)->format('Y-m-d') : null;
        $dto->channelId = $customer->channel_id !== null ? (int) $customer->channel_id : null;
        $dto->status = $customer->status !== null ? (int) $customer->status : null;
        $dto->subscribedToNewsLetter = (bool) $customer->subscribed_to_news_letter;
        $dto->isVerified = $customer->is_verified !== null ? (int) $customer->is_verified : null;
        $dto->isSuspended = $customer->is_suspended !== null ? (int) $customer->is_suspended : null;
        $dto->totalAddresses = $this->totalAddresses($customer);
        $dto->totalOrders = $this->totalOrders($customer);
        $dto->totalAmountSpent = $this->totalAmountSpent($customer);
        $dto->createdAt = $customer->created_at?->toIso8601String();
        $dto->updatedAt = $customer->updated_at?->toIso8601String();
        $dto->group = $this->mapGroup($customer);

        return $dto;
    }

    /**
     * Map group.
     */
    protected function mapGroup(AdminCustomer $customer): ?array
    {
        if ($customer->customer_group_id === null) {
            return null;
        }

        $group = $customer->group;

        if (! $group) {
            return null;
        }

        return [
            'id' => (int) $group->id,
            'code' => $group->code,
            'name' => $group->name,
        ];
    }

    /**
     * Total addresses.
     */
    protected function totalAddresses(AdminCustomer $customer): int
    {
        return (int) \DB::table('addresses')
            ->where('customer_id', $customer->id)
            ->where('address_type', 'customer')
            ->count();
    }

    /**
     * Total orders.
     */
    protected function totalOrders(AdminCustomer $customer): int
    {
        return (int) \DB::table('orders')->where('customer_id', $customer->id)->count();
    }

    /**
     * Total amount spent.
     */
    protected function totalAmountSpent(AdminCustomer $customer): float
    {
        return (float) \DB::table('orders')
            ->where('customer_id', $customer->id)
            ->sum('base_grand_total_invoiced');
    }
}
