<?php

namespace Webkul\BagistoApi\Tests;

use Illuminate\Cache\TaggableStore;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Webkul\Attribute\Models\Attribute;
use Webkul\Attribute\Models\AttributeOption;
use Webkul\BagistoApi\Http\Middleware\LogApiRequests;
use Webkul\BagistoApi\Http\Middleware\VerifyGraphQLStorefrontKey;
use Webkul\BagistoApi\Http\Middleware\VerifyStorefrontKey;
use Webkul\Category\Models\Category;
use Webkul\Core\Models\Channel;
use Webkul\Core\Repositories\CoreConfigRepository;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerGroup;
use Webkul\Product\Models\Product;
use Webkul\Product\Models\ProductAttributeValue;

abstract class BagistoApiTestCase extends BagistoApiTest
{
    use DatabaseTransactions;

    /**
     * The storefront key sent with each request.
     */
    protected string $storefrontKey = '';

    /**
     * Skip the storefront key and logging middleware, relax foreign keys and turn off the repository cache.
     */
    protected function setUp(): void
    {
        parent::setUp();

        ini_set('memory_limit', '2048M');

        $this->withoutMiddleware([
            LogApiRequests::class,
            VerifyStorefrontKey::class,
            VerifyGraphQLStorefrontKey::class,
        ]);
        $this->setForeignKeyChecks(false);
        $this->storefrontKey = 'pk_test_'.Str::random(32);

        config([
            'repository.cache.enabled' => false,
            'repository.cache.repositories' => [],
        ]);
    }

    /**
     * Restore foreign key enforcement after the test.
     */
    protected function tearDown(): void
    {
        $this->setForeignKeyChecks(true);
        parent::tearDown();
    }

    /**
     * Switch foreign key enforcement on or off for the session on any supported database.
     */
    protected function setForeignKeyChecks(bool $enabled): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('SET session_replication_role = '.($enabled ? 'DEFAULT' : 'replica'));

