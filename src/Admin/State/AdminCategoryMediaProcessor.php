<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Admin\State\Concerns\ChecksAdminPermission;
use Webkul\BagistoApi\Admin\State\Concerns\HandlesAdminMediaUpload;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\Category\Models\Category;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Core\Helpers\MediaFileName;

/**
 * Stores and removes a category logo or banner the way CategoryRepository::uploadImages does.
 */
class AdminCategoryMediaProcessor implements ProcessorInterface
{
    use ChecksAdminPermission, HandlesAdminMediaUpload;

    public function __construct(
        protected CategoryRepository $categoryRepository,
        protected MediaFileName $mediaFileName,
        protected AdminCategoryItemProvider $itemProvider,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): JsonResponse
    {
        $this->authorizedAdmin('catalog.categories.edit', 'bagistoapi::app.admin.category.no-permission');

        $id = (int) $this->pathVariable($uriVariables, 'id');

        $category = Category::find($id);

        if (! $category) {
            throw new ResourceNotFoundException(__('bagistoapi::app.admin.category.not-found'));
        }

        $this->keepSlugForRewriteListener($category);

        if ($operation instanceof Delete) {
            return $this->remove($category, (string) $this->pathVariable($uriVariables, 'type'));
        }

        return $this->upload($category);
    }

    protected function upload(Category $category): JsonResponse
    {
        $types = array_values(array_filter(['logo', 'banner'], fn ($type) => request()->hasFile($type)));

        if (count($types) !== 1) {
            throw new InvalidInputException(__('bagistoapi::app.admin.category.media-type-required'), 422);
        }

        $prefix = $types[0];

        $file = $this->validatedUpload($prefix, 'mimes:bmp,jpeg,jpg,png,webp', [
            'alt_text' => ['nullable', 'string', 'max:255'],
            'file_name' => ['nullable', 'string', 'max:'.MediaFileName::MAX_LENGTH],
        ]);

        $type = $prefix.'_path';

        Event::dispatch('catalog.category.update.before', $category->id);

        if ($category->{$type}) {
            Storage::delete($category->{$type});
        }

        $encoded = image_manager()->read($file)->encodeByExtension('webp');

        $category->{$type} = $this->mediaFileName->resolve(
            'category/'.$category->id,
            request()->input('file_name'),
            'webp'
        );

        Storage::put($category->{$type}, (string) $encoded);

        $category->save();

        if (request()->has('alt_text')) {
            $this->categoryRepository->uploadImages([
                $type => [$category->{$type}],
                $prefix.'_meta' => [['alt_text' => request()->input('alt_text')]],
            ], $category, $type);
        }

        Event::dispatch('catalog.category.update.after', $category);

        return $this->respond($category->id);
    }

    protected function remove(Category $category, string $prefix): JsonResponse
    {
        if (! in_array($prefix, ['logo', 'banner'], true)) {
            throw new InvalidInputException(__('bagistoapi::app.admin.category.media-type-required'), 422);
        }

        Event::dispatch('catalog.category.update.before', $category->id);

        $this->categoryRepository->uploadImages([], $category, $prefix.'_path');

        Event::dispatch('catalog.category.update.after', $category);

        return $this->respond($category->id);
    }

    /**
     * The URL-rewrite listener compares the request slug with the stored one; a media-only
     * write sends none, so pass the current slug through and no redirect is written.
     */
    protected function keepSlugForRewriteListener(Category $category): void
    {
        $locale = request()->input('locale') ?: app()->getLocale();

        $slug = $category->translate($locale)?->slug;

        if ($slug) {
            request()->merge(['locale' => $locale, $locale => ['slug' => $slug]]);
        }
    }

    protected function respond(int $id): JsonResponse
    {
        return $this->detailResponse(
            $this->itemProvider,
            Category::with(['translations', 'filterableAttributes'])->find($id),
        );
    }
}
