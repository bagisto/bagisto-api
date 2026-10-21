import { test, expect } from '@playwright/test';
import { SHOP_DOCS_QUERIES } from '../../graphql/Queries/shopDocs.queries';
import { GET_LOCALES_COMPLETE, GET_LOCALE_COMPLETE } from '../../graphql/Queries/locale.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { expectConnection, expectGraphQLSuccess, graphQLErrorMessages } from '../../graphql/helpers/testSupport';

const INVALID_LOCALE_ID_MESSAGE = 'Invalid ID format. Expected IRI format like "/api/shop/locales/1" or numeric ID';

test.describe('locale graphql api tests', () => {
  test('should get all locales successfully', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getLocales, { first: 10 });
    expect(response.status()).toBe(200);
    const body = await response.json();
    expectConnection(body, 'data.locales');
  });

  test('should return locales with required fields', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getLocales, { first: 1 });
    expect(response.status()).toBe(200);
    const body = await response.json();
    const locales = expectConnection(body, 'data.locales');
    expect(locales.edges.length).toBe(1);

    const locale = locales.edges[0].node;
    expect(typeof locale.id).toBe('string');
    expect(typeof locale.code).toBe('string');
    expect(typeof locale.name).toBe('string');
    expect(['ltr', 'rtl']).toContain(locale.direction);
  });

  test('should get locale by valid id', async ({ request }) => {
    const listResponse = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getLocales, { first: 1 });
    const listBody = await listResponse.json();
    const locales = expectConnection(listBody, 'data.locales');
    expect(locales.edges.length).toBe(1);
    const listed = locales.edges[0].node;

    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getLocale, { id: listed.id });
    expect(response.status()).toBe(200);
    const body = await response.json();
    expectGraphQLSuccess(body, 'data.locale');
    expect(body.data.locale._id).toBe(listed._id);
    expect(body.data.locale.code).toBe(listed.code);
  });

  test('should return the invalid id format message for a non numeric locale id', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getLocale, { id: 'invalid-locale-code-xyz' });
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(graphQLErrorMessages(body)).toEqual([INVALID_LOCALE_ID_MESSAGE]);
    expect(body.data.locale).toBeNull();
  });

  test('should handle missing id parameter gracefully', async ({ request }) => {
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

  test('should cover complete locales docs query', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_LOCALES_COMPLETE, { first: 5, after: null });
    expect(response.status()).toBe(200);
    const body = await response.json();
    const locales = expectConnection(body, 'data.locales');
    expect(typeof locales.totalCount).toBe('number');
  });

  test('should cover complete single locale docs query', async ({ request }) => {
    const listResponse = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getLocales, { first: 1 });
    const listBody = await listResponse.json();
    const locales = expectConnection(listBody, 'data.locales');
    expect(locales.edges.length).toBe(1);
    const listed = locales.edges[0].node;

    const response = await sendGraphQLRequest(request, GET_LOCALE_COMPLETE, { id: listed.id });
    expect(response.status()).toBe(200);
    const body = await response.json();
    expectGraphQLSuccess(body, 'data.locale');
    expect(body.data.locale._id).toBe(listed._id);
    expect(body.data.locale.code).toBe(listed.code);
  });
});
