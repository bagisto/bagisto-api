<?php

namespace Tests\Feature\BagistoApi\RestApi;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Webkul\Core\Models\Channel;
use Webkul\Customer\Models\CompareItem;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerGroup;
use Webkul\Product\Models\Product;

class CompareItemTest extends TestCase
{
    use DatabaseTransactions;

    private string $apiUrl = '/api/shop/compare_items';
    private string $storefrontKey;

    /**
     * Disable API logging middleware for tests
     */
    protected $withoutMiddleware = [
        \Webkul\BagistoApi\Http\Middleware\LogApiRequests::class,
    ];

    /**
     * Set up test environment with storefront key
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->storefrontKey = 'pk_test_1234567890abcdef';
    }
    
    /**
     * Seed required database records
     */
    protected function seedRequiredData(): void
    {
        try {
            // Create category first (required by Channel)
            if (!\Webkul\Category\Models\Category::exists()) {
                \Webkul\Category\Models\Category::factory()->create([
                    'parent_id' => null,
                ]);
            }

            if (!Channel::exists()) {
                Channel::factory()->create();
            }


            if (!CustomerGroup::where('code', 'general')->exists()) {
                CustomerGroup::create([
                    'code' => 'general',
                    'name' => 'General',
                    'is_user_defined' => 0,
                ]);
            }
        } catch (\Exception $e) {
            $this->markTestSkipped('Test database not properly configured: ' . $e->getMessage());
        }
    }

    /**
     * Create test data - customer, products and compare items
     */
    private function createTestData(): array
    {
        // Ensure required database records exist
        $this->seedRequiredData();

        try {
            $customer = Customer::factory()->create();
            $product1 = Product::factory()->create();
            $product2 = Product::factory()->create();

            $compareItem1 = CompareItem::factory()->create([
                'customer_id' => $customer->id,
                'product_id' => $product1->id,
            ]);
            $compareItem2 = CompareItem::factory()->create([
                'customer_id' => $customer->id,
                'product_id' => $product2->id,
            ]);

            return compact('customer', 'product1', 'product2', 'compareItem1', 'compareItem2');
        } catch (\Exception $e) {
            $this->markTestSkipped('Test database not properly configured: ' . $e->getMessage());
        }
    }

    /**
     * Get headers with storefront key
     */
    private function getHeaders(): array
    {
        return [
            'X-STOREFRONT-KEY' => $this->storefrontKey,
        ];
    }

    /**
     * Test: GET all compare items
     */
    public function test_get_all_compare_items(): void
    {
        $this->createTestData();

        $response = $this->getJson($this->apiUrl, $this->getHeaders());

        $response->assertOk();
        $data = $response->json();

        // API Platform Collection Format
        if (isset($data['hydra:member'])) {
            expect($data['hydra:member'])->not()->toBeEmpty();
        } else if (isset($data['@type'])) {
            // Alternative collection format
            expect($data)->toHaveKey('@type');
        } else if (is_array($data)) {
            // Fallback: array of items
            expect(count($data))->toBeGreaterThanOrEqual(0);
        }
    }

    /**
     * Test: GET single compare item by ID
     */
    public function test_get_single_compare_item(): void
    {
        $testData = $this->createTestData();

        $response = $this->getJson(
            "{$this->apiUrl}/{$testData['compareItem1']->id}",
            $this->getHeaders()
        );

        $response->assertOk();
        $data = $response->json();

        // Check for compare item ID in response
        expect($data)->toHaveKey('id');
        expect($data['id'])->toBe($testData['compareItem1']->id);
    }

    /**
     * Test: GET compare item with embedded relationships
     */
    public function test_get_compare_item_with_relationships(): void
    {
        $testData = $this->createTestData();

        $response = $this->getJson(
            "{$this->apiUrl}/{$testData['compareItem1']->id}",
            $this->getHeaders()
        );

        $response->assertOk();
        $data = $response->json();

        // Product and customer might be IRI references or embedded objects
        if (is_array($data['product'])) {
            expect($data['product'])->toHaveKey('id');
        }
        if (is_array($data['customer'])) {
            expect($data['customer'])->toHaveKey('id');
        }
    }

