<?php

namespace Tests\Feature\BagistoApi;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Webkul\Core\Models\Channel;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerGroup;

/**
 * Base test case for all BagistoApi tests.
 *
 * Provides shared storefront key handling, customer authentication,
 * database seeding, and foreign key constraint management.
 */
abstract class BagistoApiTestCase extends TestCase
{
    use DatabaseTransactions;

    /** Default storefront API key for tests */
    protected string $storefrontKey = 'pk_test_1234567890abcdef';

    /** Disable API logging middleware for tests */
    protected $withoutMiddleware = [
        \Webkul\BagistoApi\Http\Middleware\LogApiRequests::class,
    ];

    public function setUp(): void
    {
        parent::setUp();
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
    }

    public function tearDown(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');
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
}
