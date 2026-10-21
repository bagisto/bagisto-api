import { test, expect } from '@playwright/test';
import { SHOP_DOCS_QUERIES } from '../../graphql/Queries/shopDocs.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { expectConnection, expectGraphQLSuccess, graphQLErrorMessages } from '../../graphql/helpers/testSupport';

const LOCALE_ID_REQUIRED_MESSAGE = 'Locale ID is required';

test.describe('locales graphql api - docs aligned', () => {
  test('should return locales as a connection', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getLocales, { first: 10 });
    expect(response.status()).toBe(200);

    const body = await response.json();
    const locales = expectConnection(body, 'data.locales');
    expect(typeof locales.pageInfo.hasNextPage).toBe('boolean');
  });

  test('should return a single locale by the id the listing gives', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getLocales, { first: 1 });
    const body = await response.json();
    const locales = expectConnection(body, 'data.locales');
    expect(locales.edges.length).toBe(1);
    const listed = locales.edges[0].node;

    const singleResponse = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getLocale, {
      id: listed.id,
    });
    expect(singleResponse.status()).toBe(200);

    const singleBody = await singleResponse.json();
    expectGraphQLSuccess(singleBody, 'data.locale');
    expect(singleBody.data.locale._id).toBe(listed._id);
    expect(singleBody.data.locale.code).toBe(listed.code);
  });

  test('should return the id required message for an empty locale id', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getLocale, { id: '' });
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(graphQLErrorMessages(body)).toEqual([LOCALE_ID_REQUIRED_MESSAGE]);
    expect(body.data.locale).toBeNull();
  });
});
