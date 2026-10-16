<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\Admin\Helpers\Reporting;
use Webkul\BagistoApi\Admin\State\AdminReportingProvider;
use Webkul\BagistoApi\Tests\AdminApiTestCase;

class ReportingTest extends AdminApiTestCase
{
    /**
     * The overview stats endpoint rejects an unauthenticated request.
     */
    public function test_overview_requires_authentication(): void
    {
        $this->publicGet('/api/admin/reporting/stats')->assertStatus(401);
    }

    /**
     * The sales report endpoint rejects an unauthenticated request.
     */
    public function test_sales_requires_authentication(): void
    {
        $this->publicGet('/api/admin/reporting/sales')->assertStatus(401);
    }

    /**
     * The customers report endpoint rejects an unauthenticated request.
     */
    public function test_customers_requires_authentication(): void
    {
        $this->publicGet('/api/admin/reporting/customers')->assertStatus(401);
    }

    /**
     * The products report endpoint rejects an unauthenticated request.
     */
    public function test_products_requires_authentication(): void
    {
        $this->publicGet('/api/admin/reporting/products')->assertStatus(401);
    }

    /**
     * Without a type the overview returns the total-sales statistics.
     */
    public function test_overview_default_type(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/reporting/stats');

        $response->assertOk();
        $row = $response->json()[0];
        expect($row)->toHaveKeys(['entity', 'type', 'dateRange', 'statistics']);
        expect($row['entity'])->toBe('overview');
        expect($row['type'])->toBe('total-sales');
    }

    /**
     * An unknown overview type is rejected with a 400.
     */
    public function test_overview_invalid_type_returns_400(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/reporting/stats?type=nope');

        expect($response->getStatusCode())->toBe(400);
    }

    /**
     * Without a type the sales report returns the total-sales statistics.
     */
    public function test_sales_default_type(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/reporting/sales');

        $response->assertOk();
        $row = $response->json()[0];
        expect($row['entity'])->toBe('sales');
        expect($row['type'])->toBe('total-sales');
        expect($row['statistics'])->toBeArray();
    }

    /**
     * The sales report accepts the refunds type.
     */
    public function test_sales_type_refunds(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/reporting/sales?type=refunds');

        $response->assertOk();
        expect($response->json()[0]['type'])->toBe('refunds');
    }

    /**
     * The sales report accepts the sales-by-coupon type.
     */
    public function test_sales_type_sales_by_coupon(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/reporting/sales?type=sales-by-coupon');

        $response->assertOk();
        expect($response->json()[0]['type'])->toBe('sales-by-coupon');
        expect($response->json()[0]['statistics'])->toBeArray();
    }

    /**
     * The sales-by-coupon view returns a table of columns and records.
     */
    public function test_sales_by_coupon_view_returns_table_shape(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/reporting/sales/view?type=sales-by-coupon');

        $response->assertOk();
        $stats = $response->json()[0]['statistics'];
        expect($stats)->toHaveKeys(['columns', 'records']);
    }

    /**
     * An unknown sales report type is rejected with a 400.
     */
    public function test_sales_invalid_type(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/reporting/sales?type=bogus');

        expect($response->getStatusCode())->toBe(400);
    }

    /**
     * Without a type the customers report returns the total-customers statistics.
     */
    public function test_customers_default_type(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/reporting/customers');

        $response->assertOk();
        $row = $response->json()[0];
        expect($row['entity'])->toBe('customers');
        expect($row['type'])->toBe('total-customers');
    }

    /**
     * The customers report accepts the top-customer-groups type.
     */
    public function test_customers_type_top_groups(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/reporting/customers?type=top-customer-groups');

        $response->assertOk();
        expect($response->json()[0]['type'])->toBe('top-customer-groups');
    }

    /**
     * Without a type the products report returns the total-sold-quantities statistics.
     */
    public function test_products_default_type(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/reporting/products');

        $response->assertOk();
        $row = $response->json()[0];
        expect($row['entity'])->toBe('products');
        expect($row['type'])->toBe('total-sold-quantities');
    }

    /**
     * The products report accepts the top-search-terms type.
     */
    public function test_products_type_top_search_terms(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/reporting/products?type=top-search-terms');

        $response->assertOk();
        expect($response->json()[0]['type'])->toBe('top-search-terms');
    }

    /**
     * Top-selling products by revenue serialise their Eloquent image collections without a 500.
     */
    public function test_products_top_selling_by_revenue_does_not_500(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/reporting/products?type=top-selling-products-by-revenue');
        expect($response->getStatusCode())->not->toBe(500);
        $response->assertOk();
        expect($response->json()[0]['type'])->toBe('top-selling-products-by-revenue');
        expect($response->json()[0]['statistics'])->toBeArray();
    }

    /**
     * Top-selling products by quantity serialise their Eloquent image collections without a 500.
     */
    public function test_products_top_selling_by_quantity_does_not_500(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/reporting/products?type=top-selling-products-by-quantity');
        expect($response->getStatusCode())->not->toBe(500);
        $response->assertOk();
        expect($response->json()[0]['type'])->toBe('top-selling-products-by-quantity');
        expect($response->json()[0]['statistics'])->toBeArray();
    }

    /**
     * An unknown products report type is rejected with a 400.
     */
    public function test_products_invalid_type(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/reporting/products?type=junk');

        expect($response->getStatusCode())->toBe(400);
    }

    /**
     * Every reporting endpoint accepts a start and end date range.
     */
    public function test_date_range_applies_to_all_endpoints(): void
    {
        $admin = $this->createAdmin();

        $start = now()->subDays(15)->format('Y-m-d');
        $end = now()->format('Y-m-d');

        foreach (['reporting/stats', 'reporting/sales', 'reporting/customers', 'reporting/products'] as $path) {
            $response = $this->adminGet($admin, "/api/admin/{$path}?start={$start}&end={$end}");
            $response->assertOk();
            $dateRange = $response->json()[0]['dateRange'];
            expect($dateRange)->not->toBeNull();
        }
    }

