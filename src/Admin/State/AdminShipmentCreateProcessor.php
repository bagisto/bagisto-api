<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\Models\AdminShipment;
use Webkul\BagistoApi\Admin\State\Concerns\BuildsAdminShipment;
use Webkul\BagistoApi\Admin\State\Concerns\TranslatesActionPayload;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Repositories\ShipmentRepository;

class AdminShipmentCreateProcessor implements ProcessorInterface
{
    use BuildsAdminShipment;
    use TranslatesActionPayload;

    public function __construct(
        protected AdminOrderActionGuard $guard,
        protected ShipmentRepository $shipmentRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminShipment
    {
        $admin = $this->guard->resolveAdmin();
        $order = $this->guard->resolveOrder($uriVariables, $context, 'orderId');

        $this->guard->assertCanShip($order, $admin);

        $source = $this->extractSource($data, $context);
        if ($source === null || $source <= 0) {
            throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.shipment.source-required'), 422);
        }

        $items = $this->extractItems($data, $context);
        $nested = $this->nestedShipmentItemsMap($items, $source);

        if (empty($nested)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.shipment.items-required'), 422);
        }

        $this->validateInventory($order, $nested);

        $carrierTitle = $this->extractField($data, $context, 'carrierTitle', 'carrier_title');
        $trackNumber = $this->extractField($data, $context, 'trackNumber', 'track_number');

        $payload = [
            'order_id' => $order->id,
            'shipment' => [
                'source' => $source,
                'items' => $nested,
                'carrier_title' => $carrierTitle,
                'track_number' => $trackNumber,
            ],
        ];

        try {
            $shipment = $this->shipmentRepository->create($payload);
        } catch (\Throwable $e) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.order.actions.shipment.failed').' '.$e->getMessage(),
                422,
                $e,
            );
        }

        return $this->buildAdminShipment($shipment->fresh(['items', 'items.order_item', 'order', 'order.addresses', 'order.payment']));
    }

    protected function extractSource(mixed $data, array $context): ?int
    {
        $val = null;
        if (is_object($data) && property_exists($data, 'source') && $data->source !== null) {
            $val = $data->source;
        } else {
            $val = $context['args']['input']['source']
                ?? request()->input('source')
                ?? null;
        }

        return $val !== null ? (int) $val : null;
    }

    protected function extractItems(mixed $data, array $context): array
    {
        if (is_object($data) && property_exists($data, 'items') && $data->items) {
            return array_map(function ($i) {
                return is_object($i) ? get_object_vars($i) : (array) $i;
            }, (array) $data->items);
        }

        return (array) ($context['args']['input']['items']
            ?? request()->input('items')
            ?? []);
    }

    protected function extractField(mixed $data, array $context, string $camel, string $snake): ?string
    {
        if (is_object($data) && property_exists($data, $camel) && $data->{$camel} !== null) {
            return (string) $data->{$camel};
        }

        $v = $context['args']['input'][$camel]
            ?? request()->input($camel)
            ?? request()->input($snake)
            ?? null;

        return $v !== null ? (string) $v : null;
    }

    protected function validateInventory(Order $order, array $nested): void
    {
        $byId = $order->items->keyBy('id');

        foreach ($nested as $itemId => $sourceMap) {
            $item = $byId->get($itemId);
            if (! $item) {
                throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.shipment.items-required'), 422);
            }

            $totalForItem = array_sum($sourceMap);

            if ($totalForItem > (int) $item->qty_to_ship) {
                throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.shipment.qty-exceeds', [
                    'sku' => $item->sku,
                    'requested' => $totalForItem,
                    'available' => (int) $item->qty_to_ship,
                ]), 422);
            }

            $isComposite = $this->isComposite($item);

            foreach ($sourceMap as $sourceId => $qty) {
                $qty = (int) $qty;
                if ($qty <= 0) {
                    continue;
                }

                if ($isComposite) {
                    // Mirror core: validate each child's proportional qty against
                    // its own qty_to_ship AND the child product's inventory at the
                    // chosen source. Bundle/configurable/grouped parents have no
                    // stock of their own, so checking the parent (as the old code
                    // did) silently passed any quantity.
                    $this->assertCompositeChildrenShippable($item, (int) $sourceId, $qty);
                } else {
                    $this->assertStockAvailable($item, (int) $sourceId, $qty);
                }
            }
        }
    }

    protected function isComposite($item): bool
    {
        try {
            return (bool) $item->getTypeInstance()->isComposite();
        } catch (\Throwable) {
            return false;
        }
    }

    protected function assertCompositeChildrenShippable($item, int $sourceId, int $qty): void
    {
        foreach ($item->children as $child) {
            if (! $child->qty_ordered) {
                continue;
            }

            $finalQty = ($child->qty_ordered / $item->qty_ordered) * $qty;

            $availableQty = $child->product
                ? (float) $child->product->inventories()->where('inventory_source_id', $sourceId)->sum('qty')
                : 0.0;

            if ($child->qty_to_ship < $finalQty || $availableQty < $finalQty) {
                throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.shipment.inventory-insufficient', [
                    'sku' => $child->sku ?? $item->sku,
                ]), 422);
            }
        }
    }

    protected function assertStockAvailable($item, int $sourceId, int $qty): void
    {
        if (! $item->product) {
            return;
        }

        try {
            if (! $item->getTypeInstance()->isStockable()) {
                return;
            }

            $available = (float) $item->product->inventories()
                ->where('inventory_source_id', $sourceId)
                ->sum('qty');

            if ($available < $qty) {
                throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.shipment.inventory-insufficient', [
                    'sku' => $item->sku,
                ]), 422);
            }
        } catch (InvalidInputException $e) {
            throw $e;
        } catch (\Throwable) {
        }
    }
}
