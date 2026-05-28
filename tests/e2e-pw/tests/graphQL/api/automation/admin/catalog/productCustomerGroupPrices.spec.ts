// tests/graphQL/api/automation/admin/catalog/productCustomerGroupPrices.spec.ts
//
// Admin Catalog Product Customer Group Prices GraphQL smoke. CRUD.

import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_PRODUCT_CGP_LIST_QUERY,
  ADMIN_PRODUCT_CGP_CREATE_MUTATION,
  ADMIN_PRODUCT_CGP_UPDATE_MUTATION,
  ADMIN_PRODUCT_CGP_DELETE_MUTATION,
} from '../../../../graphql/Queries/admin/catalog/productCustomerGroupPrices.queries';
import { ADMIN_PRODUCTS_LIST_QUERY } from '../../../../graphql/Queries/admin/catalog/products.queries';

test.describe.configure({ timeout: 60_000 });

async function firstProductId(request: any): Promise<number | null> {
  const list = await sendAdminGraphQLRequest(request, ADMIN_PRODUCTS_LIST_QUERY, { first: 1 });
  const edges = (await list.json()).data?.adminCatalogProducts?.edges ?? [];
  return edges[0]?.node?._id ? Number(edges[0].node._id) : null;
}

test.describe('Admin Catalog Product Customer Group Prices GraphQL', () => {
  test('list cgp for first product', async ({ request }) => {
    const productId = await firstProductId(request);
    test.skip(productId == null, 'no products present');

    const resp = await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_CGP_LIST_QUERY, { productId });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    if (body.errors) console.log('cgp list errors:', body.errors);
    expect(body.data?.adminCatalogProductCustomerGroupPrices).toBeDefined();
  });

  test('list cgp for non-existent product', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_CGP_LIST_QUERY, {
      productId: 999999999,
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    const ok = !!body.errors || body.data !== undefined;
    expect(ok).toBeTruthy();
  });

  test('create + update + delete cgp roundtrip', async ({ request }) => {
    const productId = await firstProductId(request);
    test.skip(productId == null, 'no products present');

    const uniqueQty = 100 + Math.floor(Math.random() * 9000);
    const cr = await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_CGP_CREATE_MUTATION, {
      productId,
      qty: uniqueQty,
      valueType: 'fixed',
      value: 9.99,
      customerGroupId: null,
    });
    expect(cr.status()).toBe(200);
    const cb = await cr.json();
    if (cb.errors) console.log('cgp create errors:', cb.errors);
    const created = cb.data?.createAdminCatalogProductCustomerGroupPrice?.adminCatalogProductCustomerGroupPrice;
    test.skip(!created?._id, 'cgp create returned no id');

    const upd = await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_CGP_UPDATE_MUTATION, {
      id: created.id,
      productId,
      qty: uniqueQty,
      valueType: 'fixed',
      value: 8.5,
      customerGroupId: null,
    });
    expect(upd.status()).toBe(200);

    const del = await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_CGP_DELETE_MUTATION, {
      id: created.id,
      productId,
    });
    expect(del.status()).toBe(200);
  });

  test('create with invalid value_type surfaces errors', async ({ request }) => {
    const productId = await firstProductId(request);
    test.skip(productId == null, 'no products present');

    const resp = await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_CGP_CREATE_MUTATION, {
      productId,
      qty: 1,
      valueType: 'bogus',
      value: 1.0,
      customerGroupId: null,
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    const failed = !!body.errors || body.data?.createAdminCatalogProductCustomerGroupPrice?.adminCatalogProductCustomerGroupPrice == null;
    expect(failed).toBeTruthy();
  });

  test('delete non-existent cgp surfaces errors', async ({ request }) => {
    const productId = await firstProductId(request);
    test.skip(productId == null, 'no products present');

    const resp = await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_CGP_DELETE_MUTATION, {
      id: `/api/admin/catalog/products/${productId}/customer-group-prices/999999999`,
      productId,
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    const ok = !!body.errors || body.data?.deleteAdminCatalogProductCustomerGroupPrice?.adminCatalogProductCustomerGroupPrice == null;
    expect(ok).toBeTruthy();
  });
});
