<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\Product;

/**
 * Custom provider for Product relation fields (upSells, crossSells, relatedProducts)
 * Intercepts relation queries and returns only the related products, not all products
 */
class ProductRelationProvider implements ProviderInterface
{
    public function __construct(
        private readonly Pagination $pagination
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $source = $context['source'] ?? null;
        $args = $context['args'] ?? [];
        $info = $context['info'] ?? null;

        if (! $source instanceof Product || ! $info) {
            return null;
        }

        $fieldName = $info->fieldName ?? null;

        $relationMethods = [
            'upSells'         => 'up_sells',
            'crossSells'      => 'cross_sells',
            'relatedProducts' => 'related_products',
            'superAttributes' => 'super_attributes',
            'reviews'         => 'reviews',
        ];

        if (! isset($relationMethods[$fieldName])) {
            return null;
        }

        $relationMethod = $relationMethods[$fieldName];

        $relationBuilder = $source->{$relationMethod}();

        $first = isset($args['first']) ? (int) $args['first'] : null;
        $last = isset($args['last']) ? (int) $args['last'] : null;
        $after = $args['after'] ?? null;
        $before = $args['before'] ?? null;

        $defaultPerPage = 30;
        $perPage = $first ?? $defaultPerPage;

        if (! empty($after)) {
            $decoded = base64_decode($after);
            $offset = is_numeric($decoded) ? (int) $decoded : 0;

            $startIndex = $offset + 1;

            $usePerPage = $first ?? $defaultPerPage;

            $total = $relationBuilder->count();
            $items = $relationBuilder->skip($startIndex)->take($usePerPage)->get();

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

            $total = $relationBuilder->count();

            $endExclusive = max(0, $cursorIndex);
            $startIndex = max(0, $endExclusive - $useLast);
            $length = $endExclusive - $startIndex;

            $items = $relationBuilder->skip($startIndex)->take($length)->get();

            $currentPage = (int) floor($startIndex / max(1, $useLast)) + 1;

            $laravelPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $total,
                $useLast,
                $currentPage,
                ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
            );
        } elseif ($first !== null) {
            $laravelPaginator = $relationBuilder->paginate($first);
        } elseif ($last !== null) {
            $useLast = $last;
            $total = $relationBuilder->count();
            $startIndex = max(0, $total - $useLast);
            $items = $relationBuilder->skip($startIndex)->take($useLast)->get();
            $currentPage = (int) floor($startIndex / max(1, $useLast)) + 1;

            $laravelPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $total,
                $useLast,
                $currentPage,
                ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
            );
        } else {
            $laravelPaginator = $relationBuilder->paginate($perPage);
        }

        if (isset($source->locale) || isset($source->channel)) {
            $collection = $laravelPaginator->getCollection()->map(function ($product) use ($source) {
                if (isset($source->locale)) {
                    $product->locale = $source->locale;
                }
                if (isset($source->channel)) {
                    $product->channel = $source->channel;
                }

                return $product;
            });

            $laravelPaginator->setCollection($collection);
        }

        $paginator = new Paginator($laravelPaginator);

        return $paginator;
    }
}
