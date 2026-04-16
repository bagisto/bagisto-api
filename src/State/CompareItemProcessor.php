<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request as RequestFacade;
use Webkul\BagistoApi\Dto\CreateCompareItemInput;
use Webkul\BagistoApi\Dto\DeleteCompareItemInput;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\CompareItem;
use Webkul\BagistoApi\Models\Product;

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
            $this->hydrateCreateInputFromContext($data, $context);

            return $this->handleCreate($data);
        }

        if ($data instanceof CompareItem && $operation instanceof \ApiPlatform\Metadata\Post) {
            $input = new CreateCompareItemInput;
            $input->product_id = request()->input('product_id') ?? request()->input('productId');

            return $this->handleCreate($input);
        }

        if ($data instanceof DeleteCompareItemInput) {
            $this->hydrateDeleteInputFromContext($data, $context);

            return $this->handleDelete($data);
        }

        $result = $this->persistProcessor->process($data, $operation, $uriVariables, $context);

        if ($result instanceof CompareItem && $result->id) {
            $result->loadMissing(['product', 'customer']);
        }

        return $result;
    }

    /**
     * Handle create operation for compare items
     */
    private function handleCreate(CreateCompareItemInput $input): CompareItem
    {
        if (empty($input->product_id)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.compare-item.product-id-required'));
        }

        $product = Product::find($input->product_id);

        if (! $product) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.compare-item.product-not-found'));
        }

        $user = Auth::guard('sanctum')->user();

        if (! $user) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        $existingItem = CompareItem::where('customer_id', $user->id)
            ->where('product_id', $input->product_id)
            ->first();

        if ($existingItem) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.compare-item.already-exists'));
        }

        $compareItem = CompareItem::create([
            'product_id'  => $input->product_id,
            'customer_id' => $user->id,
        ]);

        $compareItem->load(['product', 'customer']);

        return $compareItem;
    }

    /**
     * Handle delete operation for compare items
     */
    private function handleDelete(DeleteCompareItemInput $input): CompareItem
    {
        $user = Auth::guard('sanctum')->user();

        if (! $user) {
            throw new AuthorizationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        if (empty($input->id)) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.compare-item.id-required'));
        }

        $compareItemId = basename($input->id);
        $compareItem = CompareItem::find($compareItemId);

        if (! $compareItem) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.compare-item.not-found'));
        }

        if ($compareItem->customer_id !== $user->id) {
            throw new AuthorizationException(__('bagistoapi::app.auth.cannot-update-other-profile'));
        }

        $compareItem->load(['product', 'customer']);
        $compareItem->delete();

        return $compareItem;
    }

    private function hydrateCreateInputFromContext(CreateCompareItemInput $input, array $context): void
    {
        if (! empty($input->product_id)) {
            return;
        }

        $productId = $this->extractProductId($context['args']['input'] ?? $context['args'] ?? null);

        if ($productId === null) {
            $request = $this->request ?? RequestFacade::instance();

            if ($request) {
                $productId = $this->extractProductId($request->input('variables.input'))
                    ?? $this->extractProductId($request->input('input'))
                    ?? $this->extractProductId($request->input('extensions.variables.input'));
            }
        }

        if ($productId !== null) {
            $input->product_id = $productId;
        }
    }

    private function hydrateDeleteInputFromContext(DeleteCompareItemInput $input, array $context): void
    {
        if (! empty($input->id)) {
            return;
        }

        $id = $this->extractId($context['args']['input'] ?? $context['args'] ?? null);

        if ($id === null) {
            $request = $this->request ?? RequestFacade::instance();

            if ($request) {
                $id = $this->extractId($request->input('variables.input'))
                    ?? $this->extractId($request->input('input'))
                    ?? $this->extractId($request->input('extensions.variables.input'));
            }
        }

        if ($id !== null) {
            $input->id = $id;
        }
    }

    private function extractProductId(mixed $input): ?int
    {
        if (is_array($input)) {
            $value = $input['product_id'] ?? $input['productId'] ?? null;

            return is_numeric($value) ? (int) $value : null;
        }

        if (is_object($input)) {
            $value = $input->product_id ?? $input->productId ?? null;

            return is_numeric($value) ? (int) $value : null;
        }

        return null;
    }

    private function extractId(mixed $input): ?string
    {
        if (is_array($input)) {
            $value = $input['id'] ?? null;

            return $value !== null && $value !== '' ? (string) $value : null;
        }

        if (is_object($input)) {
            $value = $input->id ?? null;

            return $value !== null && $value !== '' ? (string) $value : null;
        }

        return null;
    }
}
