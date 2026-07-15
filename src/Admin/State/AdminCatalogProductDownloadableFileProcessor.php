<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\AuthorizationException;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Product\Models\Product;

class AdminCatalogProductDownloadableFileProcessor implements ProcessorInterface
{
    use ChecksAdminPermission;

    protected const MAX_BYTES = 128 * 1024 * 1024;

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $admin = AdminAuthHelper::resolveAdmin();
        if (! $admin) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        if (! $this->adminHasPermission($admin, 'catalog.products.edit')) {
            throw new AuthorizationException(__('bagistoapi::app.admin.product.downloadable.no-permission'));
        }

        $productId = (int) ($uriVariables['productId'] ?? request()->route('productId') ?? 0);

        if (! Product::find($productId)) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.product.not-found'));
        }

        $isSample = str_contains(request()->path(), 'downloadable-samples');

        return $this->handleUpload($productId, $isSample);
    }

    protected function handleUpload(int $productId, bool $isSample): JsonResponse
    {
        $file = request()->file('file');

        if (! $file instanceof UploadedFile || ! $file->isValid()) {
            throw new InvalidInputException(__('bagistoapi::app.admin.product.downloadable.file-required'), 422);
        }

        if ($file->getSize() > self::MAX_BYTES) {
            throw new InvalidInputException(__('bagistoapi::app.admin.product.downloadable.file-too-large'), 422);
        }

        $dir = ($isSample ? 'product_downloadable_samples/' : 'product_downloadable_links/').$productId;

        try {
            Event::dispatch('catalog.product.update.before', $productId);

            $path = $file->store($dir, 'private');

            if (! $path) {
                throw new InvalidInputException(__('bagistoapi::app.admin.product.downloadable.upload-failed'), 500);
            }

            Event::dispatch('catalog.product.update.after', Product::find($productId));
        } catch (InvalidInputException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            throw new InvalidInputException(__('bagistoapi::app.admin.product.downloadable.upload-failed'), 500);
        }

        return new JsonResponse([
            'type' => $isSample ? 'sample' : 'link',
            'path' => $path,
            'name' => $file->getClientOriginalName(),
            'url' => $this->safeUrl($path),
        ], 201);
    }

    protected function safeUrl(string $path): ?string
    {
        try {
            return Storage::disk('private')->url($path);
        } catch (\Throwable) {
            return null;
        }
    }
}
