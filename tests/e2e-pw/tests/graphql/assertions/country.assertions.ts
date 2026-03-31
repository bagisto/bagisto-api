import { expect } from '@playwright/test';

/* ===================================================
 * ATOMIC ASSERTIONS (Reusable)
 * =================================================== */

/**
 * Assert no GraphQL errors in response
 */
export function assertNoGraphQLErrors(body: any) {
  expect(body.errors, 'GraphQL errors found').toBeUndefined();
  expect(body.data).toBeTruthy();
}

/**
 * Assert GraphQL errors exist (for negative tests)
 */
export function assertGraphQLErrors(body: any, expectedErrorMessage?: string) {
  expect(body.errors).toBeTruthy();
  if (expectedErrorMessage) {
    const errorMessages = body.errors.map((e: any) => e.message).join(' ');
    expect(errorMessages).toContain(expectedErrorMessage);
  }
}

/**
 * Assert basic country structure
 */
export function assertBasicCountry(country: any) {
  expect(country).toBeTruthy();
  expect(country).toHaveProperty('id');
  expect(country).toHaveProperty('code');
  expect(country).toHaveProperty('name');

  expect(typeof country.id).toBe('string');
  expect(country.id.length).toBeGreaterThan(0);

  expect(typeof country.code).toBe('string');
  expect(country.code.trim().length).toBeGreaterThan(0);

  expect(typeof country.name).toBe('string');
  expect(country.name.trim().length).toBeGreaterThan(0);
}

/**
 * Assert country with internal ID
 */
export function assertCountryWithInternalId(country: any) {
  assertBasicCountry(country);
  expect(country).toHaveProperty('_id');
  expect(typeof country._id).toBe('number');
}

/* ===================================================
 * STATE ASSERTIONS
 * =================================================== */

/**
 * Assert basic state structure
 */
export function assertBasicState(state: any) {
  expect(state).toBeTruthy();
  expect(state).toHaveProperty('id');
  expect(state).toHaveProperty('code');
  expect(state).toHaveProperty('defaultName');
  expect(state).toHaveProperty('countryId');
  expect(state).toHaveProperty('countryCode');

  expect(typeof state.id).toBe('string');
  expect(typeof state.code).toBe('string');
  expect(typeof state.defaultName).toBe('string');
  expect(typeof state.countryId).toBe('string');
  expect(typeof state.countryCode).toBe('string');
}

/**
 * Assert state with internal ID
 */
export function assertStateWithInternalId(state: any) {
  assertBasicState(state);
  expect(state).toHaveProperty('_id');
  expect(typeof state._id).toBe('number');
}

/**
 * Assert states connection
 */
export function assertStatesConnection(states: any) {
  expect(states).toBeTruthy();
  expect(states).toHaveProperty('edges');
  expect(states).toHaveProperty('totalCount');
  expect(Array.isArray(states.edges)).toBe(true);

  states.edges.forEach((edge: any) => {
    expect(edge).toHaveProperty('node');
    assertStateWithInternalId(edge.node);
  });
}

/* ===================================================
 * TRANSLATION ASSERTIONS
 * =================================================== */

/**
 * Assert country translation
 */
export function assertCountryTranslation(translation: any) {
  expect(translation).toBeTruthy();
  expect(translation).toHaveProperty('id');
  expect(translation).toHaveProperty('locale');
  expect(translation).toHaveProperty('name');

  expect(typeof translation.id).toBe('string');
  expect(typeof translation.locale).toBe('string');
  expect(typeof translation.name).toBe('string');
}

/**
 * Assert state translation
 */
export function assertStateTranslation(translation: any) {
  expect(translation).toBeTruthy();
  expect(translation).toHaveProperty('id');
  expect(translation).toHaveProperty('locale');
  expect(translation).toHaveProperty('defaultName');

  expect(typeof translation.id).toBe('string');
  expect(typeof translation.locale).toBe('string');
  expect(typeof translation.defaultName).toBe('string');
}

/**
 * Assert translations connection
 */
export function assertTranslationsConnection(translations: any) {
  expect(translations).toBeTruthy();
  expect(translations).toHaveProperty('edges');
  expect(translations).toHaveProperty('totalCount');
  expect(Array.isArray(translations.edges)).toBe(true);

  translations.edges.forEach((edge: any) => {
    expect(edge).toHaveProperty('node');
    assertCountryTranslation(edge.node);
  });
}

/**
 * Assert state translations connection
 */
