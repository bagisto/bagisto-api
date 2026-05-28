<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Models\AdminCustomer;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\Customer\Models\Customer;

class AdminCustomerItemProvider extends AbstractAdminItemProvider
{
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.customer.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return Customer::with(['group'])->find($id);
    }

    public function mapToDto(object $entity): object
    {
        return $this->doMap($entity);
    }

    protected function doMap(Customer $c): AdminCustomer
    {
        $dto = new AdminCustomer;
        $dto->id = (int) $c->id;
        $dto->firstName = $c->first_name;
        $dto->lastName = $c->last_name;
        $dto->name = trim((string) $c->first_name.' '.(string) $c->last_name);
        $dto->email = $c->email;
        $dto->phone = $c->phone;
        $dto->gender = $c->gender;
        $dto->dateOfBirth = $c->date_of_birth?->format('Y-m-d') ?? (is_string($c->getOriginal('date_of_birth')) ? $c->getOriginal('date_of_birth') : null);
        $dto->customerGroupId = $c->customer_group_id !== null ? (int) $c->customer_group_id : null;
        $dto->customerGroupName = $c->group?->name;
        $dto->channelId = $c->channel_id !== null ? (int) $c->channel_id : null;
        $dto->status = $c->status !== null ? (int) $c->status : null;
        $dto->subscribedToNewsLetter = (bool) $c->subscribed_to_news_letter;
        $dto->isVerified = $c->is_verified !== null ? (int) $c->is_verified : null;
        $dto->isSuspended = $c->is_suspended !== null ? (int) $c->is_suspended : null;

        $dto->totalAddresses = (int) $c->addresses()->count();
        $dto->totalOrders = (int) $c->orders()->count();
        $dto->totalAmountSpent = (float) $c->orders()->sum('base_grand_total_invoiced');

        $dto->createdAt = $c->created_at?->toIso8601String();
        $dto->updatedAt = $c->updated_at?->toIso8601String();

        return $dto;
    }
}
