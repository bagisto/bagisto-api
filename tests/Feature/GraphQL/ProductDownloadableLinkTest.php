<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\Product\Models\ProductDownloadableLink;

class ProductDownloadableLinkTest extends GraphQLTestCase
{
    /**
     * The productDownloadableLinks query lists the downloadable links, including a new one.
     */
    public function test_product_downloadable_links_query_lists_links(): void
    {
        $link = $this->seedDownloadableLink();

        $response = $this->graphQL(<<<'GQL'
            query {
              productDownloadableLinks(first: 100) {
                edges {
                  node {
                    _id
                    type
                  }
                }
              }
            }
        GQL);

        $response->assertOk();

        $ids = collect($response->json('data.productDownloadableLinks.edges'))->pluck('node._id')->all();

        expect($response->json('errors'))->toBeNull()
            ->and($ids)->toContain($link->id);
    }

    /**
     * The productDownloadableLink query returns one link with its price and download count.
     */
    public function test_product_downloadable_link_query_returns_link(): void
    {
        $link = $this->seedDownloadableLink();

        $response = $this->graphQL(<<<GQL
            query {
              productDownloadableLink(id: "/api/shop/product-downloadable-links/{$link->id}") {
                _id
                type
                price
                downloads
                product {
                  _id
                }
              }
            }
        GQL);

        $response->assertOk();

        expect($response->json('errors'))->toBeNull()
            ->and($response->json('data.productDownloadableLink._id'))->toBe($link->id)
            ->and($response->json('data.productDownloadableLink.type'))->toBe('url')
            ->and((float) $response->json('data.productDownloadableLink.price'))->toBe(5.0)
            ->and($response->json('data.productDownloadableLink.downloads'))->toBe(3)
            ->and($response->json('data.productDownloadableLink.product._id'))->toBe($link->product_id);
    }

    /**
     * The productDownloadableLink query reports an error for an unknown link.
     */
    public function test_product_downloadable_link_query_rejects_an_unknown_id(): void
    {
        $response = $this->graphQL(<<<'GQL'
            query {
              productDownloadableLink(id: "/api/shop/product-downloadable-links/999999999") {
                _id
              }
            }
        GQL);

        $response->assertOk();

        expect($response->json('errors'))->not->toBeEmpty()
            ->and($response->json('data.productDownloadableLink'))->toBeNull();
    }

    /**
     * Create a downloadable product with one URL link.
     */
    private function seedDownloadableLink(): ProductDownloadableLink
    {
        $product = $this->createBaseProduct('downloadable', ['sku' => 'GQL-DL-LINK-'.uniqid()]);

        return ProductDownloadableLink::create([
            'title' => 'GraphQL Link',
            'price' => 5.0,
            'url' => 'https://example.com/file.zip',
            'type' => 'url',
            'downloads' => 3,
            'sort_order' => 1,
            'product_id' => $product->id,
        ]);
    }
}
