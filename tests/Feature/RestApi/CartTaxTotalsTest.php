<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Dto\CartData;
use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Checkout\Models\Cart;

class CartTaxTotalsTest extends RestApiTestCase
{
    public function test_cart_tax_amount_is_read_from_the_tax_total_columns(): void
    {
        $this->seedRequiredData();

        $cart = Cart::factory()->create([
            'tax_total' => 12.5,
            'base_tax_total' => 12.5,
        ]);

        $data = CartData::fromModel($cart->fresh());

        expect($data->baseTaxAmount)->toBe(12.5);
        expect($data->taxAmount)->toBe($data->taxTotal);
        expect($data->taxAmount)->toBeGreaterThan(0.0);
        expect($data->formattedTaxAmount)->toBe($data->formattedTaxTotal);
    }
}
