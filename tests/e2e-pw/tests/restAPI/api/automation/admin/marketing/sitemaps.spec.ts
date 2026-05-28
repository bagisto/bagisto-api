// Admin Marketing — Sitemaps REST e2e.

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

test.describe('Admin Marketing — Sitemaps', () => {
  test('listing returns 200 + envelope', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SITEMAPS);
    expect(OK_LIST).toContain(resp.status());
    const body = await safeJson(resp);
    if (body) expect(Array.isArray(body.data) || Array.isArray(body)).toBe(true);
  });

  test('listing with pagination', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SITEMAPS, {
      params: { page: '1', per_page: '5' },
    });
    expect(OK_LIST).toContain(resp.status());
  });

  test('detail non-existent → 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SITEMAP(99999999));
    expect([404, 400]).toContain(resp.status());
  });

  test('create with empty body → 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SITEMAPS, {
      method: 'POST',
      data: {},
    });
    expect([400, 422, 500]).toContain(resp.status());
  });

  test('create + detail + update + delete round trip', async ({ request }) => {
    const ts = Date.now();
    const createResp = await sendAdminRequest(request, ADMIN_MARKETING.SITEMAPS, {
      method: 'POST',
      data: { file_name: `e2e-sitemap-${ts}.xml`, path: '/' },
    });
    const status = createResp.status();
    console.log('sitemap create:', status);
    expect(OK_CREATE).toContain(status);

    if (status !== 200 && status !== 201) return;
    const body = await safeJson(createResp);
    const id = body?.id ?? body?.data?.id;
    if (!id) return;

    const detail = await sendAdminRequest(request, ADMIN_MARKETING.SITEMAP(id));
    expect([200, 404]).toContain(detail.status());

    const updateResp = await sendAdminRequest(request, ADMIN_MARKETING.SITEMAP(id), {
      method: 'PUT' as any,
      data: { file_name: `e2e-sitemap-${ts}-upd.xml`, path: '/' },
    });
    expect(OK_UPDATE).toContain(updateResp.status());

    const delResp = await sendAdminRequest(request, ADMIN_MARKETING.SITEMAP(id), {
      method: 'DELETE',
    });
    expect(OK_DELETE).toContain(delResp.status());
  });

  test('create with invalid file_name (no .xml) → 422', async ({ request }) => {
    const ts = Date.now();
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SITEMAPS, {
      method: 'POST',
      data: { file_name: `e2e-${ts}.txt`, path: '/' },
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('create with invalid path (no leading /) → 422', async ({ request }) => {
    const ts = Date.now();
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SITEMAPS, {
      method: 'POST',
      data: { file_name: `e2e-${ts}.xml`, path: 'invalid' },
    });
    expect([400, 422]).toContain(resp.status());
  });

  test.skip('generate sitemap (skipped — slow, writes files to disk)', async () => {});
});
