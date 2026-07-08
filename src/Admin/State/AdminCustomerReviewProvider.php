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
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCustomerReview;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;

class AdminCustomerReviewProvider implements ProviderInterface
{
    protected const DEFAULT_PER_PAGE = 10;

    protected const MAX_PER_PAGE = 50;

    protected const SORTABLE = ['id', 'rating', 'created_at'];

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $isCollection = $operation instanceof GetCollection || $operation instanceof QueryCollection;

        return $isCollection
            ? $this->provideCollection($context)
            : $this->provideItem($uriVariables, $context);
    }

    protected function provideItem(array $uriVariables, array $context): object
    {
        $id = $uriVariables['id'] ?? $context['args']['id'] ?? null;

        if (is_string($id) && str_contains($id, '/')) {
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
            $rows->each(fn (AdminCustomerReview $r) => $r->setRelation('images', collect()));
            $items = $rows;
        } else {
            $items = $rows->map(fn (AdminCustomerReview $r) => $this->toListDto($r))->all();
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

        if (isset($args['rating']) && $args['rating'] !== '' && $args['rating'] !== null) {
            $query->where('rating', (int) $args['rating']);
        }

        if (isset($args['product_id']) && $args['product_id'] !== '' && $args['product_id'] !== null) {
            $query->where('product_id', (int) $args['product_id']);
        }

        if (isset($args['customer_id']) && $args['customer_id'] !== '' && $args['customer_id'] !== null) {
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
            if ($decoded !== false && ctype_digit($decoded)) {
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

        if (is_string($sort) && str_contains($sort, '-')) {
            [$col, $dir] = explode('-', $sort, 2);
            $sort = $col;
            $order = $order ?? $dir;
        }

        $sort = in_array($sort, self::SORTABLE, true) ? $sort : $default;
        $order = strtolower((string) $order) === 'asc' ? 'asc' : 'desc';

        return [$sort, $order];
    }

    protected function toListDto(AdminCustomerReview $r): AdminCustomerReviewListDto
    {
        $dto = new AdminCustomerReviewListDto;
        $dto->id = (int) $r->id;
        $dto->title = $r->title;
        $dto->comment = $r->comment;
        $dto->rating = $r->rating !== null ? (int) $r->rating : null;
        $dto->status = $r->status;
        $dto->name = $r->name;
        $dto->created_at = $r->created_at ? Carbon::parse($r->created_at)->toIso8601String() : null;
        $dto->updated_at = $r->updated_at ? Carbon::parse($r->updated_at)->toIso8601String() : null;
        $dto->product = $this->mapProduct($r);
        $dto->customer = $this->mapCustomer($r);

        return $dto;
    }

    protected function toDetailDto(AdminCustomerReview $r): AdminCustomerReviewDetailDto
    {
        $dto = new AdminCustomerReviewDetailDto;
        $dto->id = (int) $r->id;
        $dto->title = $r->title;
        $dto->comment = $r->comment;
        $dto->rating = $r->rating !== null ? (int) $r->rating : null;
        $dto->status = $r->status;
        $dto->name = $r->name;
        $dto->created_at = $r->created_at ? Carbon::parse($r->created_at)->toIso8601String() : null;
        $dto->updated_at = $r->updated_at ? Carbon::parse($r->updated_at)->toIso8601String() : null;
        $dto->product = $this->mapProduct($r);
        $dto->customer = $this->mapCustomer($r);
        $dto->images = $r->images->map(fn ($img) => [
            'id' => (int) $img->id,
            'path' => $img->path,
            'url' => $img->url,
        ])->all();

        return $dto;
    }

    protected function mapProduct(AdminCustomerReview $r): ?array
    {
        if ($r->product_id === null) {
            return null;
        }

        $product = $r->product;

        return [
            'id' => (int) $r->product_id,
            'name' => $product?->name,
            'sku' => $product?->sku,
        ];
    }

    protected function mapCustomer(AdminCustomerReview $r): ?array
    {
        if ($r->customer_id === null) {
            return null;
        }

        $customer = $r->customer;

        return [
            'id' => (int) $r->customer_id,
            'name' => $customer?->name,
            'email' => $customer?->email,
        ];
    }

    protected function isGraphQL(array $context): bool
    {
        return ! empty($context['graphql_operation_name']);
    }
}
