<?php

namespace Webkul\BagistoApi\State\Concerns;

use Illuminate\Support\Collection;
use Webkul\RMA\Enums\DefaultRMAStatusEnum;
use Webkul\RMA\Helpers\Helper as RMAHelper;
use Webkul\RMA\Repositories\RMAItemRepository;

trait ResolvesReturnableItems
{
    /**
     * RMA statuses whose quantities no longer hold the order item.
     */
    public const RELEASED_RMA_STATUSES = [
        DefaultRMAStatusEnum::CANCELED->value,
        DefaultRMAStatusEnum::DECLINED->value,
    ];

    protected function resolveReturnableItems(
        RMAHelper $rmaHelper,
        RMAItemRepository $rmaItemRepository,
        int $orderId
    ): Collection {
        $items = $rmaHelper->getOrderItems($orderId);

        if ($items->isEmpty()) {
            return collect();
        }

        $heldQuantities = $rmaItemRepository
            ->whereIn('order_item_id', $items->pluck('order_item_id')->all())
            ->whereHas('rma', fn ($query) => $query->whereNotIn('rma_status_id', self::RELEASED_RMA_STATUSES))
            ->groupBy('order_item_id')
            ->selectRaw('order_item_id, SUM(quantity) as total_quantity')
            ->pluck('total_quantity', 'order_item_id');

        return $items->map(function ($item) use ($heldQuantities) {
            $heldQuantity = (int) ($heldQuantities[$item->order_item_id] ?? 0);

            $item->rma_quantity = $heldQuantity;
            $item->currentQuantity = (int) $item->qty_ordered - $heldQuantity;

            return $item;
        })->values();
    }
}
