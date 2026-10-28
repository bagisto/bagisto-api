<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\Dto\AdminInvoiceRestDto;
use Webkul\BagistoApi\Admin\Models\AdminInvoice;
use Webkul\BagistoApi\Admin\State\Concerns\BuildsAdminInvoice;
use Webkul\BagistoApi\Admin\State\Concerns\TranslatesActionPayload;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Repositories\InvoiceRepository;

class AdminInvoiceCreateProcessor implements ProcessorInterface
{
    use BuildsAdminInvoice;
    use TranslatesActionPayload;

    /**
     * Create a new instance.
     */
    public function __construct(
        protected AdminOrderActionGuard $guard,
        protected InvoiceRepository $invoiceRepository,
    ) {}

    /**
     * Process.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminInvoice|AdminInvoiceRestDto
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

        if ($this->wantsTransaction($data, $context)) {
            request()->merge(['can_create_transaction' => '1']);
        }

        try {
            $invoice = $this->invoiceRepository->create([
                'order_id' => $order->id,
                'invoice' => ['items' => $flat],
            ]);
        } catch (\Throwable $exception) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.order.actions.invoice.failed').' '.$exception->getMessage(),
                422,
                $exception,
            );
        }

        if (! empty($context['graphql_operation_name'])) {
            return $this->loadInvoiceForGraphQL((int) $invoice->id);
        }

        return $this->buildInvoiceRestDto($invoice->fresh(['items', 'items.product', 'order', 'order.addresses']));
    }

    /**
     * Extract items.
     */
    protected function extractItems(mixed $data, array $context): array
    {
        if (
            is_object($data)
            && property_exists($data, 'items')
            && $data->items
        ) {
            return array_map(function ($index) {
                return is_object($index) ? get_object_vars($index) : (array) $index;
            }, (array) $data->items);
        }

        return (array) (
            $context['args']['input']['items']
            ?? request()->input('items')
            ?? []
        );
    }

    /**
     * Wants transaction.
     */
    protected function wantsTransaction(mixed $data, array $context): bool
    {
        $value = null;

        if (
            is_object($data)
            && property_exists($data, 'canCreateTransaction')
        ) {
            $value = $data->canCreateTransaction;
        }

        $value ??= $context['args']['input']['canCreateTransaction']
            ?? request()->input('canCreateTransaction')
            ?? request()->input('can_create_transaction');

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Validate qty.
     */
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
                    'sku' => $item->sku,
                    'requested' => $qty,
                    'available' => (int) $item->qty_to_invoice,
                ]), 422);
            }
        }
    }
}
