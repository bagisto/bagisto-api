<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\ReturnableItem;
use Webkul\RMA\Helpers\Helper as RMAHelper;
use Webkul\Sales\Repositories\OrderRepository;

class ReturnableItemProvider implements ProviderInterface
{
    public function __construct(
        private readonly RMAHelper $rmaHelper,
        private readonly OrderRepository $orderRepository,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        $orderId = $uriVariables['orderId']
            ?? $context['args']['orderId']
            ?? request()->query('order_id');

        if (! $orderId) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.return.invalid-order'));
        }

        $order = $this->orderRepository->findOneWhere([
            'id' => (int) $orderId,
            'customer_id' => $customer->id,
        ]);

        if (! $order) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.return.invalid-order'));
        }

        return $this->rmaHelper->getOrderItems((int) $order->id)
            ->map(function ($item) {
                $row = new ReturnableItem;
                $row->order_item_id = (int) $item->order_item_id;
                $row->product_id = $item->product_id !== null ? (int) $item->product_id : null;
                $row->sku = $item->sku;
                $row->name = $item->name;
                $row->type = $item->type;
                $row->url_key = $item->url_key;
                $row->price = $item->price !== null ? (float) $item->price : null;
                $row->base_image_url = $item->base_image ? Storage::url($item->base_image) : null;
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
