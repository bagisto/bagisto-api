<?php

namespace Webkul\BagistoApi\Admin\Resolver;

use Webkul\BagistoApi\Admin\Models\AdminReportingOverview;

class AdminReportingOverviewQueryResolver extends AdminReportingQueryResolver
{
    /**
     * The entity.
     */
    protected string $entity = 'overview';

    /**
     * The resource class.
     */
    protected string $resourceClass = AdminReportingOverview::class;
}