export function assertStateTranslationsConnection(translations: any) {
  expect(translations).toBeTruthy();
  expect(translations).toHaveProperty('edges');
  expect(Array.isArray(translations.edges)).toBe(true);

  translations.edges.forEach((edge: any) => {
    expect(edge).toHaveProperty('node');
    assertStateTranslation(edge.node);
  });
}

/* ===================================================
 * PAGINATION ASSERTIONS
 * =================================================== */

/**
 * Assert pagination info
 */
export function assertPageInfo(pageInfo: any) {
  expect(pageInfo).toBeTruthy();
  expect(pageInfo).toHaveProperty('hasNextPage');
  expect(pageInfo).toHaveProperty('endCursor');

  expect(typeof pageInfo.hasNextPage).toBe('boolean');
}

/* ===================================================
 * COUNTRY STATES RESPONSE ASSERTIONS
 * =================================================== */

/**
 * Assert country states connection
 */
export function assertCountryStatesConnection(states: any) {
  expect(states).toBeTruthy();
  expect(states).toHaveProperty('edges');
  expect(states).toHaveProperty('totalCount');
  expect(Array.isArray(states.edges)).toBe(true);

  states.edges.forEach((edge: any) => {
    expect(edge).toHaveProperty('node');
    assertStateWithInternalId(edge.node);
  });
}

/**
 * Assert country states connection with translations
 */
export function assertCountryStatesWithTranslationsConnection(states: any) {
  expect(states).toBeTruthy();
  expect(states).toHaveProperty('edges');
  expect(states).toHaveProperty('totalCount');
  expect(Array.isArray(states.edges)).toBe(true);

  states.edges.forEach((edge: any) => {
    expect(edge).toHaveProperty('node');
    const state = edge.node;
    assertStateWithInternalId(state);
    expect(state).toHaveProperty('translations');
    assertStateTranslationsConnection(state.translations);
  });
}

/**
 * Assert country states pagination connection
 */
export function assertCountryStatesPaginationConnection(states: any) {
  expect(states).toBeTruthy();
  expect(states).toHaveProperty('edges');
  expect(states).toHaveProperty('pageInfo');
  expect(states).toHaveProperty('totalCount');
  expect(Array.isArray(states.edges)).toBe(true);

  states.edges.forEach((edge: any) => {
    expect(edge).toHaveProperty('node');
    expect(edge).toHaveProperty('cursor');
    assertStateWithInternalId(edge.node);
    expect(typeof edge.cursor).toBe('string');
  });

  const pageInfo = states.pageInfo;
  expect(pageInfo).toHaveProperty('endCursor');
  expect(pageInfo).toHaveProperty('startCursor');
  expect(pageInfo).toHaveProperty('hasNextPage');
  expect(pageInfo).toHaveProperty('hasPreviousPage');
  expect(typeof pageInfo.hasNextPage).toBe('boolean');
  expect(typeof pageInfo.hasPreviousPage).toBe('boolean');
}

/**
 * Assert country states dropdown connection
 */
export function assertCountryStatesDropdownConnection(states: any) {
  expect(states).toBeTruthy();
  expect(states).toHaveProperty('edges');
  expect(states).toHaveProperty('totalCount');
  expect(Array.isArray(states.edges)).toBe(true);

  states.edges.forEach((edge: any) => {
    expect(edge).toHaveProperty('node');
    const state = edge.node;
    expect(state).toHaveProperty('id');
    expect(state).toHaveProperty('_id');
    expect(state).toHaveProperty('code');
    expect(state).toHaveProperty('defaultName');
    expect(typeof state.id).toBe('string');
    expect(typeof state._id).toBe('number');
    expect(typeof state.code).toBe('string');
    expect(typeof state.defaultName).toBe('string');
  });
}

/**
 * Assert single country state basic response
 */
export function assertGetCountryStateBasicResponse(body: any) {
  assertNoGraphQLErrors(body);
  const countryState = body.data.countryState;
  expect(countryState).toBeTruthy();
  assertStateWithInternalId(countryState);
}

/**
 * Assert single country state with translations response
 */
export function assertGetCountryStateWithTranslationsResponse(body: any) {
  assertNoGraphQLErrors(body);
  const countryState = body.data.countryState;
  expect(countryState).toBeTruthy();
  assertStateWithInternalId(countryState);
  expect(countryState).toHaveProperty('translations');
  assertStateTranslationsConnection(countryState.translations);
}

/**
 * Assert single country state for address validation response
 */
