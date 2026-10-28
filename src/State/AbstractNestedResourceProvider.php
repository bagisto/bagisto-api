<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;

abstract class AbstractNestedResourceProvider implements ProviderInterface
{
    abstract protected function getModelClass(): string;

    /**
     * The column a collection is ordered by, or null to leave the order to the database.
     */
    protected function orderColumn(): ?string
    {
        return null;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $modelClass = $this->getModelClass();

        if (isset($uriVariables['productId']) && isset($uriVariables['id'])) {
            return $modelClass::where('id', $uriVariables['id'])
                ->where('product_id', $uriVariables['productId'])
                ->first();
        }

        if (isset($uriVariables['productId'])) {
            $query = $modelClass::where('product_id', $uriVariables['productId']);

            if ($column = $this->orderColumn()) {
                $query->orderBy($column);
            }

            return $query->get();
        }

        if (isset($uriVariables['id'])) {
            return $modelClass::find($uriVariables['id']);
        }

        if ($column = $this->orderColumn()) {
            return $modelClass::orderBy($column)->get();
        }

        return $modelClass::all();
    }
}
