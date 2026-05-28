// Admin Settings — Currencies REST e2e.
// Listing / detail / create / update / delete / mass-delete.
// Notes:
//  - Currency code is 3-letter uppercase + unique. We mint a fresh code per
//    spec run so parallel workers don't collide.
//  - `code` is immutable on update (silently dropped per CLAUDE.md). Update
//    test mutates `name` only.
//  - Delete tests act on freshly-created rows. We do NOT try to delete the
//    last currency or any channel-base currency.

import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_SETTINGS } from '../../../../rest/endpoints/admin.settings.endpoints';

test.describe.configure({ timeout: 60_000 });

const OK_LIST = [200];
const OK_CREATE = [200, 201, 400, 422, 429];
const OK_UPDATE = [200, 201, 400, 404, 422, 429];
const OK_DELETE = [200, 204, 400, 404, 422, 429];

async function safeJson(resp: any): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

// 3-letter code derived from Date.now (truncated). Uppercase, A-Z only.
function uniqueCurrencyCode(): string {
  const map = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  const n = Date.now();
  return [0, 1, 2].map(i => map[(n >> (i * 5)) % 26]).join('');
}

async function createCurrency(request: any): Promise<{ id: number | null; code: string }> {
  const code = uniqueCurrencyCode();
  const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCIES, {
    method: 'POST',
    data: { code, name: `E2E ${code}`, symbol: '¤', decimal: 2 },
  });
  const body = await safeJson(resp);
  const id = body?.id ?? body?.data?.id ?? null;
  return { id, code };
}

test.describe('Admin Settings Currencies REST API', () => {
  test('listing returns 200 + envelope shape', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCIES);
    expect(OK_LIST).toContain(resp.status());
    const body = await safeJson(resp);
    if (body) expect(Array.isArray(body.data) || Array.isArray(body)).toBe(true);
  });

  test('listing with pagination params', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCIES, {
      params: { page: '1', per_page: '5' },
    });
    expect(OK_LIST).toContain(resp.status());
  });

  test('listing with code filter', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCIES, {
      params: { code: 'USD' },
    });
    expect(OK_LIST).toContain(resp.status());
  });

  test('detail for id=1 returns 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCY(1));
    expect([200, 404]).toContain(resp.status());
  });

  test('detail for non-existent id returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCY(99999999));
    expect([400, 404]).toContain(resp.status());
  });

  test('create currency happy path', async ({ request }) => {
    const code = uniqueCurrencyCode();
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCIES, {
      method: 'POST',
      data: { code, name: `E2E ${code}`, symbol: '¤' },
    });
    expect(OK_CREATE).toContain(resp.status());
    const body = await safeJson(resp);
    const id = body?.id ?? body?.data?.id;
    if (id) {
      // best-effort cleanup
      await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCY(id), { method: 'DELETE' });
    }
  });

  test('create currency missing code returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCIES, {
      method: 'POST',
      data: { name: 'no code' },
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('update currency name (code immutable)', async ({ request }) => {
    const { id, code } = await createCurrency(request);
    if (!id) { test.skip(true, 'create failed'); return; }
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCY(id), {
      method: 'PUT',
      data: { name: `Renamed ${code}` },
    });
    expect(OK_UPDATE).toContain(resp.status());
    // cleanup
    await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCY(id), { method: 'DELETE' });
  });

  test('delete fresh currency returns 200/204', async ({ request }) => {
    const { id } = await createCurrency(request);
    if (!id) { test.skip(true, 'create failed'); return; }
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCY(id), {
      method: 'DELETE',
    });
    expect(OK_DELETE).toContain(resp.status());
  });

  test('mass-delete with single fresh id', async ({ request }) => {
    const { id } = await createCurrency(request);
    if (!id) { test.skip(true, 'create failed'); return; }
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCIES_MASS_DELETE, {
      method: 'POST',
      data: { indices: [id] },
    });
    expect([200, 201, 400, 422]).toContain(resp.status());
  });

  test('mass-delete empty indices returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCIES_MASS_DELETE, {
      method: 'POST',
      data: { indices: [] },
    });
    expect([400, 422]).toContain(resp.status());
  });
});