export function assertGetCountryStateForAddressValidationResponse(body: any) {
  assertNoGraphQLErrors(body);
  const countryState = body.data.countryState;
  expect(countryState).toBeTruthy();
  assertStateWithInternalId(countryState);
}

/**
 * Assert single country state complete details response
 */
export function assertGetCountryStateCompleteResponse(body: any) {
  assertNoGraphQLErrors(body);
  const countryState = body.data.countryState;
  expect(countryState).toBeTruthy();
  assertStateWithInternalId(countryState);
  expect(countryState).toHaveProperty('translations');
  assertStateTranslationsConnection(countryState.translations);
}

/**
 * Assert getCountryStates basic response
 */
export function assertGetCountryStatesBasicResponse(body: any) {
  assertNoGraphQLErrors(body);
  const countryStates = body.data.countryStates;
  assertCountryStatesConnection(countryStates);
}

/**
 * Assert getCountryStates with translations response
 */
export function assertGetCountryStatesWithTranslationsResponse(body: any) {
  assertNoGraphQLErrors(body);
  const countryStates = body.data.countryStates;
  assertCountryStatesWithTranslationsConnection(countryStates);
}

/**
 * Assert getCountryStates with pagination response
 */
export function assertGetCountryStatesWithPaginationResponse(body: any) {
  assertNoGraphQLErrors(body);
  const countryStates = body.data.countryStates;
  assertCountryStatesPaginationConnection(countryStates);
}

/**
 * Assert getCountryStates for dropdown response
 */
export function assertGetCountryStatesForDropdownResponse(body: any) {
  assertNoGraphQLErrors(body);
  const countryStates = body.data.countryStates;
  assertCountryStatesDropdownConnection(countryStates);
}

/* ===================================================
 * RESPONSE ASSERTIONS
 * =================================================== */

/**
 * Assert getCountry basic response
 */
export function assertGetCountryBasicResponse(body: any) {
  assertNoGraphQLErrors(body);
  const country = body.data.country;
  expect(country).toBeTruthy();
  assertCountryWithInternalId(country);
}

/**
 * Assert getCountry with states response
 */
export function assertGetCountryWithStatesResponse(body: any) {
  assertNoGraphQLErrors(body);
  const country = body.data.country;
  expect(country).toBeTruthy();
  assertCountryWithInternalId(country);

  expect(country).toHaveProperty('states');
  assertStatesConnection(country.states);

  // Verify each state has translations
  country.states.edges.forEach((edge: any) => {
    const state = edge.node;
    if (state.translations) {
      assertStateTranslationsConnection(state.translations);
    }
  });
}

/**
 * Assert getCountry with translations response
 */
export function assertGetCountryWithTranslationsResponse(body: any) {
  assertNoGraphQLErrors(body);
  const country = body.data.country;
  expect(country).toBeTruthy();
  assertCountryWithInternalId(country);

  expect(country).toHaveProperty('translations');
  assertTranslationsConnection(country.translations);
}

/**
 * Assert countries with all translations response
 */
export function assertGetCountriesWithAllTranslationsResponse(body: any) {
  assertNoGraphQLErrors(body);
  const countries = body.data.countries;
  expect(countries).toBeTruthy();
  expect(countries).toHaveProperty('edges');
  expect(countries).toHaveProperty('totalCount');
  expect(Array.isArray(countries.edges)).toBe(true);

  countries.edges.forEach((edge: any) => {
    expect(edge).toHaveProperty('node');
    const country = edge.node;
    expect(country).toHaveProperty('id');
    expect(country).toHaveProperty('_id');
    expect(country).toHaveProperty('code');
    expect(country).toHaveProperty('translations');
    expect(typeof country.id).toBe('string');
    expect(typeof country._id).toBe('number');
    expect(typeof country.code).toBe('string');
    
    assertTranslationsConnection(country.translations);
  });

  expect(typeof countries.totalCount).toBe('number');
  expect(countries.totalCount).toBeGreaterThan(0);
}

/**
 * Assert countries for address form response
 */
export function assertGetCountriesForAddressFormResponse(body: any) {
  assertNoGraphQLErrors(body);
  const countries = body.data.countries;
  expect(countries).toBeTruthy();
  expect(countries).toHaveProperty('edges');
  expect(countries).toHaveProperty('pageInfo');
  expect(countries).toHaveProperty('totalCount');
  expect(Array.isArray(countries.edges)).toBe(true);

  countries.edges.forEach((edge: any) => {
    expect(edge).toHaveProperty('node');
    expect(edge).toHaveProperty('cursor');
    const country = edge.node;
    assertCountryWithInternalId(country);
    expect(country).toHaveProperty('states');
    assertStatesConnection(country.states);
    expect(typeof edge.cursor).toBe('string');
  });

  const pageInfo = countries.pageInfo;
  expect(pageInfo).toHaveProperty('hasNextPage');
  expect(pageInfo).toHaveProperty('endCursor');
  expect(typeof pageInfo.hasNextPage).toBe('boolean');
}

