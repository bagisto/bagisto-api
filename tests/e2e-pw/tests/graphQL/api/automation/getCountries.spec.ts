import { test, expect } from '@playwright/test';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../graphql/helpers/testSupport';
import {
  GET_COUNTRY_BASIC,
  GET_COUNTRY_WITH_STATES,
  GET_COUNTRY_WITH_TRANSLATIONS,
  GET_COUNTRY_COMPLETE,
  GET_COUNTRIES_BASIC,
  GET_COUNTRIES_WITH_STATES,
  GET_COUNTRIES_WITH_PAGINATION,
  GET_COUNTRIES_WITH_ALL_TRANSLATIONS,
  GET_COUNTRIES_FOR_ADDRESS_FORM,
  GET_COUNTRIES_WITH_LAST_PAGINATION,
  GET_COUNTRIES_WITH_FIRST_LAST,
} from '../../graphql/Queries/country.queries';
import {
  assertNoGraphQLErrors,
  assertGetCountryBasicResponse,
  assertGetCountryWithStatesResponse,
  assertGetCountryWithTranslationsResponse,
  assertGetCountryCompleteResponse,
  assertGetCountriesBasicResponse,
  assertGetCountriesWithStatesResponse,
  assertGetCountriesWithPaginationResponse,
  assertGetCountriesWithAllTranslationsResponse,
  assertGetCountriesForAddressFormResponse,
} from '../../graphql/assertions/country.assertions';

const SEEDED_COUNTRIES = [
  { id: '1', _id: 1, code: 'AF', name: 'Afghanistan' },
  { id: '2', _id: 2, code: 'AX', name: 'Åland Islands' },
  { id: '3', _id: 3, code: 'AL', name: 'Albania' },
];

const COUNTRY_WITHOUT_STATES = SEEDED_COUNTRIES[0];

const SEEDED_COUNTRY_TOTAL = 254;

const DEFAULT_PAGE_SIZE = 10;

const MAXIMUM_PAGE_SIZE = 50;

const INVALID_COUNTRY_ID_MESSAGE = 'Invalid ID format. Expected IRI format like "/api/shop/countries/1" or numeric ID';

const COUNTRY_ID_REQUIRED_MESSAGE = 'Country ID is required';

const COUNTRY_NOT_FOUND_MESSAGE = 'Country not found';

const EMPTY_CURSOR_MESSAGE = 'Empty cursor is invalid';

function cursorFor(offset: number): string {
  return Buffer.from(String(offset)).toString('base64');
}

function expectSeededCountry(country: any, seeded: { _id: number; code: string; name: string }) {
  expect(country._id).toBe(seeded._id);
  expect(country.code).toBe(seeded.code);
  expect(country.name).toBe(seeded.name);
  expect(country.id).toBe(`/api/shop/countries/${seeded._id}`);
}

async function countryErrorBody(request: any, id: any): Promise<any> {
  const response = await sendGraphQLRequest(request, GET_COUNTRY_BASIC, { id });
  expect(response.status()).toBe(200);

  return response.json();
}

