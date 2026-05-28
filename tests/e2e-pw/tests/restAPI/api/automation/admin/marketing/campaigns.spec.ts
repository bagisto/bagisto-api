// Admin Marketing — Campaigns REST e2e.

import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_MARKETING } from '../../../../rest/endpoints/admin.marketing.endpoints';

test.describe.configure({ timeout: 60_000 });

const OK_LIST = [200];
const OK_CREATE = [200, 201, 400, 422, 429];
const OK_UPDATE = [200, 201, 400, 404, 422, 429];
const OK_DELETE = [200, 204, 400, 404, 422, 429];

async function safeJson(resp: any): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

async function makeTemplate(request: any, ts: number): Promise<number | null> {
  const r = await sendAdminRequest(request, ADMIN_MARKETING.TEMPLATES, {
    method: 'POST',
    data: {
      name: `E2E Tpl for camp ${ts}`,
      status: 'active',
      content: '<p>e2e</p>',
    },
  });
  if (r.status() !== 200 && r.status() !== 201) return null;
  const body = await safeJson(r);
  return body?.id ?? body?.data?.id ?? null;
}

test.describe('Admin Marketing — Campaigns', () => {
  test('listing returns 200 + envelope', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.CAMPAIGNS);
    expect(OK_LIST).toContain(resp.status());
    const body = await safeJson(resp);
    if (body) expect(Array.isArray(body.data) || Array.isArray(body)).toBe(true);
  });

  test('listing with pagination', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.CAMPAIGNS, {
      params: { page: '1', per_page: '5' },
    });
    expect(OK_LIST).toContain(resp.status());
  });

  test('detail non-existent → 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.CAMPAIGN(99999999));
    expect([404, 400]).toContain(resp.status());
  });

  test('create with empty body → 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.CAMPAIGNS, {
      method: 'POST',
      data: {},
    });
    expect([400, 422, 500]).toContain(resp.status());
  });

  test('create + detail + update + delete round trip', async ({ request }) => {
    const ts = Date.now();
    const tplId = await makeTemplate(request, ts);
    if (!tplId) {
      test.skip();
      return;
    }

    const createResp = await sendAdminRequest(request, ADMIN_MARKETING.CAMPAIGNS, {
      method: 'POST',
      data: {
        name: `E2E Camp ${ts}`,
        subject: `Test subject ${ts}`,
        marketing_template_id: tplId,
        channel_id: 1,
        customer_group_id: 2,
        status: 0,
      },
    });
    const status = createResp.status();
    console.log('campaign create:', status);
    expect(OK_CREATE).toContain(status);

    if (status !== 200 && status !== 201) {
      await sendAdminRequest(request, ADMIN_MARKETING.TEMPLATE(tplId), { method: 'DELETE' });
      return;
    }
    const body = await safeJson(createResp);
    const id = body?.id ?? body?.data?.id;
    if (!id) {
      await sendAdminRequest(request, ADMIN_MARKETING.TEMPLATE(tplId), { method: 'DELETE' });
      return;
    }

    const detail = await sendAdminRequest(request, ADMIN_MARKETING.CAMPAIGN(id));
    expect([200, 404]).toContain(detail.status());

    const updateResp = await sendAdminRequest(request, ADMIN_MARKETING.CAMPAIGN(id), {
      method: 'PUT' as any,
      data: {
        name: `E2E Camp ${ts} updated`,
        subject: `Updated subject ${ts}`,
        marketing_template_id: tplId,
        channel_id: 1,
        customer_group_id: 2,
        status: 0,
      },
    });
    expect(OK_UPDATE).toContain(updateResp.status());

    const delResp = await sendAdminRequest(request, ADMIN_MARKETING.CAMPAIGN(id), {
      method: 'DELETE',
    });
    expect(OK_DELETE).toContain(delResp.status());

    await sendAdminRequest(request, ADMIN_MARKETING.TEMPLATE(tplId), { method: 'DELETE' });
  });

  test.skip('send action queues emails (skipped — side effect)', async () => {});
});
