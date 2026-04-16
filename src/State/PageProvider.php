<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\Page;
use Webkul\CMS\Repositories\PageRepository;

/**
 * Provider for fetching CMS pages
 * Returns arrays for REST API and Eloquent models for GraphQL
 */
class PageProvider implements ProviderInterface
{
    public function __construct(
        private readonly PageRepository $pageRepository
    ) {}

    public function provide(
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): object|array|null {
        // Check if it's a GraphQL operation (class name contains "GraphQl")
        $isGraphQL = str_contains($operation::class, 'GraphQl');

        // For GraphQL operations, return Eloquent models
        if ($isGraphQL) {
            return $this->provideForGraphQL($operation, $uriVariables, $context);
        }

        // For REST operations, return formatted arrays
        return $this->provideForRest($operation, $uriVariables, $context);
    }

    /**
     * Provide data for GraphQL - return Eloquent models
     */
    private function provideForGraphQL(Operation $operation, array $uriVariables, array $context): mixed
    {
        $name = $operation->getName();

        // Handle pageByUrlKey - this goes through the resolver
        if ($name === 'pageByUrlKey') {
            return null; // Let the resolver handle it
        }

        // Check if it's an item query
        $isItem = $operation instanceof Get;

        if ($isItem) {
            if (isset($uriVariables['id'])) {
                return Page::with('translations')->find($uriVariables['id']);
            }

            return null;
        }

        // Collection query
        return Page::with('translations')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Provide data for REST API - return formatted arrays
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
     * Provide single page item
     */
    private function provideItem(array $uriVariables, array $context): ?array
    {
        if (isset($uriVariables['id'])) {
            $id = $uriVariables['id'];
            $page = Page::with('translations')->find($id);

            if (! $page) {
                return null;
            }

            return $this->formatPage($page);
        }

        return null;
    }

    /**
     * Provide collection of pages
     */
    private function provideCollection(array $context): iterable
    {
        $pages = Page::with('translations')
            ->orderBy('created_at', 'desc')
            ->get();

        return $pages->map(function ($page) {
            return $this->formatPage($page);
        });
    }

    /**
     * Format page for REST API response
     */
    private function formatPage($page): array
    {
        $translation = $page->translations->firstWhere('locale', app()->getLocale())
            ?? $page->translations->first();

        return [
            'id'          => '/api/shop/pages/'.$page->id,
            '_id'         => $page->id,
            'layout'      => $page->layout,
            'createdAt'   => $page->created_at?->toIso8601String(),
            'updatedAt'   => $page->updated_at?->toIso8601String(),
            'translation' => $translation ? $this->formatTranslation($translation) : null,
        ];
    }

    /**
     * Format translation for REST API response
     */
    private function formatTranslation($translation): array
    {
        return [
            'id'              => '/api/shop/page_translations/'.$translation->id,
            '_id'             => $translation->id,
            'pageTitle'       => $translation->page_title,
            'urlKey'          => $translation->url_key,
            'htmlContent'     => $translation->html_content,
            'metaTitle'       => $translation->meta_title,
            'metaDescription' => $translation->meta_description,
            'metaKeywords'    => $translation->meta_keywords,
            'locale'          => $translation->locale,
            'cmsPageId'       => (string) $translation->cms_page_id,
        ];
    }
}
