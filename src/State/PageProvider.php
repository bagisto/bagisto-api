<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Illuminate\Database\Eloquent\Builder;
use Webkul\BagistoApi\Models\Page;
use Webkul\CMS\Repositories\PageRepository;

class PageProvider implements ProviderInterface
{
    /**
     * Create a new instance.
     */
    public function __construct(
        private readonly PageRepository $pageRepository
    ) {}

    /**
     * Provide.
     */
    public function provide(
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): object|array|null {
        $isGraphQL = str_contains($operation::class, 'GraphQl');

        if ($isGraphQL) {
            return $this->provideForGraphQL($operation, $uriVariables, $context);
        }

        return $this->provideForRest($operation, $uriVariables, $context);
    }

    /**
     * Provide data for GraphQL - return Eloquent models.
     */
    private function provideForGraphQL(Operation $operation, array $uriVariables, array $context): mixed
    {
        $name = $operation->getName();

        if ($name === 'pageByUrlKey') {
            return null;
        }

        $isItem = $operation instanceof Get;

        if ($isItem) {
            if (isset($uriVariables['id'])) {
                return $this->channelPages()->find($uriVariables['id']);
            }

            return null;
        }

        return $this->channelPages()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Provide data for REST API - return formatted arrays.
     */
    private function provideForRest(Operation $operation, array $uriVariables, array $context)
    {
        if ($operation instanceof Get) {
            return $this->provideItem($uriVariables, $context);
        }

        if ($operation instanceof GetCollection) {
            return $this->provideCollection($context);
        }

        return null;
    }

    /**
     * Provide single page item.
     */
    private function provideItem(array $uriVariables, array $context): ?array
    {
        if (isset($uriVariables['id'])) {
            $id = $uriVariables['id'];
            $page = $this->channelPages()->find($id);

            if (! $page) {
                return null;
            }

            return $this->formatPage($page);
        }

        return null;
    }

    /**
     * Provide collection of pages with page/itemsPerPage pagination honoured.
     */
    private function provideCollection(array $context): iterable
    {
        $request = request();
        $page = max(1, (int) $request->query('page', 1));
        $itemsPerPage = (int) $request->query('itemsPerPage', 10);
        $itemsPerPage = max(1, min($itemsPerPage, 100));

        $query = $this->channelPages()->orderBy('created_at', 'desc');

        if ($urlKey = $request->query('url_key')) {
            $query->whereHas('translations', fn ($translations) => $translations->where('url_key', $urlKey));
        }

        return $query
            ->offset(($page - 1) * $itemsPerPage)
            ->limit($itemsPerPage)
            ->get()
            ->map(fn ($page) => $this->formatPage($page));
    }

    /**
     * Build the page query limited to the current channel.
     */
    private function channelPages(): Builder
    {
        return Page::with('translations')
            ->whereHas('channels', function ($query) {
                $query->where('channels.id', core()->getCurrentChannel()->id);
            });
    }

    /**
     * Format page for REST API response.
     */
    private function formatPage($page): array
    {
        $translation = $page->translations->firstWhere('locale', app()->getLocale())
            ?? $page->translations->first();

        return [
            'id' => '/api/shop/pages/'.$page->id,
            '_id' => $page->id,
            'layout' => $page->layout,
            'createdAt' => $page->created_at?->toIso8601String(),
            'updatedAt' => $page->updated_at?->toIso8601String(),
            'translation' => $translation ? $this->formatTranslation($translation) : null,
        ];
    }

    /**
     * Format translation for REST API response.
     */
    private function formatTranslation($translation): array
    {
        return [
            'id' => '/api/shop/page_translations/'.$translation->id,
            '_id' => $translation->id,
            'pageTitle' => $translation->page_title,
            'urlKey' => $translation->url_key,
            'htmlContent' => $translation->html_content,
            'metaTitle' => $translation->meta_title,
            'metaDescription' => $translation->meta_description,
            'metaKeywords' => $translation->meta_keywords,
            'locale' => $translation->locale,
            'cmsPageId' => (string) $translation->cms_page_id,
        ];
    }
}
