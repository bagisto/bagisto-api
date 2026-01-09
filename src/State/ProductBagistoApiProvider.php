<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\Product;

/**
 * Handle custom filtering arguments for BagistoApi product queries.
 */
class ProductBagistoApiProvider implements ProviderInterface
{
    public function __construct(
        private readonly Pagination $pagination
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $args = $context['args'] ?? [];

        $query = Product::query();

        if (! empty($args['query'])) {
            $searchTerm = $args['query'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('sku', 'like', "%{$searchTerm}%")
                    ->orWhereHas('attribute_values', function ($attrQuery) use ($searchTerm) {
                        $attrQuery->where('text_value', 'like', "%{$searchTerm}%");
                    });
            });
        }

        $sortKey = $args['sortKey'] ?? 'id';

        $reverse = $args['reverse'] ?? false;

        $channel = $args['channel'] ?? null;

        $locale = $args['locale'] ?? null;
        $direction = $reverse ? 'desc' : 'asc';

        switch (strtoupper($sortKey)) {
            case 'TITLE':
            case 'NAME':
                $query->leftJoin('product_attribute_values as pav_name', function ($join) {
                    $join->on('products.id', '=', 'pav_name.product_id')
                        ->where('pav_name.attribute_id', '=', 2);
                })
                    ->orderBy('pav_name.text_value', $direction)
                    ->select('products.*');

                if ($locale) {
                    $query->where('pav_name.locale', $locale);
                }

                if ($channel) {
                    $query->where('pav_name.channel', $channel);
                }

                break;

            case 'CREATED_AT':
                $query->orderBy('created_at', $direction);

                break;

            case 'UPDATED_AT':
                $query->orderBy('updated_at', $direction);

                break;

            case 'PRICE':
                $query->leftJoin('product_attribute_values as pav_price', function ($join) {
                    $join->on('products.id', '=', 'pav_price.product_id')
                        ->where('pav_price.attribute_id', '=', 11);
                })
                    ->orderBy('pav_price.float_value', $direction)
                    ->select('products.*');

                if ($locale) {
                    $query->where('pav_price.locale', $locale);
                }

                if ($channel) {
                    $query->where('pav_price.channel', $channel);
                }

                break;

            case 'ID':
            default:
                $query->orderBy('id', $direction);
        }

        $first = isset($args['first']) ? (int) $args['first'] : null;

        $last = isset($args['last']) ? (int) $args['last'] : null;

        $after = $args['after'] ?? null;
        $before = $args['before'] ?? null;

        // default page size
        $defaultPerPage = 30;
        $perPage = $first ?? $defaultPerPage;

        $filters = [];
        if (! empty($args['filter'])) {
            if (is_string($args['filter'])) {
                $decodedFilter = json_decode($args['filter'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decodedFilter)) {
                    $filters = $decodedFilter;
                }
            } elseif (is_array($args['filter'])) {
                $filters = $args['filter'];
            }
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
            unset($filters['type']);
        }

        if (! empty($filters['sku'])) {
            $query->where('sku', $filters['sku']);
            unset($filters['sku']);
        }

        if (isset($filters['new'])) {
            $newValue = filter_var($filters['new'], FILTER_VALIDATE_BOOLEAN);
            $query->distinct()
                ->leftJoin('product_flat', 'products.id', '=', 'product_flat.product_id')
                ->where('product_flat.new', $newValue)
                ->select('products.*');
            unset($filters['new']);
        }

        if (isset($filters['featured'])) {
            $featuredValue = filter_var($filters['featured'], FILTER_VALIDATE_BOOLEAN);
            $query->distinct()
                ->leftJoin('product_flat', 'products.id', '=', 'product_flat.product_id')
                ->where('product_flat.featured', $featuredValue)
                ->select('products.*');
            unset($filters['featured']);
        }

