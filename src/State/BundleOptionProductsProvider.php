<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\GraphQl\Serializer\ItemNormalizer;
use ApiPlatform\Laravel\Eloquent\Paginator;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\GraphQl\Operation;
use ApiPlatform\Metadata\Operation as BaseOperation;
use ApiPlatform\State\Pagination\Pagination;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\ProductBundleOption;

/**
 * Filters ProductBundleOptionProducts by parent option for BagistoApi nested queries.
 */
class BundleOptionProductsProvider implements ProviderInterface
{
    /**
     * Create a new provider instance.
     */
    public function __construct(private readonly Pagination $pagination) {}

    /**
     * Provide filtered bundle option products for the requested option.
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

        if (! $source || ! $info || ! isset($source[ItemNormalizer::ITEM_IDENTIFIERS_KEY])) {
            return [];
        }

        if ($info->fieldName !== 'bundleOptionProducts') {
            return [];
        }

        $identifiers = $source[ItemNormalizer::ITEM_IDENTIFIERS_KEY];
        $parentOptionId = $identifiers['id'] ?? $identifiers[0] ?? null;

        if (! $parentOptionId) {
            return [];
        }

        $parentOption = ProductBundleOption::find($parentOptionId);

        if (! $parentOption) {
            return [];
        }

        $itemsPerPage = $this->pagination->getLimit($operation, $context);
        $paginatedResult = $parentOption->bundle_option_products()->paginate($itemsPerPage, ['*'], 'page', 1);

        return new Paginator($paginatedResult);
    }
}
