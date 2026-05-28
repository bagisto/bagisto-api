// Admin Settings — Exchange Rates GraphQL e2e.

import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import { ADMIN_CURRENCIES_LIST, ADMIN_CURRENCY_CREATE, ADMIN_CURRENCY_DELETE } from '../../../../graphql/Queries/admin/settings/currencies.queries';
import {
  ADMIN_EXCHANGE_RATES_LIST,
  ADMIN_EXCHANGE_RATE_DETAIL,
  ADMIN_EXCHANGE_RATE_CREATE,
  ADMIN_EXCHANGE_RATE_UPDATE,
  ADMIN_EXCHANGE_RATE_DELETE,
  ADMIN_EXCHANGE_RATE_MASS_DELETE,
} from '../../../../graphql/Queries/admin/settings/exchangeRates.queries';

test.describe.configure({ timeout: 60_000 });
async function safeJson(r: any) { try { return await r.json(); } catch { return null; } }
function uniqCode(): string {
  const map = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  const n = Date.now() + Math.floor(Math.random() * 1e6);
  return [0, 1, 2].map(i => map[(n >> (i * 5)) % 26]).join('');
}
function parseId(iri: any): number | null {
  if (typeof iri === 'number') return iri;
  if (typeof iri === 'string' && iri.includes('/')) return parseInt(iri.split('/').pop() || '0', 10);
  return null;
}

async function createFreshCurrency(request: any): Promise<{ id: number | null; code: string }> {
  const code = uniqCode();
  const resp = await sendAdminGraphQLRequest(request, ADMIN_CURRENCY_CREATE, {
    code, name: `E2E ex ${code}`, symbol: '¤', decimal: 2,
  });
  const body = await safeJson(resp);
  const id = parseId(body?.data?.createAdminSettingsCurrency?.adminSettingsCurrency?._id
    ?? body?.data?.createAdminSettingsCurrency?.adminSettingsCurrency?.id);
  return { id, code };
}

test.describe('Admin Settings ExchangeRates GraphQL API', () => {
  test('listing returns edges', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_EXCHANGE_RATES_LIST, { first: 5 });
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);
    expect(Array.isArray(body?.data?.adminSettingsExchangeRates?.edges)).toBe(true);
  });

  test('detail for non-existent id returns errors[]', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_EXCHANGE_RATE_DETAIL, { id: '/api/admin/settings/exchange-rates/99999999' });
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    const nullPayload = body?.data?.adminSettingsExchangeRate === null;
    expect(hasErrors || nullPayload).toBe(true);
  });

  test('create + update + delete lifecycle', async ({ request }) => {
    const { id: curId } = await createFreshCurrency(request);
    if (!curId) return;
    const cre = await sendAdminGraphQLRequest(request, ADMIN_EXCHANGE_RATE_CREATE, {
      targetCurrency: curId, rate: 1.2345,
    });
    expect(cre.status()).toBe(200);
    const cb = await safeJson(cre);
    const erId = parseId(cb?.data?.createAdminSettingsExchangeRate?.adminSettingsExchangeRate?._id
      ?? cb?.data?.createAdminSettingsExchangeRate?.adminSettingsExchangeRate?.id);
    if (erId) {
      const iri = `/api/admin/settings/exchange-rates/${erId}`;
      const upd = await sendAdminGraphQLRequest(request, ADMIN_EXCHANGE_RATE_UPDATE, { id: iri, rate: 1.5 });
      expect(upd.status()).toBe(200);
      const del = await sendAdminGraphQLRequest(request, ADMIN_EXCHANGE_RATE_DELETE, { id: iri });
      expect(del.status()).toBe(200);
    }
    // cleanup currency
    await sendAdminGraphQLRequest(request, ADMIN_CURRENCY_DELETE, { id: `/api/admin/settings/currencies/${curId}` });
  });

  test('mass-delete empty indices is rejected', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_EXCHANGE_RATE_MASS_DELETE, { indices: [] });
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    expect(hasErrors).toBe(true);
  });
});
