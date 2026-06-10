<?php

namespace Webkul\BagistoApi\Admin\State;

/**
 * CSV export for the sales reporting sub-page.
 */
class AdminReportingSalesExportProvider extends AdminReportingExportProvider
{
    protected string $entity = 'sales';
}
