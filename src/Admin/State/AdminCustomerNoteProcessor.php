<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCustomerNote;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Repositories\CustomerNoteRepository;

class AdminCustomerNoteProcessor implements ProcessorInterface
{
    public function __construct(protected CustomerNoteRepository $noteRepository) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }
        $this->assertPermission($admin);

        $isGraphQL = $operation instanceof \ApiPlatform\Metadata\GraphQl\Mutation;
        $customerId = (int) ($uriVariables['customerId']
            ?? request()->route('customerId')
            ?? ($context['args']['input']['customerId'] ?? null)
            ?? ($context['args']['input']['customer_id'] ?? null)
            ?? 0);

        $input = $isGraphQL
            ? ($context['args']['input'] ?? [])
            : request()->all();

        if ($customerId <= 0) {
            $customerId = (int) ($input['customerId'] ?? $input['customer_id'] ?? 0);
        }

        if ($customerId <= 0 || ! Customer::find($customerId)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.customer.not-found'));
        }

        $note = trim((string) ($input['note'] ?? ''));
        if ($note === '') {
            throw new InvalidInputException(__('bagistoapi::app.admin.customer.note.empty'), 422);
        }

        $customerNotified = (int) (bool) ($input['customerNotified'] ?? $input['customer_notified'] ?? 0);

        Event::dispatch('customer.note.create.before', $customerId);

        $row = $this->noteRepository->create([
            'customer_id'       => $customerId,
            'note'              => $note,
            'customer_notified' => $customerNotified,
        ]);

        Event::dispatch('customer.note.create.after', $row);

        $dto = new AdminCustomerNote;
        $dto->id = (int) $row->id;
        $dto->customerId = (int) $row->customer_id;
        $dto->note = $row->note;
        $dto->customerNotified = (bool) $row->customer_notified;
        $dto->createdAt = $row->created_at?->toIso8601String();

        return $dto;
    }

    protected function assertPermission(object $admin): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.customer.no-permission'));
        }
        if (($role->permission_type ?? null) === 'all') {
            return;
        }
        $perms = $role->permissions ?? [];
        if (is_string($perms)) {
            $perms = array_map('trim', explode(',', $perms));
        }
        if (! is_array($perms)) {
            $perms = [];
        }
        if (! in_array('customers.customers.edit', $perms, true) && ! in_array('*', $perms, true)) {
            throw new AuthorizationException(__('bagistoapi::app.admin.customer.no-permission'));
        }
    }
}
