<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\Models\AdminInvoice;
use Webkul\BagistoApi\Admin\State\Concerns\BuildsAdminInvoice;
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
    use BuildsAdminInvoice;
    use TranslatesActionPayload;

    public function __construct(
        protected AdminOrderActionGuard $guard,
        protected InvoiceRepository $invoiceRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminInvoice
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

        return $this->buildAdminInvoice($invoice->fresh(['items', 'items.product', 'order', 'order.addresses']));
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
}
