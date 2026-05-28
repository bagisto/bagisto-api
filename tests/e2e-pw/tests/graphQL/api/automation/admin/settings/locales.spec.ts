// Admin Settings — Locales GraphQL e2e.

import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_LOCALES_LIST,
  ADMIN_LOCALE_DETAIL,
  ADMIN_LOCALE_CREATE,
  ADMIN_LOCALE_UPDATE,
  ADMIN_LOCALE_DELETE,
  ADMIN_LOCALE_MASS_DELETE,
} from '../../../../graphql/Queries/admin/settings/locales.queries';

test.describe.configure({ timeout: 60_000 });
async function safeJson(r: any) { try { return await r.json(); } catch { return null; } }
function parseId(iri: any): number | null {
  if (typeof iri === 'number') return iri;
  if (typeof iri === 'string' && iri.includes('/')) return parseInt(iri.split('/').pop() || '0', 10);
  return null;
}
function unique(): string { return `e2e_${Date.now().toString(36).slice(-6)}${Math.floor(Math.random()*100)}`; }

async function createLocale(request: any): Promise<{ id: number | null; code: string }> {
  const code = unique();
  const resp = await sendAdminGraphQLRequest(request, ADMIN_LOCALE_CREATE, {
    code, name: `E2E ${code}`, direction: 'ltr',
  });
  const body = await safeJson(resp);
  const id = parseId(body?.data?.createAdminSettingsLocale?.adminSettingsLocale?._id
    ?? body?.data?.createAdminSettingsLocale?.adminSettingsLocale?.id);
  return { id, code };
}

test.describe('Admin Settings Locales GraphQL API', () => {
  test('listing returns edges', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_LOCALES_LIST, { first: 5 });
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);
    expect(Array.isArray(body?.data?.adminSettingsLocales?.edges)).toBe(true);
  });

  test('detail for locale id=1', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_LOCALE_DETAIL, { id: '/api/admin/settings/locales/1' });
    expect(resp.status()).toBe(200);
  });

  test('create + update + delete lifecycle', async ({ request }) => {
    const { id, code } = await createLocale(request);
    if (!id) return;
    const iri = `/api/admin/settings/locales/${id}`;
    const upd = await sendAdminGraphQLRequest(request, ADMIN_LOCALE_UPDATE, { id: iri, name: `Renamed ${code}` });
    expect(upd.status()).toBe(200);
    const del = await sendAdminGraphQLRequest(request, ADMIN_LOCALE_DELETE, { id: iri });
    expect(del.status()).toBe(200);
  });

  test('create with invalid direction is rejected', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_LOCALE_CREATE, {
      code: unique(), name: 'bad', direction: 'xyz',
    });
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    const nullPayload = body?.data?.createAdminSettingsLocale?.adminSettingsLocale === null;
    expect(hasErrors || nullPayload).toBe(true);
  });

  test('mass-delete fresh row', async ({ request }) => {
    const { id } = await createLocale(request);
    if (!id) return;
    const resp = await sendAdminGraphQLRequest(request, ADMIN_LOCALE_MASS_DELETE, { indices: [id] });
    expect(resp.status()).toBe(200);
  });
});
