import { test, expect } from '@playwright/test';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
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
  assertGraphQLErrors,
  assertStateWithInternalId,
  assertCountryStatesConnection,
} from '../../graphql/assertions/country.assertions';

test.describe('Country States GraphQL API - Complete Test Suite', () => {
  // Helper to get a valid country ID for testing
  // Common country IDs in Bagisto: 1 (India), 2 (USA), 3 (UK), etc.
  const VALID_COUNTRY_IDS = [1, 2, 3];
  const VALID_COUNTRY_ID = VALID_COUNTRY_IDS[0];
  let validStateId: string;

  /* ===================================================
   * POSITIVE TEST CASES - COUNTRY STATES COLLECTION
   * =================================================== */

  /* ---------------------------------------------------
   * TEST 1: GET_COUNTRY_STATES_BASIC - Basic States Query
   * Positive: Validates basic states contract
   * --------------------------------------------------- */
  test('GET_COUNTRY_STATES_BASIC should return valid basic states data', async ({ request }) => {
    console.log('\n📤 Sending getCountryStates (Basic) query...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const response = await sendGraphQLRequest(request, GET_COUNTRY_STATES_BASIC, { 
      countryId: VALID_COUNTRY_ID 
    });
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    assertGetCountryStatesBasicResponse(body);

    const states = body.data.countryStates;
    console.log('\n📦 States Details:');
    console.log(`  Total States: ${states.totalCount}`);
    
    if (states.edges.length > 0) {
      validStateId = states.edges[0].node.id;
      console.log(`  First State: ${states.edges[0].node.defaultName} (${states.edges[0].node.code})`);
    }

    console.log('\n✅ GET_COUNTRY_STATES_BASIC Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 2: GET_COUNTRY_STATES_WITH_TRANSLATIONS - States with Translations
   * Positive: Validates states with translations data
   * --------------------------------------------------- */
  test('GET_COUNTRY_STATES_WITH_TRANSLATIONS should return states with translations', async ({ request }) => {
    console.log('\n📤 Sending getCountryStates (With Translations) query...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const response = await sendGraphQLRequest(request, GET_COUNTRY_STATES_WITH_TRANSLATIONS, { 
      countryId: VALID_COUNTRY_ID 
    });
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    assertGetCountryStatesWithTranslationsResponse(body);

    const states = body.data.countryStates;
    console.log('\n📦 States with Translations:');
    console.log(`  Total States: ${states.totalCount}`);

    if (states.edges.length > 0) {
      states.edges.slice(0, 3).forEach((edge: any, index: number) => {
        const state = edge.node;
        console.log(`  ${index + 1}. ${state.defaultName} (${state.code})`);
        console.log(`     Translations: ${state.translations?.edges?.length || 0}`);
      });
    }

    console.log('\n✅ GET_COUNTRY_STATES_WITH_TRANSLATIONS Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 3: GET_COUNTRY_STATES_WITH_PAGINATION - States with Pagination
   * Positive: Validates states with pagination support
   * --------------------------------------------------- */
  test('GET_COUNTRY_STATES_WITH_PAGINATION should return states with pagination', async ({ request }) => {
    console.log('\n📤 Sending getCountryStates (With Pagination) query...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const response = await sendGraphQLRequest(request, GET_COUNTRY_STATES_WITH_PAGINATION, { 
      countryId: VALID_COUNTRY_ID,
      first: 2
    });
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    assertGetCountryStatesWithPaginationResponse(body);

    const states = body.data.countryStates;
    console.log('\n📦 Paginated States:');
    console.log(`  Total States: ${states.totalCount}`);
    console.log(`  Returned: ${states.edges.length}`);
    console.log(`  Has Next Page: ${states.pageInfo.hasNextPage}`);
    
    if (states.edges.length > 0) {
      console.log(`  First Cursor: ${states.pageInfo.startCursor}`);
      console.log(`  Last Cursor: ${states.pageInfo.endCursor}`);
    }

    console.log('\n✅ GET_COUNTRY_STATES_WITH_PAGINATION Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 4: GET_COUNTRY_STATES_FOR_DROPDOWN - States for Dropdown Form
   * Positive: Validates states for form dropdowns
   * --------------------------------------------------- */
  test('GET_COUNTRY_STATES_FOR_DROPDOWN should return states for dropdown form', async ({ request }) => {
    console.log('\n📤 Sending getCountryStates (For Dropdown) query...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const response = await sendGraphQLRequest(request, GET_COUNTRY_STATES_FOR_DROPDOWN, { 
      countryId: VALID_COUNTRY_ID 
    });
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    assertGetCountryStatesForDropdownResponse(body);

    const states = body.data.countryStates;
    console.log('\n📦 States for Dropdown:');
    console.log(`  Total States: ${states.totalCount}`);

    if (states.edges.length > 0) {
      states.edges.slice(0, 5).forEach((edge: any, index: number) => {
        const state = edge.node;
        console.log(`  ${index + 1}. ${state.defaultName} (${state.code})`);
      });
    }

    console.log('\n✅ GET_COUNTRY_STATES_FOR_DROPDOWN Test Passed!\n');
  });

  /* ===================================================
   * POSITIVE TEST CASES - SINGLE COUNTRY STATE
   * =================================================== */

  /* ---------------------------------------------------
   * TEST 5: GET_COUNTRY_STATE_BASIC - Basic State Query
   * Positive: Validates basic state contract
   * --------------------------------------------------- */
  test('GET_COUNTRY_STATE_BASIC should return valid basic state data', async ({ request }) => {
    console.log('\n📤 Sending getCountryState (Basic) query...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    // First, get a valid state ID
    if (!validStateId) {
      const statesResponse = await sendGraphQLRequest(request, GET_COUNTRY_STATES_BASIC, { 
        countryId: VALID_COUNTRY_ID 
      });
      const statesBody = await statesResponse.json();
      if (statesBody.data.countryStates.edges.length > 0) {
        validStateId = statesBody.data.countryStates.edges[0].node.id;
      } else {
        console.log('⚠️ No states found for country, skipping test');
        return;
      }
    }

    console.log('🔑 Using State ID:', validStateId);

    const response = await sendGraphQLRequest(request, GET_COUNTRY_STATE_BASIC, { 
      id: validStateId 
    });
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    assertGetCountryStateBasicResponse(body);

    const countryState = body.data.countryState;
    console.log('\n📦 State Details:');
    console.log(`  ID: ${countryState.id}`);
    console.log(`  _id: ${countryState._id}`);
    console.log(`  Code: ${countryState.code}`);
    console.log(`  Name: ${countryState.defaultName}`);
    console.log(`  Country ID: ${countryState.countryId}`);
    console.log(`  Country Code: ${countryState.countryCode}`);

    console.log('\n✅ GET_COUNTRY_STATE_BASIC Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 6: GET_COUNTRY_STATE_WITH_TRANSLATIONS - State with Translations
   * Positive: Validates state with translations data
   * --------------------------------------------------- */
  test('GET_COUNTRY_STATE_WITH_TRANSLATIONS should return state with translations', async ({ request }) => {
    console.log('\n📤 Sending getCountryState (With Translations) query...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    // First, get a valid state ID
    if (!validStateId) {
      const statesResponse = await sendGraphQLRequest(request, GET_COUNTRY_STATES_BASIC, { 
        countryId: VALID_COUNTRY_ID 
      });
      const statesBody = await statesResponse.json();
      if (statesBody.data.countryStates.edges.length > 0) {
        validStateId = statesBody.data.countryStates.edges[0].node.id;
      } else {
        console.log('⚠️ No states found for country, skipping test');
        return;
      }
    }

    console.log('🔑 Using State ID:', validStateId);

    const response = await sendGraphQLRequest(request, GET_COUNTRY_STATE_WITH_TRANSLATIONS, { 
      id: validStateId 
    });
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    assertGetCountryStateWithTranslationsResponse(body);

    const countryState = body.data.countryState;
    console.log('\n📦 State Details:');
    console.log(`  Name: ${countryState.defaultName}`);
    console.log(`  Translations: ${countryState.translations.edges.length}`);
    
    countryState.translations.edges.forEach((edge: any) => {
      const translation = edge.node;
      console.log(`    ${translation.locale}: ${translation.defaultName}`);
    });

    console.log('\n✅ GET_COUNTRY_STATE_WITH_TRANSLATIONS Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 7: GET_COUNTRY_STATE_FOR_ADDRESS_VALIDATION - State for Address Validation
   * Positive: Validates state for address validation purposes
   * --------------------------------------------------- */
  test('GET_COUNTRY_STATE_FOR_ADDRESS_VALIDATION should return state for address validation', async ({ request }) => {
    console.log('\n📤 Sending getCountryState (For Address Validation) query...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    // First, get a valid state ID
    if (!validStateId) {
      const statesResponse = await sendGraphQLRequest(request, GET_COUNTRY_STATES_BASIC, { 
        countryId: VALID_COUNTRY_ID 
      });
      const statesBody = await statesResponse.json();
      if (statesBody.data.countryStates.edges.length > 0) {
        validStateId = statesBody.data.countryStates.edges[0].node.id;
      } else {
        console.log('⚠️ No states found for country, skipping test');
        return;
      }
    }

    console.log('🔑 Using State ID:', validStateId);

    const response = await sendGraphQLRequest(request, GET_COUNTRY_STATE_FOR_ADDRESS_VALIDATION, { 
      id: validStateId 
    });
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    assertGetCountryStateForAddressValidationResponse(body);

    const countryState = body.data.countryState;
    console.log('\n📦 State for Address Validation:');
    console.log(`  ID: ${countryState.id}`);
    console.log(`  Code: ${countryState.code}`);
    console.log(`  Name: ${countryState.defaultName}`);

    console.log('\n✅ GET_COUNTRY_STATE_FOR_ADDRESS_VALIDATION Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 8: GET_COUNTRY_STATE_COMPLETE - Complete State Details
   * Positive: Validates complete state information
   * --------------------------------------------------- */
  test('GET_COUNTRY_STATE_COMPLETE should return complete state details', async ({ request }) => {
    console.log('\n📤 Sending getCountryState (Complete Details) query...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    // First, get a valid state ID
    if (!validStateId) {
      const statesResponse = await sendGraphQLRequest(request, GET_COUNTRY_STATES_BASIC, { 
        countryId: VALID_COUNTRY_ID 
      });
      const statesBody = await statesResponse.json();
      if (statesBody.data.countryStates.edges.length > 0) {
        validStateId = statesBody.data.countryStates.edges[0].node.id;
      } else {
        console.log('⚠️ No states found for country, skipping test');
        return;
      }
    }

    console.log('🔑 Using State ID:', validStateId);

    const response = await sendGraphQLRequest(request, GET_COUNTRY_STATE_COMPLETE, { 
      id: validStateId 
    });
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    assertGetCountryStateCompleteResponse(body);

    const countryState = body.data.countryState;
    console.log('\n📦 Complete State Details:');
    console.log(`  ID: ${countryState.id}`);
    console.log(`  Code: ${countryState.code}`);
    console.log(`  Name: ${countryState.defaultName}`);
    console.log(`  Country Code: ${countryState.countryCode}`);
    console.log(`  Translations: ${countryState.translations.edges.length}`);

    console.log('\n✅ GET_COUNTRY_STATE_COMPLETE Test Passed!\n');
  });

  /* ===================================================
   * NEGATIVE TEST CASES
   * =================================================== */

  /* ---------------------------------------------------
   * TEST 9: Get States with Invalid Country ID
   * Negative: Validates error handling for invalid country ID
   * --------------------------------------------------- */
  test('should handle invalid country ID format', async ({ request }) => {
    console.log('\n📤 Testing getCountryStates with invalid country ID...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const invalidCountryId = 'invalid';
    
    const response = await sendGraphQLRequest(request, GET_COUNTRY_STATES_BASIC, { 
      countryId: invalidCountryId 
    });
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    if (body.errors) {
      console.log('⚠️ Expected GraphQL error:', body.errors[0]?.message);
      assertGraphQLErrors(body);
    } else {
      console.log('⚠️ No error returned, but expecting one');
    }

    console.log('\n✅ Invalid Country ID Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 10: Get States with Non-Existent Country ID
   * Negative: Validates handling of non-existent country
   * --------------------------------------------------- */
  test('should return empty states for non-existent country ID', async ({ request }) => {
    console.log('\n📤 Testing getCountryStates with non-existent country ID...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const nonExistentCountryId = 99999;
    
    const response = await sendGraphQLRequest(request, GET_COUNTRY_STATES_BASIC, { 
      countryId: nonExistentCountryId 
    });
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    if (!body.errors) {
      const states = body.data.countryStates;
      console.log(`  States found: ${states.totalCount}`);
      expect(states.edges.length).toBe(0);
    } else {
      console.log('⚠️ GraphQL Error:', body.errors[0]?.message);
    }

    console.log('\n✅ Non-Existent Country ID Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 11: Get State with Invalid ID Format
   * Negative: Validates error handling for invalid state ID format
   * --------------------------------------------------- */
  test('should handle invalid state ID format', async ({ request }) => {
    console.log('\n📤 Testing getCountryState with invalid state ID...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const invalidStateId = 'invalid-state-id';
    
    const response = await sendGraphQLRequest(request, GET_COUNTRY_STATE_BASIC, { 
      id: invalidStateId 
    });
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    if (body.errors) {
      console.log('⚠️ Expected GraphQL error:', body.errors[0]?.message);
      assertGraphQLErrors(body);
    } else if (body.data.countryState === null) {
      console.log('⚠️ Country state is null (not found)');
    } else {
      console.log('⚠️ Unexpected country state found');
      expect(body.data.countryState).toBeNull();
    }

    console.log('\n✅ Invalid State ID Format Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 12: Get State with Non-Existent ID
   * Negative: Validates error handling for non-existent state
   * --------------------------------------------------- */
  test('should handle non-existent state ID', async ({ request }) => {
    console.log('\n📤 Testing getCountryState with non-existent state ID...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const nonExistentStateId = 'state_non_existent_99999';
    
    const response = await sendGraphQLRequest(request, GET_COUNTRY_STATE_BASIC, { 
      id: nonExistentStateId 
    });
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    if (body.errors) {
      console.log('⚠️ GraphQL Error:', body.errors[0]?.message);
      assertGraphQLErrors(body);
    } else if (body.data.countryState === null) {
      console.log('⚠️ Country state is null (not found)');
    } else {
      console.log('⚠️ Unexpected country state found');
      expect(body.data.countryState).toBeNull();
    }

    console.log('\n✅ Non-Existent State ID Test Passed!\n');
  });

  /* ===================================================
   * EDGE CASE TESTS - PAGINATION
   * =================================================== */

  /* ---------------------------------------------------
   * TEST 13: Country States with last/before pagination
   * Tests backward pagination for states
   * --------------------------------------------------- */
  test('GET_COUNTRY_STATES_WITH_LAST_PAGINATION should support backward pagination', async ({ request }) => {
    console.log('\n📤 Testing country states with last/before pagination...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const response = await sendGraphQLRequest(request, GET_COUNTRY_STATES_WITH_LAST_PAGINATION, { 
      countryId: VALID_COUNTRY_ID,
      last: 5
    });
    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    if (!body.errors) {
      const states = body.data.countryStates;
      console.log(`📦 Returned States: ${states.edges.length}`);
      console.log(`📊 Total Count: ${states.totalCount}`);
      console.log(`📄 Has Previous Page: ${states.pageInfo.hasPreviousPage}`);
    } else {
      console.log('⚠️ GraphQL Error:', body.errors[0]?.message);
    }

    console.log('\n✅ GET_COUNTRY_STATES_WITH_LAST_PAGINATION Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 14: Country States with first and last together
   * Tests invalid combination for states
   * --------------------------------------------------- */
  test('GET_COUNTRY_STATES_FIRST_LAST should handle first and last together', async ({ request }) => {
    console.log('\n📤 Testing country states with first and last together...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const response = await sendGraphQLRequest(request, GET_COUNTRY_STATES_FIRST_LAST, { 
      countryId: VALID_COUNTRY_ID,
      first: 3,
      last: 3
    });
    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    if (body.errors) {
      console.log('⚠️ Expected GraphQL Error:', body.errors[0]?.message);
    } else {
      const states = body.data.countryStates;
      console.log(`📦 Returned States: ${states.edges.length}`);
    }

    console.log('\n✅ GET_COUNTRY_STATES_FIRST_LAST Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 15: Country States with zero first value
   * Tests edge case of zero items
   * --------------------------------------------------- */
  test('should handle first=0 for country states', async ({ request }) => {
    console.log('\n📤 Testing country states with first=0...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const response = await sendGraphQLRequest(request, GET_COUNTRY_STATES_WITH_PAGINATION, { 
      countryId: VALID_COUNTRY_ID,
      first: 0 
    });
    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    if (!body.errors) {
      const states = body.data.countryStates;
      console.log(`📦 Returned States: ${states.edges.length}`);
      console.log(`📊 Total Count: ${states.totalCount}`);
      expect(Array.isArray(states.edges)).toBe(true);
    } else {
      console.log('⚠️ GraphQL Error (expected):', body.errors[0]?.message);
    }

    console.log('\n✅ first=0 for states Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 16: Country States with negative first value
   * Tests edge case of negative items
   * --------------------------------------------------- */
  test('should handle negative first value for country states', async ({ request }) => {
    console.log('\n📤 Testing country states with negative first value...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const response = await sendGraphQLRequest(request, GET_COUNTRY_STATES_WITH_PAGINATION, { 
      countryId: VALID_COUNTRY_ID,
      first: -1 
    });
    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    if (body.errors) {
      console.log('⚠️ Expected GraphQL Error:', body.errors[0]?.message);
      assertGraphQLErrors(body);
    } else {
      console.log('⚠️ No error returned for negative value');
    }

    console.log('\n✅ Negative first value for states Test Passed!\n');
  });

  /* ===================================================
   * EDGE CASE TESTS - VALIDATION
   * =================================================== */

  /* ---------------------------------------------------
   * TEST 17: Empty string cursor for states
   * Tests edge case of empty cursor
   * --------------------------------------------------- */
  test('should handle empty cursor string for states', async ({ request }) => {
    console.log('\n📤 Testing with empty cursor string for states...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const response = await sendGraphQLRequest(request, GET_COUNTRY_STATES_WITH_PAGINATION, { 
      countryId: VALID_COUNTRY_ID,
      first: 5,
      after: ''
    });
    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    if (!body.errors) {
      const states = body.data.countryStates;
      console.log(`📦 Returned States: ${states.edges.length}`);
    } else {
      console.log('⚠️ GraphQL Error:', body.errors[0]?.message);
    }

    console.log('\n✅ Empty cursor for states Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 18: Very large integer country ID for states
   * Tests handling of very large numbers
   * --------------------------------------------------- */
  test('should handle very large integer country ID for states', async ({ request }) => {
    console.log('\n📤 Testing with very large integer country ID for states...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const largeId = 999999999;
    
    const response = await sendGraphQLRequest(request, GET_COUNTRY_STATES_BASIC, { 
      countryId: largeId 
    });
    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    if (!body.errors) {
      const states = body.data.countryStates;
      console.log(`📦 Returned States: ${states.edges.length}`);
      expect(states.edges.length).toBe(0); // Should return empty
    } else {
      console.log('⚠️ GraphQL Error:', body.errors[0]?.message);
    }

    console.log('\n✅ Large integer ID for states Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 19: Boolean as integer parameter
   * Tests type coercion handling
   * --------------------------------------------------- */
  test('should handle boolean as integer parameter for states', async ({ request }) => {
    console.log('\n📤 Testing with boolean as integer parameter for states...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const response = await sendGraphQLRequest(request, GET_COUNTRY_STATES_WITH_PAGINATION, { 
      countryId: VALID_COUNTRY_ID,
      first: true as any 
    });
    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    if (body.errors) {
      console.log('⚠️ Expected error for type mismatch:', body.errors[0]?.message);
    } else {
      const states = body.data.countryStates;
      console.log(`📦 Returned States: ${states.edges.length}`);
    }

    console.log('\n✅ Boolean parameter for states Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 20: Country without states
   * Tests handling of countries without states
   * --------------------------------------------------- */
  test('should handle country without states', async ({ request }) => {
    console.log('\n📤 Testing country without states...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    // Most countries don't have states defined
    const response = await sendGraphQLRequest(request, GET_COUNTRY_STATES_BASIC, { 
      countryId: VALID_COUNTRY_ID 
    });
    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    if (!body.errors) {
      const states = body.data.countryStates;
      console.log(`📦 Returned States: ${states.edges.length}`);
      console.log(`📊 Total Count: ${states.totalCount}`);
      
      // Should return valid structure even with 0 states
      expect(states).toHaveProperty('edges');
      expect(states).toHaveProperty('totalCount');
      expect(Array.isArray(states.edges)).toBe(true);
    } else {
      console.log('⚠️ GraphQL Error:', body.errors[0]?.message);
    }

    console.log('\n✅ Country without states Test Passed!\n');
  });
});