<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Role;

class CatalogProductCartOptionsTest extends AdminApiTestCase
{
    public function test_simple_product_returns_customizable_options_and_initial_price(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');

        $response = $this->adminGet($admin, "/api/admin/catalog/products/{$product->id}/cart-options");

        $response->assertOk();
        $row = $response->json()[0];
        expect($row['productId'])->toBe($product->id);
        expect($row['type'])->toBe('simple');
        expect($row['options'])->toBeArray();
        expect($row['initialPrice'])->not->toBeNull();
    }

    public function test_grouped_product_returns_an_array_of_associated_products(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('grouped');

        $response = $this->adminGet($admin, "/api/admin/catalog/products/{$product->id}/cart-options");

        $response->assertOk();
        $row = $response->json()[0];
        expect($row['type'])->toBe('grouped');
        expect($row['options'])->toBeArray();
        expect($row['initialPrice'])->toBeNull();
    }

    public function test_unknown_product_returns_404(): void
    {
        $admin = $this->createAdmin();

        $this->adminGet($admin, '/api/admin/catalog/products/99999999/cart-options')->assertStatus(404);
    }

    public function test_requires_authentication(): void
    {
        $this->publicGet('/api/admin/catalog/products/1/cart-options')->assertStatus(401);
    }

    public function test_requires_the_catalog_products_permission(): void
    {
        $role = Role::create([
            'name' => 'no-catalog',
            'description' => 'No catalog access',
            'permission_type' => 'custom',
            'permissions' => ['dashboard'],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);
        $product = $this->createBaseProduct('simple');

        $this->adminGet($admin, "/api/admin/catalog/products/{$product->id}/cart-options")->assertStatus(403);
    }
}
