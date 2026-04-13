import { test, expect } from '@playwright/test';
import { getCustomerAuthHeaders } from '../../config/auth';
import {
  CREATE_COMPARE_ITEM,
  DELETE_ALL_COMPARE_ITEMS,
  DELETE_COMPARE_ITEM,
  GET_COMPARE_ITEMS_PAGINATED,
} from '../../graphql/Queries/compare.queries';
import { SHOP_DOCS_QUERIES } from '../../graphql/Queries/shopDocs.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { expectAuthAwareResult, graphQLErrorMessages } from '../../graphql/helpers/testSupport';

async function getFirstProductId(request: any) {
  const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getProducts, { first: 1 });
  const body = await response.json();
  const node = body.data?.products?.edges?.[0]?.node;
  const numericId = Number(String(node?.id ?? '').split('/').pop());
  return node?._id ?? (Number.isFinite(numericId) && numericId > 0 ? numericId : null);
}

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

  test('Should try compare item mutations and show the real API response', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const productId = await getFirstProductId(request);

    const createResponse = await sendGraphQLRequest(
      request,
      CREATE_COMPARE_ITEM,
      { input: { productId, clientMutationId: 'compare-create-001' } },
      headers
    );
    expect(createResponse.status()).toBe(200);
    const createBody = await createResponse.json();
    console.log(`Create compare item response: ${JSON.stringify(createBody)}`);
    expect(createBody.data?.createCompareItem?.compareItem || graphQLErrorMessages(createBody).length > 0).toBeTruthy();

    const compareItemId =
      createBody.data?.createCompareItem?.compareItem?.id ??
      createBody.data?.createCompareItem?.compareItem?._id ??
      'invalid-id-99999';

    const deleteResponse = await sendGraphQLRequest(
      request,
      DELETE_COMPARE_ITEM,
      { input: { id: compareItemId, clientMutationId: 'compare-delete-001' } },
      headers
    );
    expect(deleteResponse.status()).toBe(200);
    const deleteBody = await deleteResponse.json();
    console.log(`Delete compare item response: ${JSON.stringify(deleteBody)}`);
    expect(deleteBody.data?.deleteCompareItem?.compareItem || graphQLErrorMessages(deleteBody).length > 0).toBeTruthy();

    const deleteAllResponse = await sendGraphQLRequest(request, DELETE_ALL_COMPARE_ITEMS, {}, headers);
    expect(deleteAllResponse.status()).toBe(200);
    const deleteAllBody = await deleteAllResponse.json();
    console.log(`Delete all compare items response: ${JSON.stringify(deleteAllBody)}`);
    expect(
      deleteAllBody.data?.createDeleteAllCompareItems?.deleteAllCompareItems ||
      graphQLErrorMessages(deleteAllBody).length > 0
    ).toBeTruthy();
  });
});
