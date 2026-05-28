<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use Webkul\BagistoApi\Admin\Models\AdminReportingCustomers;

class AdminReportingCustomersQueryResolver extends AdminReportingQueryResolver
{
    protected string $entity = 'customers';

    protected string $resourceClass = AdminReportingCustomers::class;
}
