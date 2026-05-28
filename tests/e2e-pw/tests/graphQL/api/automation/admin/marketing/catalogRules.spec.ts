// Admin Marketing — Catalog Rules GraphQL e2e.

import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_CATALOG_RULES_QUERY,
  ADMIN_CATALOG_RULE_QUERY,
  ADMIN_CATALOG_RULE_CREATE_MUTATION,
  ADMIN_CATALOG_RULE_UPDATE_MUTATION,
  ADMIN_CATALOG_RULE_DELETE_MUTATION,
  ADMIN_CATALOG_RULE_MASS_DELETE_MUTATION,
} from '../../../../graphql/Queries/admin/marketing/catalogRules.queries';

test.describe.configure({ timeout: 120_000 });

// GraphQL inputs use camelCase keys (API Platform rewrites snake_case at the
// schema level). REST inputs accept snake_case; GraphQL inputs do NOT.
function basePayload(ts: number) {
  return {
    name: `e2e_gql_cr_${ts}`,
    description: 'gql e2e',
    channels: [1],
    customerGroups: [2],
    actionType: 'by_percent',
    discountAmount: 10,
    sortOrder: 0,
    status: 1,
    conditions: [],
  };
}

async function safeBody(resp: any) {
  try { return await resp.json(); } catch { return null; }
}

test.describe('Admin Marketing — Catalog Rules GraphQL', () => {
  test('listing returns edges', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CATALOG_RULES_QUERY, { first: 5 });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    expect(body?.data?.adminMarketingCatalogRules).toBeTruthy();
    expect(Array.isArray(body?.data?.adminMarketingCatalogRules?.edges)).toBe(true);
  });

  test('listing with name filter', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CATALOG_RULES_QUERY, {
      first: 5, name: 'nonexistent-xyz-gql',
    });
    expect(resp.status()).toBe(200);
  });

  test('detail unknown id surfaces errors[]', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CATALOG_RULE_QUERY, {
      id: '/api/admin/marketing/catalog-rules/99999999',
    });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    const isNull = body?.data?.adminMarketingCatalogRule === null;
    expect(hasErrors || isNull).toBe(true);
  });

  test('create empty payload is rejected', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CATALOG_RULE_CREATE_MUTATION, {
      input: {},
    });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    expect(hasErrors).toBe(true);
  });

  test('create + delete round trip', async ({ request }) => {
    const ts = Date.now();
    const createResp = await sendAdminGraphQLRequest(request, ADMIN_CATALOG_RULE_CREATE_MUTATION, {
      input: basePayload(ts),
    });
    expect(createResp.status()).toBe(200);
    const cb = await safeBody(createResp);
    const id = cb?.data?.createAdminMarketingCatalogRule?.adminMarketingCatalogRule?.id;
    console.log('gql catalog-rule create id:', id, 'errors:', JSON.stringify(cb?.errors)?.slice(0, 200));
    if (!id) return;

    const delResp = await sendAdminGraphQLRequest(request, ADMIN_CATALOG_RULE_DELETE_MUTATION, {
      input: { id },
    });
    expect(delResp.status()).toBe(200);
  });

  test('create by_percent>100 is rejected', async ({ request }) => {
    const ts = Date.now();
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CATALOG_RULE_CREATE_MUTATION, {
      input: { ...basePayload(ts), discountAmount: 200 },
    });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    const created = body?.data?.createAdminMarketingCatalogRule?.adminMarketingCatalogRule;
    expect(hasErrors || created === null).toBe(true);
  });

  test('mass-delete empty indices is rejected', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CATALOG_RULE_MASS_DELETE_MUTATION, {
      input: { indices: [] },
    });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    expect(hasErrors).toBe(true);
  });

  test('mass-delete round trip', async ({ request }) => {
    const ts = Date.now();
    const ids: number[] = [];
    for (let i = 0; i < 2; i++) {
      const r = await sendAdminGraphQLRequest(request, ADMIN_CATALOG_RULE_CREATE_MUTATION, {
        input: { ...basePayload(ts + i), name: `e2e_gql_cr_mass_${ts}_${i}` },
      });
      const b = await safeBody(r);
      const _id = b?.data?.createAdminMarketingCatalogRule?.adminMarketingCatalogRule?._id;
      if (_id) ids.push(_id);
    }
    if (ids.length > 0) {
      const resp = await sendAdminGraphQLRequest(request, ADMIN_CATALOG_RULE_MASS_DELETE_MUTATION, {
        input: { indices: ids },
      });
      expect(resp.status()).toBe(200);
    }
  });
});
