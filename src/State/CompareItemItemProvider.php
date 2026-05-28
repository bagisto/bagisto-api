<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Support\Facades\Auth;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\CompareItem;

/**
 * Provider for REST GET /api/shop/compare-items/{id}
 *
 * Enforces authentication and customer ownership so a customer cannot
 * read another customer's compare-item row by guessing its ID.
 */
class CompareItemItemProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $customer = Auth::guard('sanctum')->user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        $id = $uriVariables['id'] ?? null;

        if ($id === null || ! ctype_digit((string) $id)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.compare-item.not-found'));
        }

        $compareItem = CompareItem::where('id', (int) $id)
            ->where('customer_id', $customer->id)
            ->with(['product', 'customer'])
            ->first();

        if (! $compareItem) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.compare-item.not-found'));
        }

        return $compareItem;
    }
}
