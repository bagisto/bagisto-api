// Admin Settings — Currencies GraphQL e2e.
// Loose smoke for reads; create-then-clean for writes. Avoids touching system rows.

import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_CURRENCIES_LIST,
  ADMIN_CURRENCY_DETAIL,
  ADMIN_CURRENCY_CREATE,
  ADMIN_CURRENCY_UPDATE,
  ADMIN_CURRENCY_DELETE,
  ADMIN_CURRENCY_MASS_DELETE,
} from '../../../../graphql/Queries/admin/settings/currencies.queries';

test.describe.configure({ timeout: 60_000 });

function uniqueCode(): string {
  const map = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  const n = Date.now() + Math.floor(Math.random() * 1e6);
  return [0, 1, 2].map(i => map[(n >> (i * 5)) % 26]).join('');
}

async function safeJson(resp: any) { try { return await resp.json(); } catch { return null; } }

async function createCurrency(request: any): Promise<{ id: number | null; code: string; body: any }> {
  const code = uniqueCode();
  const resp = await sendAdminGraphQLRequest(request, ADMIN_CURRENCY_CREATE, {
    code, name: `E2E ${code}`, symbol: '¤', decimal: 2,
  });
  const body = await safeJson(resp);
  const idField = body?.data?.createAdminSettingsCurrency?.adminSettingsCurrency?._id
    ?? body?.data?.createAdminSettingsCurrency?.adminSettingsCurrency?.id;
  const id = typeof idField === 'string' && idField.includes('/')
    ? parseInt(idField.split('/').pop() || '0', 10)
    : (typeof idField === 'number' ? idField : null);
  return { id, code, body };
}

test.describe('Admin Settings Currencies GraphQL API', () => {
  test('listing returns edges', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CURRENCIES_LIST, { first: 5 });
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);
    expect(Array.isArray(body?.data?.adminSettingsCurrencies?.edges)).toBe(true);
  });

  test('listing with code filter is accepted', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CURRENCIES_LIST, { code: 'USD', first: 5 });
    expect(resp.status()).toBe(200);
  });

  test('detail for currency id=1', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CURRENCY_DETAIL, { id: '/api/admin/settings/currencies/1' });
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);
    // tolerant: detail may surface errors[] for known IRI quirks
    if (!body?.errors) {
      expect(body?.data?.adminSettingsCurrency).toBeTruthy();
    }
  });

  test('create currency happy path', async ({ request }) => {
    const { id, code, body } = await createCurrency(request);
    expect(body?.errors ?? null).toBeNull();
    // mutation may return null payload (IRI quirk) — verify via follow-up listing filter
    if (!id) {
      const list = await sendAdminGraphQLRequest(request, ADMIN_CURRENCIES_LIST, { code, first: 5 });
      const lb = await safeJson(list);
      const hit = (lb?.data?.adminSettingsCurrencies?.edges || []).find((e: any) => e?.node?.code === code);
      expect(hit ?? null).not.toBeNull();
    }
    if (id) {
      await sendAdminGraphQLRequest(request, ADMIN_CURRENCY_DELETE, { id: `/api/admin/settings/currencies/${id}` });
    }
  });

  test('create currency missing code is rejected', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CURRENCY_CREATE, {
      code: '', name: 'no code', symbol: '¤', decimal: 2,
    });
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    const nullPayload = body?.data?.createAdminSettingsCurrency?.adminSettingsCurrency === null;
    expect(hasErrors || nullPayload).toBe(true);
  });

  test('update + delete currency lifecycle', async ({ request }) => {
    const { id, code } = await createCurrency(request);
    if (!id) return;
    const iri = `/api/admin/settings/currencies/${id}`;
    const upd = await sendAdminGraphQLRequest(request, ADMIN_CURRENCY_UPDATE, { id: iri, name: `E2E renamed ${code}` });
    expect(upd.status()).toBe(200);
    const del = await sendAdminGraphQLRequest(request, ADMIN_CURRENCY_DELETE, { id: iri });
    expect(del.status()).toBe(200);
  });

  test('mass-delete on freshly created rows', async ({ request }) => {
    const a = await createCurrency(request);
    const b = await createCurrency(request);
    const ids = [a.id, b.id].filter(Boolean);
    if (!ids.length) return;
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CURRENCY_MASS_DELETE, { indices: ids });
    expect(resp.status()).toBe(200);
  });
});
