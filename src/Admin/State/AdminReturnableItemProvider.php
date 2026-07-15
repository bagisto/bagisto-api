<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Models\AdminReturnableItem;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\RMA\Helpers\Helper as RMAHelper;

class AdminReturnableItemProvider implements ProviderInterface
{
    use ChecksAdminPermission;

    public function __construct(
        private readonly RMAHelper $rmaHelper,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $this->authorizedAdmin('sales.rma.requests.create', 'bagistoapi::app.admin.rma.no-permission');

        $orderId = $context['args']['orderId'] ?? request()->query('order_id');

        if (! $orderId) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.rma.invalid-order'));
        }

        return $this->rmaHelper->getOrderItems((int) $orderId)
            ->map(function ($item) {
                $row = new AdminReturnableItem;
                $row->order_item_id = (int) $item->order_item_id;
                $row->product_id = $item->product_id !== null ? (int) $item->product_id : null;
                $row->sku = $item->sku;
                $row->name = $item->name;
                $row->type = $item->type;
                $row->price = $item->price !== null ? (float) $item->price : null;
                $row->qty_ordered = (int) $item->qty_ordered;
                $row->current_quantity = (int) $item->currentQuantity;
                $row->for_return_quantity = (int) $item->forReturnQuantity;
                $row->for_cancel_quantity = (int) $item->forCancelQuantity;
                $row->rma_quantity = (int) $item->rma_quantity;
                $row->rma_return_period = $item->rma_return_period !== null ? (int) $item->rma_return_period : null;

                return $row;
            })
            ->values()
            ->all();
    }
}
