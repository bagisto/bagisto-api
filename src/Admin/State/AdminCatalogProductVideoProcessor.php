<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GraphQl\Mutation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Admin\Models\AdminCatalogProductVideo;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Core\Helpers\MediaFileName;
use Webkul\Product\Models\ProductVideo;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Product\Repositories\ProductVideoRepository;

class AdminCatalogProductVideoProcessor implements ProcessorInterface
{
    use ChecksAdminPermission;

    /**
     * Content types an uploaded video may have, as detected from its bytes.
     */
    protected const ALLOWED_MIMES = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'];

    /**
     * Extensions an uploaded video may be sent with.
     */
    protected const ALLOWED_EXTS = ['mp4', 'webm', 'ogg', 'mov'];

    /**
     * Largest video accepted, in bytes.
     */
    protected const MAX_BYTES = 100 * 1024 * 1024;

    /**
     * Create a new processor instance.
     */
    public function __construct(
        protected ProductRepository $productRepository,
        protected ProductVideoRepository $productVideoRepository,
        protected MediaFileName $mediaFileName,
    ) {}

    /**
     * Route the video request to upload or delete.
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $this->authorizedAdmin('catalog.products.edit', 'bagistoapi::app.admin.product.video.no-permission');

        $isGraphQL = $operation instanceof Mutation;

        if (
            $isGraphQL
            && $operation->getName() === 'create'
        ) {
            throw new InvalidInputException(__('bagistoapi::app.admin.product.video.graphql-upload-unsupported'), 422);
        }

        if (
            $isGraphQL
            && $operation->getName() === 'delete'
        ) {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            $videoId = ! empty($rawArgs['id'])
                ? (int) basename((string) $rawArgs['id'])
                : (int) ($rawArgs['videoId'] ?? 0);
            $productId = (int) ($this->productVideoRepository->find($videoId)?->product_id ?? 0);

            return $this->handleDelete($productId, $videoId);
        }

        if ($operation instanceof Delete) {
            $productId = (int) ($uriVariables['productId'] ?? request()->route('productId') ?? 0);
            $videoId = (int) ($uriVariables['id'] ?? request()->route('id') ?? 0);

            return $this->toRestResponse($this->handleDelete($productId, $videoId), 200);
        }

        if ($operation instanceof Post) {
            $productId = (int) ($uriVariables['productId'] ?? request()->route('productId') ?? 0);
            $file = request()->file('video');
            $position = request()->input('position');

            return $this->toRestResponse($this->handleUpload($productId, $file, $position !== null ? (int) $position : null), 201);
        }

        return null;
    }

    /**
     * Store an uploaded video file under a generated name and create its row for the product.
     */
    protected function handleUpload(int $productId, mixed $file, ?int $position): AdminCatalogProductVideo
    {
        $product = $this->productRepository->find($productId);

        if (! $product) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.product.not-found'));
        }

        if (
            ! $file instanceof UploadedFile
            || ! $file->isValid()
        ) {
            throw new InvalidInputException(__('bagistoapi::app.admin.product.video.video-required'), 422);
        }

        if (! $this->isAllowedVideo($file)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.product.video.video-invalid-type'), 422);
        }

        if ($file->getSize() > self::MAX_BYTES) {
            throw new InvalidInputException(__('bagistoapi::app.admin.product.video.video-too-large'), 422);
        }

        try {
            Event::dispatch('catalog.product.update.before', $productId);

            $path = $this->mediaFileName->resolve(
                $this->productVideoRepository->getProductDirectory($product),
                null,
                $this->mediaFileName->extension($file),
            );

            if (! Storage::putFileAs(dirname($path), $file, basename($path))) {
                throw new InvalidInputException(__('bagistoapi::app.admin.product.video.upload-failed'), 500);
            }

            if ($position === null) {
                $position = (int) $this->productVideoRepository->where('product_id', $productId)->max('position') + 1;
            }

            $video = $this->productVideoRepository->create([
                'type' => 'videos',
                'path' => $path,
                'product_id' => $productId,
                'position' => $position,
            ]);

            Event::dispatch('catalog.product.update.after', $product);
        } catch (InvalidInputException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            throw new InvalidInputException(__('bagistoapi::app.admin.product.video.upload-failed'), 500);
        }

        return $this->mapRow($this->productVideoRepository->find($video->id), __('bagistoapi::app.admin.product.video.uploaded'));
    }

    /**
     * Delete a video row of the product and its stored file.
     */
    protected function handleDelete(int $productId, int $videoId): AdminCatalogProductVideo
    {
        $product = $this->productRepository->find($productId);

        if (! $product) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.product.not-found'));
        }

        $video = $this->productVideoRepository->findOneWhere([
            'id' => $videoId,
            'product_id' => $productId,
        ]);

        if (! $video) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.product.video.not-found'));
        }

        try {
            Event::dispatch('catalog.product.update.before', $productId);

            if ($video->path) {
                Storage::disk('public')->delete($video->path);

                Storage::delete($video->path);
            }

            $this->productVideoRepository->delete($video->id);

            Event::dispatch('catalog.product.update.after', $product);
        } catch (\Throwable $exception) {
            report($exception);

            throw new InvalidInputException(__('bagistoapi::app.admin.product.video.delete-failed'), 500);
        }

        $result = new AdminCatalogProductVideo;
        $result->id = $videoId;
        $result->success = true;
        $result->message = __('bagistoapi::app.admin.product.video.deleted');

        return $result;
    }

    /**
     * Whether the file's detected content type and its sent extension are both an allowed video type.
     */
    protected function isAllowedVideo(UploadedFile $file): bool
    {
        return in_array(strtolower((string) $file->getMimeType()), self::ALLOWED_MIMES, true)
            && in_array(strtolower((string) $file->getClientOriginalExtension()), self::ALLOWED_EXTS, true);
    }

    /**
     * Convert a video result DTO to a raw JSON response for REST.
     */
    protected function toRestResponse(AdminCatalogProductVideo $dto, int $status): JsonResponse
    {
        return new JsonResponse(array_filter([
            'id' => $dto->id,
            'productId' => $dto->product_id,
            'path' => $dto->path,
            'position' => $dto->position,
            'url' => $dto->url,
            'success' => $dto->success,
            'message' => $dto->message,
        ], static fn ($value) => $value !== null), $status);
    }

    /**
     * Map a video row to the resource DTO.
     */
    protected function mapRow(ProductVideo $video, ?string $message = null): AdminCatalogProductVideo
    {
        $dto = new AdminCatalogProductVideo;
        $dto->id = (int) $video->id;
        $dto->product_id = (int) $video->product_id;
        $dto->path = $video->path;
        $dto->position = (int) $video->position;
        $dto->url = $this->safeUrl($video);
        $dto->success = true;
        $dto->message = $message;

        return $dto;
    }

    /**
     * Return the public URL of a video, or null when it cannot be built.
     */
    protected function safeUrl(ProductVideo $video): ?string
    {
        try {
            return $video->url ?? Storage::disk('public')->url($video->path);
        } catch (\Throwable) {
            return null;
        }
    }
}
