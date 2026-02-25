<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Dto\DeleteAllCompareItemsInput;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Models\CompareItem;
use Webkul\BagistoApi\Models\DeleteAllCompareItems;
use Illuminate\Support\Facades\Auth;

/**
 * DeleteAllCompareItemsProcessor - Deletes all compare items for the authenticated customer
 */
class DeleteAllCompareItemsProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
    ) {}

    /**
     * Process delete all compare items operation
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
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

        CompareItem::where('customer_id', $user->id)->delete();

        return new DeleteAllCompareItems(
            __('bagistoapi::app.graphql.compare-item.delete-all-success'),
            $deletedCount,
            $user->id
        );
    }
}
