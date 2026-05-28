<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use Webkul\BagistoApi\Admin\Models\AdminReportingSales;

class AdminReportingSalesQueryResolver extends AdminReportingQueryResolver
{
    protected string $entity = 'sales';

    protected string $resourceClass = AdminReportingSales::class;
}
