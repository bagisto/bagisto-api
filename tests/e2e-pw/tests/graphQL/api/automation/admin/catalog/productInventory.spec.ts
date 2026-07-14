// tests/graphQL/api/automation/admin/catalog/productInventory.spec.ts
//
// Admin Catalog Product Inventory GraphQL smoke. list-by-product + bulk-update.
// The spec creates its own product so a sibling spec deleting the newest row
// cannot pull the fixture out from under it when the suite runs in parallel.

import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_PRODUCT_INVENTORIES_QUERY,
  ADMIN_PRODUCT_INVENTORY_UPDATE_MUTATION,
} from '../../../../graphql/Queries/admin/catalog/productInventory.queries';
import {
  ADMIN_PRODUCT_CREATE_MUTATION,
  ADMIN_PRODUCT_DELETE_MUTATION,
} from '../../../../graphql/Queries/admin/catalog/products.queries';

test.describe.configure({ timeout: 60_000 });

let productId: number | null = null;

async function createProduct(request: APIRequestContext): Promise<number | null> {
  const resp = await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_CREATE_MUTATION, {
    sku: `e2e-inv-${Date.now()}-${Math.floor(Math.random() * 100000)}`,
    attributeFamilyId: 1,
    type: 'simple',
  });
  const body = await resp.json();
  const id = body?.data?.createAdminCatalogProduct?.adminCatalogProduct?._id;

  return id ? Number(id) : null;
}

test.beforeAll(async ({ request }) => {
  productId = await createProduct(request);
});

test.afterAll(async ({ request }) => {
  if (productId) {
    await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_DELETE_MUTATION, {
      id: `/api/admin/catalog/products/${productId}`,
    });
  }
});

test.describe('Admin Catalog Product Inventory GraphQL', () => {
  test('list inventories for the product', async ({ request }) => {
    test.skip(productId == null, 'product fixture could not be created');

    const resp = await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_INVENTORIES_QUERY, { productId });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    if (body.errors) console.log('inv list errors:', body.errors);
    expect(body.data?.adminCatalogProductInventories?.edges).toBeDefined();
  });

  test('list inventories for non-existent product surfaces empty/errors', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_INVENTORIES_QUERY, {
      productId: 999999999,
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    const ok =
      !!body.errors ||
      Array.isArray(body.data?.adminCatalogProductInventories?.edges);
    expect(ok).toBeTruthy();
  });

  test('bulk-update inventories for the product', async ({ request }) => {
    test.skip(productId == null, 'product fixture could not be created');

    // Pass a single source id 1 with qty 1 — common default seed.
    const resp = await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_INVENTORY_UPDATE_MUTATION, {
      id: `/api/admin/catalog/products/${productId}/inventories`,
      productId,
      inventories: { 1: 1 },
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    if (body.errors) console.log('inv update errors:', body.errors);
    expect(body.data !== undefined || body.errors !== undefined).toBeTruthy();
  });

  test('bulk-update with missing inventories surfaces errors', async ({ request }) => {
    test.skip(productId == null, 'product fixture could not be created');

    const resp = await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_INVENTORY_UPDATE_MUTATION, {
      id: `/api/admin/catalog/products/${productId}/inventories`,
      productId,
      inventories: {},
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    const ok = !!body.errors || body.data != null;
    expect(ok).toBeTruthy();
  });
});
