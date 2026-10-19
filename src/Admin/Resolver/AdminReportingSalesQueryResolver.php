<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use Webkul\BagistoApi\Admin\Models\AdminReportingSales;

class AdminReportingSalesQueryResolver extends AdminReportingQueryResolver
{
    /**
     * The entity.
     */
    protected string $entity = 'sales';

    /**
     * The resource class.
     */
    protected string $resourceClass = AdminReportingSales::class;
}
