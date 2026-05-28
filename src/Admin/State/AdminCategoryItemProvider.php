<?php

namespace Webkul\BagistoApi\Admin\State;

use Illuminate\Support\Facades\Storage;
use Webkul\BagistoApi\Admin\Models\AdminCategory;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\Category\Models\Category;

class AdminCategoryItemProvider extends AbstractAdminItemProvider
{
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.category.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return Category::with(['translations', 'filterableAttributes'])->find($id);
    }

    protected function mapToDto(object $category): AdminCategory
    {
        /** @var Category $category */
        $dto = new AdminCategory;
        $dto->id = (int) $category->id;
        $dto->position = (int) ($category->position ?? 0);
        $dto->status = (int) $category->status;
        $dto->parentId = $category->parent_id !== null ? (int) $category->parent_id : null;
        $dto->displayMode = $category->display_mode;
        $dto->logoUrl = $category->logo_path ? Storage::url($category->logo_path) : null;
        $dto->bannerUrl = $category->banner_path ? Storage::url($category->banner_path) : null;

        $primary = $category->translations->where('locale', app()->getLocale())->first()
            ?? $category->translations->first();

        $dto->name = $primary?->name;
        $dto->slug = $primary?->slug;
        $dto->description = $primary?->description;
        $dto->locale = $primary?->locale;
        $dto->createdAt = $category->created_at?->toIso8601String();
        $dto->updatedAt = $category->updated_at?->toIso8601String();

        // Build translations as plain associative arrays to avoid API Platform
        // serialising AdminCategoryTranslationDto instances as IRIs.
        $dto->translations = $category->translations->map(function ($t) {
            return [
                'locale'          => $t->locale,
                'name'            => $t->name,
                'slug'            => $t->slug,
                'description'     => $t->description,
                'metaTitle'       => $t->meta_title,
                'metaDescription' => $t->meta_description,
                'metaKeywords'    => $t->meta_keywords,
            ];
        })->values()->all();

        $dto->filterableAttributeIds = $category->filterableAttributes
            ->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->values()
            ->all();

        return $dto;
    }
}
