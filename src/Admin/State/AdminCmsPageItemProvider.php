<?php

namespace Webkul\BagistoApi\Admin\State;

use Webkul\BagistoApi\Admin\Models\AdminCmsPage;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\CMS\Models\Page;

class AdminCmsPageItemProvider extends AbstractAdminItemProvider
{
    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.cms.page.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return Page::with(['translations', 'channels'])->find($id);
    }

    protected function mapToDto(object $page): AdminCmsPage
    {
        /** @var Page $page */
        $dto = new AdminCmsPage;
        $dto->id = (int) $page->id;

        $primary = $page->translations->where('locale', app()->getLocale())->first()
            ?? $page->translations->first();

        $dto->urlKey = $primary?->url_key;
        $dto->pageTitle = $primary?->page_title;
        $dto->htmlContent = $primary?->html_content;
        $dto->metaTitle = $primary?->meta_title;
        $dto->metaKeywords = $primary?->meta_keywords;
        $dto->metaDescription = $primary?->meta_description;
        $dto->locale = $primary?->locale;
        $dto->createdAt = $page->created_at?->toIso8601String();
        $dto->updatedAt = $page->updated_at?->toIso8601String();

        $dto->translations = $page->translations->map(function ($t) {
            return [
                'locale'           => $t->locale,
                'url_key'          => $t->url_key,
                'page_title'       => $t->page_title,
                'html_content'     => $t->html_content,
                'meta_title'       => $t->meta_title,
                'meta_keywords'    => $t->meta_keywords,
                'meta_description' => $t->meta_description,
            ];
        })->values()->all();

        $dto->channels = $page->channels->map(function ($c) {
            return [
                'id'   => (int) $c->id,
                'code' => $c->code,
                'name' => $c->name,
            ];
        })->values()->all();

        if ($dto->channels) {
            $dto->channel = implode(',', array_column($dto->channels, 'code'));
        }

        return $dto;
    }
}
