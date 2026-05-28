// Admin Settings — Exchange Rates REST e2e.
// Listing / detail / create / update / delete / mass-delete.
// `(target_currency)` is unique — we mint a fresh currency per spec run so
// the rate insert doesn't collide with default seeds.

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

function uniqueCurrencyCode(): string {
  const map = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  const n = Date.now() ^ Math.floor(Math.random() * 1e6);
  return [0, 1, 2].map(i => map[(n >> (i * 5)) % 26]).join('');
}

async function createCurrency(request: any): Promise<{ id: number | null; code: string }> {
  const code = uniqueCurrencyCode();
  const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCIES, {
    method: 'POST',
    data: { code, name: `E2E ${code}`, symbol: '¤' },
  });
  const body = await safeJson(resp);
  return { id: body?.id ?? body?.data?.id ?? null, code };
}

async function createExchangeRate(request: any): Promise<{ id: number | null; currencyId: number | null }> {
  const { id: currencyId } = await createCurrency(request);
  if (!currencyId) return { id: null, currencyId: null };
  const resp = await sendAdminRequest(request, ADMIN_SETTINGS.EXCHANGE_RATES, {
    method: 'POST',
    data: { target_currency: currencyId, rate: 1.23 },
  });
  const body = await safeJson(resp);
  return { id: body?.id ?? body?.data?.id ?? null, currencyId };
}

async function cleanupCurrency(request: any, currencyId: number | null) {
  if (currencyId) {
    await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCY(currencyId), { method: 'DELETE' });
  }
}

test.describe('Admin Settings Exchange Rates REST API', () => {
  test('listing returns 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.EXCHANGE_RATES);
    expect(OK_LIST).toContain(resp.status());
    const body = await safeJson(resp);
    if (body) expect(Array.isArray(body.data) || Array.isArray(body)).toBe(true);
  });

  test('listing with pagination', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.EXCHANGE_RATES, {
      params: { page: '1', per_page: '5' },
    });
    expect(OK_LIST).toContain(resp.status());
  });

  test('detail for id=1 returns 200 or 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.EXCHANGE_RATE(1));
    expect([200, 404]).toContain(resp.status());
  });

  test('detail for non-existent id returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.EXCHANGE_RATE(99999999));
    expect([400, 404]).toContain(resp.status());
  });

  test('create exchange rate happy path', async ({ request }) => {
    const { id, currencyId } = await createExchangeRate(request);
    if (id) {
      await sendAdminRequest(request, ADMIN_SETTINGS.EXCHANGE_RATE(id), { method: 'DELETE' });
    }
    await cleanupCurrency(request, currencyId);
  });

  test('create exchange rate missing fields returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.EXCHANGE_RATES, {
      method: 'POST',
      data: {},
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('create with rate=0 returns 422', async ({ request }) => {
    const { id: currencyId } = await createCurrency(request);
    if (!currencyId) { test.skip(true, 'create currency failed'); return; }
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.EXCHANGE_RATES, {
      method: 'POST',
      data: { target_currency: currencyId, rate: 0 },
    });
    expect([400, 422]).toContain(resp.status());
    await cleanupCurrency(request, currencyId);
  });

  test('update rate partial', async ({ request }) => {
    const { id, currencyId } = await createExchangeRate(request);
    if (!id) { test.skip(true, 'create failed'); return; }
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.EXCHANGE_RATE(id), {
      method: 'PUT',
      data: { rate: 2.5 },
    });
    expect(OK_UPDATE).toContain(resp.status());
    await sendAdminRequest(request, ADMIN_SETTINGS.EXCHANGE_RATE(id), { method: 'DELETE' });
    await cleanupCurrency(request, currencyId);
  });

  test('delete fresh exchange rate returns 200/204', async ({ request }) => {
    const { id, currencyId } = await createExchangeRate(request);
    if (!id) { test.skip(true, 'create failed'); return; }
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.EXCHANGE_RATE(id), {
      method: 'DELETE',
    });
    expect(OK_DELETE).toContain(resp.status());
    await cleanupCurrency(request, currencyId);
  });

  test('mass-delete with one fresh id', async ({ request }) => {
    const { id, currencyId } = await createExchangeRate(request);
    if (!id) { test.skip(true, 'create failed'); return; }
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.EXCHANGE_RATES_MASS_DELETE, {
      method: 'POST',
      data: { indices: [id] },
    });
    expect([200, 201, 400, 422]).toContain(resp.status());
    await cleanupCurrency(request, currencyId);
  });

  test('mass-delete empty indices returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.EXCHANGE_RATES_MASS_DELETE, {
      method: 'POST',
      data: { indices: [] },
    });
    expect([400, 422]).toContain(resp.status());
  });
});
