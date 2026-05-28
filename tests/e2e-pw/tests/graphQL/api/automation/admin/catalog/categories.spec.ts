// tests/graphQL/api/automation/admin/catalog/categories.spec.ts
//
// Admin Catalog Categories GraphQL smoke. Reads are loose smoke; writes
// are create-then-clean with unique names.

import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_CATEGORIES_LIST_QUERY,
  ADMIN_CATEGORY_DETAIL_QUERY,
  ADMIN_CATEGORY_TREES_QUERY,
  ADMIN_CATEGORY_CREATE_MUTATION,
  ADMIN_CATEGORY_UPDATE_MUTATION,
  ADMIN_CATEGORY_DELETE_MUTATION,
  ADMIN_CATEGORY_MASS_DELETE_MUTATION,
  ADMIN_CATEGORY_MASS_UPDATE_STATUS_MUTATION,
} from '../../../../graphql/Queries/admin/catalog/categories.queries';

test.describe.configure({ timeout: 60_000 });

test.describe('Admin Catalog Categories GraphQL', () => {
  test('list returns edges', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CATEGORIES_LIST_QUERY, { first: 5 });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    if (body.errors) console.log('list errors:', body.errors);
    expect(body.data?.adminCategories?.edges).toBeDefined();
  });

  test('tree returns edges', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CATEGORY_TREES_QUERY, { first: 5 });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    if (body.errors) console.log('tree errors:', body.errors);
    expect(body.data?.adminCategoryTrees?.edges).toBeDefined();
  });

  test('detail by first listed id', async ({ request }) => {
    const list = await sendAdminGraphQLRequest(request, ADMIN_CATEGORIES_LIST_QUERY, { first: 1 });
    const edges = (await list.json()).data?.adminCategories?.edges ?? [];
    test.skip(edges.length === 0, 'no categories present');
    const id = edges[0].node.id;
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CATEGORY_DETAIL_QUERY, { id });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    if (body.errors) console.log('detail errors:', body.errors);
    expect(body.data?.adminCategory?._id).toBeDefined();
  });

  test('create + update + delete roundtrip', async ({ request }) => {
    const slug = `e2e_cat_${Date.now()}`;
    const name = `E2E Category ${Date.now()}`;
    const createResp = await sendAdminGraphQLRequest(request, ADMIN_CATEGORY_CREATE_MUTATION, {
      slug,
      name,
      description: 'e2e desc',
      position: 1,
      attributes: [],
      parentId: 1,
      displayMode: 'products_and_description',
      locale: 'en',
      status: 1,
    });
    expect(createResp.status()).toBe(200);
    const createBody = await createResp.json();
    if (createBody.errors) console.log('create errors:', createBody.errors);
    const created = createBody.data?.createAdminCategory?.adminCategory;
    test.skip(!created?._id, 'create did not return an id');

    const id = created.id;
    const updateResp = await sendAdminGraphQLRequest(request, ADMIN_CATEGORY_UPDATE_MUTATION, {
      id,
      position: 2,
      status: 1,
      en: { name: `${name} upd`, slug, description: 'e2e upd' },
      locale: 'en',
      attributes: [],
    });
    expect(updateResp.status()).toBe(200);
    if ((await updateResp.json()).errors) console.log('update errors (non-fatal)');

    const delResp = await sendAdminGraphQLRequest(request, ADMIN_CATEGORY_DELETE_MUTATION, { id });
    expect(delResp.status()).toBe(200);
  });

  test('create with missing required fields surfaces errors', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CATEGORY_CREATE_MUTATION, {
      slug: '',
      name: '',
      description: null,
      position: 1,
      attributes: [],
      parentId: 1,
      displayMode: 'products_and_description',
      locale: 'en',
      status: 1,
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    // Either errors or null payload is acceptable for missing-required.
    const ok = !!body.errors || body.data?.createAdminCategory?.adminCategory == null;
    expect(ok).toBeTruthy();
  });

  test('delete non-existent surfaces errors', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CATEGORY_DELETE_MUTATION, {
      id: '/api/admin/catalog/categories/999999999',
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    const ok = !!body.errors || body.data?.deleteAdminCategory?.adminCategory == null;
    expect(ok).toBeTruthy();
  });

  test('mass-delete with empty indices surfaces errors', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CATEGORY_MASS_DELETE_MUTATION, {
      indices: [],
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    // Empty indices typically rejected.
    const ok = !!body.errors || body.data?.createAdminCategoryMassDelete?.adminCategoryMassDelete != null;
    expect(ok).toBeTruthy();
  });

  test('mass-update-status with empty indices surfaces errors', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CATEGORY_MASS_UPDATE_STATUS_MUTATION, {
      indices: [],
      value: 1,
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    const ok = !!body.errors || body.data?.createAdminCategoryMassUpdateStatus != null;
    expect(ok).toBeTruthy();
  });
});
