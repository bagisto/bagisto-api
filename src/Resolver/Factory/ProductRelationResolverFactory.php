<?php

namespace Webkul\BagistoApi\Resolver\Factory;

use ApiPlatform\GraphQl\Resolver\Factory\ResolverFactoryInterface;
use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use GraphQL\Type\Definition\ResolveInfo;
use Webkul\BagistoApi\Dto\CartData;
use Webkul\BagistoApi\Dto\DownloadLinkOutput;
use Webkul\BagistoApi\Models\Attribute;
use Webkul\BagistoApi\Models\Product;
use Webkul\BagistoApi\Models\ReadCart;
use Webkul\BagistoApi\State\Concerns\ResolvesCursorWindow;
use Webkul\BagistoApi\State\ProductRelationProvider;
use Webkul\Checkout\Models\Cart;

class ProductRelationResolverFactory implements ResolverFactoryInterface
{
    use ResolvesCursorWindow;

    /**
     * Product connection fields that are rebuilt by hand when the inner resolver returns nothing.
     */
    private const RELATION_FIELDS = ['upSells', 'crossSells', 'relatedProducts', 'superAttributes', 'reviews', 'bookingProducts'];

    /**
     * The inner factory.
     */
    private readonly ResolverFactoryInterface $innerFactory;

    /**
     * The relation provider.
     */
    private readonly ProductRelationProvider $relationProvider;

    /**
     * Create a new instance.
     */
    public function __construct(
        ResolverFactoryInterface $innerFactory,
        ProductRelationProvider $relationProvider
    ) {
        $this->innerFactory = $innerFactory;
        $this->relationProvider = $relationProvider;
    }

    /**
     * Wrap the inner resolver with the hand-built connections this package needs.
     */
    public function __invoke(
        ?string $resourceClass = null,
        ?string $rootClass = null,
        ?Operation $operation = null,
        ?PropertyMetadataFactoryInterface $propertyMetadataFactory = null
    ): callable {
        $innerResolver = ($this->innerFactory)(
            $resourceClass,
            $rootClass,
            $operation,
            $propertyMetadataFactory
        );

        return function (?array $source, array $args, $context, ResolveInfo $info) use ($innerResolver, $operation, $resourceClass, $rootClass) {
            if ($this->isCartItemsField($resourceClass, $rootClass, $info, $source)) {
                return $this->cartItemsConnection($source);
            }

            if ($this->isFilterableAttributesField($info, $source)) {
                return $this->filterableAttributesConnection($source);
            }

            if ($this->isEmptyProductRelation($rootClass, $info, $source)) {
                $product = $this->resolveSourceProduct($source, $context);

                if (
                    $product
                    && $operation
                ) {
                    return $this->productRelationConnection($product, $operation, $args, $info);
                }
            }

            return $this->normalizeResult($innerResolver($source, $args, $context, $info));
        };
    }

    /**
     * The `items` field of a cart payload that only carries the cart id.
     */
    private function isCartItemsField(?string $resourceClass, ?string $rootClass, ResolveInfo $info, ?array $source): bool
    {
        $cartClasses = [ReadCart::class, CartData::class];

        return (in_array($resourceClass, $cartClasses, true) || in_array($rootClass, $cartClasses, true))
            && $info->fieldName === 'items'
            && is_array($source)
            && isset($source['id'])
            && is_numeric($source['id']);
    }

    /**
     * The `filterableAttributes` field of a category node.
     */
    private function isFilterableAttributesField(ResolveInfo $info, ?array $source): bool
    {
        return $info->fieldName === 'filterableAttributes'
            && is_array($source)
            && (isset($source['_id']) || isset($source['id']));
    }

    /**
     * A storefront product relation field that the inner resolver left empty.
     */
    private function isEmptyProductRelation(?string $rootClass, ResolveInfo $info, ?array $source): bool
    {
        return $rootClass === Product::class
            && in_array($info->fieldName, self::RELATION_FIELDS)
            && is_array($source)
            && array_key_exists($info->fieldName, $source)
            && ($source[$info->fieldName] === [] || $source[$info->fieldName] === null);
    }

    /**
     * Build the cart items connection from the stored cart.
     */
    private function cartItemsConnection(array $source): array
    {
        $cart = Cart::find($source['id']);

        if (! $cart) {
            return $this->emptyConnection();
        }

        $items = CartData::fromModel($cart)->items ?? [];

        $edges = array_map(fn ($item, $index) => [
            'node' => is_array($item) ? $item : (array) $item,
            'cursor' => base64_encode((string) $index),
        ], $items, array_keys($items));

        return [
            'totalCount' => count($items),
            'edges' => $edges,
            'pageInfo' => $this->pageInfo(count($items)),
        ];
    }

    /**
     * Build the filterable attributes connection of a category.
     */
    private function filterableAttributesConnection(array $source): array
    {
        $rawId = $source['_id'] ?? $source['id'];
        $categoryId = is_numeric($rawId) ? (int) $rawId : (int) basename((string) $rawId);

        $attributes = Attribute::query()
            ->join('category_filterable_attributes as cfa', 'cfa.attribute_id', '=', 'attributes.id')
            ->where('cfa.category_id', $categoryId)
            ->with([
                'options' => fn ($query) => $query->orderBy('sort_order'),
                'translations',
                'options.translations',
            ])
            ->orderBy('attributes.id')
            ->get(['attributes.*']);

        $edges = [];

        foreach ($attributes as $position => $attribute) {
            $edges[] = [
                'node' => [
                    'id' => '/api/shop/attributes/'.$attribute->id,
                    '_id' => (int) $attribute->id,
                    'code' => $attribute->code,
                    'adminName' => $attribute->admin_name,
                    'type' => $attribute->type,
                ],

                'cursor' => base64_encode((string) $position),
            ];
        }

        return [
            'totalCount' => count($edges),
            'edges' => $edges,
            'pageInfo' => $this->pageInfo(count($edges)),
        ];
    }

