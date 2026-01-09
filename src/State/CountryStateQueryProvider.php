<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\CountryState;

/**
 * Provides CountryState data for single item REST API queries.
 * Supports both numeric ID and IRI format
 */
class CountryStateQueryProvider implements ProviderInterface
{
    public function provide(
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): ?CountryState {
        $id = $uriVariables['id'] ?? null;

        if (! $id) {
            return null;
        }

        // Extract numeric ID from any format (numeric or IRI)
        $numericId = $this->extractNumericId($id);

        if ($numericId === null) {
            return null;
        }

        return CountryState::find($numericId);
    }

    /**
     * Extract numeric ID from any format
     * Supports:
     * - /api/shop/country-states/1 (IRI format)
     * - 1 (numeric only)
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
