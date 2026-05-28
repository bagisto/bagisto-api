<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminProduct;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\Product\Facades\ProductImage;
use Webkul\Product\Models\Product;

/**
 * Provider for the admin product search endpoint.
 *
 * REST: GET /api/admin/products
 * GraphQL: adminProducts query
 *
 * Returns ALL statuses by default (no automatic enabled-only filter — admin
 * needs to see disabled products). Light query — name/sku LIKE search,
 * optional type / status / categoryId filters. No variant fallback, no
 * attribute filters; this endpoint is for picking, not browsing.
 */
class AdminProductProvider implements ProviderInterface
{
    protected const DEFAULT_PER_PAGE = 30;

    protected const MAX_PER_PAGE = 50;

    protected const SORTABLE = ['id', 'sku', 'created_at', 'updated_at'];

    /** Attribute IDs in Bagisto core (constant across installs). */
    protected const ATTR_NAME = 2;

    protected const ATTR_STATUS = 7;

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Paginator
    {
        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $args = $context['args'] ?? [];

        [$perPage, $page] = $this->resolvePaging($args);

        $query = Product::query()
            ->with(['attribute_family', 'images', 'attribute_values']);

        $this->applyFilters($query, $args);
        $this->applySort($query);

        $total = (clone $query)->count('products.id');

        $rows = $query
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get()
            ->map(fn (Product $p) => $this->toAdminProduct($p))
            ->all();

        return new Paginator(
            new LengthAwarePaginator($rows, $total, $perPage, $page, ['path' => request()->url()])
        );
    }

    /**
     * @return array{0: int, 1: int}
     */
    protected function resolvePaging(array $args): array
    {
        if (isset($args['first']) || isset($args['after'])) {
            $perPage = (int) ($args['first'] ?? self::DEFAULT_PER_PAGE);
            $perPage = max(1, min($perPage, self::MAX_PER_PAGE));

            $offset = 0;
            if ($after = $args['after'] ?? null) {
                $decoded = base64_decode($after, true);
                $offset = ctype_digit((string) $decoded) ? ((int) $decoded + 1) : 0;
            }

            return [$perPage, (int) floor($offset / $perPage) + 1];
        }

        $perPage = (int) (request()->query('per_page') ?: self::DEFAULT_PER_PAGE);
        $perPage = max(1, min($perPage, self::MAX_PER_PAGE));
        $page = max(1, (int) (request()->query('page') ?: 1));

        return [$perPage, $page];
    }

    protected function filterValue(array $args, string $key): mixed
    {
        return $args[$key] ?? request()->query($key);
    }

    protected function applyFilters($query, array $args): void
    {
        if ($search = $this->filterValue($args, 'query')) {
            $query->where(function ($q) use ($search) {
                $q->where('products.sku', 'like', '%'.$search.'%')
                    ->orWhereHas('attribute_values', function ($attr) use ($search) {
                        $attr->where('attribute_id', self::ATTR_NAME)
                            ->where('text_value', 'like', '%'.$search.'%');
                    });
            });
        }

        if ($sku = $this->filterValue($args, 'sku')) {
            $query->where('products.sku', $sku);
        }

        if ($type = $this->filterValue($args, 'type')) {
            $query->where('products.type', $type);
        }

        $status = $this->filterValue($args, 'status');
        if ($status !== null && $status !== '') {
            $boolean = (int) filter_var($status, FILTER_VALIDATE_BOOLEAN);
            $query->whereHas('attribute_values', function ($q) use ($boolean) {
                $q->where('attribute_id', self::ATTR_STATUS)
                    ->where('boolean_value', $boolean);
            });
        }

        if ($categoryId = $this->filterValue($args, 'categoryId') ?? $this->filterValue($args, 'category_id')) {
            $categoryId = (int) $categoryId;
            $query->whereHas('categories', fn ($q) => $q->where('id', $categoryId));
        }

        $query->select('products.*')->distinct();
    }

    protected function applySort($query): void
    {
        $sort = request()->query('sort');
        $order = strtolower((string) request()->query('order')) === 'asc' ? 'asc' : 'desc';

        $sort = in_array($sort, self::SORTABLE, true) ? $sort : 'id';
        $query->orderBy('products.'.$sort, $order);
    }

    protected function toAdminProduct(Product $product): AdminProduct
    {
        $row = new AdminProduct;

        $row->id = $product->id;
        $row->sku = $product->sku;
        $row->type = $product->type;
        $row->name = $product->name;

        $statusAttr = $product->attribute_values
            ->firstWhere('attribute_id', self::ATTR_STATUS);
        $row->status = $statusAttr ? (int) $statusAttr->boolean_value : null;

        $price = $this->resolvePrice($product);
        $row->price = $price;
        $row->formattedPrice = $price !== null ? core()->formatPrice($price) : null;

        $row->baseImageUrl = $this->resolveBaseImageUrl($product);

        try {
            $row->isSaleable = (bool) $product->getTypeInstance()->isSaleable();
        } catch (\Throwable $e) {
            $row->isSaleable = false;
        }

        return $row;
    }

    protected function resolvePrice(Product $product): ?float
    {
        try {
            $minimal = $product->getTypeInstance()->getMinimalPrice();
            if ($minimal !== null) {
                return (float) $minimal;
            }
        } catch (\Throwable $e) {
        }

        $row = DB::table('product_attribute_values')
            ->where('product_id', $product->id)
            ->where('attribute_id', 11)
            ->first();

        return $row && $row->float_value !== null ? (float) $row->float_value : null;
    }

    protected function resolveBaseImageUrl(Product $product): ?string
    {
        try {
            $image = ProductImage::getProductBaseImage($product);

            return $image['medium_image_url'] ?? $image['original_image_url'] ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
