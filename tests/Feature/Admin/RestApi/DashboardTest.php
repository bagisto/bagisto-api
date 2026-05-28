<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;

class DashboardTest extends AdminApiTestCase
{
    public function test_requires_authentication(): void
    {
        $this->publicGet('/api/admin/dashboard/stats')->assertStatus(401);
    }

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

    public function test_type_today(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/dashboard/stats?type=today');

        $response->assertOk();
        $row = $response->json()[0];
        expect($row['type'])->toBe('today');
        expect($row['statistics'])->toHaveKeys(['total_sales', 'total_orders', 'total_customers', 'orders']);
    }

    public function test_type_top_selling_products(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/dashboard/stats?type=top-selling-products');

        $response->assertOk();
        expect($response->json()[0]['type'])->toBe('top-selling-products');
        expect($response->json()[0]['statistics'])->toBeArray();
    }

    /**
     * Regression — when at least one top-selling row carries a `product->images`
     * Eloquent collection, Symfony Serializer used to recurse into the Eloquent
     * model's properties and call `Schema\Builder::getTypes()`, triggering
     * HTTP 500 "This database driver does not support user-defined types".
     * The provider now deep-normalises Eloquent models / collections to plain
     * arrays before returning.
     */
    public function test_top_selling_products_does_not_500(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/dashboard/stats?type=top-selling-products');

        expect($response->getStatusCode())->not->toBe(500);
        $response->assertOk();
        expect($response->json()[0]['statistics'])->toBeArray();
    }

    public function test_type_stock_threshold_products(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/dashboard/stats?type=stock-threshold-products');

        $response->assertOk();
        expect($response->json()[0]['statistics'])->toBeArray();
    }

    public function test_invalid_type_returns_400(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/dashboard/stats?type=does-not-exist');

        expect($response->getStatusCode())->toBe(400);
    }

    public function test_date_range_param(): void
    {
        $admin = $this->createAdmin();

        $start = now()->subDays(10)->format('Y-m-d');
        $end = now()->format('Y-m-d');

        $response = $this->adminGet($admin, "/api/admin/dashboard/stats?start={$start}&end={$end}");

        $response->assertOk();
        expect($response->json()[0]['dateRange'])->toBeString();
    }
}
