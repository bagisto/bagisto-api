<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Dto\AdminInvoiceRestDto;
use Webkul\BagistoApi\Admin\Models\AdminInvoice;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Admin\State\Concerns\BuildsAdminInvoice;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Sales\Models\Invoice;

class AdminInvoiceProvider implements ProviderInterface
{
    use BuildsAdminInvoice;

    /**
     * Provide one invoice: the Eloquent model for GraphQL, the flat REST DTO otherwise.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AdminInvoice|AdminInvoiceRestDto
    {
        AdminReadPermissions::authorize($operation, AdminInvoice::class, 'sales.invoices.view');

        $id = (int) basename((string) ($uriVariables['id'] ?? $context['args']['id'] ?? 0));

        if ($id <= 0) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.order.actions.invoice.not-found'));
        }

        if (! empty($context['graphql_operation_name'])) {
            $model = $this->loadInvoiceForGraphQL($id);

            if (! $model) {
                throw new ResourceNotFoundException(__('bagistoapi::app.admin.order.actions.invoice.not-found'));
            }

            return $model;
        }

        $invoice = Invoice::with(['items', 'items.product', 'order', 'order.addresses'])->find($id);

        if (! $invoice) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.order.actions.invoice.not-found'));
        }

        return $this->buildInvoiceRestDto($invoice);
    }
}
