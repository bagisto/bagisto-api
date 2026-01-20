<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator as LaravelPaginator;

class ProductGraphQLProvider implements ProviderInterface
{
    public function __construct(
        private readonly Pagination $pagination
    ) {}

    private ?array $attributeTypeCache = null;

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        // Cache attribute types once for the entire request
        $this->attributeTypeCache = \DB::table('attributes')
            ->pluck('type', 'code')
            ->toArray();

        $args = $context['args'] ?? [];

        $query = Product::query();

        $query->whereHas('attribute_values', function ($q) {
            $q->where('attribute_id', 8)
              ->where('boolean_value', 1);
        });

        $query->whereHas('attribute_values', function ($q) {
            $q->where('attribute_id', 7)
              ->where('boolean_value', 1);
        });

        if (! empty($args['query'])) {
            $searchTerm = $args['query'];

            $query->where(function ($q) use ($searchTerm) {
                $q->where('sku', 'like', "%{$searchTerm}%")
                  ->orWhereHas('attribute_values', function ($attr) use ($searchTerm) {
                      $attr->where('text_value', 'like', "%{$searchTerm}%");
                  });
            });
        }

        $sortKey   = strtoupper($args['sortKey'] ?? 'ID');
        $reverse   = (bool) ($args['reverse'] ?? false);
        $direction = $reverse ? 'desc' : 'asc';
        $locale    = $args['locale'] ?? null;
        $channel   = $args['channel'] ?? null;

        switch ($sortKey) {
            case 'TITLE':
            case 'NAME':
                $query->leftJoin('product_attribute_values as pav_name', function ($join) {
                    $join->on('products.id', '=', 'pav_name.product_id')
                         ->where('pav_name.attribute_id', 2);
                })
                ->orderBy('pav_name.text_value', $direction)
                ->orderBy('products.id', $direction)
                ->select('products.*');

                if ($locale) {
                    $query->where('pav_name.locale', $locale);
                }

                if ($channel) {
                    $query->where('pav_name.channel', $channel);
                }
                break;

            case 'CREATED_AT':
                $query->orderBy('products.created_at', $direction)
                      ->orderBy('products.id', $direction);
                break;

            case 'UPDATED_AT':
                $query->orderBy('products.updated_at', $direction)
                      ->orderBy('products.id', $direction);
                break;

            case 'PRICE':
                $user = auth()->user();
                $customerGroup = $user?->getDefaultGroup()
                    ?? app(\Webkul\Customer\Repositories\CustomerGroupRepository::class)
                        ->findOneByField('code', 'guest');

                $query->leftJoin('product_price_indices', function ($join) use ($customerGroup) {
                    $join->on('products.id', '=', 'product_price_indices.product_id')
                         ->where('product_price_indices.customer_group_id', $customerGroup->id);
                })
                ->orderBy('product_price_indices.min_price', $direction)
                ->orderBy('products.id', $direction)
                ->select('products.*');
                break;

            case 'ID':
            default:
                $query->orderBy('products.id', $direction);
        }

        $filters = [];

