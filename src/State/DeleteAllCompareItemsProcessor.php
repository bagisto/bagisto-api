<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Webkul\BagistoApi\Dto\DeleteAllCompareItemsInput;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Models\CompareItem;
use Webkul\BagistoApi\Models\DeleteAllCompareItems;
use Webkul\BagistoApi\State\Concerns\StorefrontFeatureGate;

/**
 * DeleteAllCompareItemsProcessor - Deletes all compare items for the authenticated customer
 */
class DeleteAllCompareItemsProcessor implements ProcessorInterface
{
    use StorefrontFeatureGate;

    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
    ) {}

    /**
     * Process delete all compare items operation
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $this->assertCompareEnabled();

        if ($data instanceof DeleteAllCompareItemsInput) {
            return $this->handleDeleteAll();
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    /**
     * Delete all compare items for the authenticated customer
     */
    private function handleDeleteAll(): DeleteAllCompareItems
    {
        $user = Auth::guard('sanctum')->user();

        if (! $user) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        $deletedCount = CompareItem::where('customer_id', $user->id)->count();

        Event::dispatch('customer.compare.delete-all.before');

        CompareItem::where('customer_id', $user->id)->delete();

        Event::dispatch('customer.compare.delete-all.after');

        return new DeleteAllCompareItems(
            __('bagistoapi::app.graphql.compare-item.delete-all-success'),
            $deletedCount,
            $user->id
        );
    }
}
