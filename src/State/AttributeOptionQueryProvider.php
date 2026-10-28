<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Webkul\BagistoApi\Models\AttributeOption;

class AttributeOptionQueryProvider implements ProviderInterface
{
    /**
     * Provide.
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?AttributeOption
    {
        $id = $uriVariables['id'] ?? $context['args']['id'] ?? null;

        if (! $id) {
            throw new BadRequestHttpException(
                __('bagistoapi::app.graphql.attribute-option.id-required')
            );
        }

        $numericId = $this->extractNumericId($id);

        if ($numericId === null) {
            throw new BadRequestHttpException(
                __('bagistoapi::app.graphql.attribute-option.invalid-id-format')
            );
        }

        $attributeOption = AttributeOption::find($numericId);

        if (! $attributeOption) {
            throw new BadRequestHttpException(
                __('bagistoapi::app.graphql.attribute-option.not-found')
            );
        }

        return $attributeOption;
    }

    /**
     * Extract numeric ID from any format
     * Supports:
     */
    private function extractNumericId($id): ?int
    {
        if (is_numeric($id)) {
            return intval($id);
        }

        if (
            is_string($id)
            && str_contains($id, '/')
        ) {
            $parts = explode('/', $id);
            $lastPart = end($parts);

            if (is_numeric($lastPart)) {
                return intval($lastPart);
            }
        }

        return null;
    }
}
