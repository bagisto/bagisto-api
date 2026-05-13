<?php

namespace Webkul\BagistoApi\Resolver\Factory;

use ApiPlatform\GraphQl\Resolver\Factory\ResolverFactoryInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Property\Factory\PropertyMetadataFactoryInterface;
use GraphQL\Type\Definition\ResolveInfo;
use Webkul\BagistoApi\Dto\CartData;
use Webkul\BagistoApi\Models\Product;
use Webkul\BagistoApi\Models\ReadCart;
use Webkul\BagistoApi\State\ProductRelationProvider;

/**
 * Decorator resolver factory that intercepts Product relation field queries
 * and delegates to ProductRelationProvider instead of the default provider
 */
class ProductRelationResolverFactory implements ResolverFactoryInterface
{
    private readonly ResolverFactoryInterface $innerFactory;

    private readonly ProductRelationProvider $relationProvider;

    public function __construct(
        ResolverFactoryInterface $innerFactory,
        ProductRelationProvider $relationProvider
    ) {
        $this->innerFactory = $innerFactory;
        $this->relationProvider = $relationProvider;
    }

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

        $capturedOperation = $operation;

        return function (?array $source, array $args, $context, ResolveInfo $info) use ($innerResolver, $capturedOperation, $resourceClass, $rootClass) {

            // Handle CartData items field - fetch items fresh from database to avoid denormalization issues
            $isCartContext = in_array($resourceClass, [ReadCart::class, CartData::class], true)
                || in_array($rootClass, [ReadCart::class, CartData::class], true);

            if (
                $isCartContext
                && $info->fieldName === 'items'
                && is_array($source)
                && isset($source['id'])
                && is_numeric($source['id'])
            ) {
                $cartId = $source['id'];

                // Fetch items fresh from database
                $cart = \Webkul\Checkout\Models\Cart::find($cartId);
                if ($cart) {
                    // Get fresh CartData with properly populated items
                    $cartData = \Webkul\BagistoApi\Dto\CartData::fromModel($cart);
                    $items = $cartData->items ?? [];

                    // Build CartItemCursorConnection structure
                    $edges = array_map(function ($item, $index) {
                        return [
                            'node'   => is_array($item) ? $item : (array) $item,
                            'cursor' => base64_encode((string) $index),
                        ];
                    }, $items, array_keys($items));

                    return [
                        'totalCount' => count($items),
                        'edges'      => $edges,
                        'pageInfo'   => [
                            'startCursor'     => base64_encode('0'),
                            'endCursor'       => base64_encode((string) max(0, count($items) - 1)),
                            'hasNextPage'     => false,
                            'hasPreviousPage' => false,
                        ],
                    ];
                }

                // Return empty connection if cart not found
                return [
                    'totalCount' => 0,
                    'edges'      => [],
                    'pageInfo'   => [
                        'startCursor'     => base64_encode('0'),
                        'endCursor'       => base64_encode('0'),
                        'hasNextPage'     => false,
                        'hasPreviousPage' => false,
                    ],
                ];
            }

            // Handle Category.filterableAttributes - scope Attribute collection to the
            // category_filterable_attributes pivot instead of returning all attributes.
            if (
                $info->fieldName === 'filterableAttributes'
                && is_array($source)
                && (isset($source['_id']) || isset($source['id']))
            ) {
                $rawId = $source['_id'] ?? $source['id'];
                $categoryId = is_numeric($rawId) ? (int) $rawId : (int) basename((string) $rawId);

                $attributes = \Webkul\BagistoApi\Models\Attribute::query()
                    ->join('category_filterable_attributes as cfa', 'cfa.attribute_id', '=', 'attributes.id')
                    ->where('cfa.category_id', $categoryId)
                    ->with([
                        'options' => fn ($q) => $q->orderBy('sort_order'),
                        'translations',
                        'options.translations',
                    ])
                    ->orderBy('attributes.id')
                    ->get(['attributes.*']);

                $edges = [];
                foreach ($attributes as $i => $attr) {
                    $edges[] = [
                        'node' => [
                            'id'         => '/api/shop/attributes/'.$attr->id,
                            '_id'        => (int) $attr->id,
                            'code'       => $attr->code,
                            'adminName'  => $attr->admin_name,
                            'type'       => $attr->type,
                        ],
                        'cursor' => base64_encode((string) $i),
                    ];
                }

                return [
                    'totalCount' => count($edges),
                    'edges'      => $edges,
                    'pageInfo'   => [
                        'startCursor'     => base64_encode('0'),
                        'endCursor'       => base64_encode((string) max(0, count($edges) - 1)),
                        'hasNextPage'     => false,
                        'hasPreviousPage' => false,
                    ],
                ];
            }

            $relationFields = ['upSells', 'crossSells', 'relatedProducts', 'superAttributes', 'reviews', 'bookingProducts'];

            if (
                in_array($info->fieldName, $relationFields)
                && is_array($source)
                && array_key_exists($info->fieldName, $source)
                && ($source[$info->fieldName] === [] || $source[$info->fieldName] === null)
            ) {

                $product = null;

                if (isset($context['source']) && $context['source'] instanceof Product) {
                    $product = $context['source'];
                }

                if (! $product && isset($source['#itemIdentifiers']) && is_array($source['#itemIdentifiers'])) {
                    $identifiers = $source['#itemIdentifiers'];
                    if (isset($identifiers['id'])) {
                        $product = Product::find($identifiers['id']);
                    }
                }

                if ($product) {
                    $providerContext = [
                        'source' => $product,
                        'args'   => $args,
                        'info'   => $info,
                    ];

                    if ($capturedOperation) {
                        $paginator = $this->relationProvider->provide($capturedOperation, [], $providerContext);

                        if ($paginator instanceof \ApiPlatform\Laravel\Eloquent\Paginator) {
                            $data = [
                                'totalCount' => (int) $paginator->getTotalItems(),
                                'edges'      => [],
                                'pageInfo'   => [
                                    'startCursor'     => base64_encode('0'),
                                    'endCursor'       => base64_encode((string) max(0, $paginator->count() - 1)),
                                    'hasNextPage'     => $paginator->hasNextPage(),
                                    'hasPreviousPage' => $paginator->getCurrentPage() > 1,
                                ],
                            ];

                            $offset = 0;
                            foreach ($paginator as $index => $item) {
                                $raw = $item->toArray();

                                $convertKey = function (string $key): string {
                                    $key = str_replace(['_', '-', ' '], ' ', $key);
                                    $key = mb_strtolower($key);
                                    $parts = preg_split('/\s+/', trim($key));
                                    if (! $parts) {
                                        return '';
                                    }

                                    $first = array_shift($parts);
                                    $camel = $first;
                                    foreach ($parts as $part) {
                                        $camel .= mb_convert_case($part, MB_CASE_TITLE, 'UTF-8');
                                    }

                                    return $camel;
                                };

                                $mapKeys = function ($value) use (&$mapKeys, $convertKey) {
                                    if (! is_array($value)) {
                                        return $value;
                                    }

                                    // Preserve sequential (numeric-indexed) arrays
                                    if (array_values($value) === $value) {
                                        $result = [];
                                        foreach ($value as $v) {
                                            $result[] = $mapKeys($v);
                                        }

                                        return $result;
                                    }

                                    $result = [];
                                    foreach ($value as $k => $v) {
                                        $newKey = $convertKey((string) $k);
                                        $result[$newKey] = $mapKeys($v);
                                    }

                                    return $result;
                                };

                                $node = $mapKeys($raw);

                                $isSequentialArray = static function ($value): bool {
                                    return is_array($value) && array_values($value) === $value;
                                };

                                $listToConnection = function (array $list) use ($isSequentialArray, &$listToConnection): array {
                                    $edges = [];
                                    foreach ($list as $i => $item) {
                                        $node = is_array($item) ? $item : (array) $item;

                                        if (! array_key_exists('_id', $node) && array_key_exists('id', $node) && is_numeric($node['id'])) {
                                            $node['_id'] = (int) $node['id'];
                                        }

                                        // Convert nested collection-like fields to Relay connections when present as lists
                                        if (array_key_exists('translations', $node) && $isSequentialArray($node['translations'])) {
                                            $node['translations'] = $listToConnection($node['translations']);
                                        }

                                        $edges[] = [
                                            'node'   => $node,
                                            'cursor' => base64_encode((string) $i),
                                        ];
                                    }

                                    return [
                                        'totalCount' => count($list),
                                        'edges'      => $edges,
                                        'pageInfo'   => [
                                            'startCursor'     => base64_encode('0'),
                                            'endCursor'       => base64_encode((string) max(0, count($list) - 1)),
                                            'hasNextPage'     => false,
                                            'hasPreviousPage' => false,
                                        ],
                                    ];
                                };

                                // Normalize BookingProduct eventTickets list into a Relay connection shape
                                if (isset($node['eventTickets']) && $isSequentialArray($node['eventTickets'])) {
                                    $node['eventTickets'] = $listToConnection($node['eventTickets']);
                                }

                                // Determine the resource type based on the model class
                                $modelClass = class_basename($item);

                                if ($modelClass === 'Product') {
                                    $node['id'] = '/api/shop/products/'.$item->id;
                                    $node['sku'] = $item->sku;
                                    $node['baseImageUrl'] = env('API_URL').$item->getBaseImageUrlAttribute();

                                } elseif ($modelClass === 'BookingProduct') {
                                    $node['id'] = '/api/shop/booking-products/'.$item->id;

                                } elseif ($modelClass === 'Attribute') {
                                    $node['id'] = '/api/shop/attributes/'.$item->id;
                                    $node['code'] = $item->code;
                                } elseif ($modelClass === 'ProductReview') {
                                    $node['id'] = '/api/shop/reviews/'.$item->id;

                                }

                                $node['_id'] = $item->id;

                                $data['edges'][] = [
                                    'node'   => $node,
                                    'cursor' => base64_encode((string) ($index + $offset)),
                                ];
                            }

                            return $data;
                        }

                        return $paginator;
                    }
                }
            }

            // Skip normalization for DTO objects (non-Eloquent models)
            // This allows mutations to return DTOs without triggering ReadProvider
            $result = $innerResolver($source, $args, $context, $info);
            if ($result !== null && ! ($result instanceof \Illuminate\Database\Eloquent\Model)) {
                if (is_object($result) && ! is_array($result) && class_exists('Webkul\BagistoApi\Dto\DownloadLinkOutput')) {
                    // Return DTOs as-is, they're already properly formatted
                    if ($result instanceof \Webkul\BagistoApi\Dto\DownloadLinkOutput) {
                        return (array) $result;
                    }
                }
            }

            return $result;
        };
    }
}
