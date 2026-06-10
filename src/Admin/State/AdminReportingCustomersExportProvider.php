<?php

namespace Webkul\BagistoApi\Admin\State;

/**
 * CSV export for the customers reporting sub-page.
 */
class AdminReportingCustomersExportProvider extends AdminReportingExportProvider
{
    protected string $entity = 'customers';
}
