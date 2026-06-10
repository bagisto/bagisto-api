<?php

namespace Webkul\BagistoApi\Admin\Resolver;

/**
 * GraphQL View Details (table-form) resolver for the sales reporting sub-page.
 */
class AdminReportingSalesViewResolver extends AdminReportingSalesQueryResolver
{
    protected string $mode = 'table';
}
