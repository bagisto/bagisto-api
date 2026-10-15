<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\GraphQl\QueryCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerReviewDetailDto;
use Webkul\BagistoApi\Admin\Dto\AdminCustomerReviewListDto;
use Webkul\BagistoApi\Admin\Models\AdminCustomerReview;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;

class AdminCustomerReviewProvider implements ProviderInterface
{
    protected const DEFAULT_PER_PAGE = 10;

    protected const MAX_PER_PAGE = 50;

    protected const SORTABLE = ['id', 'rating', 'created_at'];

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        AdminReadPermissions::authorize($operation, AdminCustomerReview::class);

        $isCollection = $operation instanceof GetCollection || $operation instanceof QueryCollection;

        return $isCollection
            ? $this->provideCollection($context)
            : $this->provideItem($uriVariables, $context);
    }

    protected function provideItem(array $uriVariables, array $context): object
    {
        $id = $uriVariables['id'] ?? $context['args']['id'] ?? null;

        if (
            is_string($id)
            && str_contains($id, '/')
        ) {
            $id = basename($id);
        }

        $review = AdminCustomerReview::with(['images', 'product', 'customer'])->find((int) $id);

        if (! $review) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.customer.review.not-found'));
        }

        if ($this->isGraphQL($context)) {
            return $review;
        }

        return $this->toDetailDto($review);
    }

    protected function provideCollection(array $context): Paginator
    {
        $args = $context['args'] ?? array_merge(
            request()->query(),
            request()->input('filter') ?? []
        );

        [$page, $perPage] = $this->resolvePaging($args);

        $query = AdminCustomerReview::query()->with(['product', 'customer']);

        $this->applyFilters($query, $args);
        $this->applySort($query, $args);

        $total = (clone $query)->count();
        $rows = $query->offset(($page - 1) * $perPage)->limit($perPage)->get();

        if ($this->isGraphQL($context)) {
            $rows->each(fn (AdminCustomerReview $customerReview) => $customerReview->setRelation('images', collect()));
            $items = $rows;
        } else {
            $items = $rows->map(fn (AdminCustomerReview $customerReview) => $this->toListDto($customerReview))->all();
        }

        return new Paginator(new LengthAwarePaginator(
            $items, $total, $perPage, $page, ['path' => request()->url()]
        ));
    }

    protected function applyFilters($query, array $args): void
    {
        if (! empty($args['status'])) {
            $query->where('status', (string) $args['status']);
        }

        if (
            isset($args['rating'])
            && $args['rating'] !== ''
            && $args['rating'] !== null
        ) {
            $query->where('rating', (int) $args['rating']);
        }

        if (
            isset($args['product_id'])
            && $args['product_id'] !== ''
            && $args['product_id'] !== null
        ) {
            $query->where('product_id', (int) $args['product_id']);
        }

        if (
            isset($args['customer_id'])
            && $args['customer_id'] !== ''
            && $args['customer_id'] !== null
        ) {
            $query->where('customer_id', (int) $args['customer_id']);
        }

        if (! empty($args['created_at_from'])) {
            $query->where('created_at', '>=', $args['created_at_from']);
        }

        if (! empty($args['created_at_to'])) {
            $query->where('created_at', '<=', $args['created_at_to']);
        }
    }

    protected function applySort($query, array $args): void
    {
        [$column, $direction] = $this->resolveSort($args);

        $query->orderBy(in_array($column, self::SORTABLE, true) ? $column : 'id', $direction);
    }

    protected function resolvePaging(array $args): array
    {
        $first = $args['first'] ?? null;
        $perPage = $first !== null
            ? (int) $first
            : (int) ($args['per_page'] ?? self::DEFAULT_PER_PAGE);

        if ($perPage <= 0) {
            $perPage = self::DEFAULT_PER_PAGE;
        }

        $perPage = min($perPage, self::MAX_PER_PAGE);

        if (! empty($args['after'])) {
            $decoded = base64_decode((string) $args['after'], true);
            if (
                $decoded !== false
                && ctype_digit($decoded)
            ) {
                $offset = (int) $decoded + 1;

                return [(int) floor($offset / $perPage) + 1, $perPage];
            }
        }

        return [max(1, (int) ($args['page'] ?? 1)), $perPage];
    }

    protected function resolveSort(array $args): array
    {
        $default = self::SORTABLE[0];

        $sort = $args['sort'] ?? null;
        $order = $args['order'] ?? null;

        if (
            is_string($sort)
            && str_contains($sort, '-')
        ) {
            [$column, $direction] = explode('-', $sort, 2);
            $sort = $column;
            $order = $order ?? $direction;
        }

        $sort = in_array($sort, self::SORTABLE, true) ? $sort : $default;
        $order = strtolower((string) $order) === 'asc' ? 'asc' : 'desc';

        return [$sort, $order];
    }

    protected function toListDto(AdminCustomerReview $customerReview): AdminCustomerReviewListDto
    {
        $dto = new AdminCustomerReviewListDto;
        $dto->id = (int) $customerReview->id;
        $dto->title = $customerReview->title;
        $dto->comment = $customerReview->comment;
        $dto->rating = $customerReview->rating !== null ? (int) $customerReview->rating : null;
        $dto->status = $customerReview->status;
        $dto->name = $customerReview->name;
        $dto->created_at = $customerReview->created_at ? Carbon::parse($customerReview->created_at)->toIso8601String() : null;
        $dto->updated_at = $customerReview->updated_at ? Carbon::parse($customerReview->updated_at)->toIso8601String() : null;
        $dto->product = $this->mapProduct($customerReview);
        $dto->customer = $this->mapCustomer($customerReview);

        return $dto;
    }

    protected function toDetailDto(AdminCustomerReview $customerReview): AdminCustomerReviewDetailDto
    {
        $dto = new AdminCustomerReviewDetailDto;
        $dto->id = (int) $customerReview->id;
        $dto->title = $customerReview->title;
        $dto->comment = $customerReview->comment;
        $dto->rating = $customerReview->rating !== null ? (int) $customerReview->rating : null;
        $dto->status = $customerReview->status;
        $dto->name = $customerReview->name;
        $dto->created_at = $customerReview->created_at ? Carbon::parse($customerReview->created_at)->toIso8601String() : null;
        $dto->updated_at = $customerReview->updated_at ? Carbon::parse($customerReview->updated_at)->toIso8601String() : null;
        $dto->product = $this->mapProduct($customerReview);
        $dto->customer = $this->mapCustomer($customerReview);
        $dto->images = $customerReview->images->map(fn ($image) => [
            'id' => (int) $image->id,
            'path' => $image->path,
            'url' => $image->url,
        ])->all();

        return $dto;
    }

    protected function mapProduct(AdminCustomerReview $customerReview): ?array
    {
        if ($customerReview->product_id === null) {
            return null;
        }

        $product = $customerReview->product;

        return [
            'id' => (int) $customerReview->product_id,
            'name' => $product?->name,
            'sku' => $product?->sku,
        ];
    }

    protected function mapCustomer(AdminCustomerReview $customerReview): ?array
    {
        if ($customerReview->customer_id === null) {
            return null;
        }

        $customer = $customerReview->customer;

        return [
            'id' => (int) $customerReview->customer_id,
            'name' => $customer?->name,
            'email' => $customer?->email,
        ];
    }

    protected function isGraphQL(array $context): bool
    {
        return ! empty($context['graphql_operation_name']);
    }
}
