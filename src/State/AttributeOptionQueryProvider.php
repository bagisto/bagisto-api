<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Webkul\BagistoApi\Models\AttributeOption;

/**
 * Custom provider for single AttributeOption GraphQL query
 * Validates ID format and returns proper error messages
 */
class AttributeOptionQueryProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?AttributeOption
    {
        // Get ID from uriVariables or from GraphQL args context
        $id = $uriVariables['id'] ?? $context['args']['id'] ?? null;

        if (! $id) {
            throw new BadRequestHttpException(
                __('bagistoapi::app.graphql.attribute-option.id-required')
            );
        }

        // Extract numeric ID from any format
        $numericId = $this->extractNumericId($id);

        // If extraction failed, provide helpful error message
        if ($numericId === null) {
            throw new BadRequestHttpException(
                __('bagistoapi::app.graphql.attribute-option.invalid-id-format')
            );
        }

        // Fetch the attribute option
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
     * - /api/shop/attribute-options/1 (IRI format - valid)
     * - 1 (numeric only - also valid now)
     * Returns null if invalid format
     */
    private function extractNumericId($id): ?int
    {
        // If already numeric, return as is
        if (is_numeric($id)) {
            return intval($id);
        }

        // If string, extract from IRI format
        if (is_string($id) && str_contains($id, '/')) {
            $parts = explode('/', $id);
            $lastPart = end($parts);

            if (is_numeric($lastPart)) {
                return intval($lastPart);
            }
        }

        // Invalid format
        return null;
    }
}