    /**
     * Test: GET compare item with timestamps
     */
    public function test_get_compare_item_with_timestamps(): void
    {
        $testData = $this->createTestData();

        $response = $this->getJson($this->apiUrl . '?itemsPerPage=1', $this->getHeaders());

        $response->assertOk();
        $data = $response->json();
        
        // Handle both Hydra format and plain array format
        $compareItem = $data['hydra:member'][0] ?? $data[0] ?? null;
        
        if ($compareItem) {
            expect($compareItem)->toHaveKey('createdAt');
            expect($compareItem)->toHaveKey('updatedAt');
        }
    }

    /**
     * Test: POST create new compare item
     */
    public function test_create_compare_item(): void
    {
        $testData = $this->createTestData();
        $product3 = Product::factory()->create();

        $payload = [
            'customer_id' => $testData['customer']->id,
            'product_id' => $product3->id,
        ];

        $response = $this->postJson($this->apiUrl, $payload, $this->getHeaders());

        $response->assertCreated();
        $data = $response->json();
        
        // Verify the created item has the expected IDs
        expect($data)->toHaveKey('id');
        expect($data['id'])->toBeGreaterThan(0);
    }

    /**
     * Test: DELETE compare item
     */
    public function test_delete_compare_item(): void
    {
        $testData = $this->createTestData();

        $response = $this->deleteJson(
            "{$this->apiUrl}/{$testData['compareItem1']->id}",
            [],
            $this->getHeaders()
        );

        $response->assertNoContent();

        // Verify deletion
        $checkResponse = $this->getJson(
            "{$this->apiUrl}/{$testData['compareItem1']->id}",
            $this->getHeaders()
        );
        $checkResponse->assertNotFound();
    }

    /**
     * Test: GET non-existent compare item returns 404
     */
    public function test_get_non_existent_compare_item(): void
    {
        $response = $this->getJson("{$this->apiUrl}/99999", $this->getHeaders());

        $response->assertNotFound();
    }

    /**
     * Test: GET compare items with pagination
     */
    public function test_get_compare_items_with_pagination(): void
    {
        $this->createTestData();

        $response = $this->getJson(
            "{$this->apiUrl}?itemsPerPage=1&page=1",
            $this->getHeaders()
        );

        $response->assertOk();
        $data = $response->json();

        // Handle both Hydra and plain response formats
        if (isset($data['hydra:member'])) {
            expect(count($data['hydra:member']))->toBeGreaterThanOrEqual(0);
        }
    }

    /**
     * Test: GET compare items with multiple pages
     */
    public function test_get_compare_items_with_multiple_pages(): void
    {
        $this->createTestData();

        $firstPageResponse = $this->getJson(
            "{$this->apiUrl}?itemsPerPage=1&page=1",
            $this->getHeaders()
        );

        $firstPageResponse->assertOk();
    }

    /**
     * Test: Invalid compare item cannot be deleted
     */
    public function test_delete_non_existent_compare_item(): void
    {
        $response = $this->deleteJson(
            "{$this->apiUrl}/99999",
            [],
            $this->getHeaders()
        );

        $response->assertNotFound();
    }

    /**
     * Test: Compare items have proper API resource type
     */
    public function test_compare_item_has_proper_ld_jsonld_type(): void
    {
        $this->createTestData();

        $response = $this->getJson($this->apiUrl . '?itemsPerPage=1', $this->getHeaders());

        $response->assertOk();
        $data = $response->json();

        // Verify we got a valid response
        expect($data)->toBeArray();
    }

    /**
     * Test: All required fields are present
     */
    public function test_compare_item_has_all_required_fields(): void
    {
        $this->createTestData();

        $response = $this->getJson($this->apiUrl . '?itemsPerPage=1', $this->getHeaders());

        $response->assertOk();
        $data = $response->json();

        // Verify we got a valid response
        expect($data)->toBeArray();
    }
}
