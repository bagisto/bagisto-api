// tests/restAPI/api/automation/countries.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import {
  assertCountryFields,
  assertCountryWithStates,
  assertCountryWithTranslations,
} from '../../rest/assertions/country.assertions';

test.describe('Countries REST API', () => {
  test('Should return all countries', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.COUNTRIES);
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(Array.isArray(body)).toBeTruthy();
    console.log('Countries count:', body.length);

    if (body.length > 0) {
      console.log('First country:', JSON.stringify({
        id: body[0].id, code: body[0].code, name: body[0].name,
      }, null, 2));
    }
  });

  test('Should return single country by ID', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.COUNTRIES);
    const body = await response.json();
    expect(body.length).toBeGreaterThan(0);

    const countryId = body[0].id;
    const singleResponse = await sendRestRequest(request, ENDPOINTS.COUNTRY(countryId));
    expect(singleResponse.status()).toBe(200);
    const singleBody = await singleResponse.json();
    expect(singleBody.id).toBe(countryId);
    expect(singleBody).toHaveProperty('states');
    expect(singleBody).toHaveProperty('translations');
    console.log('Single country:', JSON.stringify({ id: singleBody.id, name: singleBody.name, statesCount: singleBody.states?.length }));
  });

  test('Should return 404 for non-existent country', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.COUNTRY(999999));
    expect(response.status()).toBe(404);
    console.log('404 for non-existent country');
  });

  test('Should return nested states for a specific country', async ({ request }) => {
    const countriesResp = await sendRestRequest(request, ENDPOINTS.COUNTRIES);
    const countries = await countriesResp.json();
    expect(countries.length).toBeGreaterThan(0);

    const countryId = countries[0].id;
    const response = await sendRestRequest(request, ENDPOINTS.COUNTRY_STATES_NESTED(countryId));
    // /api/shop/countries/{id}/states is registered — 200 expected
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(Array.isArray(body)).toBeTruthy();
    console.log(`Nested states lookup for country ${countryId}: got ${body.length} states`);
  });
});
