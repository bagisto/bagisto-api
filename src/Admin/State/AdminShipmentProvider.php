<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Dto\AdminShipmentDetailDto;
use Webkul\BagistoApi\Admin\State\Concerns\MapsOrderActionItems;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Sales\Models\Shipment;

class AdminShipmentProvider implements ProviderInterface
{
    use MapsOrderActionItems;

    public function __construct(protected AdminOrderActionGuard $guard) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AdminShipmentDetailDto
    {
        $this->guard->resolveAdmin();

        $id = (int) basename((string) ($uriVariables['id'] ?? $context['args']['id'] ?? 0));

        if ($id <= 0) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.order.actions.shipment.not-found'));
        }

        $shipment = Shipment::with(['items', 'order'])->find($id);

        if (! $shipment) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.order.actions.shipment.not-found'));
        }

        $currency = $shipment->order?->order_currency_code ?? '';

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
            ? $shipment->items->map(fn ($row) => $this->mapItem($row, $currency))->all()
            : [];

        return $dto;
    }
}
