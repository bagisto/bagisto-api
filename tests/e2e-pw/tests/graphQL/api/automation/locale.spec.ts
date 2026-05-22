import { test, expect } from '@playwright/test';
import { SHOP_DOCS_QUERIES } from '../../graphql/Queries/shopDocs.queries';
import { GET_LOCALES_COMPLETE, GET_LOCALE_COMPLETE } from '../../graphql/Queries/locale.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { expectConnection, expectGraphQLSuccess, graphQLErrorMessages } from '../../graphql/helpers/testSupport';

test.describe('Locale GraphQL API Tests', () => {
  test('Should get all locales successfully', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getLocales, { first: 10 });
    expect(response.status()).toBe(200);
    const body = await response.json();
    expectConnection(body, 'data.locales');
  });

  test('Should return locales with required fields', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getLocales, { first: 1 });
    expect(response.status()).toBe(200);
    const body = await response.json();
    const locales = expectConnection(body, 'data.locales');

    if (locales.edges.length > 0) {
      const locale = locales.edges[0].node;
      expect(locale.id).toBeDefined();
      expect(locale.code).toBeDefined();
      expect(locale.name).toBeDefined();
      expect(locale.direction).toBeDefined();
    }
  });

  test('Should get locale by valid id', async ({ request }) => {
    const listResponse = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getLocales, { first: 1 });
    const listBody = await listResponse.json();
    const locales = expectConnection(listBody, 'data.locales');

    if (locales.edges.length === 0) {
      console.log('No locales available in this environment.');
      return;
    }

    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getLocale, { id: locales.edges[0].node.id });
    expect(response.status()).toBe(200);
    const body = await response.json();
    expectGraphQLSuccess(body, 'data.locale');
  });

  test('Should handle invalid locale id gracefully', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getLocale, { id: 'invalid-locale-code-xyz' });
    expect(response.status()).toBe(200);
    const body = await response.json();
    console.log(`Locale invalid id response: ${graphQLErrorMessages(body).join(' | ')}`);
    expect(body.data?.locale === null || graphQLErrorMessages(body).length > 0).toBeTruthy();
  });

  test('Should handle missing id parameter gracefully', async ({ request }) => {
    const invalidQuery = `
      query GetLocale {
        locale {
          id
        }
      }
    `;
    const response = await sendGraphQLRequest(request, invalidQuery);
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(graphQLErrorMessages(body).length).toBeGreaterThan(0);
  });

  test('Should cover complete locales docs query', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_LOCALES_COMPLETE, { first: 5, after: null });
    expect(response.status()).toBe(200);
    const body = await response.json();
    const locales = expectConnection(body, 'data.locales');
    expect(typeof locales.totalCount).toBe('number');
  });

  test('Should cover complete single locale docs query', async ({ request }) => {
    const listResponse = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getLocales, { first: 1 });
    const listBody = await listResponse.json();
    const locales = expectConnection(listBody, 'data.locales');

    if (locales.edges.length === 0) {
      console.log('No locales available for complete coverage.');
      expect(true).toBeTruthy();
      return;
    }

    const response = await sendGraphQLRequest(request, GET_LOCALE_COMPLETE, { id: locales.edges[0].node.id });
    expect(response.status()).toBe(200);
    const body = await response.json();
    expectGraphQLSuccess(body, 'data.locale');
  });
});
