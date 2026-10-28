<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\GraphQl\Serializer\ItemNormalizer;
use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\GraphQl\Operation;
use ApiPlatform\Metadata\Operation as BaseOperation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\Product;

class DownloadableSamplesProvider implements ProviderInterface
{
    /**
     * Create a new provider instance.
     */
    public function __construct(
        private readonly Pagination $pagination,
        private readonly CursorAwareCollectionProvider $collectionProvider,
    ) {}

    /**
     * Provide the downloadable samples of the parent product, or every row for the root collection query.
     */
    public function provide(BaseOperation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if (! ($operation instanceof Operation)) {
            return [];
        }

        if (! ($operation instanceof CollectionOperationInterface)) {
            return [];
        }

        $source = $context['source'] ?? null;
        $info = $context['info'] ?? null;

        if (
            ! $source
            || ! $info
            || ! isset($source[ItemNormalizer::ITEM_IDENTIFIERS_KEY])
        ) {
            return $this->collectionProvider->provide($operation, $uriVariables, $context);
        }

        if ($info->fieldName !== 'downloadableSamples') {
            return [];
        }

        $identifiers = $source[ItemNormalizer::ITEM_IDENTIFIERS_KEY];
        $parentProductId = $identifiers['id'] ?? $identifiers[0] ?? null;

        if (! $parentProductId) {
            return [];
        }

        $parentProduct = Product::find($parentProductId);

        if (! $parentProduct) {
            return [];
        }

        $itemsPerPage = $this->pagination->getLimit($operation, $context);
        $paginatedResult = $parentProduct->downloadable_samples()->paginate($itemsPerPage, ['*'], 'page', 1);

        return new Paginator($paginatedResult);
    }
}
