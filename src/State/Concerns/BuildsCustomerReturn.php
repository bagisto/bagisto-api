<?php

namespace Webkul\BagistoApi\State\Concerns;

use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Models\CustomerReturn;
use Webkul\RMA\Repositories\RMARepository;

trait BuildsCustomerReturn
{
    /**
     * The return relations constant.
     */
    public const RETURN_RELATIONS = ['status', 'item.orderItem', 'item.reason', 'images', 'order', 'additionalFields.customField'];

    /**
     * Build customer return.
     */
    protected function buildCustomerReturn($rma, bool $detail, RMARepository $rmaRepository): CustomerReturn
    {
        $customerReturn = new CustomerReturn;

        $customerReturn->id = (int) $rma->id;
        $customerReturn->order_id = $rma->order_id !== null ? (int) $rma->order_id : null;
        $customerReturn->order_increment_id = $rma->order?->increment_id;
        $customerReturn->status_id = $rma->rma_status_id !== null ? (int) $rma->rma_status_id : null;
        $customerReturn->status_title = $rma->status?->title;
        $customerReturn->status_color = $rma->status?->color;
        $customerReturn->package_condition = $rma->package_condition;
        $customerReturn->information = $rma->information;
        $customerReturn->messages_count = (int) $rma->messages()->count();
        $customerReturn->item = $this->buildReturnItem($rma->item);
        $customerReturn->custom_attributes = $this->buildReturnCustomAttributes($rma);
        $customerReturn->created_at = $rma->created_at?->toIso8601String();
        $customerReturn->updated_at = $rma->updated_at?->toIso8601String();

        if ($detail) {
            $customerReturn->can_close = $rmaRepository->canCloseRma($rma);
            $customerReturn->can_reopen = $rmaRepository->canReopenRma($rma);
            $customerReturn->is_expired = $rmaRepository->isRmaExpired($rma);
            $customerReturn->images = $rma->images->map(fn ($image) => [
                'id' => (int) $image->id,
                'path' => $image->path,
                'url' => $image->path ? Storage::url($image->path) : null,
            ])->values()->all();
        }

        return $customerReturn;
    }

    /**
     * Answers to the return form's custom fields, as the storefront view shows them.
     *
     * @return array<int,array<string,mixed>>
     */
    protected function buildReturnCustomAttributes($rma): array
    {
        return $rma->additionalFields->map(fn ($field) => [
            'field_id' => $field->rma_custom_field_id !== null ? (int) $field->rma_custom_field_id : null,
            'code' => $field->customField?->code,
            'label' => $field->customField?->label,
            'type' => $field->customField?->type,
            'value' => $field->value,
        ])->values()->all();
    }

    /**
     * Build return item.
     */
    protected function buildReturnItem($item): ?array
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
}
