<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Admin\Dto\AdminCatalogProductImageDeleteInput;
use Webkul\BagistoApi\Admin\Dto\AdminCatalogProductImageReorderInput;
use Webkul\BagistoApi\Admin\Dto\AdminCatalogProductImageUpdateInput;
use Webkul\BagistoApi\Admin\Models\AdminCatalogProductImage;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Support\CoreCapabilities;
use Webkul\Core\Helpers\MediaFileName;
use Webkul\Product\Models\ProductImage;
use Webkul\Product\Repositories\ProductImageRepository;
use Webkul\Product\Repositories\ProductRepository;

class AdminCatalogProductImageProcessor implements ProcessorInterface
{
    use ChecksAdminPermission;

    /**
     * Content types an uploaded image may have, as detected from its bytes.
     */
    protected const ALLOWED_MIMES = [
        'image/bmp',
        'image/x-ms-bmp',
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/webp',
    ];

    /**
     * Extensions an uploaded image may be sent with, matching core's `images.files.*` rule.
     */
    protected const ALLOWED_EXTS = ['bmp', 'jpeg', 'jpg', 'png', 'webp'];

    /**
     * Largest image accepted, in bytes.
     */
    protected const MAX_BYTES = 4 * 1024 * 1024;

    /**
     * Create a new processor instance.
     */
    public function __construct(
        protected ProductRepository $productRepository,
        protected ProductImageRepository $productImageRepository,
        protected MediaFileName $mediaFileName,
    ) {}

    /**
     * Route the image request to upload, update, reorder or delete.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $this->authorizedAdmin('catalog.products.edit', 'bagistoapi::app.admin.product.image.no-permission');

        $isGraphQL = $operation instanceof Mutation;

        if (
            $isGraphQL
            && $operation->getName() === 'delete'
        ) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];

            $imageId = 0;
            if (! empty($rawArgs['id'])) {
                $imageId = (int) basename((string) $rawArgs['id']);
            } elseif (
                $data instanceof AdminCatalogProductImageDeleteInput
                && ! empty($data->imageId)
            ) {
                $imageId = (int) $data->imageId;
            }

            return $this->handleDelete($this->productIdOfImage($imageId), $imageId);
        }

        if (
            $isGraphQL
            && $data instanceof AdminCatalogProductImageUpdateInput
        ) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];

            $imageId = (int) ($rawArgs['imageId'] ?? $rawArgs['image_id'] ?? $data->image_id ?? 0);

            if (
                ! $imageId
                && ! empty($rawArgs['id'])
            ) {
                $imageId = (int) basename((string) $rawArgs['id']);
            }

            $productId = (int) ($rawArgs['productId'] ?? $rawArgs['product_id'] ?? $data->product_id ?? $this->productIdOfImage($imageId));

            return $this->handleUpdate(
                $productId,
                $imageId,
                array_key_exists('altText', $rawArgs) || array_key_exists('alt_text', $rawArgs)
                    ? (string) ($rawArgs['altText'] ?? $rawArgs['alt_text'])
                    : $data->alt_text,
                $rawArgs['position'] ?? $data->position,
            );
        }

        if (
            $isGraphQL
            && $data instanceof AdminCatalogProductImageReorderInput
        ) {
            if ($operation->getName() === 'create') {
                throw new InvalidInputException(
                    __('bagistoapi::app.admin.product.image.graphql-upload-unsupported'),
                    422,
                );
            }

            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            $productId = (int) ($rawArgs['productId'] ?? $rawArgs['product_id'] ?? $data->productId ?? 0);
            $order = $rawArgs['order'] ?? $data->order ?? [];

            return $this->handleReorder($productId, (array) $order);
        }

        if ($operation instanceof Delete) {
            $productId = (int) ($uriVariables['productId'] ?? request()->route('productId') ?? 0);
            $imageId = (int) ($uriVariables['id'] ?? request()->route('id') ?? 0);

            return $this->toRestResponse($this->handleDelete($productId, $imageId), 200);
        }

        if ($operation instanceof Put) {
            $productId = (int) ($uriVariables['productId'] ?? request()->route('productId') ?? 0);
            $imageId = (int) ($uriVariables['id'] ?? request()->route('id') ?? 0);

            if ($imageId) {
                $dto = $this->handleUpdate(
                    $productId,
                    $imageId,
                    request()->has('alt_text') || request()->has('altText')
                        ? (string) (request()->input('alt_text') ?? request()->input('altText'))
                        : null,
                    request()->input('position'),
                );

                return $this->toRestResponse($dto, 200);
            }

            $order = (array) (request()->input('order') ?? []);

            return $this->toRestResponse($this->handleReorder($productId, $order), 200);
        }

        if ($operation instanceof Post) {
            $productId = (int) ($uriVariables['productId'] ?? request()->route('productId') ?? 0);
            $file = request()->file('image');
            $position = request()->input('position');
            $position = $position !== null ? (int) $position : null;
            $altText = request()->input('alt_text') ?? request()->input('altText');
            $dto = $this->handleUpload($productId, $file, $position, $altText !== null ? (string) $altText : null);

            return $this->toRestResponse($dto, 201);
        }

        return null;
    }

    /**
     * Store an uploaded image file as webp and create its row for the product.
     */
    protected function handleUpload(int $productId, mixed $file, ?int $position, ?string $altText = null): AdminCatalogProductImage
    {
        $product = $this->productRepository->find($productId);

        if (! $product) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.product.not-found'));
        }

