<?php

namespace Webkul\BagistoApi\Admin\Resolver;

/**
 * GraphQL View Details (table-form) resolver for the products reporting sub-page.
 */
class AdminReportingProductsViewResolver extends AdminReportingProductsQueryResolver
{
    protected string $mode = 'table';
}
