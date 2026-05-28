<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\Dto\AdminInvoiceDetailDto;
use Webkul\BagistoApi\Admin\State\Concerns\MapsOrderActionItems;
use Webkul\BagistoApi\Admin\State\Concerns\TranslatesActionPayload;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Repositories\InvoiceRepository;

/**
 * POST /api/admin/orders/{orderId}/invoices + createAdminInvoice mutation.
 *
 * Eligibility split via AdminOrderActionGuard. Item qty validation mirrors
 * `InvoiceRepository::isValidQuantity()` (rejects qty > qty_to_invoice with a
 * sku-carrying message).
 */
class AdminInvoiceCreateProcessor implements ProcessorInterface
{
    use MapsOrderActionItems;
    use TranslatesActionPayload;

    public function __construct(
        protected AdminOrderActionGuard $guard,
        protected InvoiceRepository $invoiceRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminInvoiceDetailDto
    {
        $admin = $this->guard->resolveAdmin();
        $order = $this->guard->resolveOrder($uriVariables, $context, 'orderId');

        $this->guard->assertCanInvoice($order, $admin);

        $items = $this->extractItems($data, $context);
        $flat = $this->flatItemsMap($items);

        if (empty($flat)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.invoice.items-required'), 422);
        }

        $this->validateQty($order, $flat);

        try {
            $invoice = $this->invoiceRepository->create([
                'order_id' => $order->id,
                'invoice'  => ['items' => $flat],
            ]);
        } catch (\Throwable $e) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.order.actions.invoice.failed').' '.$e->getMessage(),
                422,
                $e,
            );
        }

        return $this->toDto($invoice->fresh(['items']));
    }

    protected function extractItems(mixed $data, array $context): array
    {
        if (is_object($data) && property_exists($data, 'items') && $data->items) {
            return array_map(function ($i) {
                return is_object($i) ? get_object_vars($i) : (array) $i;
            }, (array) $data->items);
        }

        return (array) (
            $context['args']['input']['items']
            ?? request()->input('items')
            ?? []
        );
    }

    protected function validateQty(Order $order, array $flat): void
    {
        $byId = $order->items->keyBy('id');
        foreach ($flat as $itemId => $qty) {
            $item = $byId->get($itemId);
            if (! $item) {
                throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.invoice.items-required'), 422);
            }
            if ($qty > (int) $item->qty_to_invoice) {
                throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.invoice.qty-exceeds', [
                    'sku'       => $item->sku,
                    'requested' => $qty,
                    'available' => (int) $item->qty_to_invoice,
                ]), 422);
            }
        }
    }

    protected function toDto($invoice): AdminInvoiceDetailDto
    {
        $currency = $invoice->order_currency_code ?? $invoice->order->order_currency_code;

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
