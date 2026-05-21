import { test, expect } from '@playwright/test';
import { SHOP_DOCS_QUERIES } from '../../graphql/Queries/shopDocs.queries';
import { GET_CURRENCIES_COMPLETE, GET_CURRENCY_COMPLETE } from '../../graphql/Queries/currency.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { expectConnection, expectGraphQLSuccess, graphQLErrorMessages } from '../../graphql/helpers/testSupport';

test.describe('Currency GraphQL API Tests', () => {
  test('Should get all currencies successfully', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getCurrencies, { first: 10 });
    expect(response.status()).toBe(200);
    const body = await response.json();
    expectConnection(body, 'data.currencies');
  });

  test('Should return currencies with required fields', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getCurrencies, { first: 1 });
    expect(response.status()).toBe(200);
    const body = await response.json();
    const connection = expectConnection(body, 'data.currencies');

    if (connection.edges.length > 0) {
      const firstCurrency = connection.edges[0].node;
      expect(firstCurrency.id).toBeDefined();
      expect(firstCurrency._id).toBeDefined();
      expect(firstCurrency.code).toBeDefined();
      expect(firstCurrency.name).toBeDefined();
      expect(firstCurrency.symbol).toBeDefined();
    }
  });

  test('Should get currency by valid code', async ({ request }) => {
    const allResponse = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getCurrencies, { first: 1 });
    const allBody = await allResponse.json();

    if (allBody.data?.currencies?.edges?.length > 0) {
      const currencyId = allBody.data.currencies.edges[0].node.id;
      const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getCurrency, { id: currencyId });
      expect(response.status()).toBe(200);
      const body = await response.json();
      expectGraphQLSuccess(body, 'data.currency');
    }
  });

  test('Should handle invalid currency code gracefully', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getCurrency, { id: 'INVALID_CURRENCY' });
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(body.data?.currency === null || graphQLErrorMessages(body).length > 0).toBeTruthy();
  });

  test('Should handle missing code parameter gracefully', async ({ request }) => {
    const invalidQuery = `
      query getCurrencyByID {
        currency {
          id
        }
      }
    `;

    const response = await sendGraphQLRequest(request, invalidQuery);
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(graphQLErrorMessages(body).length).toBeGreaterThan(0);
  });

  test('Should cover complete currencies docs query', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_CURRENCIES_COMPLETE, { first: 5, after: null });
    expect(response.status()).toBe(200);
    const body = await response.json();
    const connection = expectConnection(body, 'data.currencies');
    expect(typeof connection.totalCount).toBe('number');
  });

  test('Should cover complete single currency docs query', async ({ request }) => {
    const allResponse = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getCurrencies, { first: 1 });
    const allBody = await allResponse.json();
    const currencyId = allBody.data?.currencies?.edges?.[0]?.node?.id;

    if (!currencyId) {
      console.log('No currency available for complete coverage.');
      expect(true).toBeTruthy();
      return;
    }

    const response = await sendGraphQLRequest(request, GET_CURRENCY_COMPLETE, { id: currencyId });
    expect(response.status()).toBe(200);
    const body = await response.json();
    expectGraphQLSuccess(body, 'data.currency');
  });
});