    /**
     * The overview date range carries the previous and current periods.
     */
    public function test_overview_date_range_is_array(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/reporting/stats');

        $response->assertOk();
        $dateRange = $response->json()[0]['dateRange'];
        expect($dateRange)->toBeArray()->and($dateRange)->toHaveKeys(['previous', 'current']);
    }

    /**
     * The sales view returns its total-sales statistics as a table of columns and records.
     */
    public function test_sales_view_returns_table_shape(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/reporting/sales/view?type=total-sales');

        $response->assertOk();
        $row = $response->json()[0];
        expect($row['entity'])->toBe('sales');
        expect($row['type'])->toBe('total-sales');
        expect($row['statistics'])->toHaveKey('columns');
        expect($row['statistics'])->toHaveKey('records');
    }

    /**
     * The customers view returns its statistics as a table of columns and records.
     */
    public function test_customers_view_returns_table_shape(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/reporting/customers/view?type=customers-with-most-sales');

        $response->assertOk();
        $row = $response->json()[0];
        expect($row['entity'])->toBe('customers');
        expect($row['statistics'])->toHaveKey('columns');
        expect($row['statistics'])->toHaveKey('records');
    }

    /**
     * The products view returns its statistics as a table of columns and records.
     */
    public function test_products_view_returns_table_shape(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/reporting/products/view?type=top-selling-products-by-revenue');

        $response->assertOk();
        $row = $response->json()[0];
        expect($row['entity'])->toBe('products');
        expect($row['statistics'])->toHaveKey('columns');
        expect($row['statistics'])->toHaveKey('records');
    }

    /**
     * Every reporting view endpoint rejects an unauthenticated request.
     */
    public function test_view_requires_authentication(): void
    {
        $this->publicGet('/api/admin/reporting/sales/view')->assertStatus(401);
        $this->publicGet('/api/admin/reporting/customers/view')->assertStatus(401);
        $this->publicGet('/api/admin/reporting/products/view')->assertStatus(401);
    }

    /**
     * An unknown sales view type is rejected with a 400.
     */
    public function test_sales_view_invalid_type_returns_400(): void
    {
        $admin = $this->createAdmin();
        expect($this->adminGet($admin, '/api/admin/reporting/sales/view?type=bogus')->getStatusCode())->toBe(400);
    }

    /**
     * The sales export downloads a CSV file named after the entity and type.
     */
    public function test_sales_export_returns_csv(): void
    {
        $admin = $this->createAdmin();
        $response = $this->get('/api/admin/reporting/sales/export?type=total-sales&format=csv', array_merge(
            $this->adminHeaders($admin),
            ['Accept' => 'text/csv'],
        ));

        $response->assertOk();
        expect($response->headers->get('Content-Type'))->toContain('text/csv');
        expect($response->headers->get('Content-Disposition'))->toContain('sales-total-sales.csv');
    }

    /**
     * The customers export downloads a CSV file named after the entity and type.
     */
    public function test_customers_export_returns_csv(): void
    {
        $admin = $this->createAdmin();
        $response = $this->get('/api/admin/reporting/customers/export?type=customers-with-most-sales&format=csv', array_merge(
            $this->adminHeaders($admin),
            ['Accept' => 'text/csv'],
        ));

        $response->assertOk();
        expect($response->headers->get('Content-Type'))->toContain('text/csv');
        expect($response->headers->get('Content-Disposition'))->toContain('customers-customers-with-most-sales.csv');
    }

    /**
     * The products export downloads a CSV file.
     */
    public function test_products_export_returns_csv(): void
    {
        $admin = $this->createAdmin();
        $response = $this->get('/api/admin/reporting/products/export?type=top-selling-products-by-revenue&format=csv', array_merge(
            $this->adminHeaders($admin),
            ['Accept' => 'text/csv'],
        ));

        $response->assertOk();
        expect($response->headers->get('Content-Type'))->toContain('text/csv');
    }

    /**
     * The export downloads an XLSX spreadsheet when that format is requested.
     */
    public function test_export_supports_xlsx(): void
    {
        $admin = $this->createAdmin();

        $response = $this->get('/api/admin/reporting/sales/export?type=total-sales&format=xlsx', array_merge(
            $this->adminHeaders($admin),
            ['Accept' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        ));

        $response->assertStatus(200);

        expect($response->headers->get('Content-Type'))->toContain('spreadsheetml');
        expect($response->headers->get('Content-Disposition'))->toContain('.xlsx');
    }

    /**
     * An unsupported export format is rejected with a 422.
     */
    public function test_export_unsupported_format_returns_422(): void
    {
        $admin = $this->createAdmin();
        $this->get('/api/admin/reporting/sales/export?type=total-sales&format=pdf', array_merge(
            $this->adminHeaders($admin),
            ['Accept' => 'text/csv'],
        ))->assertStatus(422);
    }

    /**
     * The export endpoint rejects an unauthenticated request.
     */
    public function test_export_requires_authentication(): void
    {
        $this->get('/api/admin/reporting/sales/export?type=total-sales', ['Accept' => 'text/csv'])->assertStatus(401);
    }

    /**
     * Every reporting stat type maps to a method core's Reporting helper has.
     */
    public function test_every_stat_type_maps_to_an_existing_core_method(): void
    {
        foreach (AdminReportingProvider::ENTITY_MAPS as $map) {
            foreach ($map as $method) {
                expect(method_exists(Reporting::class, $method))->toBeTrue();
            }
        }
    }
}
