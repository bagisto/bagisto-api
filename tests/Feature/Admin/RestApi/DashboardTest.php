<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\Admin\Helpers\Dashboard;
use Webkul\BagistoApi\Admin\State\AdminDashboardProvider;
use Webkul\BagistoApi\Tests\AdminApiTestCase;

class DashboardTest extends AdminApiTestCase
{
    /**
     * The dashboard stats endpoint rejects an unauthenticated request.
     */
    public function test_requires_authentication(): void
    {
        $this->publicGet('/api/admin/dashboard/stats')->assertStatus(401);
    }

    /**
     * Without a type the endpoint returns the over-all statistics.
     */
    public function test_default_type_returns_over_all(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/dashboard/stats');

        $response->assertOk();
        $rows = $response->json();
        expect($rows)->toBeArray()->and($rows)->not->toBeEmpty();
        expect($rows[0])->toHaveKeys(['type', 'dateRange', 'statistics']);
        expect($rows[0]['type'])->toBe('over-all');
        expect($rows[0]['statistics'])->toHaveKeys([
            'total_customers',
            'total_orders',
            'total_sales',
            'avg_sales',
            'total_unpaid_invoices',
        ]);
    }

    /**
     * The today type returns today's sales, orders and customers.
     */
    public function test_type_today(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/dashboard/stats?type=today');

        $response->assertOk();
        $row = $response->json()[0];
        expect($row['type'])->toBe('today');
        expect($row['statistics'])->toHaveKeys(['total_sales', 'total_orders', 'total_customers', 'orders']);
    }

    /**
     * The top-selling-products type returns its statistics as an array.
     */
    public function test_type_top_selling_products(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/dashboard/stats?type=top-selling-products');

        $response->assertOk();
        expect($response->json()[0]['type'])->toBe('top-selling-products');
        expect($response->json()[0]['statistics'])->toBeArray();
    }

    /**
     * Top-selling products whose rows carry Eloquent image collections serialise without a 500.
     */
    public function test_top_selling_products_does_not_500(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/dashboard/stats?type=top-selling-products');

        expect($response->getStatusCode())->not->toBe(500);
        $response->assertOk();
        expect($response->json()[0]['statistics'])->toBeArray();
    }

    /**
     * The stock-threshold-products type returns its statistics as an array.
     */
    public function test_type_stock_threshold_products(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/dashboard/stats?type=stock-threshold-products');

        $response->assertOk();
        expect($response->json()[0]['statistics'])->toBeArray();
    }

    /**
     * An unknown stat type is rejected with a 400.
     */
    public function test_invalid_type_returns_400(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/dashboard/stats?type=does-not-exist');

        expect($response->getStatusCode())->toBe(400);
    }

    /**
     * A start and end date range is accepted and echoed back as the date range.
     */
    public function test_date_range_param(): void
    {
        $admin = $this->createAdmin();

        $start = now()->subDays(10)->format('Y-m-d');
        $end = now()->format('Y-m-d');

        $response = $this->adminGet($admin, "/api/admin/dashboard/stats?start={$start}&end={$end}");

        $response->assertOk();
        expect($response->json()[0]['dateRange'])->toBeString();
    }

    /**
     * Every dashboard stat type maps to a method core's Dashboard helper has.
     */
    public function test_every_stat_type_maps_to_an_existing_core_method(): void
    {
        foreach (AdminDashboardProvider::TYPE_FUNCTIONS as $method) {
            expect(method_exists(Dashboard::class, $method))->toBeTrue();
        }
    }
}
