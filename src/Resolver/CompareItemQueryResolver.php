<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Illuminate\Support\Facades\Auth;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\CompareItem;
use Webkul\BagistoApi\Service\GenericIdNormalizer;

class CompareItemQueryResolver implements QueryItemResolverInterface
{
    public function __invoke(?object $item, array $context): object
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        $id = $context['args']['id'] ?? null;

        $numericId = $id !== null ? GenericIdNormalizer::extractNumericId($id) : null;

        if ($numericId === null) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.compare-item.not-found'));
        }

        $compareItem = CompareItem::where('id', $numericId)
            ->where('customer_id', $customer->id)
            ->first();

        if (! $compareItem) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.compare-item.not-found'));
        }

        return $compareItem;
    }
}
