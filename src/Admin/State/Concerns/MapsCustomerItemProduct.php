<?php

namespace Webkul\BagistoApi\Admin\State\Concerns;

use Webkul\Admin\Http\Resources\ProductResource;

trait MapsCustomerItemProduct
{
    /**
     * Copy the product summary core's customer panels show onto an item row, from core's product resource.
     */
    protected function fillProductSummary(object $dto, $product): void
    {
        if (! $product) {
            return;
        }

        $summary = (new ProductResource($product))->toArray(request());

        $dto->type = $summary['type'];
        $dto->isSaleable = (bool) $summary['is_saleable'];
        $dto->qtyAvailable = $summary['qty_available'];
        $dto->isOptionsRequired = (bool) $summary['is_options_required'];
    }
}
