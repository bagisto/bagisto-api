<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCustomerNote;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Customer\Models\Customer;

class AdminCustomerNoteCollectionProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $customerId = (int) (
            $uriVariables['customerId']
            ?? $context['args']['customerId']
            ?? request()->route('customerId')
            ?? 0
        );

        if ($customerId <= 0 || ! Customer::whereKey($customerId)->exists()) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.customer.not-found'));
        }

        $rows = DB::table('customer_notes')
            ->where('customer_id', $customerId)
            ->orderByDesc('id')
            ->get()
            ->map(fn ($row) => $this->toDto($row))
            ->all();

        $total = count($rows);

        return new Paginator(new LengthAwarePaginator($rows, $total, max($total, 1), 1));
    }

    protected function toDto($row): AdminCustomerNote
    {
        $dto = new AdminCustomerNote;
        $dto->id = (int) $row->id;
        $dto->note = $row->note;
        $dto->customerId = (int) $row->customer_id;
        $dto->customerNotified = (bool) $row->customer_notified;
        $dto->createdAt = $row->created_at;

        return $dto;
    }
}
