<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

class ReportingTest extends AdminApiTestCase
{
    public function test_overview_query(): void
    {
        $admin = $this->createAdmin();

        $query = 'query { statsAdminReportingOverview { entity type dateRange } }';

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        $data = $response->json('data.statsAdminReportingOverview');
        if ($data) {
            expect($data['entity'] ?? null)->toBe('overview');
        }
    }

    public function test_sales_query(): void
    {
        $admin = $this->createAdmin();

        $query = 'query { statsAdminReportingSales { entity type } }';

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        $data = $response->json('data.statsAdminReportingSales');
        if ($data) {
            expect($data['entity'] ?? null)->toBe('sales');
        }
    }

    public function test_sales_by_coupon_query(): void
    {
        $admin = $this->createAdmin();

        $query = 'query { statsAdminReportingSales(type: "sales-by-coupon") { entity type statistics } }';

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        $data = $response->json('data.statsAdminReportingSales');
        if ($data) {
            expect($data['entity'] ?? null)->toBe('sales');
            expect($data['type'] ?? null)->toBe('sales-by-coupon');
        }
    }

    public function test_customers_query(): void
    {
        $admin = $this->createAdmin();

        $query = 'query { statsAdminReportingCustomers { entity type } }';

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        $data = $response->json('data.statsAdminReportingCustomers');
        if ($data) {
            expect($data['entity'] ?? null)->toBe('customers');
        }
    }

    public function test_products_query(): void
    {
        $admin = $this->createAdmin();

        $query = 'query { statsAdminReportingProducts { entity type } }';

        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        $data = $response->json('data.statsAdminReportingProducts');
        if ($data) {
            expect($data['entity'] ?? null)->toBe('products');
        }
    }

    public function test_overview_requires_authentication(): void
    {
        $query = 'query { statsAdminReportingOverview { entity } }';

        $response = $this->adminGraphQL($query);

        expect($response->json('errors'))->not->toBeNull();
    }

    /**
     * Bug 1 regression — selecting `dateRange` / `statistics` on any of the 4
     * reporting subpage queries used to crash with HTTP 500 because the
     * resources declared `?string $dateRange` while the helper returns an
     * array `{previous,current}`. Selecting full field set must work for
     * every subpage now.
     */
    public function test_top_selling_query_does_not_return_internal_server_error_for_each_subpage(): void
    {
        $admin = $this->createAdmin();

        foreach ([
            'statsAdminReportingOverview',
            'statsAdminReportingSales',
            'statsAdminReportingCustomers',
            'statsAdminReportingProducts',
        ] as $queryName) {
            $query = "query { $queryName { entity type dateRange statistics } }";
            $response = $this->adminGraphQL($query, [], $admin);

            $response->assertOk();
            $errors = $response->json('errors') ?? [];

            foreach ($errors as $err) {
                expect($err['message'] ?? '')->not->toBe('Internal server error', "$queryName returned Internal server error");
            }
        }
    }

    public function test_date_range_resolves_over_graphql(): void
    {
        $admin = $this->createAdmin();

        $query = 'query { statsAdminReportingSales { entity dateRange } }';
        $response = $this->adminGraphQL($query, [], $admin);

        $response->assertOk();
        expect($response->json('data.statsAdminReportingSales.dateRange'))->not->toBeNull();
    }

    public function test_view_stats_queries_resolve(): void
    {
        $admin = $this->createAdmin();

        $cases = [
            'viewStatsAdminReportingSales' => 'sales',
            'viewStatsAdminReportingCustomers' => 'customers',
            'viewStatsAdminReportingProducts' => 'products',
        ];

        foreach ($cases as $field => $entity) {
            $query = "query { $field { entity type dateRange statistics } }";
            $response = $this->adminGraphQL($query, [], $admin);

            $response->assertOk();

            foreach (($response->json('errors') ?? []) as $err) {
                expect($err['message'] ?? '')->not->toBe('Internal server error', "$field returned Internal server error");
            }

            $data = $response->json("data.$field");
            expect($data['entity'] ?? null)->toBe($entity);
            expect($data['statistics'] ?? null)->not->toBeNull();
        }
    }

    public function test_view_stats_requires_authentication(): void
    {
        $query = 'query { viewStatsAdminReportingSales { entity } }';
        $response = $this->adminGraphQL($query);

        $response->assertOk();
        expect($response->json('errors'))->not->toBeNull();
    }
}
