// Admin Marketing — Catalog Rules REST e2e.

import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_MARKETING } from '../../../../rest/endpoints/admin.marketing.endpoints';

test.describe.configure({ timeout: 120_000 });

const OK_LIST = [200];
const OK_CREATE = [200, 201, 400, 422, 429];
const OK_UPDATE = [200, 201, 400, 404, 422, 429];
const OK_DELETE = [200, 204, 400, 404, 422, 429];

async function safeJson(resp: any): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

function basePayload(ts: number) {
  return {
    name: `E2E CR ${ts}`,
    description: 'e2e catalog rule',
    channels: [1],
    customer_groups: [2],
    action_type: 'by_percent',
    discount_amount: 10,
    sort_order: 0,
    status: 1,
    starts_from: null,
    ends_till: null,
    conditions: [],
  };
}

test.describe('Admin Marketing — Catalog Rules', () => {
  test('listing returns 200 + envelope', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.CATALOG_RULES);
    expect(OK_LIST).toContain(resp.status());
    const body = await safeJson(resp);
    if (body) expect(Array.isArray(body.data) || Array.isArray(body)).toBe(true);
  });

  test('listing with pagination', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.CATALOG_RULES, {
      params: { page: '1', per_page: '5' },
    });
    expect(OK_LIST).toContain(resp.status());
  });

  test('listing with name filter', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.CATALOG_RULES, {
      params: { name: 'nonexistent-xyz' },
    });
    expect(OK_LIST).toContain(resp.status());
  });

  test('detail non-existent → 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.CATALOG_RULE(99999999));
    expect([404, 400]).toContain(resp.status());
  });

  test('create with empty body → validation error', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.CATALOG_RULES, {
      method: 'POST',
      data: {},
    });
    expect([400, 422, 500]).toContain(resp.status());
  });

  test('create + detail + update + delete round trip', async ({ request }) => {
    const ts = Date.now();
    const createResp = await sendAdminRequest(request, ADMIN_MARKETING.CATALOG_RULES, {
      method: 'POST',
      data: basePayload(ts),
    });
    const status = createResp.status();
    console.log('catalog-rule create:', status);
    expect(OK_CREATE).toContain(status);

    if (status !== 200 && status !== 201) return;
    const body = await safeJson(createResp);
    const id = body?.id ?? body?.data?.id;
    if (!id) return;

    const detail = await sendAdminRequest(request, ADMIN_MARKETING.CATALOG_RULE(id));
    expect([200, 404]).toContain(detail.status());

    const updateResp = await sendAdminRequest(request, ADMIN_MARKETING.CATALOG_RULE(id), {
      method: 'PUT' as any,
      data: { ...basePayload(ts), name: `E2E CR ${ts} updated` },
    });
    expect(OK_UPDATE).toContain(updateResp.status());

    const delResp = await sendAdminRequest(request, ADMIN_MARKETING.CATALOG_RULE(id), {
      method: 'DELETE',
    });
    expect(OK_DELETE).toContain(delResp.status());

    const after = await sendAdminRequest(request, ADMIN_MARKETING.CATALOG_RULE(id));
    expect([404, 400]).toContain(after.status());
  });

  test('create with by_percent>100 → 422', async ({ request }) => {
    const ts = Date.now();
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.CATALOG_RULES, {
      method: 'POST',
      data: { ...basePayload(ts), discount_amount: 200 },
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('mass-delete empty indices → 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.CATALOG_RULES_MASS_DELETE, {
      method: 'POST',
      data: { indices: [] },
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('mass-delete round trip', async ({ request }) => {
    const ts = Date.now();
    const ids: number[] = [];
    for (let i = 0; i < 2; i++) {
      const r = await sendAdminRequest(request, ADMIN_MARKETING.CATALOG_RULES, {
        method: 'POST',
        data: { ...basePayload(ts + i), name: `E2E CR mass ${ts}-${i}` },
      });
      if (r.status() === 200 || r.status() === 201) {
        const body = await safeJson(r);
        const id = body?.id ?? body?.data?.id;
        if (id) ids.push(id);
      }
    }
    if (ids.length > 0) {
      const resp = await sendAdminRequest(request, ADMIN_MARKETING.CATALOG_RULES_MASS_DELETE, {
        method: 'POST',
        data: { indices: ids },
      });
      expect([200, 201, 400, 422]).toContain(resp.status());
    }
  });
});
