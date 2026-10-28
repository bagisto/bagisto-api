<?php

namespace Webkul\BagistoApi\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use Webkul\BagistoApi\Dto\ProductAttributeFileOutput;
use Webkul\BagistoApi\State\ProductAttributeFileProvider;

class ProductAttributeFileQueryResolver implements QueryItemResolverInterface
{
    /**
     * Create a new resolver instance.
     */
    public function __construct(protected ProductAttributeFileProvider $provider) {}

    /**
     * Resolve a product's file attribute as base64 content.
     */
    public function __invoke(?object $item, array $context): ProductAttributeFileOutput
    {
        return $this->provider->provideForGraphQl(
            (int) ($context['args']['productId'] ?? 0),
            (int) ($context['args']['attributeId'] ?? 0),
        );
    }
}
