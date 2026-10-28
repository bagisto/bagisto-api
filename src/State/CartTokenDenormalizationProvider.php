<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;

class CartTokenDenormalizationProvider implements ProviderInterface
{
    /**
     * Create a new instance.
     */
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
