<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use PHPUnit\Framework\Attributes\DataProvider;
use Webkul\BagistoApi\Tests\Concerns\CreatesSwatchConfigurableProduct;
use Webkul\BagistoApi\Tests\GraphQLTestCase;

/**
 * A configurable product's super attributes carry the swatch data the storefront renders them by.
 */
class ProductSwatchTest extends GraphQLTestCase
{
    use CreatesSwatchConfigurableProduct;

    /**
     * @return array<string, array{0: ?string, 1: string}>
     */
    public static function swatchTypes(): array
    {
        return [
            'dropdown' => ['dropdown', ''],
            'color' => ['color', '#a33333'],
            'image' => ['image', 'attribute_option/red.png'],
            'text' => ['text', 'XL'],
        ];
    }

    #[DataProvider('swatchTypes')]
    public function test_super_attributes_carry_their_swatch_data(?string $swatchType, string $swatchValue): void
    {
        $fixture = $this->createSwatchConfigurableProduct($swatchType, $swatchValue);

        $query = <<<'GQL'
            query ($id: ID!) {
              product(id: $id) {
                combinations
                superAttributeOptions
                configurationConfig
                superAttributes {
                  edges {
                    node {
                      _id
                      code
                      swatchType
                      options {
                        edges {
                          node {
                            _id
                            adminName
                            label
                            swatchAlt
                            sortOrder
                            swatchValue
                            swatchValueUrl
                            translation {
                              label
                            }
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
        GQL;

        $response = $this->graphQL($query, ['id' => '/api/shop/products/'.$fixture['productId']])->assertOk();

        $this->assertNull($response->json('errors'), json_encode($response->json('errors')));

        $attribute = collect($response->json('data.product.superAttributes.edges'))
            ->pluck('node')
            ->firstWhere('_id', $fixture['attributeId']);

        $this->assertNotNull($attribute);
        $this->assertSame($swatchType, $attribute['swatchType']);

        $options = collect($attribute['options']['edges'])->pluck('node');

        $this->assertSame([$fixture['used']], $options->pluck('_id')->all(), 'A disabled variant\'s option is not offered, as on the web.');

        $option = $options->first();

        $this->assertSame('Used', $option['adminName']);
        $this->assertSame('Used', $option['label']);

        $product = $response->json('data.product');

        $this->assertSame(
            [(string) $fixture['variantId'] => [$attribute['code'] => $fixture['used']]],
            json_decode($product['combinations'], true),
        );

        $selector = json_decode($product['superAttributeOptions'], true)[0];

        $this->assertSame($swatchType, $selector['swatchType']);
        $this->assertSame([$fixture['used']], array_column($selector['options'], 'id'));

        $config = json_decode($product['configurationConfig'], true);

        $this->assertSame(['attributes', 'index', 'variant_prices', 'variant_images', 'variant_videos', 'regular', 'final'], array_keys($config));
        $this->assertSame([(string) $fixture['variantId']], array_map('strval', array_keys($config['index'])));

        if ($swatchType === 'image') {
            $this->assertStringEndsWith('cache/small/'.$swatchValue, $option['swatchValueUrl']);
        } else {
            $this->assertNull($option['swatchValueUrl']);
        }
    }
}
