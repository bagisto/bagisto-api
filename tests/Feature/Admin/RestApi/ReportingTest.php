<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

class ReportingTest extends AdminApiTestCase
{
    public function test_overview_requires_authentication(): void
    {
        $this->publicGet('/api/admin/reporting/stats')->assertStatus(401);
    }

    public function test_sales_requires_authentication(): void
    {
        $this->publicGet('/api/admin/reporting/sales')->assertStatus(401);
    }

    public function test_customers_requires_authentication(): void
    {
        $this->publicGet('/api/admin/reporting/customers')->assertStatus(401);
    }

    public function test_products_requires_authentication(): void
    {
        $this->publicGet('/api/admin/reporting/products')->assertStatus(401);
    }

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

    public function test_overview_invalid_type_returns_400(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/reporting/stats?type=nope');

        expect($response->getStatusCode())->toBe(400);
    }

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

    public function test_sales_type_refunds(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/reporting/sales?type=refunds');

        $response->assertOk();
        expect($response->json()[0]['type'])->toBe('refunds');
    }

    public function test_sales_type_sales_by_coupon(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/reporting/sales?type=sales-by-coupon');

        $response->assertOk();
        expect($response->json()[0]['type'])->toBe('sales-by-coupon');
        expect($response->json()[0]['statistics'])->toBeArray();
    }

    public function test_sales_by_coupon_view_returns_table_shape(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/reporting/sales/view?type=sales-by-coupon');

        $response->assertOk();
        $stats = $response->json()[0]['statistics'];
        expect($stats)->toHaveKeys(['columns', 'records']);
    }

    public function test_sales_invalid_type(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/reporting/sales?type=bogus');

        expect($response->getStatusCode())->toBe(400);
    }

    public function test_customers_default_type(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/reporting/customers');

        $response->assertOk();
        $row = $response->json()[0];
        expect($row['entity'])->toBe('customers');
        expect($row['type'])->toBe('total-customers');
    }

    public function test_customers_type_top_groups(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/reporting/customers?type=top-customer-groups');

        $response->assertOk();
        expect($response->json()[0]['type'])->toBe('top-customer-groups');
    }

    public function test_products_default_type(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/reporting/products');

        $response->assertOk();
        $row = $response->json()[0];
        expect($row['entity'])->toBe('products');
        expect($row['type'])->toBe('total-sold-quantities');
    }

    public function test_products_type_top_search_terms(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/reporting/products?type=top-search-terms');

        $response->assertOk();
        expect($response->json()[0]['type'])->toBe('top-search-terms');
    }

    /**
     * Regression — `top-selling-products-by-revenue` / `-by-quantity` used to
     * emit HTTP 500 "This database driver does not support user-defined types"
     * because the helper output contained Eloquent `images` collections that
     * Symfony Serializer recursed into. The provider now deep-normalises
     * Eloquent models / collections to plain arrays.
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

    public function test_products_top_selling_by_quantity_does_not_500(): void
    {
        $admin = $this->createAdmin();
        $response = $this->adminGet($admin, '/api/admin/reporting/products?type=top-selling-products-by-quantity');
        expect($response->getStatusCode())->not->toBe(500);
        $response->assertOk();
        expect($response->json()[0]['type'])->toBe('top-selling-products-by-quantity');
        expect($response->json()[0]['statistics'])->toBeArray();
    }

    public function test_products_invalid_type(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/reporting/products?type=junk');

        expect($response->getStatusCode())->toBe(400);
    }

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

    public function test_overview_date_range_is_array(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/reporting/stats');

        $response->assertOk();
        $dateRange = $response->json()[0]['dateRange'];
        expect($dateRange)->toBeArray()->and($dateRange)->toHaveKeys(['previous', 'current']);
    }

    /*
     |--------------------------------------------------------------------------
     | View Details (table-form stats) — sales / customers / products
     |--------------------------------------------------------------------------
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

    public function test_view_requires_authentication(): void
    {
        $this->publicGet('/api/admin/reporting/sales/view')->assertStatus(401);
        $this->publicGet('/api/admin/reporting/customers/view')->assertStatus(401);
        $this->publicGet('/api/admin/reporting/products/view')->assertStatus(401);
    }

    public function test_sales_view_invalid_type_returns_400(): void
    {
        $admin = $this->createAdmin();
        expect($this->adminGet($admin, '/api/admin/reporting/sales/view?type=bogus')->getStatusCode())->toBe(400);
    }

    /*
     |--------------------------------------------------------------------------
     | Export (CSV) — sales / customers / products
     |--------------------------------------------------------------------------
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

    public function test_export_unsupported_format_returns_422(): void
    {
        $admin = $this->createAdmin();
        $this->get('/api/admin/reporting/sales/export?type=total-sales&format=pdf', array_merge(
            $this->adminHeaders($admin),
            ['Accept' => 'text/csv'],
        ))->assertStatus(422);
    }

    public function test_export_requires_authentication(): void
    {
        $this->get('/api/admin/reporting/sales/export?type=total-sales', ['Accept' => 'text/csv'])->assertStatus(401);
    }
}
