<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use Webkul\BagistoApi\Admin\Dto\AdminCmsPageDetailDto;
use Webkul\BagistoApi\Admin\Helper\AdminAuthHelper;
use Webkul\BagistoApi\Admin\Models\AdminCmsPage;
use Webkul\BagistoApi\Admin\Security\AdminReadPermissions;
use Webkul\BagistoApi\Admin\State\Concerns\AbstractAdminItemProvider;
use Webkul\BagistoApi\Admin\State\Concerns\BuildsCmsPagePreviewUrl;
use Webkul\BagistoApi\Exception\AuthenticationException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;

/**
 * Item provider for the admin CMS → Pages detail endpoint.
 *
 * Returns the AdminCmsPage Eloquent model for GraphQL (so translations and
 * channels are field-selectable) and the AdminCmsPageDetailDto for REST (so the
 * REST payload stays byte-identical).
 */
class AdminCmsPageItemProvider extends AbstractAdminItemProvider
{
    use BuildsCmsPagePreviewUrl;

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        AdminReadPermissions::authorize($operation, AdminCmsPage::class);

        if (! AdminAuthHelper::resolveAdmin()) {
            throw new AuthenticationException(__('bagistoapi::app.admin.profile.unauthenticated'));
        }

        $id = (int) ($uriVariables['id'] ?? $context['args']['id'] ?? 0);

        if ($id <= 0) {
            throw new ResourceNotFoundException(__($this->getNotFoundLangKey()));
        }

        $page = $this->findEntity($id);

        if (! $page) {
            throw new ResourceNotFoundException(__($this->getNotFoundLangKey()));
        }

        if (! empty($context['graphql_operation_name'])) {
            return $page;
        }

        return $this->mapToDto($page);
    }

    protected function getNotFoundLangKey(): string
    {
        return 'bagistoapi::app.admin.cms.page.not-found';
    }

    protected function findEntity(int $id): ?object
    {
        return AdminCmsPage::with(['translations', 'channels'])->find($id);
    }

    protected function mapToDto(object $page): AdminCmsPageDetailDto
    {
        /** @var AdminCmsPage $page */
        $dto = new AdminCmsPageDetailDto;
        $dto->id = (int) $page->id;

        $primary = $page->translations->firstWhere('locale', app()->getLocale())
            ?? $page->translations->first();

        $dto->urlKey = $primary?->url_key;
        $dto->pageTitle = $primary?->page_title;
        $dto->htmlContent = $primary?->html_content;
        $dto->metaTitle = $primary?->meta_title;
        $dto->metaKeywords = $primary?->meta_keywords;
        $dto->metaDescription = $primary?->meta_description;
        $dto->locale = $primary?->locale;
        $dto->layout = $page->layout;
        $dto->previewUrl = $this->buildPreviewUrl($primary?->url_key);
        $dto->createdAt = $page->created_at?->toIso8601String();
        $dto->updatedAt = $page->updated_at?->toIso8601String();

        $dto->translations = $page->translations->map(function ($translation) {
            return [
                'locale' => $translation->locale,
                'url_key' => $translation->url_key,
                'page_title' => $translation->page_title,
                'html_content' => $translation->html_content,
                'meta_title' => $translation->meta_title,
                'meta_keywords' => $translation->meta_keywords,
                'meta_description' => $translation->meta_description,
            ];
        })->values()->all();

        $dto->channels = $page->channels->map(function ($channel) {
            return [
                'id' => (int) $channel->id,
                'code' => $channel->code,
                'name' => $channel->name,
            ];
        })->values()->all();

        if ($dto->channels) {
            $dto->channel = implode(',', array_column($dto->channels, 'code'));
        }

        return $dto;
    }
}
