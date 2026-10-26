<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\Models\AdminCustomerApiSessionRevoke;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Admin\State\Concerns\ReadsAdminAuthInput;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Services\CustomerAuth\CustomerApiAuthService;
use Webkul\Customer\Repositories\CustomerRepository;

class AdminCustomerApiSessionRevokeProcessor implements ProcessorInterface
{
    use ChecksAdminPermission;
    use ReadsAdminAuthInput;

    /**
     * Create a new customer session revoke processor.
     */
    public function __construct(
        protected CustomerRepository $customerRepository,
        protected CustomerApiAuthService $authService,
    ) {}

    /**
     * End every live storefront session of a customer.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminCustomerApiSessionRevoke
    {
        $admin = $this->authorizedAdmin('customers.api_sessions.delete', 'bagistoapi::app.admin.customer-api-sessions.errors.no-permission');

        $customerId = (int) ($uriVariables['customerId']
            ?? request()->route('customerId')
            ?? $this->authInput($operation, $context)['customer_id']
            ?? 0);

        $customer = $customerId > 0 ? $this->customerRepository->find($customerId) : null;

        if (! $customer) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.customer.not-found'));
        }

        $output = new AdminCustomerApiSessionRevoke;

        $output->customer_id = (int) $customer->id;
        $output->revoked = $this->authService->revokeAllAsAdmin($admin, $customer, request());
        $output->message = __('bagistoapi::app.admin.auth.sessions.revoked-for-admin', ['count' => $output->revoked]);

        return $output;
    }
}
