<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Admin\Models\AdminReturn;
use Webkul\RMA\Enums\DefaultRMAResolution;
use Webkul\RMA\Enums\DefaultRMAStatusEnum;
use Webkul\RMA\Repositories\RMARepository;
use Webkul\RMA\Repositories\RMAStatusRepository;

trait BuildsAdminReturn
{
    public const RETURN_RELATIONS = ['status', 'item.orderItem', 'item.reason', 'images', 'order'];

    protected function buildAdminReturn($rma, RMARepository $rmaRepository, RMAStatusRepository $statusRepository): AdminReturn
    {
        $dto = new AdminReturn;
        $dto->id = (int) $rma->id;
        $dto->orderId = $rma->order_id !== null ? (int) $rma->order_id : null;
        $dto->orderIncrementId = $rma->order?->increment_id;
        $dto->orderStatus = $rma->order?->status;
        $dto->isGuest = $rma->order && $rma->order->is_guest !== null ? (int) $rma->order->is_guest : null;
        $dto->customerName = $rma->order
            ? (trim(($rma->order->customer_first_name ?? '').' '.($rma->order->customer_last_name ?? '')) ?: null)
            : null;
        $dto->customerEmail = $rma->order?->customer_email;
        $dto->statusId = $rma->rma_status_id !== null ? (int) $rma->rma_status_id : null;
        $dto->statusTitle = $rma->status?->title;
        $dto->statusColor = $rma->status?->color;
        $dto->packageCondition = $rma->package_condition;
        $dto->information = $rma->information;
        $dto->canReopen = $rmaRepository->canReopenRma($rma);
        $dto->messagesCount = (int) $rma->messages()->count();
        $dto->item = $this->buildAdminReturnItem($rma->item);
        $dto->images = $rma->images->map(fn ($image) => [
            'id' => (int) $image->id,
            'path' => $image->path,
            'url' => $image->path ? Storage::url($image->path) : null,
        ])->values()->all();
        $dto->availableStatuses = $this->buildAvailableStatuses($rma, $statusRepository);
        $dto->createdAt = $rma->created_at ? Carbon::parse($rma->created_at)->toIso8601String() : null;
        $dto->updatedAt = $rma->updated_at ? Carbon::parse($rma->updated_at)->toIso8601String() : null;

        return $dto;
    }

    protected function buildAdminReturnItem($item): ?array
    {
        if (! $item) {
            return null;
        }

        return [
            'id' => (int) $item->id,
            'order_item_id' => $item->order_item_id !== null ? (int) $item->order_item_id : null,
            'sku' => $item->orderItem?->sku,
            'name' => $item->orderItem?->name,
            'quantity' => (int) $item->quantity,
            'resolution' => $item->resolution,
            'reason_id' => $item->rma_reason_id !== null ? (int) $item->rma_reason_id : null,
            'reason' => $item->reason?->title,
            'variant_id' => $item->variant_id !== null ? (int) $item->variant_id : null,
        ];
    }

    /**
     * The status transitions the admin may set next, mirroring core
     * RequestController::rmaStatusForRequest.
     *
     * @return array<int,array{id:int,title:string}>
     */
    protected function buildAvailableStatuses($rma, RMAStatusRepository $statusRepository): array
    {
        $activeStatusIds = $statusRepository->where('status', 1)->pluck('id');

        if ((int) $rma->rma_status_id === DefaultRMAStatusEnum::PENDING->value) {
            $ids = $activeStatusIds->intersect([
                DefaultRMAStatusEnum::ACCEPT->value,
                DefaultRMAStatusEnum::DECLINED->value,
            ]);
        } else {
            $hasCancel = $rma->item?->resolution === DefaultRMAResolution::CANCEL_ITEMS->value;

            $excluded = $hasCancel
                ? [
                    DefaultRMAStatusEnum::ACCEPT->value,
                    DefaultRMAStatusEnum::DECLINED->value,
                    DefaultRMAStatusEnum::PENDING->value,
                    DefaultRMAStatusEnum::DISPATCHED_PACKAGE->value,
                    DefaultRMAStatusEnum::RECEIVED_PACKAGE->value,
                    DefaultRMAStatusEnum::SOLVED->value,
                    DefaultRMAStatusEnum::ITEM_CANCELED->value,
                ]
                : [
                    DefaultRMAStatusEnum::ITEM_CANCELED->value,
                    DefaultRMAStatusEnum::ACCEPT->value,
                    DefaultRMAStatusEnum::DECLINED->value,
                    DefaultRMAStatusEnum::PENDING->value,
                    DefaultRMAStatusEnum::SOLVED->value,
                    DefaultRMAStatusEnum::RECEIVED_PACKAGE->value,
                ];

            $ids = $activeStatusIds->diff($excluded);
        }

        return $statusRepository
            ->whereIn('id', $ids->all())
            ->get(['id', 'title'])
            ->map(fn ($s) => ['id' => (int) $s->id, 'title' => $s->title])
            ->all();
    }
}
