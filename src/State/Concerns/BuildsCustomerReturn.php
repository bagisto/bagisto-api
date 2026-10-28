<?php

namespace Webkul\BagistoApi\State\Concerns;

use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Models\CustomerReturn;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\RMA\Repositories\RMARepository;

trait BuildsCustomerReturn
{
    public const RETURN_RELATIONS = ['status', 'item.orderItem.product.images', 'item.reason', 'images', 'order', 'additionalFields.customField'];

    protected function buildCustomerReturn($rma, bool $detail, RMARepository $rmaRepository): CustomerReturn
    {
        $r = new CustomerReturn;

        $r->id = (int) $rma->id;
        $r->order_id = $rma->order_id !== null ? (int) $rma->order_id : null;
        $r->order_increment_id = $rma->order?->increment_id;
        $r->status_id = $rma->rma_status_id !== null ? (int) $rma->rma_status_id : null;
        $r->status_title = $rma->status?->title;
        $r->status_color = $rma->status?->color;
        $r->package_condition = $rma->package_condition;
        $r->information = $rma->information;
        $r->messages_count = (int) $rma->messages()->count();
        $r->item = $this->buildReturnItem($rma->item);
        $r->custom_attributes = $this->buildReturnCustomAttributes($rma);
        $r->created_at = $rma->created_at?->toIso8601String();
        $r->updated_at = $rma->updated_at?->toIso8601String();

        if ($detail) {
            $r->can_close = $rmaRepository->canCloseRma($rma);
            $r->can_reopen = $rmaRepository->canReopenRma($rma);
            $r->is_expired = $rmaRepository->isRmaExpired($rma);
            $r->images = $rma->images->map(fn ($image) => [
                'id' => (int) $image->id,
                'path' => $image->path,
                'url' => $image->path ? Storage::url($image->path) : null,
            ])->values()->all();
        }

        return $r;
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
            'product_image' => $this->returnItemImage($item),
            'quantity' => (int) $item->quantity,
            'resolution' => $item->resolution,
            'reason_id' => $item->rma_reason_id !== null ? (int) $item->rma_reason_id : null,
            'reason' => $item->reason?->title,
            'variant_id' => $item->variant_id !== null ? (int) $item->variant_id : null,
        ];
    }

    /**
     * The returned product's base image, or the store's product placeholder when it has none or no
     * longer exists, as the storefront product endpoints answer.
     */
    protected function returnItemImage($item): ?string
    {
        $product = $item->orderItem?->product ?? app(ProductRepository::class)->getModel();

        return product_image()->getProductBaseImage($product)['medium_image_url'] ?? null;
    }
}
