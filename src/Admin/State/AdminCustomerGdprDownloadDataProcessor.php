<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Carbon;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCustomerGdprDownloadData;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerNote;
use Webkul\Customer\Models\Wishlist;
use Webkul\Product\Models\ProductReview;
use Webkul\Sales\Models\Order;

/**
 * POST /api/admin/customers/{customerId}/gdpr-download-data +
 * createAdminCustomerGdprDownloadData mutation.
 *
 * Returns a structured JSON dump of every table that references the
 * customer's id. Mirrors the storefront GDPR PDF endpoint
 * (Shop\Customer\GDPRController::pdfView) but extended — the storefront
 * version only exports orders + addresses; this exports everything an admin
 * would expect for a GDPR data-portability request.
 *
 * Permission: customers.gdpr_requests.view (read-only inspection).
 */
class AdminCustomerGdprDownloadDataProcessor implements ProcessorInterface
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

        $customer = Customer::with(['addresses', 'group'])->find($customerId);
        if (! $customer) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.customer.not-found'));
        }

        $orders = $this->collectOrders($customer);
        $addresses = $customer->addresses->map(fn ($a) => $a->toArray())->all();
        $reviews = $this->safeQuery(fn () => ProductReview::where('customer_id', $customerId)->get()->map->toArray()->all());
        $wishlist = $this->safeQuery(fn () => Wishlist::where('customer_id', $customerId)->get()->map->toArray()->all());
        $notes = $this->safeQuery(fn () => CustomerNote::where('customer_id', $customerId)->get()->map->toArray()->all());

        $customerData = $customer->toArray();
        unset($customerData['password'], $customerData['remember_token']);

        $dto = new AdminCustomerGdprDownloadData;
        $dto->id = $customerId;
        $dto->customerId = $customerId;
        $dto->customerEmail = $customer->email;
        $dto->generatedAt = Carbon::now()->toIso8601String();
        $dto->data = [
            'customer' => $customerData,
            'addresses' => $addresses,
            'orders' => $orders,
            'reviews' => $reviews,
            'wishlist' => $wishlist,
            'notes' => $notes,
        ];

        return $dto;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    protected function collectOrders(Customer $customer): array
    {
        try {
            return Order::with(['items', 'addresses', 'payment'])
                ->where('customer_id', $customer->id)
                ->get()
                ->map(fn ($o) => $o->toArray())
                ->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    protected function safeQuery(callable $fn): array
    {
        try {
            $result = $fn();

            return is_array($result) ? $result : [];
        } catch (\Throwable $e) {
            return [];
        }
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
        if (! in_array('customers.gdpr_requests.view', $perms, true) && ! in_array('*', $perms, true)) {
            throw new AuthorizationException(__('bagistoapi::app.admin.customer.gdpr.no-permission'));
        }
    }
}
