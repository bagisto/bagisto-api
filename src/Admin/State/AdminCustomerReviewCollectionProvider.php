<?php

namespace Webkul\BagistoApi\Admin\State;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Models\AdminCustomerReview;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminCollectionProvider;

/**
 * GET /api/admin/customers/reviews + adminCustomerReviews GraphQL.
 *
 * Mirrors Webkul\Admin\DataGrids\Customers\ReviewDataGrid filters.
 * Filters: status, rating, product_id, customer_id, created_at range.
 * Sort: id (default desc), rating, created_at.
 */
class AdminCustomerReviewCollectionProvider extends AbstractAdminCollectionProvider
{
    protected function getSortable(): array
    {
        return ['id', 'rating', 'created_at'];
    }

    protected function buildQuery(array $args)
    {
        return DB::table('product_reviews')
            ->leftJoin('products', 'product_reviews.product_id', '=', 'products.id')
            ->leftJoin('customers', 'product_reviews.customer_id', '=', 'customers.id')
            ->select(
                'product_reviews.id',
                'product_reviews.title',
                'product_reviews.comment',
                'product_reviews.rating',
                'product_reviews.status',
                'product_reviews.name',
                'product_reviews.product_id',
                'products.sku as product_sku',
                'product_reviews.customer_id',
                'customers.first_name as customer_first_name',
                'customers.last_name as customer_last_name',
                'customers.email as customer_email',
                'product_reviews.created_at',
                'product_reviews.updated_at',
            );
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['status'])) {
            $query->where('product_reviews.status', (string) $args['status']);
        }

        if (isset($args['rating']) && $args['rating'] !== '' && $args['rating'] !== null) {
            $query->where('product_reviews.rating', (int) $args['rating']);
        }

        if (isset($args['product_id']) && $args['product_id'] !== '' && $args['product_id'] !== null) {
            $query->where('product_reviews.product_id', (int) $args['product_id']);
        }

        if (isset($args['customer_id']) && $args['customer_id'] !== '' && $args['customer_id'] !== null) {
            $query->where('product_reviews.customer_id', (int) $args['customer_id']);
        }

        if (! empty($args['created_at_from'])) {
            $query->where('product_reviews.created_at', '>=', $args['created_at_from']);
        }
        if (! empty($args['created_at_to'])) {
            $query->where('product_reviews.created_at', '<=', $args['created_at_to']);
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $map = [
            'id'         => 'product_reviews.id',
            'rating'     => 'product_reviews.rating',
            'created_at' => 'product_reviews.created_at',
        ];

        $query->orderBy($map[$column] ?? 'product_reviews.id', $direction);
    }

    protected function mapRow(object $row): AdminCustomerReview
    {
        $dto = new AdminCustomerReview;
        $dto->id = (int) $row->id;
        $dto->title = $row->title;
        $dto->comment = $row->comment;
        $dto->rating = $row->rating !== null ? (int) $row->rating : null;
        $dto->status = $row->status;
        $dto->name = $row->name;
        $dto->productId = $row->product_id !== null ? (int) $row->product_id : null;
        $dto->productSku = $row->product_sku;
        $dto->productName = null;
        $dto->customerId = $row->customer_id !== null ? (int) $row->customer_id : null;
        $dto->customerName = trim((string) ($row->customer_first_name ?? '').' '.(string) ($row->customer_last_name ?? '')) ?: null;
        $dto->customerEmail = $row->customer_email;
        $dto->createdAt = $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null;
        $dto->updatedAt = $row->updated_at ? Carbon::parse($row->updated_at)->toIso8601String() : null;

        return $dto;
    }
}
