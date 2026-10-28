<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\Attribute\Models\Attribute;
use Webkul\BagistoApi\Models\Product as ApiProduct;
use Webkul\BagistoApi\Tests\GraphQLTestCase;
use Webkul\BookingProduct\Models\BookingProduct;
use Webkul\Product\Models\ProductReview;

class ProductQueryTest extends GraphQLTestCase
{
    /**
     * Test querying simple products.
     */
    public function test_get_all_simple_products(): void
    {
        $product = $this->createBaseProduct('simple', [
            'sku' => 'TEST-SIMPLE-QUERY-'.uniqid(),
        ]);
        $this->ensureInventory($product, 50);

        $query = <<<'GQL'
            query getAllSimpleProducts {
              products(filter: "{\"type\": \"simple\"}") {
                edges {
                  node {
                    id
                    name
                    sku
                    urlKey
                    description
                    shortDescription
                    price
                    specialPrice
                    images(first: 5) {
                      edges {
                        node {
                          id
                          publicPath
                          position
                        }
                      }
                    }
                    attributeValues {
                      edges {
                        node {
                          value
                          attribute {
                            code
                            adminName
                          }
                        }
                      }
                    }
                    categories {
                      edges {
                        node {
                          id
                          translation {
                            name
                          }
                        }
                      }
                    }
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertSuccessful();

        $data = $response->json('data.products');

        $this->assertArrayHasKey('edges', $data);
        $this->assertArrayHasKey('totalCount', $data);
        $this->assertGreaterThan(0, $data['totalCount']);
    }

    /**
     * Test querying configurable products.
     */
    public function test_get_all_configurable_products(): void
    {
        $attributes = Attribute::query()
            ->where('is_configurable', 1)
            ->where('type', 'select')
            ->orderBy('id')
            ->limit(2)
            ->get();

        if ($attributes->isEmpty()) {
            $this->markTestSkipped('No configurable select attributes found.');
        }

        $parent = $this->createBaseProduct('configurable', [
            'sku' => 'TEST-CONFIG-QUERY-'.uniqid(),
        ]);
        $this->ensureInventory($parent, 50);
        $this->upsertProductAttributeValue($parent->id, 'weight', 1.5, null, 'default');

        $child = $this->createBaseProduct('simple', [
            'sku' => 'TEST-CONFIG-CHILD-QUERY-'.uniqid(),
            'parent_id' => $parent->id,
        ]);
        $this->ensureInventory($child, 50);
        $this->upsertProductAttributeValue($child->id, 'manage_stock', 0, null, 'default');
        $this->upsertProductAttributeValue($child->id, 'weight', 1.5, null, 'default');

        DB::table('product_relations')->insert([
            'parent_id' => $parent->id,
            'child_id' => $child->id,
        ]);

        foreach ($attributes as $attribute) {
            DB::table('product_super_attributes')->insert([
                'product_id' => $parent->id,
                'attribute_id' => $attribute->id,
            ]);
        }

        $query = <<<'GQL'
            query getAllConfigurableProducts {
              products(filter: "{\"type\": \"configurable\"}") {
                edges {
                  node {
                    id
                    name
                    sku
                    type
                    combinations
                    superAttributeOptions
                    variants {
                      edges {
                        node {
                          id
                          name
                          sku
                          price
                          attributeValues {
                            edges {
                              node {
                                value
                                attribute {
                                  code
                                  adminName
                                }
                              }
                            }
                          }
                        }
                      }
                    }
                    urlKey
                    description
                    shortDescription
                    minimumPrice
                    images(first: 5) {
                      edges {
                        node {
                          id
                          publicPath
                          position
                        }
                      }
                    }
                    attributeValues {
                      edges {
                        node {
                          value
                          attribute {
                            code
                            adminName
                          }
                        }
                      }
                    }
                    categories {
                      edges {
                        node {
                          id
                          translation {
                            name
                          }
                        }
                      }
                    }
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertSuccessful();

        $data = $response->json('data.products');

        $this->assertArrayHasKey('edges', $data);
        $this->assertArrayHasKey('totalCount', $data);
    }

    /**
     * Test querying virtual products.
     */
    public function test_get_all_virtual_products(): void
    {
        $product = $this->createBaseProduct('virtual', [
            'sku' => 'TEST-VIRTUAL-QUERY-'.uniqid(),
        ]);
        $this->ensureInventory($product, 50);

        $query = <<<'GQL'
            query getAllVirtualProducts {
              products(filter: "{\"type\": \"virtual\"}") {
                edges {
                  node {
                    id
                    name
                    sku
                    type
                    urlKey
                    description
                    shortDescription
                    price
                    specialPrice
                    images(first: 5) {
                      edges {
                        node {
                          id
                          publicPath
                          position
                        }
                      }
                    }
                    attributeValues {
                      edges {
                        node {
                          value
                          attribute {
                            code
                            adminName
                          }
                        }
                      }
                    }
                    categories {
                      edges {
                        node {
                          id
                          translation {
                            name
                          }
                        }
                      }
                    }
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertSuccessful();

        $data = $response->json('data.products');

        $this->assertArrayHasKey('edges', $data);
        $this->assertArrayHasKey('totalCount', $data);
    }

    /**
     * Test querying grouped products.
     */
    public function test_get_all_grouped_products(): void
    {
        $parent = $this->createBaseProduct('grouped', [
            'sku' => 'TEST-GROUPED-QUERY-'.uniqid(),
        ]);
        $this->ensureInventory($parent, 50);

        for ($i = 1; $i <= 2; $i++) {
            $associated = $this->createBaseProduct('simple', [
                'sku' => 'TEST-GROUPED-ASSOC-QUERY-'.$parent->id.'-'.$i,
            ]);
            $this->ensureInventory($associated, 50);

            DB::table('product_grouped_products')->insert([
                'product_id' => $parent->id,
                'associated_product_id' => $associated->id,
                'qty' => 1,
                'sort_order' => $i,
            ]);
        }

        $query = <<<'GQL'
            query getAllGroupedProducts {
              products(filter: "{\"type\": \"grouped\"}") {
                edges {
                  node {
                    id
                    name
                    sku
                    type
                    urlKey
                    description
                    shortDescription
                    groupedProducts {
                      edges {
                        node {
                          id
                          qty
                          sortOrder
                          associatedProduct {
                            id
                            name
                            sku
                            price
                            specialPrice
                            images(first: 3) {
                              edges {
                                node {
                                  id
                                  publicPath
                                }
                              }
                            }
                          }
                        }
                      }
                    }
                    images(first: 5) {
                      edges {
                        node {
                          id
                          publicPath
                          position
                        }
                      }
                    }
                    categories {
                      edges {
                        node {
                          id
                          translation {
                            name
                          }
                        }
                      }
                    }
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertSuccessful();

        $data = $response->json('data.products');

        $this->assertArrayHasKey('edges', $data);
        $this->assertArrayHasKey('totalCount', $data);
    }

    /**
     * Test querying downloadable products.
     */
    public function test_get_all_downloadable_products(): void
    {
        $product = $this->createBaseProduct('downloadable', [
            'sku' => 'TEST-DOWNLOADABLE-QUERY-'.uniqid(),
        ]);
        $this->ensureInventory($product, 50);

        $query = <<<'GQL'
            query getAllDownloadableProducts {
              products(filter: "{\"type\": \"downloadable\"}") {
                edges {
                  node {
                    id
                    name
                    sku
                    type
                    urlKey
                    description
                    shortDescription
                    price
                    specialPrice
                    downloadableLinks {
                      edges {
                        node {
                          id
                          type
                          price
                          downloads
                          sortOrder
                          fileUrl
                          sampleFileUrl
                          translation {
                            title
                          }
                        }
                      }
                    }
                    downloadableSamples {
                      edges {
                        node {
                          id
                          type
                          fileUrl
                          sortOrder
                          translation {
                            title
                          }
                        }
                      }
                    }
                    images(first: 5) {
                      edges {
                        node {
                          id
                          publicPath
                          position
                        }
                      }
                    }
                    attributeValues {
                      edges {
                        node {
                          value
                          attribute {
                            code
                            adminName
                          }
                        }
                      }
                    }
                    categories {
                      edges {
                        node {
                          id
                          translation {
                            name
                          }
                        }
                      }
                    }
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertSuccessful();

        $data = $response->json('data.products');

        $this->assertArrayHasKey('edges', $data);
        $this->assertArrayHasKey('totalCount', $data);
    }

    /**
     * Test querying booking products (collection filter by type)
     * Matches spec: products(filter: "{\"type\": \"booking\"}").
     */
    public function test_get_all_booking_products(): void
    {
        $product = $this->createBaseProduct('booking', [
            'sku' => 'TEST-BOOKING-COLLECTION-'.uniqid(),
        ]);
        $this->ensureInventory($product, 50);

        $booking = BookingProduct::query()->create([
            'product_id' => $product->id,
            'type' => 'default',
            'qty' => 50,
            'location' => 'Test Location',
            'show_location' => 1,
            'available_every_week' => 1,
            'available_from' => null,
            'available_to' => null,
        ]);

        $query = <<<'GQL'
            query getAllBookingProducts {
              products(filter: "{\"type\": \"booking\"}") {
                edges {
                  node {
                    id
                    name
                    sku
                    type
                    urlKey
                    description
                    shortDescription
                    price
                    specialPrice
                    bookingProducts {
                      edges {
                        node {
                          id
                          type
                          qty
                          location
                          showLocation
                          availableEveryWeek
                          availableFrom
                          availableTo
                          createdAt
                          updatedAt
                        }
                      }
                    }
                    images(first: 5) {
                      edges {
                        node {
                          id
                          publicPath
                          position
                        }
                      }
                    }
                    categories {
                      edges {
                        node {
                          id
                          translation {
                            name
                          }
                        }
                      }
                    }
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertSuccessful();

        $json = $response->json();
        if (isset($json['errors'])) {
            $this->fail('GraphQL errors: '.json_encode($json['errors']));
        }

        $data = $response->json('data.products');

        $this->assertArrayHasKey('edges', $data);
        $this->assertArrayHasKey('totalCount', $data);

        $edges = $data['edges'] ?? [];
        if (! empty($edges)) {
            $node = $edges[0]['node'];

            $this->assertArrayHasKey('id', $node);
            $this->assertArrayHasKey('name', $node);
            $this->assertArrayHasKey('sku', $node);
            $this->assertSame('booking', $node['type']);
            $this->assertArrayHasKey('urlKey', $node);
            $this->assertArrayHasKey('description', $node);
            $this->assertArrayHasKey('shortDescription', $node);
            $this->assertArrayHasKey('price', $node);
            $this->assertArrayHasKey('specialPrice', $node);

            $this->assertArrayHasKey('bookingProducts', $node);
            $bookingEdges = $node['bookingProducts']['edges'] ?? [];
            if (! empty($bookingEdges)) {
                $bookingNode = $bookingEdges[0]['node'];
                $this->assertArrayHasKey('id', $bookingNode);
                $this->assertArrayHasKey('type', $bookingNode);
                $this->assertArrayHasKey('qty', $bookingNode);
                $this->assertArrayHasKey('location', $bookingNode);
                $this->assertArrayHasKey('showLocation', $bookingNode);
                $this->assertArrayHasKey('availableEveryWeek', $bookingNode);
                $this->assertArrayHasKey('availableFrom', $bookingNode);
                $this->assertArrayHasKey('availableTo', $bookingNode);
                $this->assertArrayHasKey('createdAt', $bookingNode);
                $this->assertArrayHasKey('updatedAt', $bookingNode);
            }

            $this->assertArrayHasKey('images', $node);
            $this->assertArrayHasKey('edges', $node['images']);
            $this->assertArrayHasKey('categories', $node);
            $this->assertArrayHasKey('edges', $node['categories']);
        }
    }

    /**
     * Test querying bundle products.
     */
    public function test_get_all_bundle_products(): void
    {
        $product = $this->createBaseProduct('bundle', [
            'sku' => 'TEST-BUNDLE-QUERY-'.uniqid(),
        ]);
        $this->ensureInventory($product, 50);

        $query = <<<'GQL'
            query getAllBundleProducts {
              products(filter: "{\"type\": \"bundle\"}") {
                edges {
                  node {
                    id
                    name
                    sku
                    type
                    urlKey
                    description
                    shortDescription
                    minimumPrice
                    bundleOptions {
                      edges {
                        node {
                          id
                          type
                          isRequired
                          sortOrder
                          translation {
                            label
                          }
                          bundleOptionProducts {
                            edges {
                              node {
                                id
                                qty
                                isDefault
                                isUserDefined
                                sortOrder
                                product {
                                  id
                                  name
                                  sku
                                  price
                                  images(first: 3) {
                                    edges {
                                      node {
                                        id
                                        publicPath
                                      }
                                    }
                                  }
                                }
                              }
                            }
                          }
                        }
                      }
                    }
                    images(first: 5) {
                      edges {
                        node {
                          id
                          publicPath
                          position
                        }
                      }
                    }
                    categories {
                      edges {
                        node {
                          id
                          translation {
                            name
                          }
                        }
                      }
                    }
                  }
                }
                totalCount
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertSuccessful();

        $data = $response->json('data.products');

        $this->assertArrayHasKey('edges', $data);
        $this->assertArrayHasKey('totalCount', $data);
    }

    /**
     * url_key is a per-locale (translatable) attribute. A product's slug may
     * exist only in a non-current locale; resolving `product(urlKey:)` must.
     */
    public function test_product_resolves_by_url_key_stored_only_in_another_locale(): void
    {
        $product = $this->createBaseProduct('simple', [
            'sku' => 'URLKEY-XLOCALE-'.uniqid(),
        ]);
        $this->ensureInventory($product, 10);

        $slug = 'x-locale-slug-'.uniqid();
        DB::table('product_attribute_values')->updateOrInsert(
            [
                'product_id' => $product->id,
                'attribute_id' => 3,
                'locale' => 'fr',
                'channel' => null,
            ],
            ['text_value' => $slug],
        );

        $query = <<<GQL
            query {
              product(urlKey: "{$slug}") {
                sku
              }
            }
        GQL;

        $response = $this->graphQL($query);

        $response->assertSuccessful();

        expect($response->json('data.product.sku'))->toBe($product->sku);
    }

    /**
     * Numeric and boolean product fields come back typed, not as strings.
     */
    public function test_product_scalars_are_typed(): void
    {
        $product = $this->createBaseProduct('simple');

        $response = $this->graphQL('query($id: ID!) { product(id: $id) { price featured new status isSaleable isInWishlist reviewsCount averageRating } }', [
            'id' => '/api/shop/products/'.$product->id,
        ]);

        $response->assertOk();

        $data = $response->json('data.product');

        expect($response->json('errors'))->toBeNull()
            ->and(is_int($data['price']) || is_float($data['price']))->toBeTrue()
            ->and($data['featured'])->toBeBool()
            ->and($data['new'])->toBeBool()
            ->and($data['status'])->toBeBool()
            ->and($data['isSaleable'])->toBeTrue()
            ->and($data['isInWishlist'])->toBe(0)
            ->and($data['reviewsCount'])->toBe(0)
            ->and(is_int($data['averageRating']) || is_float($data['averageRating']))->toBeTrue();
    }

    /**
     * A product resolves its sale badge and review summary over GraphQL, on the item and in the list.
     */
    public function test_product_resolves_the_sale_badge_and_review_summary(): void
    {
        $product = $this->createBaseProduct('simple');
        $customer = $this->createCustomer();

        foreach ([4, 5] as $rating) {
            ProductReview::factory()->create([
                'product_id' => $product->id,
                'customer_id' => $customer->id,
                'name' => 'Reviewer',
                'status' => 'approved',
                'rating' => $rating,
            ]);
        }

        $response = $this->graphQL('query($id: ID!) { product(id: $id) { _id onSale averageRating totalRating reviewsCount } products(first: 50) { edges { node { _id averageRating reviewsCount } } } }', [
            'id' => '/api/shop/products/'.$product->id,
        ]);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull()
            ->and((float) $response->json('data.product.averageRating'))->toBe(4.5)
            ->and((int) $response->json('data.product.totalRating'))->toBe(9)
            ->and((int) $response->json('data.product.reviewsCount'))->toBe(2)
            ->and((int) $response->json('data.product.onSale'))->toBe(0);

        $node = collect($response->json('data.products.edges'))->firstWhere('node._id', $product->id);

        expect($node)->not->toBeNull()
            ->and((int) $node['node']['reviewsCount'])->toBe(2);
    }

    /**
     * Reading a select attribute returns the option label without writing it back over the stored option id.
     */
    /**
     * GraphQL and REST both return the color as its option label.
     */
    public function test_color_is_the_option_label_on_both_transports(): void
    {
        $this->seedRequiredData();

        $color = Attribute::query()->where('code', 'color')->with('options')->first();

        if (
            ! $color
            || $color->options->isEmpty()
        ) {
            $this->markTestSkipped('The color attribute has no options.');
        }

        $option = $color->options->first();
        $product = $this->createBaseProduct('simple');

        $this->upsertProductAttributeValue($product->id, 'color', $option->id, null, null);

        $graphQL = $this->graphQL('query($id: ID!) { product(id: $id) { color } }', [
            'id' => '/api/shop/products/'.$product->id,
        ]);

        $rest = $this->getJson('/api/shop/products/'.$product->id, $this->storefrontHeaders());

        $rest->assertOk();

        expect($graphQL->json('errors'))->toBeNull()
            ->and($graphQL->json('data.product.color'))->toBe((string) $option->label)
            ->and($rest->json('color'))->toBe((string) $option->label);
    }

    public function test_select_attribute_read_does_not_overwrite_the_stored_option(): void
    {
        $this->seedRequiredData();

        $color = Attribute::query()->where('code', 'color')->with('options')->first();

        if (
            ! $color
            || $color->options->isEmpty()
        ) {
            $this->markTestSkipped('The color attribute has no options.');
        }

        $option = $color->options->first();
        $product = $this->createBaseProduct('simple');

        $this->upsertProductAttributeValue($product->id, 'color', $option->id, null, null);

        $model = ApiProduct::query()->with('attribute_values.attribute')->find($product->id);

        $stored = $model->attribute_values->firstWhere('attribute_id', $color->id);

        expect($model->color)->toBe((string) $option->label);
        expect((int) $stored->integer_value)->toBe((int) $option->id);
        expect((int) $stored->value)->toBe((int) $option->id);

        $again = ApiProduct::query()->find($product->id);

        expect($again->color)->toBe((string) $option->label);
    }
}
