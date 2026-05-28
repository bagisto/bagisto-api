<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Admin\Dto\AdminShipmentDetailDto;
use Webkul\BagistoApi\Admin\State\Concerns\MapsOrderActionItems;
use Webkul\BagistoApi\Admin\State\Concerns\TranslatesActionPayload;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\Sales\Models\Order;
use Webkul\Sales\Repositories\ShipmentRepository;

class AdminShipmentCreateProcessor implements ProcessorInterface
{
    use MapsOrderActionItems;
    use TranslatesActionPayload;

    public function __construct(
        protected AdminOrderActionGuard $guard,
        protected ShipmentRepository $shipmentRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): AdminShipmentDetailDto
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
                'source'        => $source,
                'items'         => $nested,
                'carrier_title' => $carrierTitle,
                'track_number'  => $trackNumber,
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

        return $this->toDto($shipment->fresh(['items']));
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
                    'sku'       => $item->sku,
                    'requested' => $totalForItem,
                    'available' => (int) $item->qty_to_ship,
                ]), 422);
            }

            foreach ($sourceMap as $sourceId => $qty) {
                if (! $item->product) {
                    continue;
                }
                try {
                    $available = (int) $item->product->inventories()
                        ->where('inventory_source_id', $sourceId)
                        ->sum('qty');
                    if ($available < $qty && $item->getTypeInstance()->isStockable()) {
                        throw new InvalidInputException(__('bagistoapi::app.admin.order.actions.shipment.inventory-insufficient', [
                            'sku' => $item->sku,
                        ]), 422);
                    }
                } catch (InvalidInputException $e) {
                    throw $e;
                } catch (\Throwable) {
                    // best-effort — skip inventory check if relation unavailable.
                }
            }
        }
    }

    protected function toDto($shipment): AdminShipmentDetailDto
    {
        $currency = $shipment->order?->order_currency_code;

        $dto = new AdminShipmentDetailDto;
        $dto->id = (int) $shipment->id;
        $dto->orderId = (int) $shipment->order_id;
        $dto->status = $shipment->status !== null ? (string) $shipment->status : null;
        $dto->totalQty = (int) $shipment->total_qty;
        $dto->totalWeight = $shipment->total_weight !== null ? (float) $shipment->total_weight : null;
        $dto->carrierCode = $shipment->carrier_code;
        $dto->carrierTitle = $shipment->carrier_title;
        $dto->trackNumber = $shipment->track_number;
        $dto->emailSent = (bool) $shipment->email_sent;
        $dto->inventorySourceId = $shipment->inventory_source_id !== null ? (int) $shipment->inventory_source_id : null;
        $dto->inventorySourceName = $shipment->inventory_source_name;
        $dto->createdAt = $shipment->created_at ? (string) $shipment->created_at : null;
        $dto->updatedAt = $shipment->updated_at ? (string) $shipment->updated_at : null;

        $dto->items = $shipment->items
            ? $shipment->items->map(fn ($row) => $this->mapItem($row, $currency ?? ''))->all()
            : [];

        return $dto;
    }
}
