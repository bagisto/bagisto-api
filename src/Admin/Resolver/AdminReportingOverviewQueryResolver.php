<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use Webkul\BagistoApi\Admin\Models\AdminReportingOverview;

class AdminReportingOverviewQueryResolver extends AdminReportingQueryResolver
{
    protected string $entity = 'overview';

    protected string $resourceClass = AdminReportingOverview::class;
}
