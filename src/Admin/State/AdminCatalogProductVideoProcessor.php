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
use Illuminate\Support\Str;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCatalogProductVideo;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Product\Models\Product;
use Webkul\Product\Models\ProductVideo;

class AdminCatalogProductVideoProcessor implements ProcessorInterface
{
    use ChecksAdminPermission;

    protected const ALLOWED_MIMES = ['video/mp4', 'video/webm', 'video/ogg', 'video/quicktime'];

    protected const ALLOWED_EXTS = ['mp4', 'webm', 'ogg', 'mov'];

    protected const MAX_BYTES = 100 * 1024 * 1024;

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        if (! $this->adminHasPermission($admin, 'catalog.products.edit')) {
            throw new AuthorizationException(__('bagistoapi::app.admin.product.video.no-permission'));
        }

        $isGraphQL = $operation instanceof Mutation;

        if ($isGraphQL && $operation->getName() === 'create') {
            throw new InvalidInputException(__('bagistoapi::app.admin.product.video.graphql-upload-unsupported'), 422);
        }

        if ($isGraphQL && $operation->getName() === 'delete') {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];
            $videoId = ! empty($rawArgs['id'])
                ? (int) basename((string) $rawArgs['id'])
                : (int) ($rawArgs['videoId'] ?? 0);
            $productId = (int) (ProductVideo::where('id', $videoId)->value('product_id') ?? 0);

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

    protected function handleUpload(int $productId, mixed $file, ?int $position): AdminCatalogProductVideo
    {
        $product = Product::find($productId);
        if (! $product) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.product.not-found'));
        }

        if (! $file instanceof UploadedFile || ! $file->isValid()) {
            throw new InvalidInputException(__('bagistoapi::app.admin.product.video.video-required'), 422);
        }

        $mime = strtolower((string) $file->getMimeType());
        $ext = strtolower((string) $file->getClientOriginalExtension());

        if (! in_array($mime, self::ALLOWED_MIMES, true) && ! in_array($ext, self::ALLOWED_EXTS, true)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.product.video.video-invalid-type'), 422);
        }

        if ($file->getSize() > self::MAX_BYTES) {
            throw new InvalidInputException(__('bagistoapi::app.admin.product.video.video-too-large'), 422);
        }

        try {
            Event::dispatch('catalog.product.update.before', $productId);

            $filename = Str::random(40).'.'.($ext ?: 'mp4');
            $path = Storage::disk('public')->putFileAs('product/'.$productId, $file, $filename);

            if (! $path) {
                throw new InvalidInputException(__('bagistoapi::app.admin.product.video.upload-failed'), 500);
            }

            if ($position === null) {
                $position = (int) ProductVideo::where('product_id', $productId)->max('position') + 1;
            }

            $video = ProductVideo::create([
                'type'       => 'videos',
                'path'       => $path,
                'product_id' => $productId,
                'position'   => $position,
            ]);

            Event::dispatch('catalog.product.update.after', $product);
        } catch (InvalidInputException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            throw new InvalidInputException(__('bagistoapi::app.admin.product.video.upload-failed'), 500);
        }

        return $this->mapRow(ProductVideo::find($video->id), __('bagistoapi::app.admin.product.video.uploaded'));
    }

    protected function handleDelete(int $productId, int $videoId): AdminCatalogProductVideo
    {
        $product = Product::find($productId);
        if (! $product) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.product.not-found'));
        }

        $video = ProductVideo::where('id', $videoId)->where('product_id', $productId)->first();
        if (! $video) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.product.video.not-found'));
        }

        try {
            Event::dispatch('catalog.product.update.before', $productId);

            if ($video->path) {
                Storage::disk('public')->delete($video->path);
            }

            $video->delete();

            Event::dispatch('catalog.product.update.after', $product);
        } catch (\Throwable $e) {
            report($e);
            throw new InvalidInputException(__('bagistoapi::app.admin.product.video.delete-failed'), 500);
        }

        $result = new AdminCatalogProductVideo;
        $result->id = $videoId;
        $result->success = true;
        $result->message = __('bagistoapi::app.admin.product.video.deleted');

        return $result;
    }

    protected function toRestResponse(AdminCatalogProductVideo $dto, int $status): JsonResponse
    {
        return new JsonResponse(array_filter([
            'id'        => $dto->id,
            'productId' => $dto->productId,
            'path'      => $dto->path,
            'position'  => $dto->position,
            'url'       => $dto->url,
            'success'   => $dto->success,
            'message'   => $dto->message,
        ], static fn ($v) => $v !== null), $status);
    }

    protected function mapRow(ProductVideo $video, ?string $message = null): AdminCatalogProductVideo
    {
        $dto = new AdminCatalogProductVideo;
        $dto->id = (int) $video->id;
        $dto->productId = (int) $video->product_id;
        $dto->path = $video->path;
        $dto->position = (int) $video->position;
        $dto->url = $this->safeUrl($video);
        $dto->success = true;
        $dto->message = $message;

        return $dto;
    }

    protected function safeUrl(ProductVideo $video): ?string
    {
        try {
            return $video->url ?? Storage::disk('public')->url($video->path);
        } catch (\Throwable) {
            return null;
        }
    }
}
