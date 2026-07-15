<?php

namespace Webkul\BagistoApi\Admin\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Admin\Models\AdminRmaCustomField;

class AdminRmaCustomFieldWriteProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?object
    {
        $dto = new AdminRmaCustomField;
        $dto->id = isset($uriVariables['id']) ? (int) $uriVariables['id'] : null;

        return $dto;
    }
}
