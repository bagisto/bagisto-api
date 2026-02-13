<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Webkul\BagistoApi\Dto\CreateCompareItemInput;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\CompareItem;
use Webkul\BagistoApi\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

/**
 * CompareItemProcessor - Handles create/delete operations for compare items
 */
class CompareItemProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly ProcessorInterface $persistProcessor,
        private ?Request $request = null
    ) {}

    /**
     * Process compare item operations
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($data instanceof CreateCompareItemInput) {
            return $this->handleCreate($data, $context);
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }

    /**
     * Handle create operation for compare items
     */
    private function handleCreate(CreateCompareItemInput $input, array $context = []): CompareItem
    {
        if (empty($input->productId)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.compare-item.product-id-required'));
        }

        $product = Product::find($input->productId);
        if (! $product) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.compare-item.product-not-found'));
        }
 
        $user = Auth::guard('sanctum')->user();
            
        if (! $user) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        $customerId = $user->id;

        $existingItem = CompareItem::where('customer_id', $customerId)
            ->where('product_id', $input->productId)
            ->first();

        if ($existingItem) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.compare-item.already-exists'));
        }

        $compareItem = CompareItem::create([
            'product_id' => $input->productId,
            'customer_id' => $customerId,
        ]);

        return $compareItem;
    }
}
