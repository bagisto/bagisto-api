import { test, expect } from '@playwright/test';
import { getCustomerAuthHeaders } from '../../config/auth';
import { SHOP_DOCS_QUERIES } from '../../graphql/Queries/shopDocs.queries';
import { GET_COMPARE_ITEMS_PAGINATED } from '../../graphql/Queries/compare.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { expectAuthAwareResult, graphQLErrorMessages } from '../../graphql/helpers/testSupport';

test.describe('Compare Items GraphQL API Tests', () => {
  test('Should get all compare items successfully', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getCompareItems, {}, headers);
    expect(response.status()).toBe(200);
    const body = await response.json();
    expectAuthAwareResult(body, 'data.compareItems');
  });

  test('Should get compare item by valid ID', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const allResponse = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getCompareItems, {}, headers);
    const allBody = await allResponse.json();

    if (allBody.data?.compareItems?.edges?.length > 0) {
      const compareItemId = allBody.data.compareItems.edges[0].node.id;
      const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getCompareItem, { id: compareItemId }, headers);
      expect(response.status()).toBe(200);
      const body = await response.json();
      expectAuthAwareResult(body, 'data.compareItem');
    } else {
      console.log(`No compare items found or auth required: ${graphQLErrorMessages(allBody).join(' | ')}`);
    }
  });

  test('Should handle invalid compare item ID gracefully', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getCompareItem, { id: 'invalid-id-99999' }, headers);
    expect(response.status()).toBe(200);
    const body = await response.json();
    console.log(`Compare invalid ID response: ${graphQLErrorMessages(body).join(' | ')}`);
    expect(body.data?.compareItem === null || graphQLErrorMessages(body).length > 0).toBeTruthy();
  });

  test('Should handle missing ID parameter gracefully', async ({ request }) => {
    const invalidQuery = `
      query GetCompareItem {
        compareItem {
          id
        }
      }
    `;
    
    const response = await sendGraphQLRequest(request, invalidQuery);
    
    expect(response.status()).toBe(200);
    
    const body = await response.json();
    
    // Should have errors for missing required parameter
    expect(body.errors !== undefined).toBeTruthy();
  });

  test('Should cover compare items paginated docs query', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const response = await sendGraphQLRequest(request, GET_COMPARE_ITEMS_PAGINATED, { first: 2, after: null }, headers);
    expect(response.status()).toBe(200);
    const body = await response.json();
    expectAuthAwareResult(body, 'data.compareItems');
  });
});
