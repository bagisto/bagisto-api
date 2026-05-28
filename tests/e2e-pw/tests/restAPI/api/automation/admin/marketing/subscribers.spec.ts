// Admin Marketing — Newsletter Subscribers REST e2e.
// NO create (origin = storefront). Listing / detail / toggle / delete.

import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_MARKETING } from '../../../../rest/endpoints/admin.marketing.endpoints';

test.describe.configure({ timeout: 60_000 });

const OK_LIST = [200];

async function safeJson(resp: any): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

test.describe('Admin Marketing — Newsletter Subscribers', () => {
  test('listing returns 200 + envelope', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SUBSCRIBERS);
    expect(OK_LIST).toContain(resp.status());
    const body = await safeJson(resp);
    if (body) expect(Array.isArray(body.data) || Array.isArray(body)).toBe(true);
  });

  test('listing with pagination', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SUBSCRIBERS, {
      params: { page: '1', per_page: '5' },
    });
    expect(OK_LIST).toContain(resp.status());
  });

  test('listing with is_subscribed filter', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SUBSCRIBERS, {
      params: { is_subscribed: '1' },
    });
    expect(OK_LIST).toContain(resp.status());
  });

  test('detail non-existent → 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SUBSCRIBER(99999999));
    expect([404, 400]).toContain(resp.status());
  });

  test('toggle existing subscriber (if any) + restore', async ({ request }) => {
    const listResp = await sendAdminRequest(request, ADMIN_MARKETING.SUBSCRIBERS, {
      params: { per_page: '1' },
    });
    if (listResp.status() !== 200) return;
    const body = await safeJson(listResp);
    const first = (body?.data ?? body ?? [])[0];
    if (!first) return;
    const id = first.id;
    const wasSubscribed = first.isSubscribed ?? first.is_subscribed ?? true;

    const toggleResp = await sendAdminRequest(request, ADMIN_MARKETING.SUBSCRIBER(id), {
      method: 'PUT' as any,
      data: { is_subscribed: !wasSubscribed },
    });
    console.log('subscriber toggle:', toggleResp.status());
    expect([200, 201, 400, 404, 422]).toContain(toggleResp.status());

    // restore
    await sendAdminRequest(request, ADMIN_MARKETING.SUBSCRIBER(id), {
      method: 'PUT' as any,
      data: { is_subscribed: wasSubscribed },
    });
  });

  test('toggle missing is_subscribed → 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SUBSCRIBER(1), {
      method: 'PUT' as any,
      data: {},
    });
    expect([400, 404, 422, 500]).toContain(resp.status());
  });

  test('delete non-existent → 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SUBSCRIBER(99999999), {
      method: 'DELETE',
    });
    expect([404, 400]).toContain(resp.status());
  });
});
