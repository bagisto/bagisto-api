<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\Queue;
use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Marketing\Jobs\UpdateCreateSearchTerm;

class ProductSearchTermTest extends GraphQLTestCase
{
    /**
     * The query constant.
     */
    private const QUERY = <<<'GQL'
        query ($query: String, $first: Int, $after: String, $filter: String) {
          products(query: $query, first: $first, after: $after, filter: $filter) {
            edges { node { id } }
          }
        }
    GQL;

    /**
     * Test search records the term for the admin.
     */
    public function test_search_records_the_term_for_the_admin(): void
    {
        $this->seedRequiredData();
        Queue::fake();

        $response = $this->graphQL(self::QUERY, [
            'query' => 'mobile shirt',
            'first' => 5,
        ]);

        $this->assertNull($response->json('errors'));

        Queue::assertPushed(UpdateCreateSearchTerm::class, function ($job) {
            $data = (fn () => $this->data)->call($job);

            return $data['term'] === 'mobile shirt'
                && $data['channel_id'] === core()->getCurrentChannel()->id
                && is_int($data['results']);
        });
    }

    /**
     * Test search with a filter is not recorded.
     */
    public function test_search_with_a_filter_is_not_recorded(): void
    {
        $this->seedRequiredData();
        Queue::fake();

        $this->graphQL(self::QUERY, [
            'query' => 'shirt',
            'filter' => '{"type":"simple"}',
        ]);

        Queue::assertNotPushed(UpdateCreateSearchTerm::class);
    }

    /**
     * Test later pages are not recorded.
     */
    public function test_later_pages_are_not_recorded(): void
    {
        $this->seedRequiredData();
        Queue::fake();

        $this->graphQL(self::QUERY, [
            'query' => 'shirt',
            'first' => 1,
            'after' => base64_encode('0'),
        ]);

        Queue::assertNotPushed(UpdateCreateSearchTerm::class);
    }

    /**
     * Test listing without a query is not recorded.
     */
    public function test_listing_without_a_query_is_not_recorded(): void
    {
        $this->seedRequiredData();
        Queue::fake();

        $this->graphQL(self::QUERY, ['first' => 5]);

        Queue::assertNotPushed(UpdateCreateSearchTerm::class);
    }

    /**
     * Test the term and its use count are saved.
     */
    public function test_the_term_and_its_use_count_are_saved(): void
    {
        $this->seedRequiredData();
        config(['queue.default' => 'sync']);

        $this->graphQL(self::QUERY, ['query' => 'unique-term-xyz']);
        $this->graphQL(self::QUERY, ['query' => 'unique-term-xyz']);

        $this->assertDatabaseHas('search_terms', [
            'term' => 'unique-term-xyz',
            'uses' => 2,
        ]);
    }
}