        if (
            ! $file instanceof UploadedFile
            || ! $file->isValid()
        ) {
            throw new InvalidInputException(__('bagistoapi::app.admin.product.image.image-required'), 422);
        }

        if (! $this->isAllowedImage($file)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.product.image.image-invalid-type'), 422);
        }

        if ($file->getSize() > self::MAX_BYTES) {
            throw new InvalidInputException(__('bagistoapi::app.admin.product.image.image-too-large'), 422);
        }

        $encoded = $this->encodeAsWebp($file);

        try {
            Event::dispatch('catalog.product.update.before', $productId);

            $path = $this->mediaFileName->resolve($this->productImageRepository->getProductDirectory($product), null, 'webp');

            if (! Storage::put($path, $encoded)) {
                throw new InvalidInputException(__('bagistoapi::app.admin.product.image.upload-failed'), 500);
            }

            if ($position === null) {
                $position = (int) $this->productImageRepository->where('product_id', $productId)->max('position') + 1;
            }

            $image = $this->productImageRepository->create([
                'type' => 'images',
                'path' => $path,
                'product_id' => $productId,
                'position' => $position,
            ]);

            if ($altText !== null) {
                $this->saveAltText($image, $altText);
            }

            Event::dispatch('catalog.product.update.after', $product);
        } catch (InvalidInputException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            throw new InvalidInputException(__('bagistoapi::app.admin.product.image.upload-failed'), 500);
        }

