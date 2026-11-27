<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use PHPUnit\Framework\Attributes\DataProvider;
use Webkul\BagistoApi\Tests\Concerns\CreatesSwatchConfigurableProduct;
use Webkul\BagistoApi\Tests\RestApiTestCase;

/**
 * A configurable product's super attributes carry what the storefront product page renders them with.
 */
class ProductSwatchTest extends RestApiTestCase
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

        $response = $this->publicGet('/api/shop/products/'.$fixture['productId'])->assertOk();

        $attribute = collect($response->json('superAttributes'))->firstWhere('id', $fixture['attributeId']);

        $this->assertNotNull($attribute);
        $this->assertSame($swatchType, $attribute['swatchType']);
        $this->assertSame([$fixture['used']], array_column($attribute['options'], 'id'), 'A disabled variant\'s option is not offered, as on the web.');

        $option = $attribute['options'][0];

        $this->assertSame('Used', $option['label']);
        $this->assertArrayHasKey('swatchAlt', $option);
        $this->assertSame([$fixture['variantId']], $option['products']);

        $this->assertSame($swatchValue === '' ? null : $swatchValue, $option['swatchValue'] === '' ? null : $option['swatchValue']);

        if ($swatchType === 'image') {
            $this->assertStringEndsWith('cache/small/'.$swatchValue, $option['swatchValueUrl']);
        } else {
            $this->assertNull($option['swatchValueUrl']);
        }
    }
}
