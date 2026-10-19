<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;

class CartTokenMutationProvider implements ProviderInterface
{
    /**
     * Provide no resource so the deserialized input reaches the processor.
     */
    public function provide(
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): ?object {
        return null;
    }
}
