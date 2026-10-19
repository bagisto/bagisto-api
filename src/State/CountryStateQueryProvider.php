<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\CountryState;

class CountryStateQueryProvider implements ProviderInterface
{
    /**
     * Provide.
     */
    public function provide(
        Operation $operation,
        array $uriVariables = [],
        array $context = []
    ): ?CountryState {
        $id = $uriVariables['id'] ?? null;

        if (! $id) {
            return null;
        }

        $numericId = $this->extractNumericId($id);

        if ($numericId === null) {
            return null;
        }

        return CountryState::find($numericId);
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
