<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Dto\AdminInvoiceDetailDto;
use Webkul\BagistoApi\Admin\State\Concerns\MapsOrderActionItems;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Sales\Models\Invoice;

/**
 * GET /api/admin/invoices/{id} + adminInvoice query.
 */
class AdminInvoiceProvider implements ProviderInterface
{
    use MapsOrderActionItems;

    public function __construct(protected AdminOrderActionGuard $guard) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AdminInvoiceDetailDto
    {
        $this->guard->resolveAdmin();

        $id = (int) basename((string) ($uriVariables['id'] ?? $context['args']['id'] ?? 0));

        if ($id <= 0) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.order.actions.invoice.not-found'));
        }

        $invoice = Invoice::with(['items', 'order'])->find($id);

        if (! $invoice) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.order.actions.invoice.not-found'));
        }

        $currency = $invoice->order_currency_code ?? $invoice->order?->order_currency_code;

        $dto = new AdminInvoiceDetailDto;
        $dto->id = (int) $invoice->id;
        $dto->incrementId = $invoice->increment_id;
        $dto->orderId = (int) $invoice->order_id;
        $dto->state = $invoice->state;
        $dto->emailSent = (bool) $invoice->email_sent;
        $dto->totalQty = (int) $invoice->total_qty;
        $dto->orderCurrencyCode = $currency;
        $dto->subTotal = (float) $invoice->sub_total;
        $dto->formattedSubTotal = core()->formatPrice((float) $invoice->sub_total, $currency);
        $dto->grandTotal = (float) $invoice->grand_total;
        $dto->formattedGrandTotal = core()->formatPrice((float) $invoice->grand_total, $currency);
        $dto->taxAmount = (float) $invoice->tax_amount;
        $dto->formattedTaxAmount = core()->formatPrice((float) $invoice->tax_amount, $currency);
        $dto->discountAmount = (float) $invoice->discount_amount;
        $dto->formattedDiscountAmount = core()->formatPrice((float) $invoice->discount_amount, $currency);
        $dto->shippingAmount = (float) $invoice->shipping_amount;
        $dto->formattedShippingAmount = core()->formatPrice((float) $invoice->shipping_amount, $currency);
        $dto->transactionId = $invoice->transaction_id;
        $dto->createdAt = $invoice->created_at ? (string) $invoice->created_at : null;
        $dto->updatedAt = $invoice->updated_at ? (string) $invoice->updated_at : null;

        $dto->items = $invoice->items
            ? $invoice->items->map(fn ($row) => $this->mapItem($row, $currency))->all()
            : [];

        return $dto;
    }
}
