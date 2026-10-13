<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;

/**
 * Stops API Platform loading a resource by id for cart mutations, which have no table behind them.
 * The input is deserialized and handled by the operation's processor.
 */
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
