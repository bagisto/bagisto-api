import { test, expect } from '@playwright/test';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { GET_THEME_CUSTOMIZATIONS_BASIC, GET_THEME_CUSTOMIZATIONS_FILTERED, GET_THEME_CUSTOMIZATIONS_COMPLETE,
  GET_THEME_CUSTOMIZATION_BY_ID, GET_THEME_CUSTOMIZATION_BY_NUMERIC_ID, GET_THEME_CUSTOMIZATION_BY_ID_COMPLETE_DETAILS
 } 
from '../../graphql/Queries/themecustomizations.queries';
import {
  assertGetThemeCustomizationsBasicResponse, assertGetThemeCustomizationsFilteredResponse,
  assertGetThemeCustomizationsCompleteResponse, assertGetThemeCustomizationByIdBasicResponse,
  assertGetThemeCustomizationByNumericIdResponse, assertTranslationsConnection,
  assertExtendedThemeCustomization, assertNoGraphQLErrors,
  assertGraphQLError
} from '../../graphql/assertions/themeCustomizations.assertions';

test.describe('Theme Customization GraphQL Tests', () => {
  test('Should fetch theme customizations (basic)', async ({ request }) => {

  console.log('\n📤 Sending themeCustomizations query...');
  console.log('Variables:', { first: 5 });

  const response = await sendGraphQLRequest( request, GET_THEME_CUSTOMIZATIONS_BASIC,
  { first: 5 });
  console.log('\n📥 RESPONSE DETAILS');
  console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
  console.log('Status Code:', response.status());
  console.log('Status Text:', response.statusText());

  expect(response.status()).toBe(200);

  const body = await response.json();
  const data = body.data.themeCustomizations;

  console.log('\n📊 RESPONSE SUMMARY');
  console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
  console.log('Total Count:', data.totalCount);
  console.log('Has Next Page:', data.pageInfo.hasNextPage);
  console.log('Returned Items:', data.edges.length);

  console.log('\n🔍 THEME CUSTOMIZATIONS LIST');
  console.log('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

  data.edges.forEach((edge: any, index: number) => {
    const node = edge.node;
    console.log(`${index + 1}. ${node.name}`);
    console.log(`   ID: ${node._id}`);
    console.log(`   Type: ${node.type}`);
    console.log(`   Theme: ${node.themeCode}`);
    console.log(`   Status: ${node.status}`);
    console.log(`   Sort Order: ${node.sortOrder}`);
    console.log(`   Locale: ${node.translation?.locale}`);
    console.log(`   Cursor: ${edge.cursor}`);
    console.log('');
  });
  console.log('\n✅ Theme Customizations API Test Passed!\n');
  assertGetThemeCustomizationsBasicResponse(body);
});


test('Should fetch theme customizations filtered by type', async ({ request }) => {
    const variables = { type: 'footer_links'};
    const response = await sendGraphQLRequest( request, GET_THEME_CUSTOMIZATIONS_FILTERED, variables );
    expect(response.status()).toBe(200);
    const body = await response.json();
    assertGetThemeCustomizationsFilteredResponse( body, variables.type );
  });

  /* ---------------------------------------------------
   * INVALID TYPE (Edge Case)
   * --------------------------------------------------- */

  test('Should return empty list for invalid type filter', async ({ request }) => {
    const variables = { type: 'invalid_type_xyz'};
    const response = await sendGraphQLRequest( request, GET_THEME_CUSTOMIZATIONS_FILTERED, variables );
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(body.errors).toBeUndefined();
    const connection = body.data.themeCustomizations;
    expect(Array.isArray(connection.edges)).toBeTruthy();
    expect(connection.edges.length).toBe(0);
    expect(typeof connection.totalCount).toBe('number');
  });


  /* ---------------------------------------------------
   * WITHOUT FILTER (type = null)
   * --------------------------------------------------- */

  test('Should fetch theme customizations without type filter', async ({ request }) => {
    const response = await sendGraphQLRequest( request, GET_THEME_CUSTOMIZATIONS_FILTERED,
    { type: null });
    expect(response.status()).toBe(200);
    const body = await response.json();
    assertGetThemeCustomizationsBasicResponse(body);
  });

  test('Should fetch complete theme customization details', async ({ request }) => {
  const response = await sendGraphQLRequest( request, GET_THEME_CUSTOMIZATIONS_COMPLETE, { first: 3 });
  expect(response.status()).toBe(200);
  const body = await response.json();
  assertGetThemeCustomizationsCompleteResponse(body);
});

test('Should fetch theme customization by ID (basic)', async ({ request }) => {
  const response = await sendGraphQLRequest( request, GET_THEME_CUSTOMIZATION_BY_ID,
  { id: "/api/theme_customizations/1" });
  expect(response.status()).toBe(200);
  const body = await response.json();
  assertGetThemeCustomizationByIdBasicResponse(body);
});

test('Should fetch theme customization by Numeric ID', async ({ request }) => {
  const response = await sendGraphQLRequest( request, GET_THEME_CUSTOMIZATION_BY_NUMERIC_ID,
  { id: "/api/theme_customizations/1" });
  expect(response.status()).toBe(200);
  const body = await response.json();
  assertGetThemeCustomizationByNumericIdResponse(body);
});

test('Should fetch theme customization by ID (complete details)', async ({ request }) => {
  const response = await sendGraphQLRequest( request, GET_THEME_CUSTOMIZATION_BY_ID_COMPLETE_DETAILS,
  { id: "1" });
  expect(response.status()).toBe(200);
  const body = await response.json();
  assertNoGraphQLErrors(body);
  const node = body.data.themeCustomization;
  expect(node).toBeDefined();
  assertExtendedThemeCustomization(node);
  expect(typeof node.channelId).toBe('string');
  expect(typeof node.createdAt).toBe('string');
  expect(typeof node.updatedAt).toBe('string');
  expect(new Date(node.createdAt).toString()).not.toBe('Invalid Date');
  expect(new Date(node.updatedAt).toString()).not.toBe('Invalid Date');
  assertTranslationsConnection(node.translations);
});




test('Should return error when ID variable is missing', async ({ request }) => {
  const response = await sendGraphQLRequest( request, GET_THEME_CUSTOMIZATION_BY_NUMERIC_ID, {});
  expect(response.status()).toBe(200);
  const body = await response.json();
  assertGraphQLError(body, 'Variable "$id"');
});

test('Should return error when ID is null', async ({ request }) => {
  const response = await sendGraphQLRequest(
    request,
    GET_THEME_CUSTOMIZATION_BY_NUMERIC_ID,
    { id: null }
  );
  const body = await response.json();
  assertGraphQLError(body, 'must not be null');
});

test('Should return error when ID is "null"', async ({ request }) => {
  const response = await sendGraphQLRequest( request, GET_THEME_CUSTOMIZATION_BY_NUMERIC_ID,
  { id: "null" });
  const body = await response.json();
  assertGraphQLError(body, 'Invalid ID format');
});

test('Should return error for invalid GraphQL syntax', async ({ request }) => {
  const invalidQuery = `
    query {
      themeCustomization(id: "1") {
        id
        name
  `;
  const response = await sendGraphQLRequest(request, invalidQuery );
  const body = await response.json();
  assertGraphQLError(body, 'Syntax Error');
});

test('Should return error when querying non-existing field', async ({ request }) => {
  const invalidFieldQuery = `
query getThemeCustomisation($id: ID!) {
  themeCustomization(id: $id) {
    id
    nonExistingField   # ❌ not in schema
  }
}
`;
  const response = await sendGraphQLRequest( request, invalidFieldQuery,
  { id: "1" });
  const body = await response.json();
  assertGraphQLError(body, 'Cannot query field');
});

test('Should return error for wrong variable type', async ({ request }) => {
  const response = await sendGraphQLRequest( request, GET_THEME_CUSTOMIZATION_BY_NUMERIC_ID,
  { id: { value: "1" } });
  const body = await response.json();
  assertGraphQLError(body, 'ID');
});

test('Should return error for empty ID string', async ({ request }) => {
  const response = await sendGraphQLRequest( request, GET_THEME_CUSTOMIZATION_BY_NUMERIC_ID,
  { id: "" });
  const body = await response.json();
  // depending on backend: null or error
  if (body.errors) {
    assertGraphQLError(body);
  } else {
    expect(body.data.themeCustomization).toBeNull();
  }
});

test('Should not allow injection-like ID values', async ({ request }) => {
  const response = await sendGraphQLRequest( request, GET_THEME_CUSTOMIZATION_BY_NUMERIC_ID,
  { id: "1 OR 1=1" });
  const body = await response.json();
  if (body.errors) {
    assertGraphQLError(body);
  } else {
    expect(body.data.themeCustomization).toBeNull();
  }
});
});
