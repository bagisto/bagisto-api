<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use Webkul\BagistoApi\Admin\Models\AdminReportingProducts;

class AdminReportingProductsQueryResolver extends AdminReportingQueryResolver
{
    protected string $entity = 'products';

    protected string $resourceClass = AdminReportingProducts::class;
}
