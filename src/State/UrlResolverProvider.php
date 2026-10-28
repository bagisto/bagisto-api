<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Exception\InvalidInputException;
use Webkul\BagistoApi\Exception\ResourceNotFoundException;
use Webkul\BagistoApi\Models\UrlResolver;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Marketing\Repositories\URLRewriteRepository;
use Webkul\Product\Repositories\ProductRepository;

/**
 * Mirrors the storefront catch-all route (ProductsCategoriesProxyController::index) for a path.
 */
class UrlResolverProvider implements ProviderInterface
{
    /**
     * Slugs the storefront accepts, including Chinese, Japanese, Arabic and Cyrillic characters.
     */
    protected const SLUG_PATTERN = '/^([\p{L}\p{N}\p{M}\x{0900}-\x{097F}\x{0590}-\x{05FF}\x{0600}-\x{06FF}\x{0400}-\x{04FF}_-]+\/?)+$/u';

    public function __construct(
        protected CategoryRepository $categoryRepository,
        protected ProductRepository $productRepository,
        protected URLRewriteRepository $urlRewriteRepository,
    ) {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        return [$this->resolve(request()->query('path'))];
    }

    public function resolve(mixed $path): UrlResolver
    {
        if (! is_string($path) || trim($path) === '') {
            throw new InvalidInputException(__('bagistoapi::app.graphql.url-resolver.path-required'), 422);
        }

        $slug = urldecode(trim($path, '/'));

        if (! preg_match(self::SLUG_PATTERN, $slug)) {
            return $this->result($slug, 'home');
        }

        if ($category = $this->categoryRepository->findBySlug($slug)) {
            return $this->result($slug, 'category', $category->id);
        }

        $searchEngine = core()->getConfigData('catalog.products.search.engine') == 'elastic'
            ? core()->getConfigData('catalog.products.search.storefront_mode')
            : 'database';

        if ($product = $this->productRepository->setSearchEngine($searchEngine)->findBySlug($slug)) {
            if (! $product->url_key || ! $product->visible_individually || ! $product->status) {
                throw new ResourceNotFoundException(__('bagistoapi::app.graphql.url-resolver.not-found'));
            }

            $rewrite = $this->urlRewriteRepository->findOneWhere([
                'entity_type' => 'product',
                'request_path' => $slug,
                'locale' => app()->getLocale(),
            ]);

            return $rewrite
                ? $this->result($slug, 'redirect', null, $rewrite->target_path, (int) $rewrite->redirect_type)
                : $this->result($slug, 'product', $product->id);
        }

        if (str_contains($slug, '/')) {
            $trimmed = last(explode('/', $slug));

            if ($this->categoryRepository->findBySlug($trimmed)) {
                return $this->result($slug, 'redirect', null, $trimmed, 301);
            }
        }

        $rewrite = $this->urlRewriteRepository->findOneWhere([
            'request_path' => $slug,
            'locale' => app()->getLocale(),
        ]);

        if ($rewrite) {
            return $this->result($slug, 'redirect', null, $rewrite->target_path, (int) $rewrite->redirect_type);
        }

        throw new ResourceNotFoundException(__('bagistoapi::app.graphql.url-resolver.not-found'));
    }

    protected function result(string $path, string $type, ?int $entityId = null, ?string $targetPath = null, ?int $redirectType = null): UrlResolver
    {
        $result = new UrlResolver;
        $result->path = $path;
        $result->type = $type;
        $result->entity_id = $entityId;
        $result->target_path = $targetPath;
        $result->redirect_type = $redirectType;

        return $result;
    }
}
