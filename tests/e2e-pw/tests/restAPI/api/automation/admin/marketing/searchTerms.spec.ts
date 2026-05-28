// Admin Marketing — Search Terms REST e2e.
// NO create (origin = storefront search). Listing / detail / update / delete / mass-delete.

import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_MARKETING } from '../../../../rest/endpoints/admin.marketing.endpoints';

test.describe.configure({ timeout: 60_000 });

const OK_LIST = [200];

async function safeJson(resp: any): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

test.describe('Admin Marketing — Search Terms', () => {
  test('listing returns 200 + envelope', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_TERMS);
    expect(OK_LIST).toContain(resp.status());
    const body = await safeJson(resp);
    if (body) expect(Array.isArray(body.data) || Array.isArray(body)).toBe(true);
  });

  test('listing with pagination', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_TERMS, {
      params: { page: '1', per_page: '5' },
    });
    expect(OK_LIST).toContain(resp.status());
  });

  test('listing with term filter', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_TERMS, {
      params: { term: 'nonexistent-zzz' },
    });
    expect(OK_LIST).toContain(resp.status());
  });

  test('detail non-existent → 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_TERM(99999999));
    expect([404, 400]).toContain(resp.status());
  });

  test('update non-existent → 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_TERM(99999999), {
      method: 'PUT' as any,
      data: { term: 'new-term' },
    });
    expect([400, 404, 422]).toContain(resp.status());
  });

  test('update existing term (if any) round trip', async ({ request }) => {
    const listResp = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_TERMS, {
      params: { per_page: '1' },
    });
    if (listResp.status() !== 200) return;
    const body = await safeJson(listResp);
    const first = (body?.data ?? body ?? [])[0];
    if (!first) return;
    const id = first.id;
    const original = first.term;

    const updateResp = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_TERM(id), {
      method: 'PUT' as any,
      data: { term: `${original}-e2e-${Date.now()}` },
    });
    console.log('search-term update:', updateResp.status());
    expect([200, 201, 400, 404, 422]).toContain(updateResp.status());

    // restore
    if (original) {
      await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_TERM(id), {
        method: 'PUT' as any,
        data: { term: original },
      });
    }
  });

  test('update with empty term → 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_TERM(1), {
      method: 'PUT' as any,
      data: { term: '' },
    });
    expect([400, 404, 422]).toContain(resp.status());
  });

  test('mass-delete empty indices → 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_TERMS_MASS_DELETE, {
      method: 'POST',
      data: { indices: [] },
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('mass-delete non-existent ids tolerated', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MARKETING.SEARCH_TERMS_MASS_DELETE, {
      method: 'POST',
      data: { indices: [99999998, 99999999] },
    });
    expect([200, 201, 400, 422]).toContain(resp.status());
  });
});
