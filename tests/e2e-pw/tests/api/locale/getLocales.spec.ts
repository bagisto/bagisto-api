import { test, expect } from '@playwright/test';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { GET_ALL_LOCALES } from '../../graphql/Queries/locale.queries';
import {
  assertNoGraphQLErrors,
  assertLocalesResponse,
  assertValidLocale,
} from '../../graphql/assertions/locale.assertions';
import { env } from '../../config/env';

test.describe('Locales GraphQL API', () => {

  /* ===================================================
   * POSITIVE TEST CASE 1
   * Scenario: Successfully fetch all locales
   * Purpose : Validate API works with valid request
   * =================================================== */
  test('Should fetch all locales successfully', async ({ request }) => {

    // ⏱️ Measure response time (LOGGING ONLY)
    const startTime = Date.now();
    const response = await sendGraphQLRequest(request, GET_ALL_LOCALES);
    const responseTime = Date.now() - startTime;

    // ✅ HTTP-level validation
    expect(response.status()).toBe(200);

    const body = await response.json();

    // ✅ GraphQL-level validation
    assertNoGraphQLErrors(body);

    // ✅ Response structure validation
    assertLocalesResponse(body);

    // ✅ Business-level validation (single locale)
    const locales = body.data.locales.edges;
    const locale = locales[0].node;
    assertValidLocale(locale);

    // 🖥️ Informational logs (NO assertions)
    console.log('Locales API response info');
    console.log(`Response time: ${responseTime} ms`);
    console.log(`Total locales: ${locales.length}`);
    console.log(
      `Locale codes: ${locales.map((l: any) => l.node.code).join(', ')}`
    );
  });

  /* ===================================================
   * POSITIVE TEST CASE 2
   * Scenario: Locale list should not be empty
   * Purpose : Ensure at least one locale exists
   * =================================================== */
  test('Locales list should not be empty', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ALL_LOCALES);
    const body = await response.json();

    const locales = body.data.locales.edges;
    expect(locales.length).toBeGreaterThan(0);
  });

  /* ===================================================
   * POSITIVE TEST CASE 3
   * Scenario: All locales should have valid fields
   * Purpose : Validate data integrity for each locale
   * =================================================== */
  test('Each locale should have valid required fields', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ALL_LOCALES);
    const body = await response.json();

    const locales = body.data.locales.edges;

    for (const edge of locales) {
      assertValidLocale(edge.node);
    }
  });

  /* ===================================================
   * NEGATIVE TEST CASE 1
   * Scenario: Invalid GraphQL query
   * Purpose : API should return GraphQL errors
   * =================================================== */
  test('Should return GraphQL error for invalid query', async ({ request }) => {
    const invalidQuery = `
      query InvalidLocalesQuery {
        localesss {
          edges {
            node {
              id
            }
          }
        }
      }
    `;

    const response = await sendGraphQLRequest(request, invalidQuery);

    // GraphQL always returns 200 even for query errors
    expect(response.status()).toBe(200);

    const body = await response.json();

    // ✅ Error validation
    expect(body.errors).toBeDefined();
    expect(Array.isArray(body.errors)).toBe(true);
    expect(body.errors.length).toBeGreaterThan(0);

    // ✅ Data should be null or undefined
    expect(body.data === null || body.data === undefined).toBeTruthy();
  });

  /* ===================================================
   * NEGATIVE TEST CASE 2
   * Scenario: Missing storefront access key
   * Purpose : API should reject unauthenticated requests
   * =================================================== */
  test('Should fail when storefront key is missing', async ({ request }) => {
    const response = await request.post(
      `${env.baseUrl}${env.graphqlEndpoint}`,
      {
        data: {
          query: GET_ALL_LOCALES,
        },
        headers: {
          'Content-Type': 'application/json',
          // ❌ X-STOREFRONT-KEY intentionally missing
        },
      }
    );

    // Expect client or auth error
    expect(response.status()).toBeGreaterThanOrEqual(400);

    const body = await response.json();
    expect(body.errors || body.message).toBeTruthy();
  });

  /* ===================================================
   * NEGATIVE TEST CASE 3
   * Scenario: Invalid storefront access key
   * Purpose : API should reject invalid credentials
   * =================================================== */
  test('Should fail with invalid storefront key', async ({ request }) => {
    const response = await sendGraphQLRequest(
      request,
      GET_ALL_LOCALES,
      {},
      {
        'X-STOREFRONT-KEY': 'invalid_storefront_key',
      }
    );

    expect(response.status()).toBeGreaterThanOrEqual(401);

    const body = await response.json();
    expect(body.errors || body.message).toBeTruthy();
  });

});