            return;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS='.($enabled ? '1' : '0'));
    }

    /**
     * Flush the core config cache tag when the store supports tags, never the whole store.
     */
    protected function forgetCoreConfigCache(): void
    {
        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(CoreConfigRepository::class)->flush();
        }
    }

    /**
     * Build the headers for a guest storefront request.
     */
    protected function storefrontHeaders(): array
    {
        return [
            'X-STOREFRONT-KEY' => $this->storefrontKey,
        ];
    }

    /**
     * Build the headers for a request authenticated as the given customer.
     */
    protected function authHeaders(Customer $customer): array
    {
        $token = $customer->createToken('test-token')->plainTextToken;

        return [
            'Authorization' => "Bearer {$token}",
            'X-STOREFRONT-KEY' => $this->storefrontKey,
        ];
    }

    /**
     * Seed the category, channel and general customer group the tests rely on, skipping when it fails.
     */
    protected function seedRequiredData(): void
    {
        try {
            if (! Category::exists()) {
                Category::factory()->create([
                    'parent_id' => null,
                ]);
            }

            if (! Channel::exists()) {
                Channel::factory()->create();
            }

            if (! CustomerGroup::where('code', 'general')->exists()) {
                CustomerGroup::create([
                    'code' => 'general',
                    'name' => 'General',
                    'is_user_defined' => 0,
                ]);
            }
        } catch (\Exception $e) {
            $this->markTestSkipped('Test database not properly configured: '.$e->getMessage());
        }
    }

    /**
     * Create a customer after seeding the required data.
     */
    protected function createCustomer(array $attributes = []): Customer
    {
        $this->seedRequiredData();

        return Customer::factory()->create($attributes);
    }

    /**
     * Create a customer and return it together with a fresh API token.
     */
    protected function createTestCustomer(): array
    {
        $customer = $this->createCustomer();

        return [
            'customer' => $customer,
            'token' => $customer->createToken('test-token')->plainTextToken,
        ];
    }

    /**
     * Get an attribute's id by its code, skipping the test when it is missing.
     */
    protected function getAttributeIdByCode(string $code): int
    {
        $attributeId = Attribute::query()->where('code', $code)->value('id');

        if (! $attributeId) {
            $this->markTestSkipped(sprintf('Required attribute "%s" not found. Run Bagisto seeders for attributes.', $code));
        }

        return (int) $attributeId;
    }

    /**
     * Insert or update a product attribute value in the column matching the attribute's type.
     */
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
            'product_id' => $productId,
            'attribute_id' => (int) $attribute->id,
            'locale' => $locale,
            'channel' => $channel,
            'text_value' => null,
            'boolean_value' => null,
            'integer_value' => null,
            'float_value' => null,
            'datetime_value' => null,
            'date_value' => null,
            'json_value' => null,
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
                'product_id' => $productId,
                'attribute_id' => (int) $attribute->id,
                'locale' => $locale,
                'channel' => $channel,
            ],
            $payload
        );
    }

    /**
     * Give the product the attribute values and channel link it needs to be saleable.
     */
    protected function ensureProductIsSaleable(Product $product, ?float $price = 10.0): void
    {
        $this->upsertProductAttributeValue($product->id, 'sku', $product->sku, null, null);
        $this->upsertProductAttributeValue($product->id, 'name', 'Test '.$product->sku, 'en', 'default');
        $this->upsertProductAttributeValue($product->id, 'url_key', strtolower($product->sku), 'en', 'default');
        $this->upsertProductAttributeValue($product->id, 'status', 1, null, 'default');
        $this->upsertProductAttributeValue($product->id, 'visible_individually', 1, null, 'default');
        $this->upsertProductAttributeValue($product->id, 'guest_checkout', 1, null, 'default');

        DB::table('product_channels')->insertOrIgnore([
            'product_id' => $product->id,
            'channel_id' => core()->getCurrentChannel()->id,
        ]);

        if ($price !== null) {
            $this->upsertProductAttributeValue($product->id, 'price', $price, null, 'default');
        }
    }

    /**
     * Set the product's stock in the first inventory source and the channel inventory index.
     */
    protected function ensureInventory(Product $product, int $qty = 50): void
    {
        $inventorySourceId = (int) (DB::table('inventory_sources')->value('id') ?? 0);

        if (! $inventorySourceId) {
            $this->markTestSkipped('No inventory_sources found. Run Bagisto seeders for inventory sources.');
        }

        DB::table('product_inventories')->updateOrInsert(
            [
                'product_id' => $product->id,
                'inventory_source_id' => $inventorySourceId,
                'vendor_id' => 0,
            ], [
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
            ], [
                'qty' => $qty,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Create a saleable product of the given type.
     */
    protected function createBaseProduct(string $type, array $overrides = []): Product
    {
        $this->seedRequiredData();

        $attributeFamilyId = (int) (DB::table('attribute_families')->value('id') ?? 1);

        $product = Product::factory()->create([
            'type' => $type,
            'attribute_family_id' => $attributeFamilyId,
            ...$overrides,
        ]);

        $this->ensureProductIsSaleable($product, 10.0);

        return $product;
    }

    /**
     * Create an attribute option with a translated label and return its id.
     */
    protected function createAttributeOption(int $attributeId, string $label, string $locale = 'en'): int
    {
        $option = AttributeOption::query()->create([
            'attribute_id' => $attributeId,
            'admin_name' => $label,
            'sort_order' => 1,
        ]);

        DB::table('attribute_option_translations')->insert([
            'attribute_option_id' => $option->id,
            'locale' => $locale,
            'label' => $label,
        ]);

        return (int) $option->id;
    }

    /**
     * Find a product in stock or create one, and return it with an inventory source id.
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
            'product' => $product,
            'inventory_source_id' => $inventorySourceId,
        ];
    }
}
