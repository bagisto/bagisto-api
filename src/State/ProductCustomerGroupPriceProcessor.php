<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;

class ProductCustomerGroupPriceProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $persistProcessor
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (isset($uriVariables['productId'])) {
            $data->product_id = $uriVariables['productId'];
        }

        $result = $this->persistProcessor->process($data, $operation, $uriVariables, $context);

        if ($result && method_exists($result, 'getKey')) {
            $result->id = $result->getKey();
        }

        return $result;
    }
}
