<?php

namespace Webkul\BagistoApi\Repositories;

use Webkul\BagistoApi\Models\CountryState;
use Webkul\Core\Eloquent\Repository;

class CountryStateRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return CountryState::class;
    }

    /**
     * Find a state by id, scoped to the given country when one is passed.
     */
    public function findForCountry(int $stateId, ?int $countryId = null): ?CountryState
    {
        $query = $this->model->newQuery()->where('id', $stateId);

        if ($countryId !== null) {
            $query->where('country_id', $countryId);
        }

        return $query->first();
    }
}
