<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Webkul\BagistoApi\Dto\CustomizableOptionFileInput;
use Webkul\BagistoApi\Dto\CustomizableOptionFileOutput;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Support\CartOptionFileStaging;
use Webkul\Product\Repositories\ProductRepository;

class CustomizableOptionFileProcessor implements ProcessorInterface
{
    /**
     * Create a new processor instance.
     */
    public function __construct(
        private readonly CartOptionFileStaging $staging,
        private readonly ProductRepository $productRepository,
    ) {}

    /**
     * Stage a file for a product's file option, from a multipart upload or base64 GraphQL input.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse|CustomizableOptionFileOutput
    {
        $ownerId = $this->resolveOwnerId();

        $input = $data instanceof CustomizableOptionFileInput ? $data : null;

        $productId = $input ? $input->product_id : (int) request()->input('product_id');
        $optionId = $input ? $input->option_id : (int) request()->input('option_id');
        $file = $input ? $this->fromBase64($input->file, $input->file_name) : request()->file('file');

        try {
            return $this->stage($operation, $file instanceof UploadedFile ? $file : null, $productId, $optionId, $ownerId);
        } finally {
            if (
                $input
                && $file
                && is_file($file->getPathname())
            ) {
                unlink($file->getPathname());
            }
        }
    }

    /**
     * Check the file against the option's extensions and the upload limit, then stage it.
     */
    private function stage(Operation $operation, ?UploadedFile $file, int $productId, int $optionId, string $ownerId): JsonResponse|CustomizableOptionFileOutput
    {
        $product = $this->productRepository->find($productId);

        if (! $product) {
            throw new ResourceNotFoundException(__('bagistoapi::app.graphql.cart.product-not-found'));
        }

        $option = $product->customizable_options->firstWhere('id', $optionId);

        if (
            ! $option
            || $option->type !== 'file'
        ) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.customizable-file-option-invalid'));
        }

        $allowed = $this->allowedExtensions($option->supported_file_extensions);
        $extension = strtolower($file?->getClientOriginalExtension() ?? '');
        $maxBytes = $this->staging->maxUploadBytes();

        if (
            ! $file
            || (
                $allowed
                && ! in_array($extension, $allowed, true)
            )
            || (
                $maxBytes > 0
                && $file->getSize() > $maxBytes
            )
        ) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.customizable-file-invalid'));
        }

        $staged = $this->staging->stage($file, $productId, $optionId, $ownerId);

        if ($operation instanceof Mutation) {
            $output = new CustomizableOptionFileOutput;
            $output->token = $staged['token'];
            $output->fileName = $staged['fileName'];
            $output->optionId = $optionId;

            return $output;
        }

        return new JsonResponse([
            'token' => $staged['token'],
            'fileName' => $staged['fileName'],
            'optionId' => $optionId,
        ], 201);
    }

    /**
     * Build an uploaded file from a base64 string, accepting an optional data URI prefix.
     */
    private function fromBase64(string $encoded, ?string $fileName = null): ?UploadedFile
    {
        if ($encoded === '') {
            return null;
        }

        $payload = $encoded;

        if (preg_match('#^data:(?<mime>[^;]+);base64,(?<data>.*)$#s', $encoded, $matches) === 1) {
            $payload = $matches['data'];
        }

        $binary = base64_decode($payload, true);

        if ($binary === false) {
            throw new InvalidInputException(__('bagistoapi::app.graphql.cart.customizable-file-invalid'));
        }

        $path = tempnam(sys_get_temp_dir(), 'cart-option-file-');
        file_put_contents($path, $binary);

        return new UploadedFile($path, $fileName ?: 'upload', null, null, true);
    }

    /**
     * Normalise the option's supported extensions to a lower-case list.
     */
    private function allowedExtensions(mixed $supported): array
    {
        if (is_array($supported)) {
            $list = $supported;
        } elseif (
            is_string($supported)
            && $supported !== ''
        ) {
            $list = explode(',', $supported);
        } else {
            $list = [];
        }

        return array_values(array_filter(array_map(fn ($extension) => strtolower(trim((string) $extension)), $list)));
    }

    /**
     * Identify who stages the file: the signed-in customer, or else the bearer cart token.
     */
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
