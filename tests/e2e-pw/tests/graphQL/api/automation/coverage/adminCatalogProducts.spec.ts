import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../graphql/helpers/adminGraphqlClient';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import { sendAdminRequest } from '../../../../restAPI/rest/helpers/adminClient';
import { ADMIN_CATALOG_COVERAGE } from '../../../../restAPI/rest/endpoints/coverage/adminCatalog.endpoints';
import { env } from '../../../config/env';
import {
  ADMIN_PRODUCTS_PICKER_COVERAGE_QUERY,
  ADMIN_PRODUCTS_PICKER_INVALID_STATUS_QUERY,
} from '../../../graphql/Queries/coverage/adminCatalog.queries';

test.describe.configure({ timeout: 90_000 });

function uniqueSku(prefix: string): string {
  return `${prefix}-${Date.now().toString(36)}-${Math.floor(Math.random() * 1e6).toString(36)}`;
}

async function pickProducts(request: APIRequestContext, variables: Record<string, any>): Promise<any> {
  const resp = await sendAdminGraphQLRequest(request, ADMIN_PRODUCTS_PICKER_COVERAGE_QUERY, variables);
  expect(resp.status()).toBe(200);
  const body = await resp.json();
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
  const connection = body.data.adminProducts;
  expect(Array.isArray(connection.edges)).toBe(true);
  return connection;
}

test.describe('admin product picker graphql', () => {
  let productId: number;
  let sku: string;

  test.beforeEach(async ({ request }) => {
    productId = 0;
    sku = uniqueSku('e2e-cov-gql-prod');
    const resp = await sendAdminRequest(request, ADMIN_CATALOG_COVERAGE.PRODUCTS, {
      method: 'POST',
      data: { sku, type: 'simple', attribute_family_id: 1 },
    });
    expect(resp.status()).toBe(201);
    productId = (await resp.json()).id;
    expect(typeof productId).toBe('number');
  });

  test.afterEach(async ({ request }) => {
    if (productId) {
      await sendAdminRequest(request, ADMIN_CATALOG_COVERAGE.PRODUCT(productId), { method: 'DELETE' });
    }
  });

  test('should find the created product by exact sku', async ({ request }) => {
    const connection = await pickProducts(request, { first: 5, sku });

    expect(connection.totalCount).toBe(1);
    expect(connection.edges).toHaveLength(1);
    expect(connection.pageInfo.hasNextPage).toBe(false);

    const { cursor, node } = connection.edges[0];
    expect(typeof cursor).toBe('string');
    expect(node._id).toBe(productId);
    expect(node.sku).toBe(sku);
    expect(node.type).toBe('simple');
    expect(typeof node.isSaleable).toBe('boolean');
  });

  test('should match the created product by a partial sku search and type', async ({ request }) => {
    const connection = await pickProducts(request, { first: 50, query: sku.slice(4), type: 'simple' });

    expect(connection.edges.map((edge: any) => edge.node._id)).toContain(productId);
    for (const edge of connection.edges) {
      expect(edge.node.type).toBe('simple');
    }
  });

  test('should return an empty connection when the type filter excludes the product', async ({ request }) => {
    const connection = await pickProducts(request, { first: 5, sku, type: 'bundle' });

    expect(connection.totalCount).toBe(0);
    expect(connection.edges).toEqual([]);
  });

  test('should page through products with the cursor', async ({ request }) => {
    const pagedSku = `${sku}-paged`;
    const resp = await sendAdminRequest(request, ADMIN_CATALOG_COVERAGE.PRODUCTS, {
      method: 'POST',
      data: { sku: pagedSku, type: 'simple', attribute_family_id: 1 },
    });
    expect(resp.status()).toBe(201);
    const pagedId = (await resp.json()).id;

    try {
      const first = await pickProducts(request, { first: 1, query: sku });
      expect(first.totalCount).toBe(2);
      expect(first.edges).toHaveLength(1);
      expect(first.pageInfo.hasNextPage).toBe(true);

      const second = await pickProducts(request, { first: 1, query: sku, after: first.pageInfo.endCursor });
      expect(second.totalCount).toBe(2);
      expect(second.edges).toHaveLength(1);
      expect(second.pageInfo.hasNextPage).toBe(false);

      const pagedIds = [first.edges[0].node._id, second.edges[0].node._id];
      expect([...pagedIds].sort((a, b) => a - b)).toEqual([productId, pagedId].sort((a, b) => a - b));
    } finally {
      await sendAdminRequest(request, ADMIN_CATALOG_COVERAGE.PRODUCT(pagedId), { method: 'DELETE' });
    }
  });

  test('should reject a status that is not an integer', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_PRODUCTS_PICKER_INVALID_STATUS_QUERY);
    const body = await resp.json();
    expect(body.errors?.[0]?.message).toBe('Int cannot represent non-integer value: "enabled"');
    expect(body.data).toBeUndefined();
  });

  test('should refuse the product picker without an admin token', async ({ request }) => {
    const resp = await sendGraphQLRequest(request, ADMIN_PRODUCTS_PICKER_COVERAGE_QUERY, { first: 1, sku }, {}, env.adminGraphqlEndpoint);
    expect(resp.status()).toBe(401);
    const body = await resp.json();
    expect(body.message).toBe('Unauthenticated.');
    expect(body.data).toBeUndefined();
  });
});
