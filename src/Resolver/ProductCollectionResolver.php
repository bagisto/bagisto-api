<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryCollectionResolverInterface;
use Webkul\BagistoApi\Models\Product;

class ProductCollectionResolver implements QueryCollectionResolverInterface
{
    /**
     * @param  iterable<Product>|null  $collection
     * @param  array<string, mixed>  $context
     * @return iterable<Product>
     */
    public function __invoke(?iterable $collection, array $context): iterable
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

        if (! empty($args['sortKey'])) {
            $sortKey = $args['sortKey'];
            $reverse = $args['reverse'] ?? false;
            $direction = $reverse ? 'desc' : 'asc';

            switch (strtoupper($sortKey)) {
                case 'CREATED_AT':
                    $query->orderBy('created_at', $direction);
                    break;
                case 'UPDATED_AT':
                    $query->orderBy('updated_at', $direction);
                    break;
                case 'SKU':
                    $query->orderBy('sku', $direction);
                    break;
                case 'ID':
                    $query->orderBy('id', $direction);
                    break;
                case 'TYPE':
                    $query->orderBy('type', $direction);
                    break;
                default:
                    $query->orderBy('created_at', $direction);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $limit = $args['first'] ?? 100;
        $query->limit($limit);

        $query->with([
            'attribute_family',
            'images',
            'attribute_values',
            'super_attributes',
            'variants' => function ($q) {
                $q->without(['variants', 'super_attributes', 'attribute_values', 'attribute_family']);
            },
        ]);

        return $query->get();
    }
}
