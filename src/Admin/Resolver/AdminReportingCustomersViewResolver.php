<?php

namespace Webkul\BagistoApi\Admin\Resolver;

/**
 * GraphQL View Details (table-form) resolver for the customers reporting sub-page.
 */
class AdminReportingCustomersViewResolver extends AdminReportingCustomersQueryResolver
{
    protected string $mode = 'table';
}
