<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\Dto\AdminInvoiceMassUpdateStatusInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminInvoiceMassUpdateStatus;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Sales\Models\Invoice;

class AdminInvoiceMassUpdateStatusProcessor implements ProcessorInterface
{
    protected const ALLOWED_STATUSES = [
        Invoice::STATUS_PENDING,
        Invoice::STATUS_PAID,
        Invoice::STATUS_OVERDUE,
    ];

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $this->assertPermission($admin, 'sales.invoices.view');

        $indices = $this->resolveArray($data, $context, 'indices');
        $value = $this->resolveScalar($data, $context, 'value');

        if (empty($indices)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.invoice.mass-status-indices-required'), 422);
        }

        if (! is_string($value) || ! in_array($value, self::ALLOWED_STATUSES, true)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.invoice.mass-status-value-invalid'), 422);
        }

        $ids = array_map('intval', $indices);
        $updated = [];

        foreach (Invoice::whereIn('id', $ids)->get() as $invoice) {
            $invoice->state = $value;
            $invoice->save();

            $updated[] = (int) $invoice->id;
        }

        $result = new AdminInvoiceMassUpdateStatus;
        $result->id = 1;
        $result->updated = $updated;
        $result->message = __('bagistoapi::app.admin.order.actions.invoice.mass-status-success');

        return $result;
    }

    protected function assertPermission(object $admin, string $permission): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.order.actions.invoice.no-permission'));
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

        if (! in_array($permission, $perms, true) && ! in_array('*', $perms, true)) {
            throw new AuthorizationException(__('bagistoapi::app.admin.order.actions.invoice.no-permission'));
        }
    }

    protected function resolveArray(mixed $data, array $context, string $key): array
    {
        if ($data instanceof AdminInvoiceMassUpdateStatusInput && is_array($data->{$key} ?? null)) {
            return $data->{$key};
        }

        $fromArgs = $context['args']['input'][$key] ?? $context['args'][$key] ?? null;
        if (is_array($fromArgs)) {
            return $fromArgs;
        }

        $fromBody = request()->input($key);
        if (is_array($fromBody)) {
            return $fromBody;
        }

        return [];
    }

    protected function resolveScalar(mixed $data, array $context, string $key): mixed
    {
        if ($data instanceof AdminInvoiceMassUpdateStatusInput && $data->{$key} !== null) {
            return $data->{$key};
        }

        return $context['args']['input'][$key]
            ?? $context['args'][$key]
            ?? request()->input($key);
    }
}
