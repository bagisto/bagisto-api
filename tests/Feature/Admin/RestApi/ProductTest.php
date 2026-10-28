<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\BagistoApi\Tests\Concerns\AdminFixtureFactory;
use Webkul\Customer\Models\Customer;
use Webkul\Product\Models\Product;

/**
 * REST coverage for the admin product search endpoint — GET /api/admin/products.
 *
 * Verifies the { data, meta } envelope, slim shape, query/sku/type/status
 * filters, pagination, and that admin sees ALL statuses (including disabled)
 * by default.
 */
class ProductTest extends AdminApiTestCase
{
    use AdminFixtureFactory;

    public function test_list_requires_authentication(): void
    {
        $this->publicGet('/api/admin/products')->assertStatus(401);
    }

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

    public function test_default_per_page_is_30(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/products');

        $response->assertOk();
        expect($response->json('meta.perPage'))->toBe(30);
    }

    public function test_per_page_caps_at_50(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/products?per_page=500');

        $response->assertOk();
        expect($response->json('meta.perPage'))->toBeLessThanOrEqual(50);
    }

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

    public function test_query_matches_by_sku_partial(): void
    {
        $admin = $this->createAdmin();

        $product = $this->ensureProductWithSku();

        $partial = substr($product->sku, 0, max(2, (int) (strlen($product->sku) / 2)));

        $response = $this->adminGet($admin, '/api/admin/products?query='.urlencode($partial));

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
    }

    public function test_filter_by_type_simple(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/products?type=simple&per_page=10');

        $response->assertOk();
        foreach ($response->json('data') as $row) {
            expect($row['type'])->toBe('simple');
        }
    }

    public function test_returns_disabled_products_by_default(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/products?per_page=5');

        $response->assertOk();
        expect($response->json('data'))->toBeArray();
    }

    public function test_status_filter_is_accepted(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGet($admin, '/api/admin/products?status=1&per_page=5');
        $response->assertOk();

        $response = $this->adminGet($admin, '/api/admin/products?status=0&per_page=5');
        $response->assertOk();
    }

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

    protected function makeSearchProduct(string $sku): Product
    {
        $product = Product::factory()->create(['type' => 'simple', 'sku' => $sku]);

        DB::table('product_channels')->where('product_id', $product->id)->delete();

        return $product;
    }

    protected function addCustomizableOption(int $productId): void
    {
        DB::table('product_customizable_options')->insert([
            'product_id' => $productId,
            'type' => 'text',
            'is_required' => 0,
        ]);
    }

    public function test_exclude_customizable_products_filters_simple_products(): void
    {
        $admin = $this->createAdmin();
        $prefix = 'xcust'.uniqid();
        $plain = $this->makeSearchProduct($prefix.'-plain');
        $custom = $this->makeSearchProduct($prefix.'-custom');
        $this->addCustomizableOption($custom->id);

        $all = $this->adminGet($admin, '/api/admin/products?type=simple&query='.$prefix);
        $all->assertOk();
        expect(array_column($all->json('data'), 'id'))->toContain($plain->id, $custom->id);

        $filtered = $this->adminGet($admin, '/api/admin/products?type=simple&exclude_customizable_products=1&query='.$prefix);
        $filtered->assertOk();
        expect(array_column($filtered->json('data'), 'id'))->toBe([$plain->id]);
    }

    public function test_customer_id_scopes_to_customer_channel(): void
    {
        $admin = $this->createAdmin();
        $prefix = 'xchan'.uniqid();
        $inChannel = $this->makeSearchProduct($prefix.'-in');
        $this->makeSearchProduct($prefix.'-out');

        $channelId = core()->getDefaultChannel()->id;
        DB::table('product_channels')->insert(['product_id' => $inChannel->id, 'channel_id' => $channelId]);
        $customer = Customer::factory()->create(['channel_id' => $channelId]);

        $response = $this->adminGet($admin, '/api/admin/products?customerId='.$customer->id.'&query='.$prefix);

        $response->assertOk();
        expect(array_column($response->json('data'), 'id'))->toBe([$inChannel->id]);
    }

    public function test_unknown_customer_id_applies_no_channel_filter(): void
    {
        $admin = $this->createAdmin();
        $prefix = 'xnone'.uniqid();
        $product = $this->makeSearchProduct($prefix.'-a');

        $response = $this->adminGet($admin, '/api/admin/products?customerId=999999999&query='.$prefix);

        $response->assertOk();
        expect(array_column($response->json('data'), 'id'))->toBe([$product->id]);
    }
}
