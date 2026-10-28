<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\User\Models\Role;

class CatalogProductCartOptionsTest extends AdminApiTestCase
{
    protected string $query = <<<'GQL'
        query CartOptions($productId: Int!) {
          adminCatalogProductCartOptions(productId: $productId) {
            productId
            type
            initialPrice
            options
          }
        }
    GQL;

    public function test_query_returns_cart_options(): void
    {
        $admin = $this->createAdmin();
        $product = $this->createBaseProduct('simple');

        $response = $this->adminGraphQL($this->query, ['productId' => $product->id], $admin);

        $response->assertOk();
        expect($response->json('errors'))->toBeNull();
        expect((int) $response->json('data.adminCatalogProductCartOptions.productId'))->toBe($product->id);
        expect($response->json('data.adminCatalogProductCartOptions.type'))->toBe('simple');
        expect($response->json('data.adminCatalogProductCartOptions.initialPrice'))->not->toBeNull();
        expect($response->json('data.adminCatalogProductCartOptions.options'))->toBeArray();
    }

    public function test_unknown_product_returns_error(): void
    {
        $admin = $this->createAdmin();

        $response = $this->adminGraphQL($this->query, ['productId' => 99999999], $admin);

        expect($response->json('errors'))->not->toBeNull();
    }

    public function test_requires_authentication(): void
    {
        $response = $this->adminGraphQL($this->query, ['productId' => 1]);

        expect($response->json('data.adminCatalogProductCartOptions'))->toBeNull();
    }

    public function test_requires_the_catalog_products_permission(): void
    {
        $role = Role::create([
            'name' => 'no-catalog-gql',
            'description' => 'No catalog access',
            'permission_type' => 'custom',
            'permissions' => ['dashboard'],
        ]);
        $admin = $this->createAdmin(['role_id' => $role->id]);
        $product = $this->createBaseProduct('simple');

        $response = $this->adminGraphQL($this->query, ['productId' => $product->id], $admin);

        expect($response->json('errors'))->not->toBeNull();
    }
}
