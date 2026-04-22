<?php

namespace Webkul\BagistoApi\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Webkul\Attribute\Models\Attribute;
use Webkul\Attribute\Models\AttributeOption;
use Webkul\Core\Models\Channel;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerGroup;
use Webkul\Product\Models\Product;
use Webkul\Product\Models\ProductAttributeValue;

/**
 * Base test case for all BagistoApi tests.
 *
 * Provides shared storefront key handling, customer authentication,
 * database seeding, and foreign key constraint management.
 */
abstract class BagistoApiTestCase extends BagistoApiTest
{
    use DatabaseTransactions;

    /** Storefront API key for tests (resolved dynamically in setUp) */
    protected string $storefrontKey = '';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([
            \Webkul\BagistoApi\Http\Middleware\LogApiRequests::class,
            \Webkul\BagistoApi\Http\Middleware\VerifyStorefrontKey::class,
            \Webkul\BagistoApi\Http\Middleware\VerifyGraphQLStorefrontKey::class,
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $this->storefrontKey = 'pk_test_'.Str::random(32);
    }

    protected function tearDown(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        parent::tearDown();
    }

    /**
     * Get storefront key header (public API access)
     */
    protected function storefrontHeaders(): array
    {
        return [
            'X-STOREFRONT-KEY' => $this->storefrontKey,
        ];
    }

    /**
     * Get headers with storefront key + customer auth token
     */
    protected function authHeaders(Customer $customer): array
    {
        $token = $customer->createToken('test-token')->plainTextToken;

        return [
            'Authorization'    => "Bearer {$token}",
            'X-STOREFRONT-KEY' => $this->storefrontKey,
        ];
    }

    /**
     * Seed required database records (channel, customer group, category)
     */
    protected function seedRequiredData(): void
    {
        try {
            if (! \Webkul\Category\Models\Category::exists()) {
                \Webkul\Category\Models\Category::factory()->create([
                    'parent_id' => null,
                ]);
            }

            if (! Channel::exists()) {
                Channel::factory()->create();
            }

            if (! CustomerGroup::where('code', 'general')->exists()) {
                CustomerGroup::create([
                    'code'            => 'general',
                    'name'            => 'General',
                    'is_user_defined' => 0,
                ]);
            }
        } catch (\Exception $e) {
            $this->markTestSkipped('Test database not properly configured: '.$e->getMessage());
        }
    }

    /**
     * Create a customer and return it
     */
    protected function createCustomer(array $attributes = []): Customer
    {
        $this->seedRequiredData();

        return Customer::factory()->create($attributes);
    }

    /**
     * Create a test customer with a valid token for Bearer authentication
     * Returns an array with customer and token keys
     */
    protected function createTestCustomer(): array
    {
        // Create customer with a token field (same way it's created during registration)
        $customer = $this->createCustomer([
            'token' => md5(uniqid(rand(), true)),
        ]);

        return [
            'customer' => $customer,
            'token'    => $customer->token,
        ];
    }

    protected function getAttributeIdByCode(string $code): int
    {
        $attributeId = Attribute::query()->where('code', $code)->value('id');

        if (! $attributeId) {
            $this->markTestSkipped(sprintf('Required attribute "%s" not found. Run Bagisto seeders for attributes.', $code));
        }

        return (int) $attributeId;
    }

    protected function upsertProductAttributeValue(
        int $productId,
        string $attributeCode,
        mixed $value,
        ?string $locale = 'en',
        ?string $channel = 'default'
    ): void {
        $attribute = Attribute::query()->where('code', $attributeCode)->first();

        if (! $attribute) {
            $this->markTestSkipped(sprintf('Required attribute "%s" not found. Run Bagisto seeders for attributes.', $attributeCode));
        }

        $type = (string) ($attribute->type ?? 'text');
        $field = ProductAttributeValue::$attributeTypeFields[$type] ?? 'text_value';

        $payload = [
            'product_id'    => $productId,
            'attribute_id'  => (int) $attribute->id,
            'locale'        => $locale,
            'channel'       => $channel,
            'text_value'    => null,
            'boolean_value' => null,
            'integer_value' => null,
            'float_value'   => null,
            'datetime_value'=> null,
            'date_value'    => null,
            'json_value'    => null,
        ];

        $normalized = $value;

        if ($field === 'boolean_value') {
            $normalized = (bool) $value;
        } elseif ($field === 'integer_value') {
            $normalized = (int) $value;
        } elseif ($field === 'float_value') {
            $normalized = (float) $value;
        } elseif ($field === 'json_value') {
            $normalized = is_string($value) ? $value : json_encode($value);
        } else {
            $normalized = is_string($value) ? $value : (string) $value;
        }

        $payload[$field] = $normalized;

        ProductAttributeValue::query()->updateOrCreate(
            [
                'product_id'   => $productId,
                'attribute_id' => (int) $attribute->id,
                'locale'       => $locale,
                'channel'      => $channel,
            ],
            $payload
        );
    }

    protected function ensureProductIsSaleable(Product $product, ?float $price = 10.0): void
    {
        $this->upsertProductAttributeValue($product->id, 'sku', $product->sku, null, null);
        $this->upsertProductAttributeValue($product->id, 'name', 'Test '.$product->sku, 'en', 'default');
        $this->upsertProductAttributeValue($product->id, 'url_key', strtolower($product->sku), 'en', 'default');
        $this->upsertProductAttributeValue($product->id, 'status', 1, null, 'default');
        $this->upsertProductAttributeValue($product->id, 'visible_individually', 1, null, 'default');

        if ($price !== null) {
            $this->upsertProductAttributeValue($product->id, 'price', $price, null, 'default');
        }
    }

    protected function ensureInventory(Product $product, int $qty = 50): void
    {
        $inventorySourceId = (int) (DB::table('inventory_sources')->value('id') ?? 0);

        if (! $inventorySourceId) {
            $this->markTestSkipped('No inventory_sources found. Run Bagisto seeders for inventory sources.');
        }

        DB::table('product_inventories')->updateOrInsert(
            [
                'product_id'          => $product->id,
                'inventory_source_id' => $inventorySourceId,
                'vendor_id'           => 0,
            ],
            [
                'qty' => $qty,
            ]
        );

        $channelId = (int) (Channel::query()->value('id') ?? 0);

        if (! $channelId) {
            $this->markTestSkipped('No channels found. Run Bagisto seeders for channels.');
        }

        DB::table('product_inventory_indices')->updateOrInsert(
            [
                'product_id' => $product->id,
                'channel_id' => $channelId,
            ],
            [
                'qty'        => $qty,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    protected function createBaseProduct(string $type, array $overrides = []): Product
    {
        $this->seedRequiredData();

        $attributeFamilyId = (int) (DB::table('attribute_families')->value('id') ?? 1);

        $product = Product::factory()->create([
            'type'                => $type,
            'attribute_family_id' => $attributeFamilyId,
            ...$overrides,
        ]);

        $this->ensureProductIsSaleable($product, 10.0);

        return $product;
    }

    protected function createAttributeOption(int $attributeId, string $label, string $locale = 'en'): int
    {
        /** @var AttributeOption $option */
        $option = AttributeOption::query()->create([
            'attribute_id' => $attributeId,
            'admin_name'   => $label,
            'sort_order'   => 1,
        ]);

        DB::table('attribute_option_translations')->insert([
            'attribute_option_id' => $option->id,
            'locale'              => $locale,
            'label'               => $label,
        ]);

        return (int) $option->id;
    }

    /**
     * Get an existing product with inventory from the database for testing
     * Returns an array with product and inventory_source_id keys
     */
    protected function createTestProduct(): array
    {
        $productWithInventory = DB::table('product_inventories')
            ->select('product_id')
            ->groupBy('product_id')
            ->havingRaw('SUM(qty) > 0')
            ->first();

        $product = $productWithInventory
            ? Product::find($productWithInventory->product_id)
            : null;

        if (! $product) {
            $product = $this->createBaseProduct('simple', [
                'sku' => 'TEST-PRODUCT-'.uniqid(),
            ]);
            $this->ensureInventory($product, 50);
        }

        $inventorySourceId = (int) (DB::table('inventory_sources')->value('id') ?? 1);

        return [
            'product'             => $product,
            'inventory_source_id' => $inventorySourceId,
        ];
    }
}
