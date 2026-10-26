<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\GraphQL;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerGroup;
use Webkul\Product\Models\ProductReview;

class DetailReadPermissionTest extends AdminApiTestCase
{
    // ====================================================================
    // Detail Queries
    // ====================================================================

    /**
     * The product detail query needs the edit permission.
     */
    public function test_product_detail_query_needs_edit_permission(): void
    {
        $product = $this->createBaseProduct('simple');
        $query = 'query($id: ID!) { adminCatalogProduct(id: $id) { _id } }';
        $variables = ['id' => '/api/admin/catalog/products/'.$product->id];

        $viewer = $this->createAdminWithRolePermissions(['catalog', 'catalog.products']);
        $editor = $this->createAdminWithRolePermissions(['catalog', 'catalog.products', 'catalog.products.edit']);

        $denied = $this->adminGraphQL($query, $variables, $viewer);
        expect($denied->json('data.adminCatalogProduct'))->toBeNull()
            ->and($denied->json('errors'))->not->toBeEmpty();

        $allowed = $this->adminGraphQL($query, $variables, $editor);
        expect($allowed->json('errors'))->toBeNull()
            ->and($allowed->json('data.adminCatalogProduct._id'))->toBe($product->id);
    }

    /**
     * The category detail query needs the edit permission.
     */
    public function test_category_detail_query_needs_edit_permission(): void
    {
        $query = 'query($id: ID!) { adminCategory(id: $id) { _id } }';
        $variables = ['id' => '/api/admin/catalog/categories/1'];

        $viewer = $this->createAdminWithRolePermissions(['catalog', 'catalog.categories']);
        $editor = $this->createAdminWithRolePermissions(['catalog', 'catalog.categories', 'catalog.categories.edit']);

        $denied = $this->adminGraphQL($query, $variables, $viewer);
        expect($denied->json('data.adminCategory'))->toBeNull()
            ->and($denied->json('errors'))->not->toBeEmpty();

        $allowed = $this->adminGraphQL($query, $variables, $editor);
        expect($allowed->json('errors'))->toBeNull()
            ->and($allowed->json('data.adminCategory._id'))->toBe(1);
    }

    /**
     * The review detail query needs the edit permission.
     */
    public function test_review_detail_query_needs_edit_permission(): void
    {
        $this->seedRequiredData();

        $customer = Customer::factory()->create([
            'customer_group_id' => CustomerGroup::where('code', 'general')->first()->id,
            'status' => 1,
        ]);

        $review = ProductReview::factory()->create([
            'product_id' => $this->createBaseProduct('simple')->id,
            'customer_id' => $customer->id,
            'name' => $customer->first_name.' '.$customer->last_name,
            'status' => 'pending',
            'rating' => 4,
        ]);

        $query = 'query($id: ID!) { adminCustomerReview(id: $id) { _id } }';
        $variables = ['id' => '/api/admin/customers/reviews/'.$review->id];

        $viewer = $this->createAdminWithRolePermissions(['customers', 'customers.reviews']);
        $editor = $this->createAdminWithRolePermissions(['customers', 'customers.reviews', 'customers.reviews.edit']);

        $denied = $this->adminGraphQL($query, $variables, $viewer);
        expect($denied->json('data.adminCustomerReview'))->toBeNull()
            ->and($denied->json('errors'))->not->toBeEmpty();

        $allowed = $this->adminGraphQL($query, $variables, $editor);
        expect($allowed->json('errors'))->toBeNull()
            ->and($allowed->json('data.adminCustomerReview._id'))->toBe($review->id);
    }
}
