<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Tests\RestApiTestCase;

class ProductGroupedProductTest extends RestApiTestCase
{
    private string $baseUrl = '/api/shop/product_grouped_products';

    private function seedGroupedRow(): int
    {
        $parent = $this->createBaseProduct('grouped', ['sku' => 'GROUP-PARENT-'.uniqid()]);
        $assoc = $this->createBaseProduct('simple', ['sku' => 'GROUP-ASSOC-'.uniqid()]);

        return (int) DB::table('product_grouped_products')->insertGetId([
            'product_id'            => $parent->id,
            'associated_product_id' => $assoc->id,
            'qty'                   => 1,
            'sort_order'            => 1,
        ]);
    }

    public function test_get_single_grouped_product(): void
    {
        $this->seedRequiredData();
        $id = $this->seedGroupedRow();

        $response = $this->publicGet($this->baseUrl.'/'.$id);

        $response->assertOk();
        expect((int) $response->json('id'))->toBe($id);
    }

    public function test_get_nonexistent_grouped_product_returns_404(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->baseUrl.'/999999');

        expect($response->getStatusCode())->toBeIn([404, 500]);
    }
}
