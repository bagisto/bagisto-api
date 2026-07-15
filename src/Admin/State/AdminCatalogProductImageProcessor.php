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
use Illuminate\Support\Str;
use Webkul\BagistoApi\Admin\Dto\AdminCatalogProductImageDeleteInput;
use Webkul\BagistoApi\Admin\Dto\AdminCatalogProductImageReorderInput;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCatalogProductImage;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Product\Models\Product;
use Webkul\Product\Models\ProductImage;
use Webkul\Product\Repositories\ProductImageRepository;
use Webkul\Product\Repositories\ProductRepository;

/**
 * Phase 5.11 — Admin Catalog Product images processor.
 *
 * Handles three operations on the AdminCatalogProductImage sub-resource:
 *   - POST   /catalog/products/{productId}/images        upload (multipart)
 *   - PUT    /catalog/products/{productId}/images/reorder reorder positions
 *   - DELETE /catalog/products/{productId}/images/{id}   remove DB row + file
 *
 * Storage convention: files live under `product/{productId}/{filename}` on the
 * `public` disk (Storage::disk('public')). Mirrors the core monolith path.
 *
 * Permission gate: catalog.products.edit for all three operations (Sanctum-token
 * pattern — reads $admin->role->permission_type / ->permissions directly; never
 * calls bouncer() because Sanctum-API requests have no session-bound admin).
 *
 * Each operation fires catalog.product.update.before/after — image edits are an
 * update event in the Bagisto monolith.
 */
class AdminCatalogProductImageProcessor implements ProcessorInterface
{
    /** Allowed mime types for uploads. */
    protected const ALLOWED_MIMES = [
        'image/bmp',
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/webp',
    ];

    /** Allowed file extensions. */
    protected const ALLOWED_EXTS = ['bmp', 'jpeg', 'jpg', 'png', 'webp'];

    /** Max upload size (bytes) — 4 MB. */
    protected const MAX_BYTES = 4 * 1024 * 1024;

    public function __construct(
        protected ProductRepository $productRepository,
        protected ProductImageRepository $productImageRepository,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $this->assertPermission($admin);

        $isGraphQL = $operation instanceof Mutation;

        if ($isGraphQL && $operation->getName() === 'delete') {
            $rawArgs = $context['args']['input'] ?? $context['args'] ?? [];

            $imageId = 0;
            if (! empty($rawArgs['id'])) {
                $imageId = (int) basename((string) $rawArgs['id']);
            } elseif ($data instanceof AdminCatalogProductImageDeleteInput && ! empty($data->imageId)) {
                $imageId = (int) $data->imageId;
            }

            $productId = (int) (ProductImage::where('id', $imageId)->value('product_id') ?? 0);

            return $this->handleDelete($productId, $imageId);
        }

        if ($isGraphQL && $data instanceof AdminCatalogProductImageReorderInput) {
            $name = $operation->getName();

            if ($name === 'create') {
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
            $dto = $this->handleDelete($productId, $imageId);

            return $this->toRestResponse($dto, 200);
        }

        if ($operation instanceof Put) {
            $productId = (int) ($uriVariables['productId'] ?? request()->route('productId') ?? 0);
            $order = (array) (request()->input('order') ?? []);
            $dto = $this->handleReorder($productId, $order);

            return $this->toRestResponse($dto, 200);
        }

        if ($operation instanceof Post) {
            $productId = (int) ($uriVariables['productId'] ?? request()->route('productId') ?? 0);
            $file = request()->file('image');
            $position = request()->input('position');
            $position = $position !== null ? (int) $position : null;
            $dto = $this->handleUpload($productId, $file, $position);

            return $this->toRestResponse($dto, 201);
        }

        return null;
    }

    protected function handleUpload(int $productId, mixed $file, ?int $position): AdminCatalogProductImage
    {
        $product = Product::find($productId);
        if (! $product) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.product.not-found'));
        }

        if (! $file instanceof UploadedFile || ! $file->isValid()) {
            throw new InvalidInputException(__('bagistoapi::app.admin.product.image.image-required'), 422);
        }

        $mime = strtolower((string) $file->getMimeType());
        $ext = strtolower((string) $file->getClientOriginalExtension());

        if (
            ! in_array($mime, self::ALLOWED_MIMES, true)
            && ! in_array($ext, self::ALLOWED_EXTS, true)
        ) {
            throw new InvalidInputException(__('bagistoapi::app.admin.product.image.image-invalid-type'), 422);
        }

        if ($file->getSize() > self::MAX_BYTES) {
            throw new InvalidInputException(__('bagistoapi::app.admin.product.image.image-too-large'), 422);
        }

        try {
            Event::dispatch('catalog.product.update.before', $productId);

            $filename = Str::random(40).'.'.($ext ?: 'jpg');
            $dir = 'product/'.$productId;

            $path = Storage::disk('public')->putFileAs($dir, $file, $filename);

            if (! $path) {
                throw new InvalidInputException(__('bagistoapi::app.admin.product.image.upload-failed'), 500);
            }

            if ($position === null) {
                $maxPos = (int) ProductImage::where('product_id', $productId)->max('position');
                $position = $maxPos + 1;
            }

            $image = $this->productImageRepository->create([
                'type' => 'images',
                'path' => $path,
                'product_id' => $productId,
                'position' => $position,
            ]);

            Event::dispatch('catalog.product.update.after', $product);
        } catch (InvalidInputException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            throw new InvalidInputException(__('bagistoapi::app.admin.product.image.upload-failed'), 500);
        }

        return $this->mapRow(ProductImage::find($image->id), true, __('bagistoapi::app.admin.product.image.uploaded'));
    }

