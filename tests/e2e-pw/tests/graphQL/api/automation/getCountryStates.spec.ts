import { test, expect } from '@playwright/test';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../graphql/helpers/testSupport';
import {
  GET_COUNTRY_STATES_BASIC,
  GET_COUNTRY_STATES_WITH_TRANSLATIONS,
  GET_COUNTRY_STATES_WITH_PAGINATION,
  GET_COUNTRY_STATES_FOR_DROPDOWN,
  GET_COUNTRY_STATE_BASIC,
  GET_COUNTRY_STATE_WITH_TRANSLATIONS,
  GET_COUNTRY_STATE_FOR_ADDRESS_VALIDATION,
  GET_COUNTRY_STATE_COMPLETE,
  GET_COUNTRY_STATES_WITH_LAST_PAGINATION,
  GET_COUNTRY_STATES_FIRST_LAST,
} from '../../graphql/Queries/country.queries';
import {
  assertNoGraphQLErrors,
  assertGetCountryStatesBasicResponse,
  assertGetCountryStatesWithTranslationsResponse,
  assertGetCountryStatesWithPaginationResponse,
  assertGetCountryStatesForDropdownResponse,
  assertGetCountryStateBasicResponse,
  assertGetCountryStateWithTranslationsResponse,
  assertGetCountryStateForAddressValidationResponse,
  assertGetCountryStateCompleteResponse,
} from '../../graphql/assertions/country.assertions';

const UNITED_STATES_ID = 244;

const UNITED_STATES_STATE_TOTAL = 65;

const COUNTRY_WITHOUT_STATES_ID = 1;

const DEFAULT_PAGE_SIZE = 10;

const ALABAMA = {
  id: '/api/shop/country-states/1',
  _id: 1,
  code: 'AL',
  defaultName: 'Alabama',
  countryId: UNITED_STATES_ID,
  countryCode: 'US',
};

const INVALID_STATE_ID_MESSAGE = 'Invalid ID format. Expected IRI format like "/api/shop/country-states/1" or numeric ID';

const STATE_NOT_FOUND_MESSAGE = 'Country state not found';

const EMPTY_CURSOR_MESSAGE = 'Empty cursor is invalid';

function cursorFor(offset: number): string {
  return Buffer.from(String(offset)).toString('base64');
}

function stateIds(connection: any): number[] {
  return connection.edges.map((edge: any) => edge.node._id);
}

function expectAlabama(state: any) {
  expect(state._id).toBe(ALABAMA._id);
  expect(state.id).toBe(ALABAMA.id);
  expect(state.code).toBe(ALABAMA.code);
  expect(state.defaultName).toBe(ALABAMA.defaultName);
  expect(state.countryId).toBe(ALABAMA.countryId);
  expect(state.countryCode).toBe(ALABAMA.countryCode);
}

async function statesBody(request: any, query: string, variables: Record<string, any>): Promise<any> {
  const response = await sendGraphQLRequest(request, query, variables);
  expect(response.status()).toBe(200);

  return response.json();
}

