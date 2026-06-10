<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Admin\Dto\AdminInvoiceSendDuplicateInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminInvoiceSendDuplicate;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Sales\Models\Invoice;

class AdminInvoiceSendDuplicateProcessor implements ProcessorInterface
{
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminInvoiceSendDuplicate
    {
        $admin = AdminAuthHelper::resolveAdmin();

        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $this->assertPermission($admin, 'sales.invoices.view');

        $invoiceId = $this->resolveInvoiceId($data, $uriVariables, $context);

        if ($invoiceId <= 0) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.order.actions.invoice.not-found'));
        }

        $invoice = Invoice::with('order')->find($invoiceId);

        if (! $invoice) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.order.actions.invoice.not-found'));
        }

        $email = $this->resolveEmail($data, $context) ?: $invoice->order?->customer_email;

        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.invoice.email-invalid'), 422);
        }

        $invoice->email = $email;

        if ($invoice->order) {
            $invoice->order->customer_email = $email;
        }

        Event::dispatch('sales.invoice.send_duplicate_email', $invoice);

        $result = new AdminInvoiceSendDuplicate;
        $result->id = (int) $invoice->id;
        $result->email = $email;
        $result->success = true;
        $result->message = __('bagistoapi::app.admin.order.actions.invoice.duplicate-sent', ['email' => $email]);

        return $result;
    }

    protected function resolveInvoiceId(mixed $data, array $uriVariables, array $context): int
    {
        if (isset($uriVariables['id'])) {
            return (int) basename((string) $uriVariables['id']);
        }

        if ($data instanceof AdminInvoiceSendDuplicateInput && $data->invoiceId !== null) {
            return (int) $data->invoiceId;
        }

        $fromArgs = $context['args']['input']['invoiceId'] ?? $context['args']['invoiceId'] ?? null;

        if ($fromArgs !== null) {
            return (int) $fromArgs;
        }

        $iri = $context['args']['input']['id'] ?? $context['args']['id'] ?? null;

        if ($iri !== null) {
            return (int) basename((string) $iri);
        }

        return (int) (request()->route('id') ?? 0);
    }

    protected function resolveEmail(mixed $data, array $context): ?string
    {
        if ($data instanceof AdminInvoiceSendDuplicateInput && $data->email) {
            return trim($data->email);
        }

        $fromArgs = $context['args']['input']['email'] ?? $context['args']['email'] ?? null;

        if ($fromArgs) {
            return trim((string) $fromArgs);
        }

        $fromBody = request()->input('email');

        return $fromBody ? trim((string) $fromBody) : null;
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
            $perms = array_filter(array_map('trim', explode(',', $perms)));
        }

        if (! in_array($permission, (array) $perms, true)) {
            throw new AuthorizationException(__('bagistoapi::app.admin.order.actions.invoice.no-permission'));
        }
    }
}
