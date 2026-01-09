<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;

abstract class AbstractNestedResourceProvider implements ProviderInterface
{
    abstract protected function getModelClass(): string;

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $modelClass = $this->getModelClass();

        if (isset($uriVariables['productId']) && isset($uriVariables['id'])) {
            return $modelClass::where('id', $uriVariables['id'])
                ->where('product_id', $uriVariables['productId'])
                ->first();
        }

        if (isset($uriVariables['productId'])) {
            return $modelClass::where('product_id', $uriVariables['productId'])->get();
        }

        if (isset($uriVariables['id'])) {
            return $modelClass::find($uriVariables['id']);
        }

        return $modelClass::all();
    }
}
