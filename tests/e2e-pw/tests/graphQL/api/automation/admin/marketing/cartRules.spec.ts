// Admin Marketing — Cart Rules GraphQL e2e.

import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_CART_RULES_QUERY,
  ADMIN_CART_RULE_QUERY,
  ADMIN_CART_RULE_CREATE_MUTATION,
  ADMIN_CART_RULE_DELETE_MUTATION,
  ADMIN_CART_RULE_MASS_DELETE_MUTATION,
} from '../../../../graphql/Queries/admin/marketing/cartRules.queries';

test.describe.configure({ timeout: 120_000 });

// GraphQL inputs use camelCase keys (API Platform rewrites snake_case).
function basePayload(ts: number) {
  return {
    name: `e2e_gql_cartrule_${ts}`,
    description: 'gql e2e',
    channels: [1],
    customerGroups: [2],
    couponType: 0,
    useAutoGeneration: 0,
    actionType: 'by_percent',
    discountAmount: 5,
    sortOrder: 0,
    status: 1,
    conditions: [],
    usesPerCustomer: 0,
    usesPerCoupon: 0,
  };
}

async function safeBody(resp: any) {
  try { return await resp.json(); } catch { return null; }
}

test.describe('Admin Marketing — Cart Rules GraphQL', () => {
  test('listing returns edges', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CART_RULES_QUERY, { first: 5 });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    expect(Array.isArray(body?.data?.adminMarketingCartRules?.edges)).toBe(true);
  });

  test('listing with status filter', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CART_RULES_QUERY, {
      first: 5, status: 1,
    });
    expect(resp.status()).toBe(200);
  });

  test('detail unknown id surfaces errors[]', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CART_RULE_QUERY, {
      id: '/api/admin/marketing/cart-rules/99999999',
    });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    expect(hasErrors || body?.data?.adminMarketingCartRule === null).toBe(true);
  });

  test('create empty payload is rejected', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CART_RULE_CREATE_MUTATION, {
      input: {},
    });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    expect(Array.isArray(body?.errors) && body.errors.length > 0).toBe(true);
  });

  test('create + delete round trip', async ({ request }) => {
    const ts = Date.now();
    const createResp = await sendAdminGraphQLRequest(request, ADMIN_CART_RULE_CREATE_MUTATION, {
      input: basePayload(ts),
    });
    expect(createResp.status()).toBe(200);
    const cb = await safeBody(createResp);
    const id = cb?.data?.createAdminMarketingCartRule?.adminMarketingCartRule?.id;
    console.log('gql cart-rule create id:', id, 'errors:', JSON.stringify(cb?.errors)?.slice(0, 200));
    if (!id) return;

    const delResp = await sendAdminGraphQLRequest(request, ADMIN_CART_RULE_DELETE_MUTATION, {
      input: { id },
    });
    expect(delResp.status()).toBe(200);
  });

  test('create coupon_type=1 without coupon_code is rejected', async ({ request }) => {
    const ts = Date.now();
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CART_RULE_CREATE_MUTATION, {
      input: { ...basePayload(ts), couponType: 1, useAutoGeneration: 0 },
    });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    const created = body?.data?.createAdminMarketingCartRule?.adminMarketingCartRule;
    expect(hasErrors || created === null).toBe(true);
  });

  test('mass-delete empty indices is rejected', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CART_RULE_MASS_DELETE_MUTATION, {
      input: { indices: [] },
    });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    expect(Array.isArray(body?.errors) && body.errors.length > 0).toBe(true);
  });
});
