<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * Fixes camelCase/snake_case mismatch between GraphQL field names and Eloquent
 * relationship names in API Platform's LinksHandler.
 *
 * GraphQL passes camelCase linkProperty (e.g. "attributeValues") but Link metadata
 * stores snake_case fromProperty (e.g. "attribute_values"). This decorator normalizes
 * linkProperty to snake_case before delegating to the original handler.
 *
 * Single-word names like "variants" or "images" are unaffected.
 *
 * @implements LinksHandlerInterface<\Illuminate\Database\Eloquent\Model>
 */
class SnakeCaseLinksHandler implements LinksHandlerInterface
{
    public function __construct(
        private readonly LinksHandlerInterface $inner,
    ) {}

    public function handleLinks(Builder $builder, array $uriVariables, array $context): Builder
    {
        if (isset($context['linkProperty'])) {
            $context['linkProperty'] = Str::snake($context['linkProperty']);
        }

        return $this->inner->handleLinks($builder, $uriVariables, $context);
    }
}
