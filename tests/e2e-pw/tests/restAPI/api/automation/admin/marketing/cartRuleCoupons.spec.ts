import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_MARKETING } from '../../../../rest/endpoints/admin.marketing.endpoints';

test.describe.configure({ timeout: 120_000 });

const RULE_NOT_FOUND = 'Cart rule not found.';

function uniqueStamp(): string {
  return `${Date.now()}${Math.floor(Math.random() * 1_000_000)}`;
}

async function makeCartRule(request: APIRequestContext, ts: string): Promise<number> {
  const r = await sendAdminRequest(request, ADMIN_MARKETING.CART_RULES, {
    method: 'POST',
    data: {
      name: `E2E CRC ${ts}`,
      description: 'e2e',
      channels: [1],
      customer_groups: [2],
      coupon_type: 1,
      use_auto_generation: 1,
      action_type: 'by_percent',
      discount_amount: 5,
      sort_order: 0,
      status: 1,
      conditions: [],
      uses_per_customer: 0,
      uses_per_coupon: 0,
    },
  });
  expect(r.status()).toBe(201);

  return (await r.json()).id;
}

async function deleteCartRule(request: APIRequestContext, id: number) {
  await sendAdminRequest(request, ADMIN_MARKETING.CART_RULE(id), { method: 'DELETE' });
}

async function listCouponIds(request: APIRequestContext, ruleId: number): Promise<number[]> {
  const resp = await sendAdminRequest(request, ADMIN_MARKETING.CART_RULE_COUPONS(ruleId));
  expect(resp.status()).toBe(200);

  return (await resp.json()).data.map((coupon: any) => coupon.id);
}

test.describe('admin marketing cart rule coupons', () => {
  test('listing for non-existent rule returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.CART_RULE_COUPONS(99999999));
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe(RULE_NOT_FOUND);
  });

  test('listing returns 200 and an empty envelope for an auto-generation rule', async ({ request }) => {
    const ruleId = await makeCartRule(request, uniqueStamp());
    try {
      const resp = await sendAdminRequest(request, ADMIN_MARKETING.CART_RULE_COUPONS(ruleId));
      expect(resp.status()).toBe(200);
      const body = await resp.json();
      expect(body.data).toEqual([]);
      expect(body.meta.total).toBe(0);
    } finally {
      await deleteCartRule(request, ruleId);
    }
  });

  test('create single coupon and delete round trip', async ({ request }) => {
    const ts = uniqueStamp();
    const ruleId = await makeCartRule(request, ts);
    try {
      const code = `E2ECODE${ts}`;
      const createResp = await sendAdminRequest(request, ADMIN_MARKETING.CART_RULE_COUPONS(ruleId), {
        method: 'POST',
        data: { code, usage_limit: 0, usage_per_customer: 0, expired_at: null },
      });
      expect(createResp.status()).toBe(201);
      const created = await createResp.json();
      expect(created).toEqual(expect.objectContaining({
        cartRuleId: ruleId,
        code,
        isPrimary: false,
        success: true,
        message: 'Coupon created successfully.',
      }));
      expect(await listCouponIds(request, ruleId)).toEqual([created.id]);

      const delResp = await sendAdminRequest(
        request,
        ADMIN_MARKETING.CART_RULE_COUPON(ruleId, created.id),
        { method: 'DELETE' }
      );
      expect(delResp.status()).toBe(200);
      expect(await delResp.json()).toEqual(expect.objectContaining({
        id: created.id,
        success: true,
        message: 'Coupon deleted successfully.',
      }));
      expect(await listCouponIds(request, ruleId)).toEqual([]);
    } finally {
      await deleteCartRule(request, ruleId);
    }
  });

  test('bulk-generate coupons', async ({ request }) => {
    const ruleId = await makeCartRule(request, uniqueStamp());
    try {
      const resp = await sendAdminRequest(
        request,
        ADMIN_MARKETING.CART_RULE_COUPONS_GENERATE(ruleId),
        {
          method: 'POST',
          data: {
            length: 10,
            format: 'alphanumeric',
            prefix: 'E2E',
            suffix: '',
            coupon_qty: 3,
          },
        }
      );
      expect(resp.status()).toBe(201);
      const body = await resp.json();
      expect(body).toEqual(expect.objectContaining({
        cartRuleId: ruleId,
        generated: 3,
        success: true,
        message: 'Generated 3 coupon(s) successfully.',
      }));
      expect(body.coupons).toHaveLength(3);
      for (const coupon of body.coupons) {
        expect(coupon.code.startsWith('E2E')).toBe(true);
        expect(coupon.cartRuleId).toBe(ruleId);
      }
      expect((await listCouponIds(request, ruleId)).sort()).toEqual(body.coupons.map((coupon: any) => coupon.id).sort());
    } finally {
      await deleteCartRule(request, ruleId);
    }
  });

  test('mass-delete coupons empty indices returns 422', async ({ request }) => {
    const ruleId = await makeCartRule(request, uniqueStamp());
    try {
      const resp = await sendAdminRequest(
        request,
        ADMIN_MARKETING.CART_RULE_COUPONS_MASS_DELETE(ruleId),
        { method: 'POST', data: { indices: [] } }
      );
      expect(resp.status()).toBe(422);
      expect((await resp.json()).detail).toBe('The indices field is required and must be a non-empty array of coupon IDs.');
    } finally {
      await deleteCartRule(request, ruleId);
    }
  });

  test('create coupon with empty body returns 422', async ({ request }) => {
    const ruleId = await makeCartRule(request, uniqueStamp());
    try {
      const resp = await sendAdminRequest(request, ADMIN_MARKETING.CART_RULE_COUPONS(ruleId), {
        method: 'POST',
        data: {},
      });
      expect(resp.status()).toBe(422);
      expect((await resp.json()).detail).toBe('The code field is required.');
      expect(await listCouponIds(request, ruleId)).toEqual([]);
    } finally {
      await deleteCartRule(request, ruleId);
    }
  });
});
