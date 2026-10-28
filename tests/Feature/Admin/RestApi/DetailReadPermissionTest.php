<?php

namespace Webkul\BagistoApi\Tests\Feature\Admin\RestApi;

use Webkul\BagistoApi\Tests\AdminApiTestCase;
use Webkul\Customer\Models\Customer;
use Webkul\Customer\Models\CustomerGroup;
use Webkul\Product\Models\ProductReview;

class DetailReadPermissionTest extends AdminApiTestCase
{
    // ====================================================================
    // Products
    // ====================================================================

    /**
     * Reading one product needs the edit permission, as core opens it only on the edit screen.
     */
    public function test_product_detail_needs_edit_permission(): void
    {
        $product = $this->createBaseProduct('simple');

        $viewer = $this->createAdminWithRolePermissions(['catalog', 'catalog.products']);
        $editor = $this->createAdminWithRolePermissions(['catalog', 'catalog.products', 'catalog.products.edit']);

        $this->adminGet($viewer, '/api/admin/catalog/products')->assertOk();
        $this->adminGet($viewer, '/api/admin/catalog/products/'.$product->id)->assertStatus(403);
        $this->adminGet($editor, '/api/admin/catalog/products/'.$product->id)->assertOk();
    }

    /**
     * Reading a product's inventories needs the edit permission.
     */
    public function test_product_inventories_need_edit_permission(): void
    {
        $product = $this->createBaseProduct('simple');

        $viewer = $this->createAdminWithRolePermissions(['catalog', 'catalog.products']);
        $editor = $this->createAdminWithRolePermissions(['catalog', 'catalog.products', 'catalog.products.edit']);

        $this->adminGet($viewer, '/api/admin/catalog/products/'.$product->id.'/inventories')->assertStatus(403);
        $this->adminGet($editor, '/api/admin/catalog/products/'.$product->id.'/inventories')->assertOk();
    }

    // ====================================================================
    // Categories
    // ====================================================================

    /**
     * Reading one category needs the edit permission.
     */
    public function test_category_detail_needs_edit_permission(): void
    {
        $viewer = $this->createAdminWithRolePermissions(['catalog', 'catalog.categories']);
        $editor = $this->createAdminWithRolePermissions(['catalog', 'catalog.categories', 'catalog.categories.edit']);

        $this->adminGet($viewer, '/api/admin/catalog/categories')->assertOk();
        $this->adminGet($viewer, '/api/admin/catalog/categories/1')->assertStatus(403);
        $this->adminGet($editor, '/api/admin/catalog/categories/1')->assertOk();
    }

    // ====================================================================
    // Reviews
    // ====================================================================

    /**
     * Reading one review needs the edit permission.
     */
    public function test_review_detail_needs_edit_permission(): void
    {
        $review = $this->seedReview();

        $viewer = $this->createAdminWithRolePermissions(['customers', 'customers.reviews']);
        $editor = $this->createAdminWithRolePermissions(['customers', 'customers.reviews', 'customers.reviews.edit']);

        $this->adminGet($viewer, '/api/admin/customers/reviews')->assertOk();
        $this->adminGet($viewer, '/api/admin/customers/reviews/'.$review->id)->assertStatus(403);
        $this->adminGet($editor, '/api/admin/customers/reviews/'.$review->id)->assertOk();
    }

    /**
     * Seed a review on a fresh product by a fresh customer.
     */
    protected function seedReview(): ProductReview
    {
        $this->seedRequiredData();

        $product = $this->createBaseProduct('simple');

        $customer = Customer::factory()->create([
            'customer_group_id' => CustomerGroup::where('code', 'general')->first()->id,
            'status' => 1,
        ]);

        return ProductReview::factory()->create([
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'name' => $customer->first_name.' '.$customer->last_name,
            'status' => 'pending',
            'rating' => 4,
        ]);
    }
}
