<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\GraphQLTestCase;

class ProductQueryTest extends GraphQLTestCase
{
    /**
     * Test querying simple products
     */
    public function test_get_all_simple_products(): void
    {
        // Create a simple product for testing
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
     * Test querying configurable products
     */
    public function test_get_all_configurable_products(): void
    {
        // Create attributes for configurable product
        $attributes = \Webkul\Attribute\Models\Attribute::query()
            ->where('is_configurable', 1)
            ->where('type', 'select')
            ->orderBy('id')
            ->limit(2)
            ->get();

        if ($attributes->isEmpty()) {
            $this->markTestSkipped('No configurable select attributes found.');
        }

        // Create parent configurable product
        $parent = $this->createBaseProduct('configurable', [
            'sku' => 'TEST-CONFIG-QUERY-'.uniqid(),
        ]);
        $this->ensureInventory($parent, 50);
        $this->upsertProductAttributeValue($parent->id, 'weight', 1.5, null, 'default');

        // Create child simple product
        $child = $this->createBaseProduct('simple', [
            'sku'       => 'TEST-CONFIG-CHILD-QUERY-'.uniqid(),
            'parent_id' => $parent->id,
        ]);
        $this->ensureInventory($child, 50);
        $this->upsertProductAttributeValue($child->id, 'manage_stock', 0, null, 'default');
        $this->upsertProductAttributeValue($child->id, 'weight', 1.5, null, 'default');

        DB::table('product_relations')->insert([
            'parent_id' => $parent->id,
            'child_id'  => $child->id,
        ]);

        // Add super attributes
        foreach ($attributes as $attribute) {
            DB::table('product_super_attributes')->insert([
                'product_id'   => $parent->id,
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
     * Test querying virtual products
     */
    public function test_get_all_virtual_products(): void
    {
        // Create a virtual product for testing
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
     * Test querying grouped products
     */
    public function test_get_all_grouped_products(): void
    {
        // Create a grouped product
        $parent = $this->createBaseProduct('grouped', [
            'sku' => 'TEST-GROUPED-QUERY-'.uniqid(),
        ]);
        $this->ensureInventory($parent, 50);

        // Create associated products
        for ($i = 1; $i <= 2; $i++) {
            $associated = $this->createBaseProduct('simple', [
                'sku' => 'TEST-GROUPED-ASSOC-QUERY-'.$parent->id.'-'.$i,
            ]);
            $this->ensureInventory($associated, 50);

            DB::table('product_grouped_products')->insert([
                'product_id'            => $parent->id,
                'associated_product_id' => $associated->id,
                'qty'                   => 1,
                'sort_order'            => $i,
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
     * Test querying downloadable products
     */
    public function test_get_all_downloadable_products(): void
    {
        // Create a downloadable product
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
     * Matches spec: products(filter: "{\"type\": \"booking\"}")
     */
    public function test_get_all_booking_products(): void
    {
        // Create a booking product for testing
        $product = $this->createBaseProduct('booking', [
            'sku' => 'TEST-BOOKING-COLLECTION-'.uniqid(),
        ]);
        $this->ensureInventory($product, 50);

        // Create the booking product record
        $booking = \Webkul\BookingProduct\Models\BookingProduct::query()->create([
            'product_id'           => $product->id,
            'type'                 => 'default',
            'qty'                  => 50,
            'location'             => 'Test Location',
            'show_location'        => 1,
            'available_every_week' => 1,
            'available_from'       => null,
            'available_to'         => null,
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

        // Verify response structure on whatever booking products are present
        $edges = $data['edges'] ?? [];
        if (! empty($edges)) {
            $node = $edges[0]['node'];

            // Verify product-level fields
            $this->assertArrayHasKey('id', $node);
            $this->assertArrayHasKey('name', $node);
            $this->assertArrayHasKey('sku', $node);
            $this->assertSame('booking', $node['type']);
            $this->assertArrayHasKey('urlKey', $node);
            $this->assertArrayHasKey('description', $node);
            $this->assertArrayHasKey('shortDescription', $node);
            $this->assertArrayHasKey('price', $node);
            $this->assertArrayHasKey('specialPrice', $node);

            // Verify bookingProducts connection structure
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

            // Verify images and categories connection structure
            $this->assertArrayHasKey('images', $node);
            $this->assertArrayHasKey('edges', $node['images']);
            $this->assertArrayHasKey('categories', $node);
            $this->assertArrayHasKey('edges', $node['categories']);
        }
    }

    /**
     * Test querying bundle products
     */
    public function test_get_all_bundle_products(): void
    {
        // Create a bundle product
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
}
