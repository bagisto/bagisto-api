<?php

namespace Webkul\BagistoApi\Tests\Feature\RestApi;

use Webkul\BagistoApi\Tests\RestApiTestCase;
use Webkul\Core\Models\Country;
use Webkul\Core\Models\CountryState;

class CountryTest extends RestApiTestCase
{
    /**
     * The countries url.
     */
    private string $countriesUrl = '/api/shop/countries';

    /**
     * The country states url.
     */
    private string $countryStatesUrl = '/api/shop/country-states';

    /**
     * The seeded id of the United States, a country with states.
     */
    private int $countryWithStates = 244;

    /**
     * The seeded id of Afghanistan, a country without states.
     */
    private int $countryWithoutStates = 1;

    // ============================================================================
    // GET /countries (Collection)
    // ============================================================================

    /**
     * Test get countries returns ok.
     */
    public function test_get_countries_returns_ok(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->countriesUrl);

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * Test get countries returns non empty list.
     */
    public function test_get_countries_returns_non_empty_list(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->countriesUrl);

        $response->assertOk();
        expect(count($response->json()))->toBeGreaterThan(0);
    }

    /**
     * Test countries have expected fields.
     */
    public function test_countries_have_expected_fields(): void
    {
        $this->seedRequiredData();

        $first = $this->publicGet($this->countriesUrl)->json(0);

        expect($first)->toHaveKey('id');
        expect($first)->toHaveKey('code');
        expect($first)->toHaveKey('name');
        expect($first)->toHaveKey('states');
        expect($first)->toHaveKey('translations');
    }

    /**
     * Test countries id is integer.
     */
    public function test_countries_id_is_integer(): void
    {
        $this->seedRequiredData();

        $first = $this->publicGet($this->countriesUrl)->json(0);

        expect($first['id'])->toBeInt();
    }

    // ============================================================================
    // Pagination
    // ============================================================================

    /**
     * Test items per page limits collection size.
     */
    public function test_items_per_page_limits_collection_size(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->countriesUrl.'?per_page=5');

        $response->assertOk();
        expect(count($response->json()))->toBe(5);
    }

    /**
     * Test page parameter returns different results.
     */
    public function test_page_parameter_returns_different_results(): void
    {
        $this->seedRequiredData();

        $page1 = $this->publicGet($this->countriesUrl.'?per_page=5&page=1')->json();
        $page2 = $this->publicGet($this->countriesUrl.'?per_page=5&page=2')->json();

        expect(collect($page1)->pluck('id')->all())
            ->not()->toEqual(collect($page2)->pluck('id')->all());
    }

    /**
     * Test page beyond total returns empty.
     */
    public function test_page_beyond_total_returns_empty(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->countriesUrl.'?per_page=10&page=9999');

        $response->assertOk();
        expect($response->json())->toBe([]);
    }

    // ============================================================================
    // GET /countries/{id} (Single)
    // ============================================================================

    /**
     * Test get single country returns ok.
     */
    public function test_get_single_country_returns_ok(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->countryUrl($this->countryWithStates));

        $response->assertOk();
    }

    /**
     * Test get single country returns correct id.
     */
    public function test_get_single_country_returns_correct_id(): void
    {
        $this->seedRequiredData();

        $body = $this->publicGet($this->countryUrl($this->countryWithStates))->json();

        expect($body['id'])->toBe($this->countryWithStates);
    }

    /**
     * Test get single country returns expected fields.
     */
    public function test_get_single_country_returns_expected_fields(): void
    {
        $this->seedRequiredData();

        $body = $this->publicGet($this->countryUrl($this->countryWithStates))->json();

        expect($body)->toHaveKey('id');
        expect($body)->toHaveKey('code');
        expect($body)->toHaveKey('name');
        expect($body)->toHaveKey('states');
        expect($body)->toHaveKey('translations');
    }

    /**
     * Test get country with states includes states.
     */
    public function test_get_country_with_states_includes_states(): void
    {
        $this->seedRequiredData();

        $body = $this->publicGet($this->countryUrl($this->countryWithStates))->json();

        expect($body['states'])->toBeArray();
        expect(count($body['states']))->toBeGreaterThan(0);
    }

    /**
     * Test get country without states returns empty states.
     */
    public function test_get_country_without_states_returns_empty_states(): void
    {
        $this->seedRequiredData();

        $body = $this->publicGet($this->countryUrl($this->countryWithoutStates))->json();

        expect($body['states'])->toBe([]);
    }

    /**
     * Test get nonexistent country returns error.
     */
    public function test_get_nonexistent_country_returns_error(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->countryUrl(999999));

        expect(in_array($response->getStatusCode(), [404, 500]))->toBeTrue();
    }

    // ============================================================================
    // GET /countries/{country_id}/states (Nested Collection)
    // ============================================================================

    /**
     * Test get nested states returns ok.
     */
    public function test_get_nested_states_returns_ok(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->nestedStatesUrl($this->countryWithStates));

        $response->assertOk();
        expect($response->json())->toBeArray();
    }

    /**
     * Test get nested states all belong to country.
     */
    public function test_get_nested_states_all_belong_to_country(): void
    {
        $this->seedRequiredData();

        $states = $this->publicGet(
            $this->nestedStatesUrl($this->countryWithStates).'?per_page=50'
        )->json();

        foreach ($states as $state) {
            expect($state['countryId'])->toBe($this->countryWithStates);
        }
    }

    /**
     * Test get nested states have expected fields.
     */
    public function test_get_nested_states_have_expected_fields(): void
    {
        $this->seedRequiredData();

        $first = $this->publicGet($this->nestedStatesUrl($this->countryWithStates))->json(0);

        expect($first)->toHaveKey('id');
        expect($first)->toHaveKey('countryId');
        expect($first)->toHaveKey('countryCode');
        expect($first)->toHaveKey('code');
        expect($first)->toHaveKey('defaultName');
    }

    /**
     * Test get nested states for country without states returns empty.
     */
    public function test_get_nested_states_for_country_without_states_returns_empty(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->nestedStatesUrl($this->countryWithoutStates));

        $response->assertOk();
        expect($response->json())->toBe([]);
    }

    // ============================================================================
    // GET /countries/{country_id}/states/{id} (Nested Single)
    // ============================================================================

    /**
     * Test get nested single state returns ok.
     */
    public function test_get_nested_single_state_returns_ok(): void
    {
        $this->seedRequiredData();
        $stateId = $this->firstStateIdForCountry($this->countryWithStates);

        $response = $this->publicGet($this->nestedStateUrl($this->countryWithStates, $stateId));

        $response->assertOk();
    }

    /**
     * Test get nested single state returns correct ids.
     */
    public function test_get_nested_single_state_returns_correct_ids(): void
    {
        $this->seedRequiredData();
        $stateId = $this->firstStateIdForCountry($this->countryWithStates);

        $body = $this->publicGet($this->nestedStateUrl($this->countryWithStates, $stateId))->json();

        expect($body['id'])->toBe($stateId);
        expect($body['countryId'])->toBe($this->countryWithStates);
    }

    // ============================================================================
    // GET /country-states/{id} (Root Single)
    // ============================================================================

    /**
     * Test get root country state returns ok.
     */
    public function test_get_root_country_state_returns_ok(): void
    {
        $this->seedRequiredData();
        $stateId = $this->firstStateIdForCountry($this->countryWithStates);

        $response = $this->publicGet($this->countryStateUrl($stateId));

        $response->assertOk();
    }

    /**
     * Test get root country state returns expected fields.
     */
    public function test_get_root_country_state_returns_expected_fields(): void
    {
        $this->seedRequiredData();
        $stateId = $this->firstStateIdForCountry($this->countryWithStates);

        $body = $this->publicGet($this->countryStateUrl($stateId))->json();

        expect($body)->toHaveKey('id');
        expect($body)->toHaveKey('countryId');
        expect($body)->toHaveKey('countryCode');
        expect($body)->toHaveKey('code');
        expect($body)->toHaveKey('defaultName');
        expect($body['id'])->toBe($stateId);
    }

    /**
     * Test root and nested state return same data.
     */
    public function test_root_and_nested_state_return_same_data(): void
    {
        $this->seedRequiredData();
        $stateId = $this->firstStateIdForCountry($this->countryWithStates);

        $root = $this->publicGet($this->countryStateUrl($stateId))->json();
        $nested = $this->publicGet($this->nestedStateUrl($this->countryWithStates, $stateId))->json();

        expect($root['id'])->toBe($nested['id']);
        expect($root['code'])->toBe($nested['code']);
        expect($root['countryId'])->toBe($nested['countryId']);
    }

    /**
     * Test get nonexistent state returns error.
     */
    public function test_get_nonexistent_state_returns_error(): void
    {
        $this->seedRequiredData();

        $response = $this->publicGet($this->countryStateUrl(999999));

        expect(in_array($response->getStatusCode(), [404, 500]))->toBeTrue();
    }

    /**
     * Country url.
     */
    private function countryUrl(int $id): string
    {
        return $this->countriesUrl.'/'.$id;
    }

    /**
     * Nested states url.
     */
    private function nestedStatesUrl(int $countryId): string
    {
        return $this->countriesUrl.'/'.$countryId.'/states';
    }

    /**
     * Nested state url.
     */
    private function nestedStateUrl(int $countryId, int $stateId): string
    {
        return $this->countriesUrl.'/'.$countryId.'/states/'.$stateId;
    }

    /**
     * Country state url.
     */
    private function countryStateUrl(int $id): string
    {
        return $this->countryStatesUrl.'/'.$id;
    }

    /**
     * First state id for country.
     */
    private function firstStateIdForCountry(int $countryId): int
    {
        $id = CountryState::where('country_id', $countryId)->orderBy('id')->value('id');

        if (! $id) {
            $this->markTestSkipped("No states found for country {$countryId}.");
        }

        return (int) $id;
    }
}
