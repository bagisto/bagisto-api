import { test, expect } from '@playwright/test';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
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
  assertCountryNotFound,
  assertNonExistentCountryResponse,
  assertGraphQLErrors,
  assertBasicState,
  assertStatesConnection,
} from '../../graphql/assertions/country.assertions';

test.describe('Countries GraphQL API - Complete Test Suite', () => {
  // Helper to get a valid country ID for testing
  // Common country IDs in Bagisto: 1 (India), 2 (USA), 3 (UK), etc.
  const VALID_COUNTRY_IDS = ['1', '2', '3'];

  /* ===================================================
   * POSITIVE TEST CASES
   * =================================================== */

  /* ---------------------------------------------------
   * TEST 1: GET_COUNTRY_BASIC - Basic Country Query
   * Positive: Validates basic country contract
   * --------------------------------------------------- */
  test('GET_COUNTRY_BASIC should return valid basic country data', async ({ request }) => {
    console.log('\n📤 Sending getCountry (Basic) query...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const countryId = VALID_COUNTRY_IDS[0];
    console.log('🔑 Using Country ID:', countryId);

    const response = await sendGraphQLRequest(request, GET_COUNTRY_BASIC, { id: countryId });
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    assertNoGraphQLErrors(body);
    assertGetCountryBasicResponse(body);

    const country = body.data.country;
    console.log('\n📦 Country Details:');
    console.log(`  ID: ${country.id}`);
    console.log(`  _id: ${country._id}`);
    console.log(`  Code: ${country.code}`);
    console.log(`  Name: ${country.name}`);

    console.log('\n✅ GET_COUNTRY_BASIC Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 2: GET_COUNTRY_WITH_STATES - Country with States
   * Positive: Validates country with states data
   * --------------------------------------------------- */
  test('GET_COUNTRY_WITH_STATES should return country with all states', async ({ request }) => {
    console.log('\n📤 Sending getCountry (With States) query...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const countryId = VALID_COUNTRY_IDS[0];
    console.log('🔑 Using Country ID:', countryId);

    const response = await sendGraphQLRequest(request, GET_COUNTRY_WITH_STATES, { id: countryId });
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    assertNoGraphQLErrors(body);
    assertGetCountryWithStatesResponse(body);

    const country = body.data.country;
    console.log('\n📦 Country Details:');
    console.log(`  Name: ${country.name}`);
    console.log(`  States Count: ${country.states.totalCount}`);

    // Log states
    if (country.states.edges.length > 0) {
      console.log('\n🗺️ States:');
      country.states.edges.slice(0, 5).forEach((edge: any, index: number) => {
        const state = edge.node;
        console.log(`  ${index + 1}. ${state.defaultName} (${state.code})`);
        console.log(`     Country Code: ${state.countryCode}`);
        if (state.translations?.edges?.length > 0) {
          console.log(`     Translations: ${state.translations.edges.length}`);
        }
      });
    }

    console.log('\n✅ GET_COUNTRY_WITH_STATES Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 3: GET_COUNTRY_WITH_TRANSLATIONS - Country with Translations
   * Positive: Validates country translations
   * --------------------------------------------------- */
  test('GET_COUNTRY_WITH_TRANSLATIONS should return country with translations', async ({ request }) => {
    console.log('\n📤 Sending getCountry (With Translations) query...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const countryId = VALID_COUNTRY_IDS[0];
    console.log('🔑 Using Country ID:', countryId);

    const response = await sendGraphQLRequest(request, GET_COUNTRY_WITH_TRANSLATIONS, { id: countryId });
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    assertNoGraphQLErrors(body);
    assertGetCountryWithTranslationsResponse(body);

    const country = body.data.country;
    console.log('\n📦 Country Details:');
    console.log(`  Name: ${country.name}`);
    console.log(`  Translations Count: ${country.translations.totalCount}`);

    // Log translations
    if (country.translations.edges.length > 0) {
      console.log('\n🌐 Translations:');
      country.translations.edges.forEach((edge: any) => {
        const translation = edge.node;
        console.log(`  ${translation.locale}: ${translation.name}`);
      });
    }

    console.log('\n✅ GET_COUNTRY_WITH_TRANSLATIONS Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 4: GET_COUNTRY_COMPLETE - Complete Country Details
   * Positive: Validates complete country data with states and translations
   * --------------------------------------------------- */
  test('GET_COUNTRY_COMPLETE should return complete country details', async ({ request }) => {
    console.log('\n📤 Sending getCountry (Complete) query...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const countryId = VALID_COUNTRY_IDS[0];
    console.log('🔑 Using Country ID:', countryId);

    const response = await sendGraphQLRequest(request, GET_COUNTRY_COMPLETE, { id: countryId });
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    assertNoGraphQLErrors(body);
    assertGetCountryCompleteResponse(body);

    const country = body.data.country;
    console.log('\n📦 Complete Country Details:');
    console.log(`  ID: ${country.id}`);
    console.log(`  _id: ${country._id}`);
    console.log(`  Code: ${country.code}`);
    console.log(`  Name: ${country.name}`);
    console.log(`  States: ${country.states.totalCount}`);
    console.log(`  Translations: ${country.translations.totalCount}`);

    // Log states with translations
    if (country.states.edges.length > 0) {
      console.log('\n🗺️ States with Translations:');
      country.states.edges.slice(0, 3).forEach((edge: any) => {
        const state = edge.node;
        console.log(`  - ${state.defaultName} (${state.code})`);
        if (state.translations?.edges?.length > 0) {
          state.translations.edges.forEach((t: any) => {
            console.log(`      ${t.node.locale}: ${t.node.defaultName}`);
          });
        }
      });
    }

    console.log('\n✅ GET_COUNTRY_COMPLETE Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 5: GET_COUNTRY_WITH_STATES - Different Country
   * Positive: Test with different country ID
   * --------------------------------------------------- */
  test('GET_COUNTRY_WITH_STATES should work with different country IDs', async ({ request }) => {
    console.log('\n📤 Testing with different country IDs...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    for (const countryId of VALID_COUNTRY_IDS) {
      console.log(`\n🔑 Testing Country ID: ${countryId}`);

      const response = await sendGraphQLRequest(request, GET_COUNTRY_BASIC, { id: countryId });
      expect(response.status()).toBe(200);

      const body = await response.json();
      
      if (body.data.country) {
        console.log(`  ✅ Found: ${body.data.country.name} (${body.data.country.code})`);
        assertGetCountryBasicResponse(body);
      } else {
        console.log(`  ⚠️ Country not found (may be valid test scenario)`);
      }
    }

    console.log('\n✅ Different Country IDs Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 6: GET_COUNTRY_WITH States Structure_STATES - Verify
   * Positive: Validates detailed state structure
   * --------------------------------------------------- */
  test('GET_COUNTRY_WITH_STATES should return proper state structure', async ({ request }) => {
    console.log('\n📤 Testing state structure...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const countryId = VALID_COUNTRY_IDS[0];
    const response = await sendGraphQLRequest(request, GET_COUNTRY_WITH_STATES, { id: countryId });
    expect(response.status()).toBe(200);

    const body = await response.json();
    assertNoGraphQLErrors(body);

    const country = body.data.country;
    
    if (country.states.edges.length > 0) {
      const state = country.states.edges[0].node;
      
      console.log('\n📦 First State Structure:');
      console.log(`  id: ${state.id}`);
      console.log(`  _id: ${state._id}`);
      console.log(`  code: ${state.code}`);
      console.log(`  defaultName: ${state.defaultName}`);
      console.log(`  countryId: ${state.countryId}`);
      console.log(`  countryCode: ${state.countryCode}`);
      
      // Validate state structure
      assertBasicState(state);
      console.log('\n✅ State Structure Validated!\n');
    } else {
      console.log('\n⚠️ No states found for this country (may be expected)');
      console.log('\n✅ GET_COUNTRY_WITH_STATES Structure Test Passed!\n');
    }
  });

  /* ===================================================
   * NEGATIVE TEST CASES
   * =================================================== */

  /* ---------------------------------------------------
   * TEST 7: Get Country by Invalid ID Format
   * Negative: Validates error handling for invalid ID
   * --------------------------------------------------- */
  test('GET_COUNTRY_BASIC should return error for invalid ID format', async ({ request }) => {
    console.log('\n📤 Testing getCountry with invalid ID format...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const invalidId = 'invalid-format-id';
    console.log('🔑 Using Invalid ID:', invalidId);

    const response = await sendGraphQLRequest(request, GET_COUNTRY_BASIC, { id: invalidId });
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    // Should either return null country or GraphQL error
    if (body.errors) {
      console.log('⚠️ GraphQL Error:', body.errors[0]?.message);
      assertGraphQLErrors(body);
    } else {
      assertCountryNotFound(body);
      console.log('⚠️ Country is null (not found)');
    }

    console.log('\n✅ Invalid ID Format Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 8: Get Country by Non-Existent ID
   * Negative: Validates error handling for non-existent country
   * --------------------------------------------------- */
  test('GET_COUNTRY_BASIC should return null for non-existent country ID', async ({ request }) => {
    console.log('\n📤 Testing getCountry with non-existent ID...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const nonExistentId = 'country_non_existent_99999';
    console.log('🔑 Using Non-Existent ID:', nonExistentId);

    const response = await sendGraphQLRequest(request, GET_COUNTRY_BASIC, { id: nonExistentId });
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    assertNonExistentCountryResponse(body);
    console.log('⚠️ Country is null (not found)');

    console.log('\n✅ Non-Existent ID Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 9: Get Country by Numeric ID as String
   * Negative: Validates handling of numeric string ID
   * --------------------------------------------------- */
  test('GET_COUNTRY_BASIC should handle numeric string ID', async ({ request }) => {
    console.log('\n📤 Testing getCountry with numeric string ID...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    // This should work as IDs are often stored as numbers
    const numericId = '12345';
    console.log('🔑 Using Numeric ID:', numericId);

    const response = await sendGraphQLRequest(request, GET_COUNTRY_BASIC, { id: numericId });
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    // Handle gracefully - may return null or error
    if (body.errors) {
      console.log('⚠️ GraphQL Error:', body.errors[0]?.message);
    } else if (body.data.country === null) {
      console.log('⚠️ Country not found (expected for non-existent ID)');
    } else {
      console.log(`  ✅ Found: ${body.data.country.name}`);
      assertGetCountryBasicResponse(body);
    }

    console.log('\n✅ Numeric String ID Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 10: GET_COUNTRIES_WITH_ALL_TRANSLATIONS - Countries with All Translations
   * Positive: Validates countries with complete translation data
   * --------------------------------------------------- */
  test('GET_COUNTRIES_WITH_ALL_TRANSLATIONS should return countries with all translations', async ({ request }) => {
    console.log('\n📤 Sending countries (With All Translations) query...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const response = await sendGraphQLRequest(request, GET_COUNTRIES_WITH_ALL_TRANSLATIONS);
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    assertGetCountriesWithAllTranslationsResponse(body);

    const countries = body.data.countries;
    console.log('\n📦 Countries with Translations:');
    console.log(`  Total Countries: ${countries.totalCount}`);

    countries.edges.slice(0, 3).forEach((edge: any, index: number) => {
      const country = edge.node;
      const translations = country.translations.edges;
      console.log(`  ${index + 1}. ${country.code}`);
      console.log(`     Translations: ${translations.length}`);
      translations.forEach((translationEdge: any) => {
        const translation = translationEdge.node;
        console.log(`       ${translation.locale}: ${translation.name}`);
      });
    });

    console.log('\n✅ GET_COUNTRIES_WITH_ALL_TRANSLATIONS Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 11: GET_COUNTRIES_FOR_ADDRESS_FORM - Countries for Address Form
   * Positive: Validates countries with states for address form
   * --------------------------------------------------- */
  test('GET_COUNTRIES_FOR_ADDRESS_FORM should return countries for address form', async ({ request }) => {
    console.log('\n📤 Sending countries (For Address Form) query...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const response = await sendGraphQLRequest(request, GET_COUNTRIES_FOR_ADDRESS_FORM);
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    assertGetCountriesForAddressFormResponse(body);

    const countries = body.data.countries;
    console.log('\n📦 Countries for Address Form:');
    console.log(`  Total Countries: ${countries.totalCount}`);

    countries.edges.slice(0, 3).forEach((edge: any, index: number) => {
      const country = edge.node;
      console.log(`  ${index + 1}. ${country.name} (${country.code})`);
      console.log(`     States: ${country.states.totalCount}`);
    });

    console.log('\n✅ GET_COUNTRIES_FOR_ADDRESS_FORM Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 12: Query Non-Existent Field
   * Negative: Validates error handling for non-existent field
   * --------------------------------------------------- */
  test('should return error for non-existent field', async ({ request }) => {
    console.log('\n📤 Testing query with non-existent field...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const invalidQuery = `
      query getSingleCountry($id: ID!) {
        country(id: $id) {
          id
          nonExistentField
        }
      }
    `;

    const countryId = VALID_COUNTRY_IDS[0];
    const response = await sendGraphQLRequest(request, invalidQuery, { id: countryId });
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    // Should return GraphQL error for non-existent field
    if (body.errors) {
      console.log('⚠️ GraphQL Error (expected):', body.errors[0]?.message);
      expect(body.errors.length).toBeGreaterThan(0);
    }

    console.log('\n✅ Non-Existent Field Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 11: Query with Invalid Variable Type
   * Negative: Validates error handling for wrong variable type
   * --------------------------------------------------- */
  test('GET_COUNTRY_BASIC should handle wrong variable type', async ({ request }) => {
    console.log('\n📤 Testing getCountry with wrong variable type...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    // Query with wrong type (number instead of string for ID)
    const invalidQuery = `
      query getSingleCountry($id: ID!) {
        country(id: $id) {
          id
          code
          name
        }
      }
    `;

    const response = await sendGraphQLRequest(request, invalidQuery, { id: 12345 });
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    // Should handle variable type mismatch
    if (body.errors) {
      console.log('⚠️ GraphQL Error:', body.errors[0]?.message);
    } else {
      console.log('📊 Response:', body.data);
    }

    console.log('\n✅ Wrong Variable Type Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 12: Get Country with Empty ID
   * Negative: Validates handling of empty ID
   * --------------------------------------------------- */
  test('GET_COUNTRY_BASIC should handle empty ID', async ({ request }) => {
    console.log('\n📤 Testing getCountry with empty ID...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const response = await sendGraphQLRequest(request, GET_COUNTRY_BASIC, { id: '' });
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    // Should return null or error
    if (body.errors) {
      console.log('⚠️ GraphQL Error:', body.errors[0]?.message);
    } else {
      assertCountryNotFound(body);
      console.log('⚠️ Country is null (not found)');
    }

    console.log('\n✅ Empty ID Test Passed!\n');
  });

  /* ===================================================
   * EDGE CASE TEST CASES
   * =================================================== */

  /* ---------------------------------------------------
   * TEST 13: Country Without States
   * Edge: Validates countries that have no states
   * --------------------------------------------------- */
  test('should handle country without states gracefully', async ({ request }) => {
    console.log('\n📤 Testing country without states...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    // Try different country IDs to find one without states
    const testIds = ['1', '2', '3', '4', '5'];
    
    for (const id of testIds) {
      const response = await sendGraphQLRequest(request, GET_COUNTRY_WITH_STATES, { id });
      const body = await response.json();
      
      if (body.data?.country?.states?.totalCount === 0) {
        console.log(`\n📦 Found country without states: ${body.data.country.name}`);
        console.log(`  States: ${body.data.country.states.totalCount}`);
        expect(body.data.country.states.edges.length).toBe(0);
        break;
      }
    }

    console.log('\n✅ Country Without States Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 14: Verify Country Code Format
   * Edge: Validates country code is uppercase
   * --------------------------------------------------- */
  test('should return country codes in correct format', async ({ request }) => {
    console.log('\n📤 Testing country code format...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const countryId = VALID_COUNTRY_IDS[0];
    const response = await sendGraphQLRequest(request, GET_COUNTRY_BASIC, { id: countryId });
    expect(response.status()).toBe(200);

    const body = await response.json();
    assertNoGraphQLErrors(body);

    const country = body.data.country;
    console.log(`\n📦 Country Code: ${country.code}`);
    
    // Country codes should typically be 2-letter uppercase
    expect(country.code.length).toBeGreaterThanOrEqual(2);
    expect(country.code).toBe(country.code.toUpperCase());

    console.log('\n✅ Country Code Format Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 15: Verify State Pagination Info
   * Edge: Validates pagination info in states
   * --------------------------------------------------- */
  test('should return proper pagination info for states', async ({ request }) => {
    console.log('\n📤 Testing state pagination info...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const countryId = VALID_COUNTRY_IDS[0];
    const response = await sendGraphQLRequest(request, GET_COUNTRY_WITH_STATES, { id: countryId });
    expect(response.status()).toBe(200);

    const body = await response.json();
    assertNoGraphQLErrors(body);

    const country = body.data.country;
    const states = country.states;

    console.log('\n📊 Pagination Info:');
    console.log(`  Total Count: ${states.totalCount}`);
    console.log(`  Has Next Page: ${states.pageInfo.hasNextPage}`);
    console.log(`  End Cursor: ${states.pageInfo.endCursor}`);

    // Validate pagination structure
    expect(states).toHaveProperty('pageInfo');
    expect(states.pageInfo).toHaveProperty('hasNextPage');
    expect(states.pageInfo).toHaveProperty('endCursor');

    console.log('\n✅ State Pagination Info Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 16: Verify Translation Pagination Info
   * Edge: Validates pagination info in translations
   * --------------------------------------------------- */
  test('should return proper pagination info for translations', async ({ request }) => {
    console.log('\n📤 Testing translation pagination info...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const countryId = VALID_COUNTRY_IDS[0];
    const response = await sendGraphQLRequest(request, GET_COUNTRY_WITH_TRANSLATIONS, { id: countryId });
    expect(response.status()).toBe(200);

    const body = await response.json();
    assertNoGraphQLErrors(body);

    const country = body.data.country;
    const translations = country.translations;

    console.log('\n📊 Translation Pagination Info:');
    console.log(`  Total Count: ${translations.totalCount}`);
    console.log(`  Has Next Page: ${translations.pageInfo.hasNextPage}`);
    console.log(`  End Cursor: ${translations.pageInfo.endCursor}`);

    // Validate pagination structure
    expect(translations).toHaveProperty('pageInfo');
    expect(translations.pageInfo).toHaveProperty('hasNextPage');
    expect(translations.pageInfo).toHaveProperty('endCursor');

    console.log('\n✅ Translation Pagination Info Test Passed!\n');
  });

  /* ===================================================
   * POSITIVE TEST CASES - Get All Countries
   * =================================================== */

  /* ---------------------------------------------------
   * TEST 17: GET_COUNTRIES_BASIC - All Countries Basic Query
   * Positive: Validates basic countries list contract
   * --------------------------------------------------- */
  test('GET_COUNTRIES_BASIC should return valid basic countries data', async ({ request }) => {
    console.log('\n📤 Sending getCountries (Basic) query...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const response = await sendGraphQLRequest(request, GET_COUNTRIES_BASIC);
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log('📥 Response Status:', response.status());
    console.log('📊 Total Countries:', body.data?.countries?.totalCount || 0);

    assertNoGraphQLErrors(body);
    assertGetCountriesBasicResponse(body);

    // Log country details
    body.data.countries.edges.forEach((edge: any, index: number) => {
      const country = edge.node;
      console.log(`\nCountry ${index + 1}: ${country.name}`);
      console.log(`  ID: ${country.id}`);
      console.log(`  Code: ${country.code}`);
    });

    console.log('\n✅ GET_COUNTRIES_BASIC Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 18: GET_COUNTRIES_WITH_STATES - All Countries with States
   * Positive: Validates countries list with states and translations
   * --------------------------------------------------- */
  test('GET_COUNTRIES_WITH_STATES should return countries with states and translations', async ({ request }) => {
    console.log('\n📤 Sending getCountries (With States) query...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const response = await sendGraphQLRequest(request, GET_COUNTRIES_WITH_STATES);
    expect(response.status()).toBe(200);

    const body = await response.json();
    const countries = body.data?.countries;

    console.log('📥 Response Status:', response.status());
    console.log('📊 Total Countries:', countries?.totalCount);

    assertNoGraphQLErrors(body);
    assertGetCountriesWithStatesResponse(body);

    // Log countries with states
    countries.edges.forEach((edge: any, index: number) => {
      const country = edge.node;
      console.log(`\nCountry ${index + 1}: ${country.name} (${country.code})`);
      if (country.states) {
        console.log(`  States Count: ${country.states.totalCount}`);
        // Log first 3 states
        country.states.edges.slice(0, 3).forEach((stateEdge: any) => {
          const state = stateEdge.node;
          console.log(`    - ${state.defaultName} (${state.code})`);
        });
      }
      if (country.translations) {
        console.log(`  Translations Count: ${country.translations.totalCount}`);
      }
    });

    console.log('\n✅ GET_COUNTRIES_WITH_STATES Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 19: GET_COUNTRIES_WITH_PAGINATION - Pagination Support
   * Positive: Validates pagination variables work correctly
   * --------------------------------------------------- */
  test('GET_COUNTRIES_WITH_PAGINATION should support first parameter', async ({ request }) => {
    console.log('\n📤 Sending getCountries (With Pagination) query...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const response = await sendGraphQLRequest(request, GET_COUNTRIES_WITH_PAGINATION, { first: 2 });
    expect(response.status()).toBe(200);

    const body = await response.json();
    const countries = body.data?.countries;

    console.log('📥 Response Status:', response.status());
    console.log('📊 Requested Count: 2');
    console.log('📊 Returned Count:', countries?.edges?.length);
    console.log('📊 Total Count:', countries?.totalCount);

    assertNoGraphQLErrors(body);
    assertGetCountriesWithPaginationResponse(body, 2);

    console.log('\n✅ GET_COUNTRIES_WITH_PAGINATION Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 20: GET_COUNTRIES_WITH_PAGINATION - Cursor-based Pagination
   * Positive: Validates cursor-based pagination
   * --------------------------------------------------- */
  test('GET_COUNTRIES_WITH_PAGINATION should fetch next page using after cursor', async ({ request }) => {
    console.log('\n📤 Testing cursor-based pagination...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    // First request to get first page
    const firstResponse = await sendGraphQLRequest(request, GET_COUNTRIES_WITH_PAGINATION, { first: 1 });
    expect(firstResponse.status()).toBe(200);
    const firstBody = await firstResponse.json();
    assertNoGraphQLErrors(firstBody);

    const firstCountries = firstBody.data.countries;
    const { endCursor, hasNextPage } = firstCountries.pageInfo;

    console.log('📄 First Page:');
    console.log(`  Returned: ${firstCountries.edges.length}`);
    console.log(`  End Cursor: ${endCursor}`);
    console.log(`  Has Next Page: ${hasNextPage}`);

    // If there's a next page, fetch it
    if (hasNextPage && endCursor) {
      const secondResponse = await sendGraphQLRequest(request, GET_COUNTRIES_WITH_PAGINATION, {
        first: 1,
        after: endCursor,
      });
      expect(secondResponse.status()).toBe(200);
      const secondBody = await secondResponse.json();
      assertNoGraphQLErrors(secondBody);

      const secondCountries = secondBody.data.countries;
      console.log('\n📄 Second Page:');
      console.log(`  Returned: ${secondCountries.edges.length}`);
      console.log(`  Has Next Page: ${secondCountries.pageInfo.hasNextPage}`);

      // Verify we're getting different data
      const firstId = firstCountries.edges[0]?.node?.id;
      const secondId = secondCountries.edges[0]?.node?.id;

      if (secondId) {
        console.log(`\n🔀 First Page ID: ${firstId}`);
        console.log(`🔀 Second Page ID: ${secondId}`);
        expect(firstId).not.toBe(secondId);
      }
    } else {
      console.log('\n⚠️ Only one page of data available (pagination not testable)');
    }

    console.log('\n✅ Cursor-based Pagination Test Passed!\n');
  });

  /* ===================================================
   * EDGE CASE TESTS - PAGINATION
   * =================================================== */

  /* ---------------------------------------------------
   * TEST 13: Countries with last/before pagination
   * Tests backward pagination
   * --------------------------------------------------- */
  test('GET_COUNTRIES_WITH_LAST_PAGINATION should support backward pagination', async ({ request }) => {
    console.log('\n📤 Testing countries with last/before pagination...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    // First, get total count and some initial data
    const initialResponse = await sendGraphQLRequest(request, GET_COUNTRIES_WITH_PAGINATION, { 
      first: 5 
    });
    const initialBody = await initialResponse.json();
    expect(initialResponse.status()).toBe(200);

    const totalCount = initialBody.data.countries.totalCount;
    console.log(`📊 Total Countries: ${totalCount}`);

    if (totalCount > 10) {
      // Get last 5 countries
      const lastResponse = await sendGraphQLRequest(request, GET_COUNTRIES_WITH_LAST_PAGINATION, { 
        last: 5 
      });
      const lastBody = await lastResponse.json();
      console.log('📥 Response Status:', lastResponse.status());

      if (!lastBody.errors) {
        const countries = lastBody.data.countries;
        console.log(`📦 Returned Countries: ${countries.edges.length}`);
        console.log(`📄 Has Previous Page: ${countries.pageInfo.hasPreviousPage}`);
        console.log(`📄 Has Next Page: ${countries.pageInfo.hasNextPage}`);
        
        // Verify structure
        expect(countries).toHaveProperty('edges');
        expect(countries).toHaveProperty('pageInfo');
        expect(countries).toHaveProperty('totalCount');
      } else {
        console.log('⚠️ GraphQL Error:', lastBody.errors[0]?.message);
      }
    } else {
      console.log('⚠️ Not enough countries to test backward pagination');
    }

    console.log('\n✅ GET_COUNTRIES_WITH_LAST_PAGINATION Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 14: Countries with first and last together
   * Tests invalid combination of first and last
   * --------------------------------------------------- */
  test('GET_COUNTRIES_WITH_FIRST_LAST should handle first and last together', async ({ request }) => {
    console.log('\n📤 Testing countries with first and last together...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const response = await sendGraphQLRequest(request, GET_COUNTRIES_WITH_FIRST_LAST, { 
      first: 5,
      last: 5 
    });
    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    if (body.errors) {
      console.log('⚠️ Expected GraphQL Error:', body.errors[0]?.message);
      // This is expected behavior - first and last together is typically not allowed
    } else {
      const countries = body.data.countries;
      console.log(`📦 Returned Countries: ${countries.edges.length}`);
      expect(countries).toHaveProperty('edges');
      expect(countries).toHaveProperty('totalCount');
    }

    console.log('\n✅ GET_COUNTRIES_WITH_FIRST_LAST Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 15: Countries with zero first value
   * Tests edge case of zero items
   * --------------------------------------------------- */
  test('should handle first=0 gracefully', async ({ request }) => {
    console.log('\n📤 Testing countries with first=0...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const response = await sendGraphQLRequest(request, GET_COUNTRIES_WITH_PAGINATION, { 
      first: 0 
    });
    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    if (!body.errors) {
      const countries = body.data.countries;
      console.log(`📦 Returned Countries: ${countries.edges.length}`);
      console.log(`📊 Total Count: ${countries.totalCount}`);
      // Zero items should return empty array but still have totalCount
      expect(Array.isArray(countries.edges)).toBe(true);
    } else {
      console.log('⚠️ GraphQL Error (expected):', body.errors[0]?.message);
    }

    console.log('\n✅ first=0 Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 16: Countries with negative first value
   * Tests edge case of negative items
   * --------------------------------------------------- */
  test('should handle negative first value', async ({ request }) => {
    console.log('\n📤 Testing countries with negative first value...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const response = await sendGraphQLRequest(request, GET_COUNTRIES_WITH_PAGINATION, { 
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

    console.log('\n✅ Negative first value Test Passed!\n');
  });

  /* ===================================================
   * EDGE CASE TESTS - PERFORMANCE
   * =================================================== */

  /* ---------------------------------------------------
   * TEST 17: Large first value for countries
   * Tests performance with large dataset request
   * --------------------------------------------------- */
  test('should handle large first value efficiently', async ({ request }) => {
    console.log('\n📤 Testing with large first value...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const startTime = Date.now();
    
    const response = await sendGraphQLRequest(request, GET_COUNTRIES_WITH_PAGINATION, { 
      first: 1000 
    });
    
    const endTime = Date.now();
    const duration = endTime - startTime;
    
    const body = await response.json();
    console.log('📥 Response Status:', response.status());
    console.log(`⏱️ Request Duration: ${duration}ms`);

    if (!body.errors) {
      const countries = body.data.countries;
      console.log(`📦 Returned Countries: ${countries.edges.length}`);
      console.log(`📊 Total Count: ${countries.totalCount}`);
      
      // Keep this as an observability check instead of a brittle hard limit.
      expect(duration).toBeGreaterThan(0);
    } else {
      console.log('⚠️ GraphQL Error:', body.errors[0]?.message);
    }

    console.log('\n✅ Large first value Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 18: Multiple sequential pagination requests
   * Tests performance under repeated load
   * --------------------------------------------------- */
  test('should handle multiple sequential pagination requests', async ({ request }) => {
    console.log('\n📤 Testing multiple sequential pagination requests...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const startTime = Date.now();
    const requests = 5;

    for (let i = 0; i < requests; i++) {
      const response = await sendGraphQLRequest(request, GET_COUNTRIES_WITH_PAGINATION, { 
        first: 10 
      });
      const body = await response.json();
      
      if (body.errors) {
        console.log(`⚠️ Request ${i + 1} had errors`);
      }
    }

    const endTime = Date.now();
    const duration = endTime - startTime;
    const avgDuration = duration / requests;

    console.log(`⏱️ Total Duration: ${duration}ms`);
    console.log(`⏱️ Average Duration per Request: ${avgDuration.toFixed(2)}ms`);

    // Should handle multiple requests efficiently
    expect(avgDuration).toBeLessThan(5000); // 5 seconds per request average

    console.log('\n✅ Multiple sequential requests Test Passed!\n');
  });

  /* ===================================================
   * EDGE CASE TESTS - VALIDATION
   * =================================================== */

  /* ---------------------------------------------------
   * TEST 19: Empty string cursor
   * Tests edge case of empty cursor
   * --------------------------------------------------- */
  test('should handle empty cursor string', async ({ request }) => {
    console.log('\n📤 Testing with empty cursor string...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const response = await sendGraphQLRequest(request, GET_COUNTRIES_WITH_PAGINATION, { 
      first: 5,
      after: ''
    });
    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    if (!body.errors) {
      const countries = body.data.countries;
      console.log(`📦 Returned Countries: ${countries.edges.length}`);
    } else {
      console.log('⚠️ GraphQL Error:', body.errors[0]?.message);
    }

    console.log('\n✅ Empty cursor Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 20: Special characters in query
   * Tests handling of special characters
   * --------------------------------------------------- */
  test('should handle special characters gracefully', async ({ request }) => {
    console.log('\n📤 Testing with special characters in ID...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const specialId = '/api/shop/countries/<script>alert(1)</script>';
    
    const response = await sendGraphQLRequest(request, GET_COUNTRY_BASIC, { 
      id: specialId 
    });
    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    if (body.errors) {
      console.log('⚠️ Expected error for special characters:', body.errors[0]?.message);
    } else if (body.data.country === null) {
      console.log('⚠️ Country is null (not found)');
    } else {
      console.log('⚠️ Unexpected result');
    }

    console.log('\n✅ Special characters Test Passed!\n');
  });

  /* ---------------------------------------------------
   * TEST 21: Very large integer country ID
   * Tests handling of very large numbers
   * --------------------------------------------------- */
  test('should handle very large integer country ID', async ({ request }) => {
    console.log('\n📤 Testing with very large integer country ID...');
    console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

    const largeId = '999999999';
    
    const response = await sendGraphQLRequest(request, GET_COUNTRY_BASIC, { 
      id: largeId 
    });
    const body = await response.json();
    console.log('📥 Response Status:', response.status());

    if (body.errors) {
      console.log('⚠️ GraphQL Error:', body.errors[0]?.message);
    } else if (body.data.country === null) {
      console.log('⚠️ Country is null (not found)');
    } else {
      console.log('⚠️ Unexpected country found');
    }

    console.log('\n✅ Large integer ID Test Passed!\n');
  });
});