    /**
     * Find the product a relation field belongs to.
     */
    private function resolveSourceProduct(array $source, mixed $context): ?Product
    {
        if (
            isset($context['source'])
            && $context['source'] instanceof Product
        ) {
            return $context['source'];
        }

        $identifiers = $source['#itemIdentifiers'] ?? null;

        if (
            is_array($identifiers)
            && isset($identifiers['id'])
        ) {
            return Product::find($identifiers['id']);
        }

        return null;
    }

    /**
     * Build a product relation connection from the relation provider.
     */
    private function productRelationConnection(Product $product, Operation $operation, array $args, ResolveInfo $info): mixed
    {
        $paginator = $this->relationProvider->provide($operation, [], [
            'source' => $product,
            'args' => $args,
            'info' => $info,
        ]);

        if (! $paginator instanceof Paginator) {
            return $paginator;
        }

        $total = (int) $paginator->getTotalItems();

        [$offset] = $this->resolveCursorWindow($args, $total, $paginator->count());

        $edges = [];

        foreach ($paginator as $index => $item) {
            $edges[] = [
                'node' => $this->productRelationNode($item),
                'cursor' => base64_encode((string) ($offset + $index)),
            ];
        }

        return [
            'totalCount' => $total,
            'edges' => $edges,

            'pageInfo' => [
                'startCursor' => base64_encode((string) $offset),
                'endCursor' => base64_encode((string) max(0, $offset + $paginator->count() - 1)),
                'hasNextPage' => $offset + $paginator->count() < $total,
                'hasPreviousPage' => $offset > 0,
            ],
        ];
    }

    /**
     * Build one connection node from a relation model.
     */
    private function productRelationNode(object $item): array
    {
        $node = $this->camelCaseKeys($item->toArray());

        if (
            isset($node['eventTickets'])
            && $this->isSequentialArray($node['eventTickets'])
        ) {
            $node['eventTickets'] = $this->listToConnection($node['eventTickets']);
        }

        $node = array_merge($node, $this->modelIdentifiers($item));
        $node['_id'] = $item->id;

        return $node;
    }

    /**
     * Storefront IRI and extra fields the node carries for its model type.
     */
    private function modelIdentifiers(object $item): array
    {
        return match (class_basename($item)) {
            'Product' => [
                'id' => '/api/shop/products/'.$item->id,
                'sku' => $item->sku,
                'baseImageUrl' => $item->getBaseImageUrlAttribute(),
            ],

            'BookingProduct' => ['id' => '/api/shop/booking-products/'.$item->id],

            'Attribute' => [
                'id' => '/api/shop/attributes/'.$item->id,
                'code' => $item->code,
            ],

            'ProductReview' => ['id' => '/api/shop/reviews/'.$item->id],
            default => [],
        };
    }

    /**
     * Turn a plain list into a connection, recursing into translations.
     */
    private function listToConnection(array $list): array
    {
        $edges = [];

        foreach ($list as $index => $item) {
            $node = is_array($item) ? $item : (array) $item;

            if (
                ! array_key_exists('_id', $node)
                && array_key_exists('id', $node)
                && is_numeric($node['id'])
            ) {
                $node['_id'] = (int) $node['id'];
            }

            if (
                array_key_exists('translations', $node)
                && $this->isSequentialArray($node['translations'])
            ) {
                $node['translations'] = $this->listToConnection($node['translations']);
            }

            $edges[] = [
                'node' => $node,
                'cursor' => base64_encode((string) $index),
            ];
        }

        return [
            'totalCount' => count($list),
            'edges' => $edges,
            'pageInfo' => $this->pageInfo(count($list)),
        ];
    }

    /**
     * Recursively convert array keys to camelCase.
     */
    private function camelCaseKeys(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if ($this->isSequentialArray($value)) {
            return array_map(fn ($child) => $this->camelCaseKeys($child), $value);
        }

        $result = [];

        foreach ($value as $key => $child) {
            $result[$this->camelCaseKey((string) $key)] = $this->camelCaseKeys($child);
        }

        return $result;
    }

    /**
     * Convert a snake, kebab or spaced key to camelCase.
     */
    private function camelCaseKey(string $key): string
    {
        $parts = preg_split('/\s+/', trim(mb_strtolower(str_replace(['_', '-', ' '], ' ', $key))));

        if (! $parts) {
            return '';
        }

        $camel = array_shift($parts);

        foreach ($parts as $part) {
            $camel .= mb_convert_case($part, MB_CASE_TITLE, 'UTF-8');
        }

        return $camel;
    }

    /**
     * Whether sequential array.
     */
    private function isSequentialArray(mixed $value): bool
    {
        return is_array($value) && array_values($value) === $value;
    }

    /**
     * Connection with no edges.
     */
    private function emptyConnection(): array
    {
        return [
            'totalCount' => 0,
            'edges' => [],
            'pageInfo' => $this->pageInfo(0),
        ];
    }

    /**
     * Page info for a connection that is returned in one page.
     */
    private function pageInfo(int $count): array
    {
        return [
            'startCursor' => base64_encode('0'),
            'endCursor' => base64_encode((string) max(0, $count - 1)),
            'hasNextPage' => false,
            'hasPreviousPage' => false,
        ];
    }

    /**
     * Download link outputs are serialised as plain arrays.
     */
    private function normalizeResult(mixed $result): mixed
    {
        return $result instanceof DownloadLinkOutput ? (array) $result : $result;
    }
}