/**
 * Assert getCountry complete response
 */
export function assertGetCountryCompleteResponse(body: any) {
  assertNoGraphQLErrors(body);
  const country = body.data.country;
  expect(country).toBeTruthy();
  assertCountryWithInternalId(country);

  // Assert states
  expect(country).toHaveProperty('states');
  assertStatesConnection(country.states);

  // Assert translations
  expect(country).toHaveProperty('translations');
  assertTranslationsConnection(country.translations);
}

/**
 * Assert country not found (null response)
 */
export function assertCountryNotFound(body: any) {
  assertNoGraphQLErrors(body);
  const country = body.data.country;
  expect(country).toBeNull();
}

/**
 * Assert invalid ID format error
 */
export function assertInvalidIDFormatError(body: any) {
  assertGraphQLErrors(body);
}

/**
 * Assert country edge structure
 */
export function assertCountryEdge(edge: any) {
  expect(edge).toBeTruthy();
  expect(edge).toHaveProperty('node');
  if (edge.cursor) {
    expect(typeof edge.cursor).toBe('string');
  }
}

/**
 * Assert countries connection
 */
export function assertCountriesConnection(countries: any) {
  expect(countries).toBeTruthy();
  expect(countries).toHaveProperty('edges');
  expect(countries).toHaveProperty('pageInfo');
  expect(Array.isArray(countries.edges)).toBe(true);

  countries.edges.forEach((edge: any) => {
    assertCountryEdge(edge);
  });

  assertPageInfo(countries.pageInfo);
}

/**
 * Assert getCountries basic response
 */
export function assertGetCountriesBasicResponse(body: any) {
  assertNoGraphQLErrors(body);
  const countries = body.data.countries;
  assertCountriesConnection(countries);

  countries.edges.forEach((edge: any) => {
    assertCountryWithInternalId(edge.node);
  });

  if (countries.totalCount !== undefined) {
    expect(typeof countries.totalCount).toBe('number');
    expect(countries.totalCount).toBeGreaterThanOrEqual(0);
  }
}

/**
 * Assert getCountries with states response
 */
export function assertGetCountriesWithStatesResponse(body: any) {
  assertNoGraphQLErrors(body);
  const countries = body.data.countries;
  assertCountriesConnection(countries);

  countries.edges.forEach((edge: any) => {
    const country = edge.node;
    assertCountryWithInternalId(country);

    if (country.states) {
      assertStatesConnection(country.states);
      
      // Verify each state has translations
      country.states.edges.forEach((stateEdge: any) => {
        const state = stateEdge.node;
        if (state.translations) {
          assertStateTranslationsConnection(state.translations);
        }
      });
    }

    if (country.translations) {
      assertTranslationsConnection(country.translations);
    }
  });

  if (countries.totalCount !== undefined) {
    expect(typeof countries.totalCount).toBe('number');
    expect(countries.totalCount).toBeGreaterThanOrEqual(0);
  }
}

/**
 * Assert getCountries with pagination response
 */
export function assertGetCountriesWithPaginationResponse(body: any, expectedCount?: number) {
  assertNoGraphQLErrors(body);
  const countries = body.data.countries;
  assertCountriesConnection(countries);

  if (expectedCount !== undefined) {
    expect(countries.edges.length).toBeLessThanOrEqual(expectedCount);
  }

  countries.edges.forEach((edge: any) => {
    assertCountryWithInternalId(edge.node);
  });

  expect(countries).toHaveProperty('totalCount');
  expect(typeof countries.totalCount).toBe('number');
  expect(countries.totalCount).toBeGreaterThanOrEqual(0);
}

/**
 * Assert country data is null for non-existent ID
 */
export function assertNonExistentCountryResponse(body: any) {
  // Handle GraphQL errors - the API may return errors for invalid IDs
  if (body.errors) {
    // If there are errors, the country should be null
    expect(body.data.country).toBeNull();
  } else {
    assertNoGraphQLErrors(body);
    expect(body.data.country).toBeNull();
  }
}