    /**
     * @param  array<int, array{id?: int|string, position?: int|string}>  $order
     */
    protected function handleReorder(int $productId, array $order): AdminCatalogProductImage
    {
        $product = Product::find($productId);
        if (! $product) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.product.not-found'));
        }

        if (empty($order)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.product.image.order-required'), 422);
        }

        $ids = [];
        foreach ($order as $row) {
            if (! is_array($row) || ! isset($row['id'])) {
                throw new InvalidInputException(__('bagistoapi::app.admin.product.image.order-invalid'), 422);
            }
            $ids[] = (int) $row['id'];
        }

        $existing = ProductImage::where('product_id', $productId)
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
                $id = (int) $row['id'];
                $pos = (int) ($row['position'] ?? 0);

                ProductImage::where('id', $id)
                    ->where('product_id', $productId)
                    ->update(['position' => $pos]);
            }

            Event::dispatch('catalog.product.update.after', $product);
        } catch (\Throwable $e) {
            report($e);
            throw new InvalidInputException(__('bagistoapi::app.admin.product.image.reorder-failed'), 500);
        }

        $result = new AdminCatalogProductImage;
        $result->id = $productId;
        $result->success = true;
        $result->message = __('bagistoapi::app.admin.product.image.reordered');
        $result->images = ProductImage::where('product_id', $productId)
            ->orderBy('position')
            ->get()
            ->map(fn ($img) => $this->mapRowArray($img))
            ->all();

        return $result;
    }

    protected function handleDelete(int $productId, int $imageId): AdminCatalogProductImage
    {
        $product = Product::find($productId);
        if (! $product) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.product.not-found'));
        }

        $image = ProductImage::where('id', $imageId)
            ->where('product_id', $productId)
            ->first();

        if (! $image) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.product.image.not-found'));
        }

        try {
            Event::dispatch('catalog.product.update.before', $productId);

            if ($image->path) {
                Storage::disk('public')->delete($image->path);
                try {
                    Storage::delete($image->path);
                } catch (\Throwable $e) {
                }
            }

            $image->delete();

            Event::dispatch('catalog.product.update.after', $product);
        } catch (\Throwable $e) {
            report($e);
            throw new InvalidInputException(__('bagistoapi::app.admin.product.image.delete-failed'), 500);
        }

        $result = new AdminCatalogProductImage;
        $result->id = $imageId;
        $result->success = true;
        $result->message = __('bagistoapi::app.admin.product.image.deleted');

        return $result;
    }

    protected function assertPermission(object $admin): void
    {
        $role = $admin->role ?? null;
        if (! $role) {
            throw new AuthorizationException(__('bagistoapi::app.admin.product.image.no-permission'));
        }

        if (($role->permission_type ?? null) === 'all') {
            return;
        }

        $perms = $role->permissions ?? [];
        if (is_string($perms)) {
            $perms = array_map('trim', explode(',', $perms));
        }
        if (! is_array($perms)) {
            $perms = [];
        }

        if (! in_array('catalog.products.edit', $perms, true) && ! in_array('*', $perms, true)) {
            throw new AuthorizationException(__('bagistoapi::app.admin.product.image.no-permission'));
        }
    }

    /**
     * Convert an AdminCatalogProductImage result DTO to a raw JsonResponse so
     * API Platform's SerializeProcessor short-circuits (it skips when $data is
     * already a Response). This avoids the project's CustomIriConverter trying
     * to generate an IRI for a non-Eloquent resource on the reorder/delete
     * write responses.
     */
    protected function toRestResponse(AdminCatalogProductImage $dto, int $status): JsonResponse
    {
        $payload = array_filter(
            [
                'id' => $dto->id,
                'productId' => $dto->productId,
                'path' => $dto->path,
                'position' => $dto->position,
                'url' => $dto->url,
                'success' => $dto->success,
                'message' => $dto->message,
                'images' => $dto->images,
            ],
            static fn ($v) => $v !== null,
        );

        return new JsonResponse($payload, $status);
    }

    protected function mapRow(ProductImage $image, bool $success = true, ?string $message = null): AdminCatalogProductImage
    {
        $dto = new AdminCatalogProductImage;
        $dto->id = (int) $image->id;
        $dto->productId = (int) $image->product_id;
        $dto->path = $image->path;
        $dto->position = (int) $image->position;
        $dto->url = $this->safeUrl($image);
        $dto->success = $success;
        $dto->message = $message;

        return $dto;
    }

    /**
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
        ];
    }

    protected function safeUrl(ProductImage $image): ?string
    {
        try {
            return $image->url;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