        if (! empty($filters['category_id'])) {
            $categoryId = (int) $filters['category_id'];
            $query->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('id', $categoryId);
            });
            unset($filters['category_id']);
        }

        if (! empty($filters['price_from']) || ! empty($filters['price_to'])) {
            $from = isset($filters['price_from']) ? (float) $filters['price_from'] : null;
            $to = isset($filters['price_to']) ? (float) $filters['price_to'] : null;

            $query->whereHas('attribute_values', function ($q) use ($from, $to) {
                $q->where('attribute_id', 11);

                if (! is_null($from) && ! is_null($to)) {
                    $q->whereBetween('float_value', [$from, $to]);
                } elseif (! is_null($from)) {
                    $q->where('float_value', '>=', $from);
                } elseif (! is_null($to)) {
                    $q->where('float_value', '<=', $to);
                }
            });
            unset($filters['price_from'], $filters['price_to']);
        }

        foreach ($filters as $attrCode => $spec) {

            if (in_array($attrCode, ['pageSize', 'first', 'last', 'after', 'before'], true)) {
                continue;
            }

            $term = $spec['match'] ?? $spec;

            $matchType = strtoupper($spec['match_type'] ?? '');

            $query->whereHas('attribute_values', callback: function ($q) use ($attrCode, $term, $matchType, $locale, $channel) {

                $q->whereIn('attribute_id', function ($sub) use ($attrCode) {
                    $sub->select('id')
                        ->from('attributes')
                        ->where('code', $attrCode);
                });

                if ($matchType === 'PARTIAL') {
                    $q->where('text_value', 'like', "%{$term}%");
                    if (! empty($locale)) {
                        $q->where('locale', $locale);
                    }

                    if (! empty($channel)) {
                        $q->where('channel', $channel);
                    }
                } else {

                    if (is_string($term) && strpos($term, ',') !== false) {
                        $values = array_values(array_filter(array_map('trim', explode(',', $term)), fn ($v) => $v !== ''));

                        if (! empty($values)) {
                            $numericValues = array_values(array_filter($values, fn ($v) => is_numeric($v)));
                            $stringValues = array_values(array_filter($values, fn ($v) => ! is_numeric($v)));

                            $q->where(function ($q2) use ($numericValues, $stringValues) {
                                if (! empty($stringValues)) {
                                    $q2->whereIn('text_value', $stringValues);

                                    if (! empty($numericValues)) {
                                        $q2->orWhereIn('integer_value', array_map('intval', $numericValues));
                                    }
                                } else {
                                    $q2->whereIn('integer_value', array_map('intval', $numericValues));
                                }
                            });
                        }
                    } else {
                        $q->where(function ($query) use ($term) {
                            $query->where('text_value', $term)
                                ->orWhere('integer_value', $term);
                        });
                    }
                }

            });
        }

        $query->with([
            'attribute_family',
            'images',
            'attribute_values',
            'super_attributes',
            'variants' => function ($q) {
                $q->without(['variants', 'super_attributes', 'attribute_values', 'attribute_family']);
            },
        ]);

        if (! empty($after)) {
            $decoded = base64_decode($after);
            $offset = is_numeric($decoded) ? (int) $decoded : 0;

            $startIndex = $offset + 1;

            $usePerPage = $first ?? $defaultPerPage;

            $total = $query->count();
            $items = $query->skip($startIndex)->take($usePerPage)->get();

            $currentPage = (int) floor($startIndex / max(1, $usePerPage)) + 1;

            $laravelPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $total,
                $usePerPage,
                $currentPage,
                ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
            );
        } elseif (! empty($before)) {
            $decoded = base64_decode($before);
            $cursorIndex = is_numeric($decoded) ? (int) $decoded : 0;

            $useLast = $last ?? $defaultPerPage;

            $total = $query->count();

            $endExclusive = max(0, $cursorIndex);
            $startIndex = max(0, $endExclusive - $useLast);
            $length = $endExclusive - $startIndex;

            $items = $query->skip($startIndex)->take($length)->get();

            $currentPage = (int) floor($startIndex / max(1, $useLast)) + 1;

            $laravelPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $total,
                $useLast,
                $currentPage,
                ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
            );
        } elseif ($first !== null) {
            $laravelPaginator = $query->paginate($first);
        } elseif ($last !== null) {
            $useLast = $last;
            $total = $query->count();
            $startIndex = max(0, $total - $useLast);
            $items = $query->skip($startIndex)->take($useLast)->get();
            $currentPage = (int) floor($startIndex / max(1, $useLast)) + 1;

            $laravelPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $total,
                $useLast,
                $currentPage,
                ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
            );
        } else {
            $laravelPaginator = $query->paginate($perPage);
        }

        if ($locale || $channel) {
            $collection = $laravelPaginator->getCollection()->map(function ($product) use ($locale, $channel) {
                if ($locale) {
                    $product->locale = $locale;
                }
                if ($channel) {
                    $product->channel = $channel;
                }

                return $product;
            });

            $laravelPaginator->setCollection($collection);
        }

        return new Paginator($laravelPaginator);
    }
}
