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

/**
 * Filters downloadable links by parent product for nested BagistoApi queries.
 */
class DownloadableLinksProvider implements ProviderInterface
{
    public function __construct(private readonly Pagination $pagination) {}

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

        if (! $source || ! $info || ! isset($source[ItemNormalizer::ITEM_IDENTIFIERS_KEY])) {
            return [];
        }

        if ($info->fieldName !== 'downloadableLinks') {
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
        $paginatedResult = $parentProduct->downloadable_links()->paginate($itemsPerPage, ['*'], 'page', 1);

        return new Paginator($paginatedResult);
    }
}