test.describe('country states graphql api - complete test suite', () => {
  test('get_country_states_basic should return the first page of a country states', async ({ request }) => {
    const body = await statesBody(request, GET_COUNTRY_STATES_BASIC, { countryId: UNITED_STATES_ID });
    assertGetCountryStatesBasicResponse(body);

    const states = body.data.countryStates;
    expect(states.totalCount).toBe(UNITED_STATES_STATE_TOTAL);
    expect(states.edges).toHaveLength(DEFAULT_PAGE_SIZE);
    expectAlabama(states.edges[0].node);
    expect(states.edges.every((edge: any) => edge.node.countryCode === 'US')).toBe(true);
  });

  test('get_country_states_with_translations should return states with translations', async ({ request }) => {
    const body = await statesBody(request, GET_COUNTRY_STATES_WITH_TRANSLATIONS, { countryId: UNITED_STATES_ID });
    assertGetCountryStatesWithTranslationsResponse(body);

    const states = body.data.countryStates;
    expect(states.totalCount).toBe(UNITED_STATES_STATE_TOTAL);
    expect(states.edges.every((edge: any) => edge.node.countryId === UNITED_STATES_ID)).toBe(true);
  });

  test('get_country_states_with_pagination should return states with pagination', async ({ request }) => {
    const body = await statesBody(request, GET_COUNTRY_STATES_WITH_PAGINATION, {
      countryId: UNITED_STATES_ID,
      first: 2,
    });
    assertGetCountryStatesWithPaginationResponse(body);

    const states = body.data.countryStates;
    expect(states.edges.map((edge: any) => edge.node.code)).toEqual(['AL', 'AK']);
    expect(states.edges.map((edge: any) => edge.cursor)).toEqual([cursorFor(0), cursorFor(1)]);
    expect(states.pageInfo).toEqual({
      startCursor: cursorFor(0),
      endCursor: cursorFor(1),
      hasNextPage: true,
      hasPreviousPage: false,
    });
    expect(states.totalCount).toBe(UNITED_STATES_STATE_TOTAL);
  });

  test('get_country_states_for_dropdown should return states for dropdown form', async ({ request }) => {
    const body = await statesBody(request, GET_COUNTRY_STATES_FOR_DROPDOWN, { countryId: UNITED_STATES_ID });
    assertGetCountryStatesForDropdownResponse(body);

    const states = body.data.countryStates;
    expect(states.edges).toHaveLength(DEFAULT_PAGE_SIZE);
    expect(states.edges[0].node).toEqual({
      id: ALABAMA.id,
      _id: ALABAMA._id,
      code: ALABAMA.code,
      defaultName: ALABAMA.defaultName,
    });
  });

  test('get_country_state_basic should return valid basic state data', async ({ request }) => {
    const body = await statesBody(request, GET_COUNTRY_STATE_BASIC, { id: ALABAMA.id });
    assertGetCountryStateBasicResponse(body);
    expectAlabama(body.data.countryState);
  });

  test('get_country_state_with_translations should return state with translations', async ({ request }) => {
    const body = await statesBody(request, GET_COUNTRY_STATE_WITH_TRANSLATIONS, { id: ALABAMA.id });
    assertGetCountryStateWithTranslationsResponse(body);
    expectAlabama(body.data.countryState);
  });

  test('get_country_state_for_address_validation should return state for address validation', async ({ request }) => {
    const body = await statesBody(request, GET_COUNTRY_STATE_FOR_ADDRESS_VALIDATION, { id: String(ALABAMA._id) });
    assertGetCountryStateForAddressValidationResponse(body);
    expectAlabama(body.data.countryState);
  });

  test('get_country_state_complete should return complete state details', async ({ request }) => {
    const body = await statesBody(request, GET_COUNTRY_STATE_COMPLETE, { id: ALABAMA.id });
    assertGetCountryStateCompleteResponse(body);
    expectAlabama(body.data.countryState);
  });

  test('should reject a non integer country id variable', async ({ request }) => {
    const body = await statesBody(request, GET_COUNTRY_STATES_BASIC, { countryId: 'invalid' });

    expect(graphQLErrorMessages(body)).toEqual([
      'Variable "$countryId" got invalid value "invalid"; Int cannot represent non-integer value: "invalid"',
    ]);
    expect(body.data).toBeUndefined();
  });

  test('should return empty states for non-existent country id', async ({ request }) => {
    const body = await statesBody(request, GET_COUNTRY_STATES_BASIC, { countryId: 99999 });
    assertNoGraphQLErrors(body);

    expect(body.data.countryStates.edges).toEqual([]);
    expect(body.data.countryStates.totalCount).toBe(0);
  });

  test('should return the invalid id format message for a malformed state id', async ({ request }) => {
    const body = await statesBody(request, GET_COUNTRY_STATE_BASIC, { id: 'invalid-state-id' });

    expect(graphQLErrorMessages(body)).toEqual([INVALID_STATE_ID_MESSAGE]);
    expect(body.data.countryState).toBeNull();
  });

  test('should return not found for a state id that does not exist', async ({ request }) => {
    const body = await statesBody(request, GET_COUNTRY_STATE_BASIC, { id: '/api/shop/country-states/999999' });

    expect(graphQLErrorMessages(body)).toEqual([STATE_NOT_FOUND_MESSAGE]);
    expect(body.data.countryState).toBeNull();
  });

  test('get_country_states_with_last_pagination should return the last states', async ({ request }) => {
    const body = await statesBody(request, GET_COUNTRY_STATES_WITH_LAST_PAGINATION, {
      countryId: UNITED_STATES_ID,
      last: 5,
    });
    assertNoGraphQLErrors(body);

    const states = body.data.countryStates;
    expect(states.totalCount).toBe(UNITED_STATES_STATE_TOTAL);
    expect(stateIds(states)).toEqual([61, 62, 63, 64, 65]);
    expect(states.edges.map((edge: any) => edge.cursor)).toEqual([60, 61, 62, 63, 64].map(cursorFor));
    expect(states.pageInfo.hasPreviousPage).toBe(true);
  });

  test('get_country_states_first_last should let last decide the page', async ({ request }) => {
    const body = await statesBody(request, GET_COUNTRY_STATES_FIRST_LAST, {
      countryId: UNITED_STATES_ID,
      first: 3,
      last: 3,
    });
    assertNoGraphQLErrors(body);

    const states = body.data.countryStates;
    expect(stateIds(states)).toEqual([63, 64, 65]);
    expect(states.edges.map((edge: any) => edge.cursor)).toEqual([62, 63, 64].map(cursorFor));
  });

  test('should answer first=0 for country states without an error', async ({ request }) => {
    const body = await statesBody(request, GET_COUNTRY_STATES_WITH_PAGINATION, {
      countryId: UNITED_STATES_ID,
      first: 0,
    });
    assertNoGraphQLErrors(body);

    expect(body.data.countryStates.totalCount).toBe(UNITED_STATES_STATE_TOTAL);
  });

  test('should answer a negative first value for country states without an error', async ({ request }) => {
    const body = await statesBody(request, GET_COUNTRY_STATES_WITH_PAGINATION, {
      countryId: UNITED_STATES_ID,
      first: -1,
    });
    assertNoGraphQLErrors(body);

    const states = body.data.countryStates;
    expect(states.totalCount).toBe(UNITED_STATES_STATE_TOTAL);
    expect(states.edges.every((edge: any) => edge.node.countryId === UNITED_STATES_ID)).toBe(true);
  });

  test('should reject an empty cursor string for states', async ({ request }) => {
    const body = await statesBody(request, GET_COUNTRY_STATES_WITH_PAGINATION, {
      countryId: UNITED_STATES_ID,
      first: 5,
      after: '',
    });

    expect(graphQLErrorMessages(body)).toEqual([EMPTY_CURSOR_MESSAGE]);
    expect(body.data.countryStates).toBeNull();
  });

  test('should return no states for a very large integer country id', async ({ request }) => {
    const body = await statesBody(request, GET_COUNTRY_STATES_BASIC, { countryId: 999999999 });
    assertNoGraphQLErrors(body);

    expect(body.data.countryStates.edges).toEqual([]);
    expect(body.data.countryStates.totalCount).toBe(0);
  });

  test('should reject a boolean for the integer first parameter', async ({ request }) => {
    const body = await statesBody(request, GET_COUNTRY_STATES_WITH_PAGINATION, {
      countryId: UNITED_STATES_ID,
      first: true,
    });

    expect(graphQLErrorMessages(body)).toEqual([
      'Variable "$first" got invalid value true; Int cannot represent non-integer value: true',
    ]);
    expect(body.data).toBeUndefined();
  });

  test('should return an empty connection for a country without states', async ({ request }) => {
    const body = await statesBody(request, GET_COUNTRY_STATES_BASIC, { countryId: COUNTRY_WITHOUT_STATES_ID });
    assertNoGraphQLErrors(body);

    expect(body.data.countryStates.edges).toEqual([]);
    expect(body.data.countryStates.totalCount).toBe(0);
  });
});
