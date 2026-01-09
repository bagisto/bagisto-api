<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;

/**
 * Handles denormalization of CartToken mutations, skipping resource loading for operations that don't require ID resolution.
 */
class CartTokenDenormalizationProvider implements ProviderInterface
{
    public function __construct(
        private ProviderInterface $decorated,
    ) {}

    /**
     * Provide denormalized input for CartToken mutations.
     */
    public function provide(
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): object|array|null {
        $operationName = $operation->getName();

        if ($operationName === 'createOrGetCart') {
            return null;
        }

        return $this->decorated->provide($operation, $uriVariables, $context);
    }
}
