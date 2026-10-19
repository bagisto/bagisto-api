<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Laravel\Eloquent\State\LinksHandlerInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class SnakeCaseLinksHandler implements LinksHandlerInterface
{
    /**
     * Create a new instance.
     */
    public function __construct(
        private readonly LinksHandlerInterface $inner,
    ) {}

    /**
     * Handle links.
     */
    public function handleLinks(Builder $builder, array $uriVariables, array $context): Builder
    {
        if (isset($context['linkProperty'])) {
            $context['linkProperty'] = Str::snake($context['linkProperty']);
        }

        return $this->inner->handleLinks($builder, $uriVariables, $context);
    }
}
