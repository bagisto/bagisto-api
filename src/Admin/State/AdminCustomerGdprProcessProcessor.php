<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCustomerGdprProcess;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\GDPR\Models\GDPRDataRequest;
use Webkul\GDPR\Repositories\GDPRDataRequestRepository;

/**
 * POST /api/admin/customers/gdpr-requests/{id}/process +
 * createAdminCustomerGdprProcess mutation.
 *
 * Marks the request approved and, for type=delete, cascades the customer
 * delete via CustomerRepository::delete() (which fires the customer.delete.*
 * events). For type=update the admin is expected to apply the requested
 * profile edits manually through the regular customer-update endpoint —
 * the GDPR table only carries a free-form `message`, not a structured patch.
 *
 * Refuses to re-process an already-approved or revoked request.
 */
class AdminCustomerGdprProcessProcessor implements ProcessorInterface
{
    public function __construct(
        protected GDPRDataRequestRepository $gdprRepository,
        protected CustomerRepository $customerRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }
        $this->assertPermission($admin);

        $rawId = $uriVariables['id']
            ?? request()->route('id')
            ?? ($context['args']['input']['requestId'] ?? null)
            ?? ($context['args']['input']['request_id'] ?? null)
            ?? ($context['args']['input']['id'] ?? null)
            ?? 0;

        $id = (int) basename((string) $rawId);

        if ($id <= 0) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.customer.gdpr.not-found'));
        }

        $request = GDPRDataRequest::find($id);
        if (! $request) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.customer.gdpr.not-found'));
        }

        if (in_array($request->status, ['approved', 'revoked'], true)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.customer.gdpr.already-processed'), 422);
        }

        $isGraphQL = $operation instanceof Mutation;
        $input = $isGraphQL
            ? ($context['args']['input'] ?? [])
            : request()->all();

        $message = isset($input['message']) ? (string) $input['message'] : null;

        $customerId = $request->customer_id !== null ? (int) $request->customer_id : null;
        $type = (string) $request->type;
        $customerDeleted = false;

        $patch = ['status' => 'approved'];
        if ($message !== null && $message !== '') {
            $patch['message'] = $message;
        }

        Event::dispatch('customer.gdpr-request.update.before');

        $this->gdprRepository->update($patch, $id);

        if ($type === 'delete' && $customerId) {
            $customer = Customer::find($customerId);
            if ($customer) {
                Event::dispatch('customer.delete.before', $customer);
                $this->customerRepository->delete($customerId);
                Event::dispatch('customer.delete.after', $customer);
                $customerDeleted = true;
            }
        }

        $fresh = GDPRDataRequest::find($id);

        Event::dispatch('customer.account.gdpr-request.update.after', $fresh);

        $dto = new AdminCustomerGdprProcess;
        $dto->id = (int) $id;
        $dto->requestId = (int) $id;
        $dto->customerId = $customerId;
        $dto->type = $type;
        $dto->status = $fresh?->status ?? 'approved';
        $dto->customerDeleted = $customerDeleted;
        $dto->processedAt = Carbon::now()->toIso8601String();
        $dto->message = $customerDeleted
            ? __('bagistoapi::app.admin.customer.gdpr.process.deleted')
            : __('bagistoapi::app.admin.customer.gdpr.process.approved');

        return $dto;
    }

    protected function assertPermission(object $admin): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.customer.gdpr.no-permission'));
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
        if (! in_array('customers.gdpr_requests.edit', $perms, true) && ! in_array('*', $perms, true)) {
            throw new AuthorizationException(__('bagistoapi::app.admin.customer.gdpr.no-permission'));
        }
    }
}
