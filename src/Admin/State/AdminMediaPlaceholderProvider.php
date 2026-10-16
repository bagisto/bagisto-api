<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;

/**
 * Hands an empty resource to the DELETE media operations so the processor runs; the
 * processor owns auth, lookup and the 404.
 */
class AdminMediaPlaceholderProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $class = $operation->getClass();

        return new $class;
    }
}
