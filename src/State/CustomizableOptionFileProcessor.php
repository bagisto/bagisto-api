<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Support\CartOptionFileStaging;
use Webkul\Product\Repositories\ProductRepository;

class CustomizableOptionFileProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly CartOptionFileStaging $staging,
        private readonly ProductRepository $productRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse
    {
        $ownerId = $this->resolveOwnerId();

        $productId = (int) request()->input('product_id');
        $optionId = (int) request()->input('option_id');
        $file = request()->file('file');

        $product = $this->productRepository->find($productId);

        if (! $product) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.product-not-found'));
        }

        $option = $product->customizable_options->firstWhere('id', $optionId);

        if (! $option || $option->type !== 'file') {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.customizable-file-option-invalid'));
        }

        $allowed = $this->allowedExtensions($option->supported_file_extensions);
        $ext = strtolower($file?->getClientOriginalExtension() ?? '');
        $maxBytes = $this->staging->maxUploadBytes();

        if (
            ! $file
            || ($allowed && ! in_array($ext, $allowed, true))
            || ($maxBytes > 0 && $file->getSize() > $maxBytes)
        ) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.customizable-file-invalid'));
        }

        $staged = $this->staging->stage($file, $productId, $optionId, $ownerId);

        return new JsonResponse([
            'token' => $staged['token'],
            'fileName' => $staged['fileName'],
            'optionId' => $optionId,
        ], 201);
    }

    private function allowedExtensions(mixed $supported): array
    {
        if (is_array($supported)) {
            $list = $supported;
        } elseif (is_string($supported) && $supported !== '') {
            $list = explode(',', $supported);
        } else {
            $list = [];
        }

        return array_values(array_filter(array_map(fn ($e) => strtolower(trim((string) $e)), $list)));
    }

    private function resolveOwnerId(): string
    {
        $customer = Auth::guard('sanctum')->user();

        if ($customer) {
            return 'customer:'.$customer->id;
        }

        $token = request()->bearerToken();

        if (! $token) {
            throw new AuthenticationException(__('bagistoapi::app.graphql.logout.unauthenticated'));
        }

        return 'cart:'.$token;
    }
}