test.describe('countries graphql api - complete test suite', () => {
  test('get_country_basic should return valid basic country data', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_COUNTRY_BASIC, { id: COUNTRY_WITHOUT_STATES.id });
    expect(response.status()).toBe(200);

    const body = await response.json();
    assertGetCountryBasicResponse(body);
    expectSeededCountry(body.data.country, COUNTRY_WITHOUT_STATES);
  });

  test('get_country_with_states should return the country with an empty states connection', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_COUNTRY_WITH_STATES, { id: COUNTRY_WITHOUT_STATES.id });
    expect(response.status()).toBe(200);

    const body = await response.json();
    assertGetCountryWithStatesResponse(body);
    expectSeededCountry(body.data.country, COUNTRY_WITHOUT_STATES);
    expect(body.data.country.states.edges).toEqual([]);
  });

  test('get_country_with_translations should return country with translations', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_COUNTRY_WITH_TRANSLATIONS, { id: COUNTRY_WITHOUT_STATES.id });
    expect(response.status()).toBe(200);

    const body = await response.json();
    assertGetCountryWithTranslationsResponse(body);
    expectSeededCountry(body.data.country, COUNTRY_WITHOUT_STATES);
  });

  test('get_country_complete should return complete country details', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_COUNTRY_COMPLETE, { id: COUNTRY_WITHOUT_STATES.id });
    expect(response.status()).toBe(200);

    const body = await response.json();
    assertGetCountryCompleteResponse(body);
    expectSeededCountry(body.data.country, COUNTRY_WITHOUT_STATES);
    expect(body.data.country.states.edges).toEqual([]);
  });

  test('get_country_basic should return each seeded country by its id', async ({ request }) => {
    for (const seeded of SEEDED_COUNTRIES) {
      const response = await sendGraphQLRequest(request, GET_COUNTRY_BASIC, { id: seeded.id });
      expect(response.status()).toBe(200);

      const body = await response.json();
      assertGetCountryBasicResponse(body);
      expectSeededCountry(body.data.country, seeded);
    }
  });

  test('get_country_with_states should report no states for a country that has none', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_COUNTRY_WITH_STATES, { id: COUNTRY_WITHOUT_STATES.id });
    expect(response.status()).toBe(200);

    const body = await response.json();
    assertNoGraphQLErrors(body);
    expect(body.data.country.states.edges).toEqual([]);
    expect(body.data.country.states.totalCount).toBe(0);
  });

  test('get_country_basic should return the invalid id format message for a malformed id', async ({ request }) => {
    const body = await countryErrorBody(request, 'invalid-format-id');

    expect(graphQLErrorMessages(body)).toEqual([INVALID_COUNTRY_ID_MESSAGE]);
    expect(body.data.country).toBeNull();
  });

  test('get_country_basic should return the invalid id format message for a non numeric id', async ({ request }) => {
    const body = await countryErrorBody(request, 'country_non_existent_99999');

    expect(graphQLErrorMessages(body)).toEqual([INVALID_COUNTRY_ID_MESSAGE]);
    expect(body.data.country).toBeNull();
  });

  test('get_country_basic should return not found for a numeric id that does not exist', async ({ request }) => {
    const body = await countryErrorBody(request, '12345');

    expect(graphQLErrorMessages(body)).toEqual([COUNTRY_NOT_FOUND_MESSAGE]);
    expect(body.data.country).toBeNull();
  });

  test('get_countries_with_all_translations should return countries with all translations', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_COUNTRIES_WITH_ALL_TRANSLATIONS);
    expect(response.status()).toBe(200);

    const body = await response.json();
    assertGetCountriesWithAllTranslationsResponse(body);
    expect(body.data.countries.edges).toHaveLength(DEFAULT_PAGE_SIZE);
    expect(body.data.countries.totalCount).toBe(SEEDED_COUNTRY_TOTAL);
  });

  test('get_countries_for_address_form should return countries for address form', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_COUNTRIES_FOR_ADDRESS_FORM);
    expect(response.status()).toBe(200);

    const body = await response.json();
    assertGetCountriesForAddressFormResponse(body);
    expect(body.data.countries.edges).toHaveLength(DEFAULT_PAGE_SIZE);
    expect(body.data.countries.totalCount).toBe(SEEDED_COUNTRY_TOTAL);
  });

  test('should return error for non-existent field', async ({ request }) => {
    const invalidQuery = `
      query getSingleCountry($id: ID!) {
        country(id: $id) {
          id
          nonExistentField
        }
      }
    `;

    const response = await sendGraphQLRequest(request, invalidQuery, { id: COUNTRY_WITHOUT_STATES.id });
    expect(response.status()).toBe(200);

    const body = await response.json();
    const messages = graphQLErrorMessages(body);
    expect(messages).toHaveLength(1);
    expect(messages[0]).toMatch(/^Cannot query field "nonExistentField" on type "Country"\./);
    expect(body.data).toBeUndefined();
  });

  test('get_country_basic should coerce an integer id and report the missing country', async ({ request }) => {
    const body = await countryErrorBody(request, 12345);

    expect(graphQLErrorMessages(body)).toEqual([COUNTRY_NOT_FOUND_MESSAGE]);
    expect(body.data.country).toBeNull();
  });

  test('get_country_basic should return the id required message for an empty id', async ({ request }) => {
    const body = await countryErrorBody(request, '');

    expect(graphQLErrorMessages(body)).toEqual([COUNTRY_ID_REQUIRED_MESSAGE]);
    expect(body.data.country).toBeNull();
  });

  test('should return country codes in correct format', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_COUNTRY_BASIC, { id: COUNTRY_WITHOUT_STATES.id });
    expect(response.status()).toBe(200);

    const body = await response.json();
    assertNoGraphQLErrors(body);
    expect(body.data.country.code).toMatch(/^[A-Z]{2}$/);
    expect(body.data.country.code).toBe(COUNTRY_WITHOUT_STATES.code);
  });

  test('should return proper pagination info for states', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_COUNTRY_WITH_STATES, { id: COUNTRY_WITHOUT_STATES.id });
    expect(response.status()).toBe(200);

    const body = await response.json();
    assertNoGraphQLErrors(body);

    const states = body.data.country.states;
    expect(states.pageInfo.hasNextPage).toBe(false);
    expect(states.pageInfo).toHaveProperty('endCursor');
    expect(states.totalCount).toBe(0);
  });

  test('should return proper pagination info for translations', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_COUNTRY_WITH_TRANSLATIONS, { id: COUNTRY_WITHOUT_STATES.id });
    expect(response.status()).toBe(200);

    const body = await response.json();
    assertNoGraphQLErrors(body);

    const translations = body.data.country.translations;
    expect(translations).toHaveProperty('pageInfo');
    expect(typeof translations.pageInfo.hasNextPage).toBe('boolean');
    expect(translations.pageInfo).toHaveProperty('endCursor');
  });

  test('get_countries_basic should return valid basic countries data', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_COUNTRIES_BASIC);
    expect(response.status()).toBe(200);

    const body = await response.json();
    assertGetCountriesBasicResponse(body);
    expect(body.data.countries.edges).toHaveLength(DEFAULT_PAGE_SIZE);
    expect(body.data.countries.totalCount).toBe(SEEDED_COUNTRY_TOTAL);
    expect(body.data.countries.pageInfo.hasNextPage).toBe(true);
  });

  test('get_countries_with_states should return countries with states and translations', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_COUNTRIES_WITH_STATES);
    expect(response.status()).toBe(200);

    const body = await response.json();
    assertGetCountriesWithStatesResponse(body);
    expect(body.data.countries.edges).toHaveLength(DEFAULT_PAGE_SIZE);
    expect(body.data.countries.totalCount).toBe(SEEDED_COUNTRY_TOTAL);
  });

  test('get_countries_with_pagination should support first parameter', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_COUNTRIES_WITH_PAGINATION, { first: 2 });
    expect(response.status()).toBe(200);

    const body = await response.json();
    assertGetCountriesWithPaginationResponse(body, 2);

    const countries = body.data.countries;
    expect(countries.edges.map((edge: any) => edge.node.code)).toEqual(['AF', 'AX']);
    expect(countries.edges.map((edge: any) => edge.cursor)).toEqual([cursorFor(0), cursorFor(1)]);
    expect(countries.pageInfo).toEqual({
      startCursor: cursorFor(0),
      endCursor: cursorFor(1),
      hasNextPage: true,
      hasPreviousPage: false,
    });
    expect(countries.totalCount).toBe(SEEDED_COUNTRY_TOTAL);
  });

  test('get_countries_with_pagination should fetch next page using after cursor', async ({ request }) => {
    const firstResponse = await sendGraphQLRequest(request, GET_COUNTRIES_WITH_PAGINATION, { first: 1 });
    expect(firstResponse.status()).toBe(200);
    const firstBody = await firstResponse.json();
    assertNoGraphQLErrors(firstBody);

    const firstCountries = firstBody.data.countries;
    expect(firstCountries.pageInfo.hasNextPage).toBe(true);
    expect(firstCountries.pageInfo.endCursor).toBe(cursorFor(0));

    const secondResponse = await sendGraphQLRequest(request, GET_COUNTRIES_WITH_PAGINATION, {
      first: 1,
      after: firstCountries.pageInfo.endCursor,
    });
    expect(secondResponse.status()).toBe(200);
    const secondBody = await secondResponse.json();
    assertNoGraphQLErrors(secondBody);

    const secondCountries = secondBody.data.countries;
    expect(secondCountries.edges).toHaveLength(1);
    expect(secondCountries.edges[0].node._id).not.toBe(firstCountries.edges[0].node._id);
    expect(secondCountries.edges[0].node.code).toBe('AX');
    expect(secondCountries.edges[0].cursor).toBe(cursorFor(1));
    expect(secondCountries.pageInfo.hasPreviousPage).toBe(true);
  });

  test('get_countries_with_last_pagination should return the last countries', async ({ request }) => {
    const lastResponse = await sendGraphQLRequest(request, GET_COUNTRIES_WITH_LAST_PAGINATION, { last: 5 });
    expect(lastResponse.status()).toBe(200);
    const lastBody = await lastResponse.json();
    assertNoGraphQLErrors(lastBody);

    const tailResponse = await sendGraphQLRequest(request, GET_COUNTRIES_WITH_PAGINATION, {
      first: 5,
      after: cursorFor(SEEDED_COUNTRY_TOTAL - 6),
    });
    const tailBody = await tailResponse.json();
    assertNoGraphQLErrors(tailBody);

    const countries = lastBody.data.countries;
    expect(countries.totalCount).toBe(SEEDED_COUNTRY_TOTAL);
    expect(countries.edges.map((edge: any) => edge.node._id)).toEqual(
      tailBody.data.countries.edges.map((edge: any) => edge.node._id)
    );
    expect(countries.edges.map((edge: any) => edge.cursor)).toEqual(
      [5, 4, 3, 2, 1].map((fromEnd) => cursorFor(SEEDED_COUNTRY_TOTAL - fromEnd))
    );
    expect(countries.pageInfo.hasPreviousPage).toBe(true);
  });

  test('get_countries_with_first_last should let last decide the page', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_COUNTRIES_WITH_FIRST_LAST, { first: 5, last: 5 });
    expect(response.status()).toBe(200);
    const body = await response.json();
    assertNoGraphQLErrors(body);

    const tailResponse = await sendGraphQLRequest(request, GET_COUNTRIES_WITH_PAGINATION, {
      first: 5,
      after: cursorFor(SEEDED_COUNTRY_TOTAL - 6),
    });
    const tailBody = await tailResponse.json();
    assertNoGraphQLErrors(tailBody);

    expect(body.data.countries.edges.map((edge: any) => edge.node._id)).toEqual(
      tailBody.data.countries.edges.map((edge: any) => edge.node._id)
    );
    expect(body.data.countries.totalCount).toBe(SEEDED_COUNTRY_TOTAL);
  });

  test('should return an empty page for first=0 with the full total count', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_COUNTRIES_WITH_PAGINATION, { first: 0 });
    expect(response.status()).toBe(200);
    const body = await response.json();
    assertNoGraphQLErrors(body);

    expect(body.data.countries.edges).toEqual([]);
    expect(body.data.countries.totalCount).toBe(SEEDED_COUNTRY_TOTAL);
  });

  test('should return an empty page for a negative first value with the full total count', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_COUNTRIES_WITH_PAGINATION, { first: -1 });
    expect(response.status()).toBe(200);
    const body = await response.json();
    assertNoGraphQLErrors(body);

    expect(body.data.countries.edges).toEqual([]);
    expect(body.data.countries.totalCount).toBe(SEEDED_COUNTRY_TOTAL);
  });

  test('should clamp a large first value to the maximum page size', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_COUNTRIES_WITH_PAGINATION, { first: 1000 });
    expect(response.status()).toBe(200);
    const body = await response.json();
    assertNoGraphQLErrors(body);

    expect(body.data.countries.edges).toHaveLength(MAXIMUM_PAGE_SIZE);
    expect(body.data.countries.pageInfo.hasNextPage).toBe(true);
    expect(body.data.countries.totalCount).toBe(SEEDED_COUNTRY_TOTAL);
  });

  test('should return the same page for repeated pagination requests', async ({ request }) => {
    const startTime = Date.now();
    const requests = 5;
    const pages: number[][] = [];

    for (let i = 0; i < requests; i++) {
      const response = await sendGraphQLRequest(request, GET_COUNTRIES_WITH_PAGINATION, { first: 10 });
      expect(response.status()).toBe(200);

      const body = await response.json();
      assertNoGraphQLErrors(body);
      expect(body.data.countries.edges).toHaveLength(10);
      pages.push(body.data.countries.edges.map((edge: any) => edge.node._id));
    }

    const avgDuration = (Date.now() - startTime) / requests;

    expect(new Set(pages.map((page) => page.join(','))).size).toBe(1);
    expect(avgDuration).toBeLessThan(5000);
  });

  test('should reject an empty cursor string', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_COUNTRIES_WITH_PAGINATION, { first: 5, after: '' });
    expect(response.status()).toBe(200);
    const body = await response.json();

    expect(graphQLErrorMessages(body)).toEqual([EMPTY_CURSOR_MESSAGE]);
    expect(body.data.countries).toBeNull();
  });

  test('should return the invalid id format message for special characters', async ({ request }) => {
    const body = await countryErrorBody(request, '/api/shop/countries/<script>alert(1)</script>');

    expect(graphQLErrorMessages(body)).toEqual([INVALID_COUNTRY_ID_MESSAGE]);
    expect(body.data.country).toBeNull();
  });

  test('should return not found for a very large integer country id', async ({ request }) => {
    const body = await countryErrorBody(request, '999999999');

    expect(graphQLErrorMessages(body)).toEqual([COUNTRY_NOT_FOUND_MESSAGE]);
    expect(body.data.country).toBeNull();
  });
});