        return $this->mapRow($this->productImageRepository->find($image->id), true, __('bagistoapi::app.admin.product.image.uploaded'));
    }

    /**
     * Set the positions of the product's images from the given order.
     *
     * @param  array<int, array{id?: int|string, position?: int|string}>  $order
     */
    protected function handleReorder(int $productId, array $order): AdminCatalogProductImage
    {
        $product = $this->productRepository->find($productId);

        if (! $product) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.product.not-found'));
        }

        if (empty($order)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.product.image.order-required'), 422);
        }

        $ids = [];

        foreach ($order as $row) {
            if (
                ! is_array($row)
                || ! isset($row['id'])
            ) {
                throw new InvalidInputException(__('bagistoapi::app.admin.product.image.order-invalid'), 422);
            }

            $ids[] = (int) $row['id'];
        }

        $existing = $this->productImageRepository
            ->where('product_id', $productId)
            ->whereIn('id', $ids)
            ->pluck('id')
            ->all();

        $missing = array_diff($ids, $existing);

        if (! empty($missing)) {
            throw new InvalidInputException(
                __('bagistoapi::app.admin.product.image.id-not-on-product', ['id' => (int) array_values($missing)[0]]),
                422,
            );
        }

        try {
            Event::dispatch('catalog.product.update.before', $productId);

            foreach ($order as $row) {
                $this->productImageRepository->update(['position' => (int) ($row['position'] ?? 0)], (int) $row['id']);
            }

            Event::dispatch('catalog.product.update.after', $product);
        } catch (\Throwable $exception) {
            report($exception);

            throw new InvalidInputException(__('bagistoapi::app.admin.product.image.reorder-failed'), 500);
        }

        $result = new AdminCatalogProductImage;
        $result->id = $productId;
        $result->success = true;
        $result->message = __('bagistoapi::app.admin.product.image.reordered');
        $result->images = $this->productImageRepository
            ->where('product_id', $productId)
            ->orderBy('position')
            ->get()
            ->map(fn ($image) => $this->mapRowArray($image))
            ->all();

        return $result;
    }

    /**
     * Delete an image row of the product and its stored file.
     */
    protected function handleDelete(int $productId, int $imageId): AdminCatalogProductImage
    {
        $product = $this->productRepository->find($productId);

        if (! $product) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.product.not-found'));
        }

        $image = $this->findProductImage($productId, $imageId);

        try {
            Event::dispatch('catalog.product.update.before', $productId);

            $this->deleteStoredFile($image);

            $this->productImageRepository->delete($image->id);

            Event::dispatch('catalog.product.update.after', $product);
        } catch (\Throwable $exception) {
            report($exception);

            throw new InvalidInputException(__('bagistoapi::app.admin.product.image.delete-failed'), 500);
        }

        $result = new AdminCatalogProductImage;
        $result->id = $imageId;
        $result->success = true;
        $result->message = __('bagistoapi::app.admin.product.image.deleted');

        return $result;
    }

    /**
     * Edit what is stored alongside an uploaded image.
     */
    protected function handleUpdate(int $productId, int $imageId, ?string $altText, mixed $position): AdminCatalogProductImage
    {
        $product = $this->productRepository->find($productId);

        if (! $product) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.product.not-found'));
        }

        $image = $this->findProductImage($productId, $imageId);

        if (
            $altText === null
            && $position === null
        ) {
            throw new InvalidInputException(__('bagistoapi::app.admin.product.image.update-empty'), 422);
        }

        Event::dispatch('catalog.product.update.before', $productId);

        if ($position !== null) {
            $image = $this->productImageRepository->update(['position' => (int) $position], $imageId);
        }

        if ($altText !== null) {
            $this->saveAltText($image, $altText);
        }

        Event::dispatch('catalog.product.update.after', $product);

        return $this->mapRow(
            $this->productImageRepository->find($imageId),
            true,
            __('bagistoapi::app.admin.product.image.updated'),
        );
    }

    /**
     * Whether the file's detected content type and its sent extension are both an allowed image type.
     */
    protected function isAllowedImage(UploadedFile $file): bool
    {
        return in_array(strtolower((string) $file->getMimeType()), self::ALLOWED_MIMES, true)
            && in_array(strtolower((string) $file->getClientOriginalExtension()), self::ALLOWED_EXTS, true);
    }

    /**
     * Re-encode the uploaded image to webp, as core's product media repository does.
     */
    protected function encodeAsWebp(UploadedFile $file): string
    {
        try {
            return (string) image_manager()->fromUpload($file)->toWebp()->toBytes();
        } catch (\Throwable) {
            throw new InvalidInputException(__('bagistoapi::app.admin.product.image.image-invalid-type'), 422);
        }
    }

    /**
     * Find an image of the product, or fail with not found.
     */
    protected function findProductImage(int $productId, int $imageId): ProductImage
    {
        $image = $this->productImageRepository->findOneWhere([
            'id' => $imageId,
            'product_id' => $productId,
        ]);

        if (! $image) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.product.image.not-found'));
        }

        return $image;
    }

    /**
     * The id of the product the image belongs to, or zero when the image is unknown.
     */
    protected function productIdOfImage(int $imageId): int
    {
        return (int) ($this->productImageRepository->find($imageId)?->product_id ?? 0);
    }

    /**
     * Delete the stored file of an image from the disks.
     */
    protected function deleteStoredFile(ProductImage $image): void
    {
        if (! $image->path) {
            return;
        }

        Storage::disk('public')->delete($image->path);

        try {
            Storage::delete($image->path);
        } catch (\Throwable) {
        }
    }

    /**
     * Convert a result DTO to a raw JSON response, so API Platform skips serializing it again.
     */
    protected function toRestResponse(AdminCatalogProductImage $dto, int $status): JsonResponse
    {
        $payload = array_filter(
            [
                'id' => $dto->id,
                'productId' => $dto->product_id,
                'path' => $dto->path,
                'position' => $dto->position,
                'url' => $dto->url,
                'success' => $dto->success,
                'message' => $dto->message,
                'images' => $dto->images,
            ],
            static fn ($value) => $value !== null,
        );

        return new JsonResponse($payload, $status);
    }

    /**
     * Store the alt text for every locale the request covers, as the product form does.
     */
    protected function saveAltText(ProductImage $image, string $altText): void
    {
        if (! app(CoreCapabilities::class)->hasProductImageAltText()) {
            return;
        }

        foreach (core()->getRequestedLocaleCodes() as $localeCode) {
            $image->translateOrNew($localeCode)->alt_text = $altText;
        }

        $image->save();
    }

    /**
     * The alt text stored for the current locale.
     */
    protected function altTextFor(ProductImage $image): ?string
    {
        if (! app(CoreCapabilities::class)->hasProductImageAltText()) {
            return null;
        }

        return $image->translate(app()->getLocale())?->alt_text;
    }

    /**
     * Map an image row to the resource DTO.
     */
    protected function mapRow(ProductImage $image, bool $success = true, ?string $message = null): AdminCatalogProductImage
    {
        $dto = new AdminCatalogProductImage;
        $dto->id = (int) $image->id;
        $dto->product_id = (int) $image->product_id;
        $dto->path = $image->path;
        $dto->position = (int) $image->position;
        $dto->url = $this->safeUrl($image);
        $dto->alt_text = $this->altTextFor($image);
        $dto->success = $success;
        $dto->message = $message;

        return $dto;
    }

    /**
     * Map an image row to the array shape of the reorder response.
     *
     * @return array<string, mixed>
     */
    protected function mapRowArray(ProductImage $image): array
    {
        return [
            'id' => (int) $image->id,
            'productId' => (int) $image->product_id,
            'path' => $image->path,
            'position' => (int) $image->position,
            'url' => $this->safeUrl($image),
            'altText' => $this->altTextFor($image),
        ];
    }

    /**
     * Return the public URL of an image, or null when it cannot be built.
     */
    protected function safeUrl(ProductImage $image): ?string
    {
        try {
            return $image->url;
        } catch (\Throwable) {
            return null;
        }
    }
}
