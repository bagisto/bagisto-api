<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use Webkul\BagistoApi\Admin\Models\AdminReportingProducts;

class AdminReportingProductsQueryResolver extends AdminReportingQueryResolver
{
    /**
     * The entity.
     */
    protected string $entity = 'products';

    /**
     * The resource class.
     */
    protected string $resourceClass = AdminReportingProducts::class;
}
