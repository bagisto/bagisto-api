<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Models\AdminShipment;
use Webkul\BagistoApi\Admin\State\Concerns\BuildsAdminShipment;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Sales\Models\Shipment;

class AdminShipmentProvider implements ProviderInterface
{
    use BuildsAdminShipment;

    public function __construct(protected AdminOrderActionGuard $guard) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): AdminShipment
    {
        $this->guard->resolveAdmin();

        $id = (int) basename((string) ($uriVariables['id'] ?? $context['args']['id'] ?? 0));

        if ($id <= 0) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.order.actions.shipment.not-found'));
        }

        $shipment = Shipment::with(['items', 'items.order_item', 'order', 'order.addresses', 'order.payment'])->find($id);

        if (! $shipment) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.order.actions.shipment.not-found'));
        }

        return $this->buildAdminShipment($shipment);
    }
}
