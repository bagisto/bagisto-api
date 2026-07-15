<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCustomerImpersonate;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Customer\Models\Customer;

/**
 * Issues a Sanctum customer token bound to the target customer.
 *
 * Audit:
 *   - Token name carries the admin id: "admin-impersonate:{adminId}"
 *   - Token ability includes "impersonated-by-admin:{adminId}"
 *   - expires_at = now() + 1 hour
 *
 * Customers use Webkul\Customer\Models\Customer (HasApiTokens trait) — the same
 * Sanctum personal_access_tokens table the storefront API uses.
 */
class AdminCustomerImpersonateProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }
        $this->assertPermission($admin);

        $customerId = (int) ($uriVariables['customerId']
            ?? request()->route('customerId')
            ?? ($context['args']['input']['customerId'] ?? null)
            ?? ($context['args']['input']['customer_id'] ?? null)
            ?? 0);

        if ($customerId <= 0) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.customer.not-found'));
        }

        $customer = Customer::find($customerId);
        if (! $customer) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.customer.not-found'));
        }

        $expiresAt = Carbon::now()->addHour();
        $tokenName = 'admin-impersonate:'.$admin->id;
        $abilities = ['*', 'impersonated-by-admin:'.$admin->id];

        $personal = $customer->createToken($tokenName, $abilities, $expiresAt);

        Log::info('admin.customer.impersonate', [
            'admin_id' => $admin->id,
            'customer_id' => $customer->id,
            'token_id' => $personal->accessToken->id ?? null,
            'expires_at' => $expiresAt->toIso8601String(),
        ]);

        $dto = new AdminCustomerImpersonate;
        $dto->id = 1;
        $dto->token = $personal->plainTextToken;
        $dto->customerId = (int) $customer->id;
        $dto->customerEmail = $customer->email;
        $dto->customerName = trim((string) $customer->first_name.' '.(string) $customer->last_name);
        $dto->impersonatedByAdminId = (int) $admin->id;
        $dto->expiresAt = $expiresAt->toIso8601String();

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
