<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Models\AdminCustomerReview;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\Product\Models\ProductReview;

class AdminCustomerReviewItemProvider extends AbstractAdminItemProvider
{
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.customer.review.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return ProductReview::with(['product', 'customer', 'images'])->find($id);
    }

    public function mapToDto(object $entity): object
    {
        return $this->doMap($entity);
    }

    protected function doMap(ProductReview $r): AdminCustomerReview
    {
        $dto = new AdminCustomerReview;
        $dto->id = (int) $r->id;
        $dto->title = $r->title;
        $dto->comment = $r->comment;
        $dto->rating = $r->rating !== null ? (int) $r->rating : null;
        $dto->status = $r->status;
        $dto->name = $r->name;
        $dto->productId = $r->product_id !== null ? (int) $r->product_id : null;

        if ($r->product) {
            $dto->productSku = $r->product->sku;
            // product_flat name fallback via accessor
            try {
                $dto->productName = $r->product->name ?? null;
            } catch (\Throwable $e) {
                $dto->productName = null;
            }
        }

        $dto->customerId = $r->customer_id !== null ? (int) $r->customer_id : null;
        if ($r->customer) {
            $dto->customerName = trim((string) $r->customer->first_name.' '.(string) $r->customer->last_name) ?: null;
            $dto->customerEmail = $r->customer->email;
        }

        $dto->images = [];
        if ($r->images) {
            foreach ($r->images as $img) {
                $url = null;
                try {
                    $url = $img->path ? \Illuminate\Support\Facades\Storage::url($img->path) : null;
                } catch (\Throwable $e) {
                    $url = $img->path;
                }
                $dto->images[] = [
                    'id'   => (int) $img->id,
                    'path' => $img->path,
                    'url'  => $url,
                ];
            }
        }

        $dto->createdAt = $r->created_at?->toIso8601String();
        $dto->updatedAt = $r->updated_at?->toIso8601String();

        return $dto;
    }
}
