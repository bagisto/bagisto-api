<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use Webkul\BagistoApi\Admin\Models\AdminReportingCustomers;

class AdminReportingCustomersQueryResolver extends AdminReportingQueryResolver
{
    /**
     * The entity.
     */
    protected string $entity = 'customers';

    /**
     * The resource class.
     */
    protected string $resourceClass = AdminReportingCustomers::class;
}
