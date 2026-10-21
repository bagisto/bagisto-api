<?php

namespace Webkul\BagistoApi\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Webkul\BagistoApi\Models\CountryState;
use Webkul\BagistoApi\Repositories\CountryStateRepository;

class CountryStateQueryProvider implements ProviderInterface
{
    /**
     * Create a new provider instance.
     */
    public function __construct(protected CountryStateRepository $countryStateRepository) {}

    /**
     * Provide the state, scoped to the country in the URI when the route is nested under one.
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

        $countryId = null;

        if (array_key_exists('country_id', $uriVariables)) {
            $countryId = $this->extractNumericId($uriVariables['country_id']);

            if ($countryId === null) {
                return null;
            }
        }

        return $this->countryStateRepository->findForCountry($numericId, $countryId);
    }

    /**
     * Extract the numeric id from a numeric value, an IRI or a single-identifier array.
     */
    private function extractNumericId($id): ?int
    {
        if (is_array($id)) {
            $id = $id['id'] ?? reset($id);
        }

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
