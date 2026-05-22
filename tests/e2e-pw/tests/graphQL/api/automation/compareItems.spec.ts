import { test, expect, APIRequestContext } from '@playwright/test';
import { getCustomerAuthHeaders } from '../../config/auth';
import {
  CREATE_COMPARE_ITEM,
  DELETE_ALL_COMPARE_ITEMS,
  DELETE_COMPARE_ITEM,
  GET_COMPARE_ITEMS_PAGINATED,
} from '../../graphql/Queries/compare.queries';
import { SHOP_DOCS_QUERIES } from '../../graphql/Queries/shopDocs.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../graphql/helpers/testSupport';

async function getProductId(request: APIRequestContext, index = 0): Promise<number> {
  const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getProducts, { first: index + 1 });
  const body = await response.json();
  const node = body.data?.products?.edges?.[index]?.node;
  const numericId = Number(String(node?.id ?? '').split('/').pop());
  expect(numericId > 0, `test store must expose product at index ${index}`).toBeTruthy();
  return numericId;
}

async function createCompareItem(
  request: APIRequestContext,
  headers: Record<string, string>,
  productId: number
): Promise<number> {
  const response = await sendGraphQLRequest(
    request,
    CREATE_COMPARE_ITEM,
    { input: { productId, clientMutationId: `compare-create-${productId}` } },
    headers
  );
  expect(response.status()).toBe(200);

  const body = await response.json();
  expect(
    body.errors,
    `create compare item errored: ${graphQLErrorMessages(body).join(' | ')}`
  ).toBeUndefined();

  const item = body.data?.createCompareItem?.compareItem;
  expect(item?._id, 'compare item should include numeric _id').toBeGreaterThan(0);
  return item._id;
}

test.describe('Compare Items GraphQL API Tests', () => {
  test.slow();

  test('Should list compare items for an authenticated customer', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);

    const response = await sendGraphQLRequest(
      request,
      SHOP_DOCS_QUERIES.getCompareItems,
      {},
      headers
    );
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(
      body.errors,
      `list compare items errored: ${graphQLErrorMessages(body).join(' | ')}`
    ).toBeUndefined();
    expect(Array.isArray(body.data?.compareItems?.edges)).toBe(true);
  });

  test('Should fetch a compare item by ID after creating one', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);
    const productId = await getProductId(request, 0);
    const compareItemId = await createCompareItem(request, headers, productId);

    const response = await sendGraphQLRequest(
      request,
      SHOP_DOCS_QUERIES.getCompareItem,
      { id: compareItemId },
      headers
    );
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(
      body.errors,
      `get compare item errored: ${graphQLErrorMessages(body).join(' | ')}`
    ).toBeUndefined();
    expect(body.data?.compareItem?._id).toBe(compareItemId);
  });

  test('Should return a GraphQL error for invalid compare item ID', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);

    const response = await sendGraphQLRequest(
      request,
      SHOP_DOCS_QUERIES.getCompareItem,
      { id: 'invalid-id-99999' },
      headers
    );
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(graphQLErrorMessages(body).length).toBeGreaterThan(0);
  });

  test('Should return a GraphQL error when required ID parameter is missing', async ({ request }) => {
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
    expect(graphQLErrorMessages(body).length).toBeGreaterThan(0);
  });

  test('Should paginate compare items via the docs-aligned query', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);

    const response = await sendGraphQLRequest(
      request,
      GET_COMPARE_ITEMS_PAGINATED,
      { first: 2, after: null },
      headers
    );
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(
      body.errors,
      `paginate compare items errored: ${graphQLErrorMessages(body).join(' | ')}`
    ).toBeUndefined();

    const connection = body.data?.compareItems;
    expect(connection).toBeTruthy();
    expect(Array.isArray(connection.edges)).toBe(true);
    expect(typeof connection.totalCount).toBe('number');
  });

  test('Should create, delete one, then delete all compare items', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);
    const productId = await getProductId(request, 0);

    const compareItemId = await createCompareItem(request, headers, productId);

    const deleteResponse = await sendGraphQLRequest(
      request,
      DELETE_COMPARE_ITEM,
      { input: { id: compareItemId, clientMutationId: 'compare-delete-001' } },
      headers
    );
    expect(deleteResponse.status()).toBe(200);
    const deleteBody = await deleteResponse.json();
    expect(
      deleteBody.errors,
      `delete compare item errored: ${graphQLErrorMessages(deleteBody).join(' | ')}`
    ).toBeUndefined();
    expect(deleteBody.data?.deleteCompareItem?.clientMutationId).toBe('compare-delete-001');

    const secondProductId = await getProductId(request, 1);
    await createCompareItem(request, headers, secondProductId);

    const deleteAllResponse = await sendGraphQLRequest(request, DELETE_ALL_COMPARE_ITEMS, {}, headers);
    expect(deleteAllResponse.status()).toBe(200);
    const deleteAllBody = await deleteAllResponse.json();
    expect(
      deleteAllBody.errors,
      `delete all compare items errored: ${graphQLErrorMessages(deleteAllBody).join(' | ')}`
    ).toBeUndefined();
    expect(deleteAllBody.data?.createDeleteAllCompareItems?.deleteAllCompareItems).toBeTruthy();
  });
});
