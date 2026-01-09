<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryCollectionResolverInterface;
use Webkul\BagistoApi\Models\Category;

class CategoryCollectionResolver implements QueryCollectionResolverInterface
{
    /**
     * Return ordered category collection
     */
    public function __invoke(?iterable $collection, array $context): iterable
    {
        $id = $context['args']['parentId'] ?? null;

        return $id
            ? Category::orderBy('position', 'ASC')->where('status', 1)->descendantsAndSelf($id)->toTree($id)
            : Category::orderBy('position', 'ASC')->where('status', 1)->get()->toTree();
    }
}
