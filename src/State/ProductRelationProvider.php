<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\Product;
use Webkul\BagistoApi\State\Concerns\ResolvesCursorWindow;

class ProductRelationProvider implements ProviderInterface
{
    use ResolvesCursorWindow;

    /**
     * Create a new instance.
     */
    public function __construct(
        private readonly Pagination $pagination
    ) {}

    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $source = $context['source'] ?? null;
        $args = $context['args'] ?? [];
        $info = $context['info'] ?? null;

        if (
            ! $source instanceof Product
            || ! $info
        ) {
            return null;
        }

        $fieldName = $info->fieldName ?? null;

        $relationMethods = [
            'upSells' => 'up_sells',
            'crossSells' => 'cross_sells',
            'relatedProducts' => 'related_products',
            'superAttributes' => 'super_attributes',
            'reviews' => 'reviews',
            'bookingProducts' => 'booking_products',
        ];

        if (! isset($relationMethods[$fieldName])) {
            return null;
        }

        $relationMethod = $relationMethods[$fieldName];

        $relationBuilder = $source->{$relationMethod}();

        if ($relationMethod === 'reviews') {
            $relationBuilder->where('status', 'approved');
        }

        $laravelPaginator = $this->paginateCursorWindow($relationBuilder, $args, $this->requestedCursorLimit($args, 30));

        if (
            isset($source->locale)
            || isset($source->channel)
        ) {
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
