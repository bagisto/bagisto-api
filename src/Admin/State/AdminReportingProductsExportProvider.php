<?php

namespace Webkul\BagistoApi\Admin\State;

/**
 * CSV export for the products reporting sub-page.
 */
class AdminReportingProductsExportProvider extends AdminReportingExportProvider
{
    protected string $entity = 'products';
}
