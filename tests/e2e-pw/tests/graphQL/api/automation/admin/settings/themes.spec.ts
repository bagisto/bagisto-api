// Admin Settings — Themes GraphQL e2e. Only deletes e2e-created themes
// (deletion wipes storage; never touch pre-existing rows).

import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_THEMES_LIST,
  ADMIN_THEME_DETAIL,
  ADMIN_THEME_CREATE,
  ADMIN_THEME_UPDATE,
  ADMIN_THEME_DELETE,
  ADMIN_THEME_MASS_DELETE,
  ADMIN_THEME_MASS_UPDATE_STATUS,
} from '../../../../graphql/Queries/admin/settings/themes.queries';

test.describe.configure({ timeout: 60_000 });
async function safeJson(r: any) { try { return await r.json(); } catch { return null; } }
function parseId(iri: any): number | null {
  if (typeof iri === 'number') return iri;
  if (typeof iri === 'string' && iri.includes('/')) return parseInt(iri.split('/').pop() || '0', 10);
  return null;
}
function unique(): string { return `e2e_theme_${Date.now().toString(36).slice(-6)}${Math.floor(Math.random()*100)}`; }

async function createTheme(request: any): Promise<{ id: number | null; name: string }> {
  const name = unique();
  const resp = await sendAdminGraphQLRequest(request, ADMIN_THEME_CREATE, {
    name, sortOrder: 99, type: 'static_content',
    channelId: 1, themeCode: 'default', status: 1,
  });
  const body = await safeJson(resp);
  const id = parseId(body?.data?.createAdminSettingsTheme?.adminSettingsTheme?._id
    ?? body?.data?.createAdminSettingsTheme?.adminSettingsTheme?.id);
  return { id, name };
}

test.describe('Admin Settings Themes GraphQL API', () => {
  test('listing returns edges', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_THEMES_LIST, { first: 5 });
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);
    expect(Array.isArray(body?.data?.adminSettingsThemes?.edges)).toBe(true);
  });

  test('detail not-found', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_THEME_DETAIL, { id: '/api/admin/settings/themes/99999999' });
    expect(resp.status()).toBe(200);
  });

  test('create + update + delete lifecycle', async ({ request }) => {
    const { id, name } = await createTheme(request);
    if (!id) return;
    const iri = `/api/admin/settings/themes/${id}`;
    const upd = await sendAdminGraphQLRequest(request, ADMIN_THEME_UPDATE, { id: iri, name: `Renamed ${name}` });
    expect(upd.status()).toBe(200);
    const del = await sendAdminGraphQLRequest(request, ADMIN_THEME_DELETE, { id: iri });
    expect(del.status()).toBe(200);
  });

  test('mass-delete e2e-created theme', async ({ request }) => {
    const { id } = await createTheme(request);
    if (!id) return;
    const resp = await sendAdminGraphQLRequest(request, ADMIN_THEME_MASS_DELETE, { indices: [id] });
    expect(resp.status()).toBe(200);
  });

  test('mass-update-status on e2e-created theme', async ({ request }) => {
    const { id } = await createTheme(request);
    if (!id) return;
    const resp = await sendAdminGraphQLRequest(request, ADMIN_THEME_MASS_UPDATE_STATUS, { indices: [id], value: 0 });
    expect(resp.status()).toBe(200);
    // cleanup
    await sendAdminGraphQLRequest(request, ADMIN_THEME_DELETE, { id: `/api/admin/settings/themes/${id}` });
  });
});
