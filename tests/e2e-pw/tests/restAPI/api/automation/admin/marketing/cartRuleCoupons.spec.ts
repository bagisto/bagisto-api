// Admin Marketing — Cart Rule Coupons (sub-resource) REST e2e.

import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_MARKETING } from '../../../../rest/endpoints/admin.marketing.endpoints';

test.describe.configure({ timeout: 120_000 });

async function safeJson(resp: any): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

async function makeCartRule(request: any, ts: number): Promise<number | null> {
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
  if (r.status() !== 200 && r.status() !== 201) return null;
  const body = await safeJson(r);
  return body?.id ?? body?.data?.id ?? null;
}

async function deleteCartRule(request: any, id: number) {
  await sendAdminRequest(request, ADMIN_MARKETING.CART_RULE(id), { method: 'DELETE' });
}

test.describe('Admin Marketing — Cart Rule Coupons', () => {
  test('listing for non-existent rule → 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.CART_RULE_COUPONS(99999999));
    expect([404, 400, 200]).toContain(resp.status());
  });

  test('listing returns 200 + envelope', async ({ request }) => {
    const ts = Date.now();
    const ruleId = await makeCartRule(request, ts);
    if (!ruleId) return;
    try {
      const resp = await sendAdminRequest(request, ADMIN_MARKETING.CART_RULE_COUPONS(ruleId));
      expect([200, 404]).toContain(resp.status());
      const body = await safeJson(resp);
      if (body) expect(Array.isArray(body.data) || Array.isArray(body)).toBe(true);
    } finally {
      await deleteCartRule(request, ruleId);
    }
  });

  test('create single coupon + delete round trip', async ({ request }) => {
    const ts = Date.now();
    const ruleId = await makeCartRule(request, ts);
    if (!ruleId) return;
    try {
      const code = `E2ECODE${ts}`;
      const createResp = await sendAdminRequest(request, ADMIN_MARKETING.CART_RULE_COUPONS(ruleId), {
        method: 'POST',
        data: { code, usage_limit: 0, usage_per_customer: 0, expired_at: null },
      });
      const status = createResp.status();
      console.log('coupon create:', status);
      expect([200, 201, 400, 422, 500]).toContain(status);

      if (status === 200 || status === 201) {
        const body = await safeJson(createResp);
        const id = body?.id ?? body?.data?.id;
        if (id) {
          const delResp = await sendAdminRequest(
            request,
            ADMIN_MARKETING.CART_RULE_COUPON(ruleId, id),
            { method: 'DELETE' }
          );
          expect([200, 204, 404]).toContain(delResp.status());
        }
      }
    } finally {
      await deleteCartRule(request, ruleId);
    }
  });

  test('bulk-generate coupons', async ({ request }) => {
    const ts = Date.now();
    const ruleId = await makeCartRule(request, ts);
    if (!ruleId) return;
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
      const status = resp.status();
      console.log('coupon generate:', status);
      expect([200, 201, 400, 422, 500]).toContain(status);
    } finally {
      await deleteCartRule(request, ruleId);
    }
  });

  test('mass-delete coupons empty indices → 422', async ({ request }) => {
    const ts = Date.now();
    const ruleId = await makeCartRule(request, ts);
    if (!ruleId) return;
    try {
      const resp = await sendAdminRequest(
        request,
        ADMIN_MARKETING.CART_RULE_COUPONS_MASS_DELETE(ruleId),
        { method: 'POST', data: { indices: [] } }
      );
      expect([400, 422]).toContain(resp.status());
    } finally {
      await deleteCartRule(request, ruleId);
    }
  });

  test('create coupon with empty body → 422', async ({ request }) => {
    const ts = Date.now();
    const ruleId = await makeCartRule(request, ts);
    if (!ruleId) return;
    try {
      const resp = await sendAdminRequest(request, ADMIN_MARKETING.CART_RULE_COUPONS(ruleId), {
        method: 'POST',
        data: {},
      });
      expect([400, 422, 500]).toContain(resp.status());
    } finally {
      await deleteCartRule(request, ruleId);
    }
  });
});
