<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\Product\Models\Product;

class ProductTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    /**
     * Test list requires authentication.
     */
    public function test_list_requires_authentication(): void
    {
        $this->publicGet('/api/admin/products')->assertStatus(401);
    }

    /**
     * Test list returns data meta envelope.
     */
    public function test_list_returns_data_meta_envelope(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/products');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
        expect($response->json('meta'))->toBeArray();
        expect($response->json('meta'))->toHaveKeys(
            ['currentPage', 'perPage', 'lastPage', 'total', 'from', 'to']
        );
    }

    /**
     * Test default per page is 30.
     */
    public function test_default_per_page_is_30(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/products');

        $response->assertOk();
        expect($response->json('meta.perPage'))->toBe(30);
    }

    /**
     * Test per page caps at 50.
     */
    public function test_per_page_caps_at_50(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/products?per_page=500');

        $response->assertOk();
        expect($response->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

    /**
     * Test list row has slim shape.
     */
    public function test_list_row_has_slim_shape(): void
    {
        $admin = $this->createAdmin();
        $this->ensureProductWithSku();

        $rows = $this->adminGet($admin, '/api/admin/products?per_page=1')->json('data');

        expect($rows[0])->toHaveKeys([
            'id', 'sku', 'type', 'name', 'status',
            'price', 'formattedPrice', 'baseImageUrl', 'isSaleable',
        ]);
    }

    /**
     * Test filter by sku exact.
     */
    public function test_filter_by_sku_exact(): void
    {
        $admin = $this->createAdmin();

        $product = $this->ensureProductWithSku();

        $response = $this->adminGet($admin, '/api/admin/products?sku='.urlencode($product->sku));

        $response->assertOk();
        foreach ($response->json('data') as $row) {
            expect($row['sku'])->toBe($product->sku);
        }
    }

    /**
     * Test query matches by sku partial.
     */
    public function test_query_matches_by_sku_partial(): void
    {
        $admin = $this->createAdmin();

        $product = $this->ensureProductWithSku();

        $partial = substr($product->sku, 0, max(2, (int) (strlen($product->sku) / 2)));

        $response = $this->adminGet($admin, '/api/admin/products?query='.urlencode($partial));

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
    }

    /**
     * Test filter by type simple.
     */
    public function test_filter_by_type_simple(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/products?type=simple&per_page=10');

        $response->assertOk();
        foreach ($response->json('data') as $row) {
            expect($row['type'])->toBe('simple');
        }
    }

    /**
     * Test returns disabled products by default.
     */
    public function test_returns_disabled_products_by_default(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/products?per_page=5');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
    }

    /**
     * Test status filter is accepted.
     */
    public function test_status_filter_is_accepted(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/products?status=1&per_page=5');
        $response->assertOk();

        $response = $this->adminGet($admin, '/api/admin/products?status=0&per_page=5');
        $response->assertOk();
    }

    /**
     * Test pagination moves window.
     */
    public function test_pagination_moves_window(): void
    {
        $admin = $this->createAdmin();

        $this->ensureProductWithSku();
        if (Product::count() < 2) {
            $this->findOrCreateSimpleProduct();
            if (Product::count() < 2) {
                Product::factory()->create(['type' => 'simple']);
            }
        }

        $first = $this->adminGet($admin, '/api/admin/products?per_page=1&page=1');
        $first->assertOk();
        expect($first->json('meta.currentPage'))->toBe(1);

        $second = $this->adminGet($admin, '/api/admin/products?per_page=1&page=2');
        $second->assertOk();
        expect($second->json('meta.currentPage'))->toBe(2);

        $firstId = $first->json('data.0.id');
        $secondId = $second->json('data.0.id');
        expect($firstId)->not()->toBe($secondId);
    }
}
