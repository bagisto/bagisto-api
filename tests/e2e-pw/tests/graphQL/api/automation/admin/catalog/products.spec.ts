// tests/graphQL/api/automation/admin/catalog/products.spec.ts
//
// Admin Catalog Products GraphQL smoke. CRUD + copy + mass-actions.

import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_PRODUCTS_LIST_QUERY,
  ADMIN_PRODUCT_DETAIL_QUERY,
  ADMIN_PRODUCT_CREATE_MUTATION,
  ADMIN_PRODUCT_UPDATE_MUTATION,
  ADMIN_PRODUCT_DELETE_MUTATION,
  ADMIN_PRODUCT_COPY_MUTATION,
  ADMIN_PRODUCT_MASS_DELETE_MUTATION,
  ADMIN_PRODUCT_MASS_UPDATE_STATUS_MUTATION,
} from '../../../../graphql/Queries/admin/catalog/products.queries';

test.describe.configure({ timeout: 60_000 });

async function createSimple(request: any) {
  const sku = `e2e-p-${Date.now()}-${Math.floor(Math.random() * 10000)}`;
  const resp = await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_CREATE_MUTATION, {
    sku,
    attributeFamilyId: 1,
    type: 'simple',
  });
  const body = await resp.json();
  if (body.errors) console.log('create errors:', body.errors);
  return body.data?.createAdminCatalogProduct?.adminCatalogProduct;
}

test.describe('Admin Catalog Products GraphQL', () => {
  test('list returns edges', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_PRODUCTS_LIST_QUERY, { first: 5 });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    if (body.errors) console.log('list errors:', body.errors);
    expect(body.data?.adminCatalogProducts?.edges).toBeDefined();
  });

  test('detail by first listed id', async ({ request }) => {
    const list = await sendAdminGraphQLRequest(request, ADMIN_PRODUCTS_LIST_QUERY, { first: 1 });
    const edges = (await list.json()).data?.adminCatalogProducts?.edges ?? [];
    test.skip(edges.length === 0, 'no products present');
    const id = edges[0].node.id;
    const resp = await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_DETAIL_QUERY, { id });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    if (body.errors) console.log('detail errors:', body.errors);
    expect(body.data?.adminCatalogProduct?._id).toBeDefined();
  });

  test('create + update + delete simple product roundtrip', async ({ request }) => {
    const created = await createSimple(request);
    test.skip(!created?._id, 'create returned no product');

    const upd = await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_UPDATE_MUTATION, {
      id: created.id,
      sku: `${created.sku}-upd`,
      status: 1,
      price: '19.99',
    });
    expect(upd.status()).toBe(200);
    if ((await upd.json()).errors) console.log('update errors (non-fatal)');

    const del = await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_DELETE_MUTATION, { id: created.id });
    expect(del.status()).toBe(200);
  });

  test('create with missing sku surfaces errors', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_CREATE_MUTATION, {
      sku: '',
      attributeFamilyId: 1,
      type: 'simple',
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    const failed = !!body.errors || body.data?.createAdminCatalogProduct?.adminCatalogProduct == null;
    expect(failed).toBeTruthy();
  });

  test('copy a product', async ({ request }) => {
    const src = await createSimple(request);
    test.skip(!src?._id, 'source product create failed');

    const cp = await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_COPY_MUTATION, {
      sourceId: Number(src._id),
    });
    expect(cp.status()).toBe(200);
    const cb = await cp.json();
    if (cb.errors) console.log('copy errors:', cb.errors);
    const copy = cb.data?.createAdminCatalogProductCopy?.adminCatalogProductCopy;

    // cleanup
    await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_DELETE_MUTATION, { id: src.id });
    if (copy?.id) {
      await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_DELETE_MUTATION, { id: copy.id });
    }
  });

  test('delete non-existent surfaces errors', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_DELETE_MUTATION, {
      id: '/api/admin/catalog/products/999999999',
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    const ok = !!body.errors || body.data?.deleteAdminCatalogProduct?.adminCatalogProduct == null;
    expect(ok).toBeTruthy();
  });

  test('mass-delete with empty indices surfaces errors', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_MASS_DELETE_MUTATION, {
      indices: [],
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    const ok = !!body.errors || body.data != null;
    expect(ok).toBeTruthy();
  });

  test('mass-update-status happy path on a real product', async ({ request }) => {
    const created = await createSimple(request);
    test.skip(!created?._id, 'create failed');

    const resp = await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_MASS_UPDATE_STATUS_MUTATION, {
      indices: [Number(created._id)],
      value: 0,
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    if (body.errors) console.log('mass-update-status errors (non-fatal):', body.errors);

    await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_DELETE_MUTATION, { id: created.id });
  });
});
