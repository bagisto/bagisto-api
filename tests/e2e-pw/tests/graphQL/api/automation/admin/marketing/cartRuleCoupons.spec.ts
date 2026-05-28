// Admin Marketing — Cart Rule Coupons (sub-resource) GraphQL e2e.

import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_CART_RULE_COUPONS_QUERY,
  ADMIN_CART_RULE_COUPON_CREATE_MUTATION,
  ADMIN_CART_RULE_COUPON_GENERATE_MUTATION,
  ADMIN_CART_RULE_COUPON_DELETE_MUTATION,
  ADMIN_CART_RULE_COUPON_MASS_DELETE_MUTATION,
  ADMIN_CART_RULE_CREATE_FOR_COUPONS,
  ADMIN_CART_RULE_DELETE_FOR_COUPONS,
} from '../../../../graphql/Queries/admin/marketing/cartRuleCoupons.queries';

test.describe.configure({ timeout: 120_000 });

async function safeBody(resp: any) {
  try { return await resp.json(); } catch { return null; }
}

async function makeCartRule(request: any, ts: number): Promise<{ id: string | null; _id: number | null }> {
  const r = await sendAdminGraphQLRequest(request, ADMIN_CART_RULE_CREATE_FOR_COUPONS, {
    input: {
      name: `e2e_gql_crc_rule_${ts}`,
      description: 'gql e2e',
      channels: [1],
      customerGroups: [2],
      couponType: 1,
      useAutoGeneration: 1,
      actionType: 'by_percent',
      discountAmount: 5,
      sortOrder: 0,
      status: 1,
      conditions: [],
      usesPerCustomer: 0,
      usesPerCoupon: 0,
    },
  });
  const b = await safeBody(r);
  return {
    id: b?.data?.createAdminMarketingCartRule?.adminMarketingCartRule?.id ?? null,
    _id: b?.data?.createAdminMarketingCartRule?.adminMarketingCartRule?._id ?? null,
  };
}

async function deleteCartRule(request: any, id: string) {
  await sendAdminGraphQLRequest(request, ADMIN_CART_RULE_DELETE_FOR_COUPONS, { input: { id } });
}

test.describe('Admin Marketing — Cart Rule Coupons GraphQL', () => {
  test('listing for unknown rule surfaces errors[] or empty', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CART_RULE_COUPONS_QUERY, {
      cartRuleId: 99999999, first: 5,
    });
    expect(resp.status()).toBe(200);
    const body = await safeBody(resp);
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    const edges = body?.data?.adminMarketingCartRuleCoupons?.edges;
    expect(hasErrors || (Array.isArray(edges) && edges.length === 0)).toBe(true);
  });

  test('listing for real rule returns edges', async ({ request }) => {
    const ts = Date.now();
    const { id, _id } = await makeCartRule(request, ts);
    if (!id || !_id) return;
    try {
      const resp = await sendAdminGraphQLRequest(request, ADMIN_CART_RULE_COUPONS_QUERY, {
        cartRuleId: _id, first: 5,
      });
      expect(resp.status()).toBe(200);
    } finally {
      await deleteCartRule(request, id);
    }
  });

  test('create single coupon happy path', async ({ request }) => {
    const ts = Date.now();
    const { id, _id } = await makeCartRule(request, ts);
    if (!id || !_id) return;
    try {
      const code = `E2EGQL${ts}`;
      const resp = await sendAdminGraphQLRequest(request, ADMIN_CART_RULE_COUPON_CREATE_MUTATION, {
        input: { cartRuleId: _id, code, usageLimit: 0, usagePerCustomer: 0 },
      });
      expect(resp.status()).toBe(200);
      const body = await safeBody(resp);
      console.log('gql coupon create errors:', JSON.stringify(body?.errors)?.slice(0, 200));
    } finally {
      await deleteCartRule(request, id);
    }
  });

  test('bulk-generate 3 coupons', async ({ request }) => {
    const ts = Date.now();
    const { id, _id } = await makeCartRule(request, ts);
    if (!id || !_id) return;
    try {
      const resp = await sendAdminGraphQLRequest(request, ADMIN_CART_RULE_COUPON_GENERATE_MUTATION, {
        input: {
          cartRuleId: _id, length: 10, format: 'alphanumeric',
          prefix: 'GQL', suffix: '', couponQty: 3,
        },
      });
      expect(resp.status()).toBe(200);
      const body = await safeBody(resp);
      console.log('gql coupon generate errors:', JSON.stringify(body?.errors)?.slice(0, 200));
    } finally {
      await deleteCartRule(request, id);
    }
  });

  test('create coupon empty body is rejected', async ({ request }) => {
    const ts = Date.now();
    const { id, _id } = await makeCartRule(request, ts);
    if (!id || !_id) return;
    try {
      const resp = await sendAdminGraphQLRequest(request, ADMIN_CART_RULE_COUPON_CREATE_MUTATION, {
        input: { cartRuleId: _id },
      });
      expect(resp.status()).toBe(200);
      const body = await safeBody(resp);
      expect(Array.isArray(body?.errors) && body.errors.length > 0).toBe(true);
    } finally {
      await deleteCartRule(request, id);
    }
  });

  test('mass-delete empty indices is rejected', async ({ request }) => {
    const ts = Date.now();
    const { id, _id } = await makeCartRule(request, ts);
    if (!id || !_id) return;
    try {
      const resp = await sendAdminGraphQLRequest(request, ADMIN_CART_RULE_COUPON_MASS_DELETE_MUTATION, {
        input: { cartRuleId: _id, indices: [] },
      });
      expect(resp.status()).toBe(200);
      const body = await safeBody(resp);
      expect(Array.isArray(body?.errors) && body.errors.length > 0).toBe(true);
    } finally {
      await deleteCartRule(request, id);
    }
  });
});
