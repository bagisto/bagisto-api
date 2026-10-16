<?php

namespace Webkul\BagistoApi\Tests\Feature\GraphQL;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webkul\Attribute\Models\Attribute;
use Webkul\BagistoApi\Tests\GraphQLTestCase;

class ProductAttributeFileTest extends GraphQLTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app['url']->forceRootUrl('http://localhost');
        Storage::fake();
    }

    private function createFileAttributeValue(string $type = 'file'): array
    {
        $this->seedRequiredData();
        $product = $this->createBaseProduct('simple');
        $attribute = Attribute::factory()->create([
            'code' => 'download_'.uniqid(),
            'type' => $type,
        ]);
        $path = 'products/'.$product->id.'/manual.pdf';
        Storage::put($path, 'sample file contents');

        DB::table('product_attribute_values')->insert([
            'product_id' => $product->id,
            'attribute_id' => $attribute->id,
            'locale' => null,
            'channel' => null,
            'text_value' => $path,
        ]);

        return [$product, $attribute, $path];
    }

    private function attributeFileQuery(): string
    {
        return <<<'GQL'
            query productAttributeFile($productId: Int!, $attributeId: Int!) {
              productAttributeFile(productId: $productId, attributeId: $attributeId) {
                productId
                attributeId
                fileName
                mimeType
                size
                content
              }
            }
        GQL;
    }

    public function test_returns_file_attribute_base64_over_graphql(): void
    {
        [$product, $attribute] = $this->createFileAttributeValue();

        $response = $this->graphQL($this->attributeFileQuery(), [
            'productId' => $product->id,
            'attributeId' => $attribute->id,
        ]);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();

        $data = $response->json('data.productAttributeFile');

        expect((int) $data['productId'])->toBe($product->id)
            ->and((int) $data['attributeId'])->toBe($attribute->id)
            ->and($data['fileName'])->toBe('manual.pdf')
            ->and(base64_decode($data['content']))->toBe('sample file contents');
    }

    public function test_graphql_query_rejects_non_file_attribute_types(): void
    {
        [$product, $attribute] = $this->createFileAttributeValue('text');

        $response = $this->graphQL($this->attributeFileQuery(), [
            'productId' => $product->id,
            'attributeId' => $attribute->id,
        ]);

        $response->assertOk();
        expect($response->json('errors'))->not->toBeNull();
    }

    public function test_graphql_query_hides_products_not_visible_in_the_storefront(): void
    {
        [$product, $attribute] = $this->createFileAttributeValue();
        $this->upsertProductAttributeValue($product->id, 'status', 0, null, 'default');

        $response = $this->graphQL($this->attributeFileQuery(), [
            'productId' => $product->id,
            'attributeId' => $attribute->id,
        ]);

        $response->assertOk();
        expect($response->json('errors'))->not->toBeNull();
    }
}
