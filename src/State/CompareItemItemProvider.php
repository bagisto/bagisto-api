<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\CompareItem;
use Webkul\BagistoApi\Models\Product;
use Webkul\BagistoApi\Support\CurrentCustomer;

class CompareItemItemProvider implements ProviderInterface
{
    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $customer = CurrentCustomer::user();

        if (! $customer) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        $id = $uriVariables['id'] ?? null;

        if (
            $id === null
            || ! ctype_digit((string) $id)
        ) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.compare-item.not-found'));
        }

        $compareItem = CompareItem::where('id', (int) $id)
            ->where('customer_id', $customer->id)
            ->with(['product' => fn ($query) => Product::withReviewSummary($query), 'customer'])
            ->first();

        if (! $compareItem) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.compare-item.not-found'));
        }

        return $compareItem;
    }
}
