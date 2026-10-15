<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\Queue;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Marketing\Jobs\UpdateCreateSearchTerm;

class ProductSearchTermTest extends RestApiTestCase
{
    public function test_search_records_the_term_for_the_admin(): void
    {
        $this->seedRequiredData();
        Queue::fake();

        $this->publicGet('/api/shop/products?query=mobile+shirt')->assertOk();

        Queue::assertPushed(UpdateCreateSearchTerm::class, function ($job) {
            $data = (fn () => $this->data)->call($job);

            return $data['term'] === 'mobile shirt';
        });
    }

    public function test_search_with_a_filter_is_not_recorded(): void
    {
        $this->seedRequiredData();
        Queue::fake();

        $this->publicGet('/api/shop/products?query=shirt&type=simple')->assertOk();

        Queue::assertNotPushed(UpdateCreateSearchTerm::class);
    }

    public function test_later_pages_are_not_recorded(): void
    {
        $this->seedRequiredData();
        Queue::fake();

        $this->publicGet('/api/shop/products?query=shirt&per_page=1&page=2')->assertOk();

        Queue::assertNotPushed(UpdateCreateSearchTerm::class);
    }

    public function test_listing_without_a_query_is_not_recorded(): void
    {
        $this->seedRequiredData();
        Queue::fake();

        $this->publicGet('/api/shop/products')->assertOk();

        Queue::assertNotPushed(UpdateCreateSearchTerm::class);
    }

    public function test_the_term_and_its_use_count_are_saved(): void
    {
        $this->seedRequiredData();
        config(['queue.default' => 'sync']);

        $this->publicGet('/api/shop/products?query=unique-term-xyz');
        $this->publicGet('/api/shop/products?query=unique-term-xyz');

        $this->assertDatabaseHas('search_terms', [
            'term' => 'unique-term-xyz',
            'uses' => 2,
        ]);
    }
}
