import { test, expect } from '@playwright/test';
import { SHOP_DOCS_QUERIES } from '../../graphql/Queries/shopDocs.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { expectConnection, expectGraphQLSuccess, graphQLErrorMessages } from '../../graphql/helpers/testSupport';

test.describe('Locales GraphQL API - Docs aligned', () => {
  test('Should return locales as a connection', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getLocales, { first: 10 });
    expect(response.status()).toBe(200);

    const body = await response.json();
    const locales = expectConnection(body, 'data.locales');
    expect(typeof locales.pageInfo.hasNextPage).toBe('boolean');
  });

  test('Should return a single locale by ID', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getLocales, { first: 1 });
    const body = await response.json();
    const locales = expectConnection(body, 'data.locales');

    if (locales.edges.length === 0) {
      console.log('No locales found in this environment.');
      return;
    }

    const singleResponse = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getLocale, {
      id: locales.edges[0].node.id,
    });
    expect(singleResponse.status()).toBe(200);

    const singleBody = await singleResponse.json();
    expectGraphQLSuccess(singleBody, 'data.locale');
  });

  test('Should return the actual validation message for an invalid locale ID', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getLocale, { id: '' });
    expect(response.status()).toBe(200);

    const body = await response.json();
    const messages = graphQLErrorMessages(body);
    console.log(`Locale invalid ID response: ${messages.join(' | ')}`);
    expect(messages.length > 0 || body.data?.locale === null).toBeTruthy();
  });
});
