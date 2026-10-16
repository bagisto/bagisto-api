<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Webkul\Attribute\Models\Attribute;
use Webkul\BagistoApi\Tests\RestApiTestCase;

class ProductAttributeFileTest extends RestApiTestCase
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

    public function test_downloads_file_attribute_for_storefront_visible_product(): void
    {
        [$product, $attribute] = $this->createFileAttributeValue();

        $response = $this->get('/api/shop/products/'.$product->id.'/attribute-files/'.$attribute->id, [
            ...$this->storefrontHeaders(),
            'Accept' => 'application/octet-stream',
        ]);

        $response->assertOk()->assertStreamedContent('sample file contents');
    }

    public function test_download_rejects_non_file_attribute_types(): void
    {
        [$product, $attribute] = $this->createFileAttributeValue('text');

        $response = $this->get('/api/shop/products/'.$product->id.'/attribute-files/'.$attribute->id, [
            ...$this->storefrontHeaders(),
            'Accept' => 'application/octet-stream',
        ]);

        $response->assertNotFound();
    }

    public function test_download_hides_products_not_visible_in_the_storefront(): void
    {
        [$product, $attribute] = $this->createFileAttributeValue();
        $this->upsertProductAttributeValue($product->id, 'status', 0, null, 'default');

        $response = $this->get('/api/shop/products/'.$product->id.'/attribute-files/'.$attribute->id, [
            ...$this->storefrontHeaders(),
            'Accept' => 'application/octet-stream',
        ]);

        $response->assertNotFound();
    }
}
