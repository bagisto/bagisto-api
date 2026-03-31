import { test, expect } from '@playwright/test';
import { getGuestCartHeaders } from '../../config/auth';
import { SHOP_DOCS_QUERIES } from '../../graphql/Queries/shopDocs.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { expectGraphQLSuccess, graphQLErrorMessages, logGraphQLMessages } from '../../graphql/helpers/testSupport';

test.describe('Cart GraphQL API Tests', () => {
  test('Should create a guest cart token successfully', async ({ request }) => {
    const guestHeaders = await getGuestCartHeaders(request);
    expect(guestHeaders.Authorization).toContain('Bearer ');
  });

  test('Should fetch the current cart using the docs-aligned read cart mutation', async ({ request }) => {
    const guestHeaders = await getGuestCartHeaders(request);
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.createReadCart, {}, guestHeaders);
    expect(response.status()).toBe(200);

    const body = await response.json();
    expectGraphQLSuccess(body, 'data.createReadCart.readCart');
  });

  test('Should return a GraphQL validation error for an invalid cart query', async ({ request }) => {
    const invalidQuery = `
      mutation invalidReadCart {
        createReadCart(input: { invalid: "value" }) {
          id
        }
      }
    `;

    const response = await sendGraphQLRequest(request, invalidQuery);
    expect(response.status()).toBe(200);

    const body = await response.json();
    logGraphQLMessages('Cart invalid query', body);
    expect(graphQLErrorMessages(body).length).toBeGreaterThan(0);
  });
});
