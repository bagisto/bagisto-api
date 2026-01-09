<?php

namespace Webkul\BagistoApi\State;

use Webkul\BagistoApi\Models\ProductCustomerGroupPrice;

class ProductCustomerGroupPriceProvider extends AbstractNestedResourceProvider
{
    protected function getModelClass(): string
    {
        return ProductCustomerGroupPrice::class;
    }
}