        if (! empty($args['filter'])) {
            if (is_string($args['filter'])) {
                $decoded = json_decode($args['filter'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $filters = $decoded;
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
            $query->leftJoin('product_flat', 'products.id', '=', 'product_flat.product_id')
                  ->where('product_flat.new', filter_var($filters['new'], FILTER_VALIDATE_BOOLEAN))
                  ->select('products.*')
                  ->distinct('products.id');
            unset($filters['new']);
        }

        if (isset($filters['featured'])) {
            $query->leftJoin('product_flat', 'products.id', '=', 'product_flat.product_id')
                  ->where('product_flat.featured', filter_var($filters['featured'], FILTER_VALIDATE_BOOLEAN))
                  ->select('products.*')
                  ->distinct('products.id');
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
            $to   = isset($filters['price_to']) ? (float) $filters['price_to'] : null;

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

        // Setup for variant filtering with COALESCE
        $prefix = \DB::getTablePrefix();
        $attributeFilters = [];

        // Collect attribute filters that need to be applied
        foreach ($filters as $attrCode => $spec) {
            if (in_array($attrCode, ['pageSize', 'first', 'last', 'after', 'before'], true)) {
                continue;
            }

            $attributeFilters[$attrCode] = [
                'term' => $spec['match'] ?? $spec,
                'matchType' => strtoupper($spec['match_type'] ?? ''),
            ];
        }

        // Apply attribute filters if any exist
        if (! empty($attributeFilters)) {
            // Join variants table once for attribute filtering
            $query->leftJoin('products as variants', \DB::raw('COALESCE('.$prefix.'variants.parent_id, '.$prefix.'variants.id)'), '=', 'products.id');

            // Join all attribute tables needed for both products and variants
            foreach ($attributeFilters as $attrCode => $filterData) {
                $productAlias = 'pav_' . $attrCode . '_product';
                $variantAlias = 'pav_' . $attrCode . '_variant';

                // Join product attribute values
                $query->leftJoin('product_attribute_values as ' . $productAlias, function ($join) use ($productAlias, $attrCode) {
                    $join->on('products.id', '=', $productAlias . '.product_id')
                         ->whereIn($productAlias . '.attribute_id', function ($sub) use ($attrCode) {
                             $sub->select('id')->from('attributes')->where('code', $attrCode);
                         });
                });

                // Join variant attribute values
                $query->leftJoin('product_attribute_values as ' . $variantAlias, function ($join) use ($variantAlias, $attrCode) {
                    $join->on('variants.id', '=', $variantAlias . '.product_id')
                         ->whereIn($variantAlias . '.attribute_id', function ($sub) use ($attrCode) {
                             $sub->select('id')->from('attributes')->where('code', $attrCode);
                         });
                });
            }

            // Apply filters: (all filters on products) OR (all filters on variants)
            $query->where(function ($filterQuery) use ($attributeFilters, $locale, $channel) {
                // Check all filters against product attributes
                $filterQuery->where(function ($productFilterQuery) use ($attributeFilters, $locale, $channel) {
                    foreach ($attributeFilters as $attrCode => $filterData) {
                        $productAlias = 'pav_' . $attrCode . '_product';
                        $term = $filterData['term'];
                        $matchType = $filterData['matchType'];
                        $attributeType = $this->attributeTypeCache[$attrCode] ?? 'text';

                        $productFilterQuery->where(function ($q) use ($term, $matchType, $locale, $channel, $productAlias, $attributeType) {
                            $this->applyAttributeFilter($q, $term, $matchType, $locale, $channel, $productAlias, $attributeType);
                        });
                    }
                });

                // OR check all filters against variant attributes
                $filterQuery->orWhere(function ($variantFilterQuery) use ($attributeFilters, $locale, $channel) {
                    foreach ($attributeFilters as $attrCode => $filterData) {
                        $variantAlias = 'pav_' . $attrCode . '_variant';
                        $term = $filterData['term'];
                        $matchType = $filterData['matchType'];
                        $attributeType = $this->attributeTypeCache[$attrCode] ?? 'text';

                        $variantFilterQuery->where(function ($q) use ($term, $matchType, $locale, $channel, $variantAlias, $attributeType) {
                            $this->applyAttributeFilter($q, $term, $matchType, $locale, $channel, $variantAlias, $attributeType);
                        });
                    }
                });
            });
        }

        $query->select('products.*')->distinct();

        $query->with([
            'attribute_family',
            'images',
            'attribute_values',
            'super_attributes',
            'variants' => fn ($q) =>
                $q->without(['variants', 'super_attributes', 'attribute_values', 'attribute_family']),
        ]);

        $first  = isset($args['first']) ? (int) $args['first'] : null;
        $last   = isset($args['last']) ? (int) $args['last'] : null;
        $after  = $args['after'] ?? null;
        $before = $args['before'] ?? null;

        $limit  = $first ?? $last ?? 30;
        $offset = 0;

        if ($after) {
            $decoded = base64_decode($after, true);
            $offset  = ctype_digit((string) $decoded) ? ((int) $decoded + 1) : 0;
        }

        if ($before) {
            $decoded = base64_decode($before, true);
            $cursor  = ctype_digit((string) $decoded) ? (int) $decoded : 0;
            $offset  = max(0, $cursor - $limit);
        }

        $total = (clone $query)
            ->distinct('products.id')
            ->count('products.id');

        if ($offset > $total) {
            $offset = max(0, $total - $limit);
        }

        $items = $query
            ->distinct('products.id')
            ->offset($offset)
            ->limit($limit)
            ->get();

        $currentPage = $total > 0 ? (int) floor($offset / $limit) + 1 : 1;

        return new Paginator(
            new LaravelPaginator(
                $items,
                $total,
                $limit,
                $currentPage,
                ['path' => request()->url()]
            )
        );
    }


    /**
     * Apply attribute filter logic to a query based on attribute type
     */
    private function applyAttributeFilter($q, $term, $matchType, $locale, $channel, $alias, $attributeType = null)
    {
        // Fallback to text_value if attribute type not provided
        if (!$attributeType) {
            $attributeType = 'text';
        }

        // Apply locale filter if provided
        if ($locale) {
            $q->where($alias . '.locale', $locale);
        }

        // Apply channel filter if provided
        if ($channel) {
            $q->where($alias . '.channel', $channel);
        }

        if ($matchType === 'PARTIAL') {
            $this->applyPartialFilter($q, $term, $alias, $attributeType);
        } else {
            $this->applyExactFilter($q, $term, $alias, $attributeType);
        }
    }

    /**
     * Get the correct column name based on attribute type
     */
    private function getColumnForType($attributeType)
    {
        return match ($attributeType) {
            'text', 'textarea'  => 'text_value',
            'select','multiselect','dropdown' => 'integer_value',
            'decimal', 'price' => 'float_value',
            'integer' => 'integer_value',
            'boolean' => 'boolean_value',
            'datetime' => 'datetime_value',
            'date' => 'date_value',
            'json' => 'json_value',
            default => 'text_value',
        };
    }

    /**
     * Apply partial (LIKE) filter based on attribute type
     */
    private function applyPartialFilter($q, $term, $alias, $attributeType)
    {
        $column = $this->getColumnForType($attributeType);
        $q->where($alias . '.' . $column, 'like', "%{$term}%");
    }

    /**
     * Apply exact match filter based on attribute type
     */
    private function applyExactFilter($q, $term, $alias, $attributeType)
    {
        $column = $this->getColumnForType($attributeType);
       
        if (is_string($term) && str_contains($term, ',')) {
            $values = array_filter(array_map('trim', explode(',', $term)));
            
            // Convert values based on attribute type
            $convertedValues = $this->convertValuesForType($values, $attributeType);
            
            $q->whereIn($alias . '.' . $column, $convertedValues);
        } else {
            $convertedValue = $this->convertValueForType($term, $attributeType);
            
            $q->where($alias . '.' . $column, $convertedValue);
        }
    }

    /**
     * Convert a single value based on attribute type
     */
    private function convertValueForType($value, $attributeType)
    {
        return match ($attributeType) {
            'decimal', 'price' => (float) $value,
            'integer' => (int) $value,
            'boolean' => $value ? '1' : '0',
            default => $value,
        };
    }

    /**
     * Convert multiple values based on attribute type
     */
    private function convertValuesForType($values, $attributeType)
    {
        return match ($attributeType) {
            'decimal', 'price' => array_map('floatval', $values),
            'integer' => array_map('intval', $values),
            'boolean' => array_map(fn($v) => (int) filter_var($v, FILTER_VALIDATE_BOOLEAN), $values),
            default => $values,
        };
    }
}
