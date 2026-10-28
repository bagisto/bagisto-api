import { test, expect, APIRequestContext } from '@playwright/test';
import { SHOP_DOCS_QUERIES } from '../../graphql/Queries/shopDocs.queries';
import { GET_CURRENCIES_COMPLETE, GET_CURRENCY_COMPLETE } from '../../graphql/Queries/currency.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { expectConnection, expectGraphQLSuccess } from '../../graphql/helpers/testSupport';

const INVALID_ID_FORMAT = /invalid[- ]id[- ]format/i;

async function firstListedCurrency(request: APIRequestContext): Promise<any> {
  const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getCurrencies, { first: 1 });
  expect(response.status()).toBe(200);
  const connection = expectConnection(await response.json(), 'data.currencies');
  expect(connection.edges.length, 'the store seeds at least its base currency').toBeGreaterThan(0);

  return connection.edges[0].node;
}

test.describe('currency graphql api tests', () => {
  test('should get all currencies successfully', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getCurrencies, { first: 10 });
    expect(response.status()).toBe(200);
    const body = await response.json();
    expectConnection(body, 'data.currencies');
  });

  test('should return currencies with required fields', async ({ request }) => {
    const currency = await firstListedCurrency(request);

    expect(currency.id).toBe(`/api/shop/currencies/${currency._id}`);
    expect(typeof currency._id).toBe('number');
    expect(currency.code).toMatch(/^[A-Z]{3}$/);
    expect(typeof currency.name).toBe('string');
    expect(typeof currency.symbol).toBe('string');
  });

  test('should get currency by valid id', async ({ request }) => {
    const listed = await firstListedCurrency(request);

    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getCurrency, { id: listed.id });
    expect(response.status()).toBe(200);
    const body = await response.json();
    expectGraphQLSuccess(body, 'data.currency');
    expect(body.data.currency._id).toBe(listed._id);
    expect(body.data.currency.code).toBe(listed.code);
  });

  test('should refuse a currency id that is neither numeric nor an iri', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getCurrency, { id: 'INVALID_CURRENCY' });
    expect(response.status()).toBe(200);
    const body = await response.json();

    expect(body.data?.currency ?? null).toBeNull();
    expect(body.errors).toHaveLength(1);
    expect(body.errors[0].message).toMatch(INVALID_ID_FORMAT);
  });

  test('should reject the currency query when the required id is missing', async ({ request }) => {
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
    expect(body.data ?? null).toBeNull();
    expect(body.errors[0].message).toBe('Field "currency" argument "id" of type "ID!" is required but not provided.');
  });

  test('should cover complete currencies docs query', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_CURRENCIES_COMPLETE, { first: 5, after: null });
    expect(response.status()).toBe(200);
    const body = await response.json();
    const connection = expectConnection(body, 'data.currencies');
    expect(typeof connection.totalCount).toBe('number');
    expect(connection.edges.length).toBe(Math.min(5, connection.totalCount));
  });

  test('should cover complete single currency docs query', async ({ request }) => {
    const listed = await firstListedCurrency(request);

    const response = await sendGraphQLRequest(request, GET_CURRENCY_COMPLETE, { id: listed.id });
    expect(response.status()).toBe(200);
    const body = await response.json();
    expectGraphQLSuccess(body, 'data.currency');
    expect(body.data.currency._id).toBe(listed._id);
    expect(body.data.currency.code).toBe(listed.code);
  });
});
