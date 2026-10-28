// Admin Appearance — Themes GraphQL e2e. Read-only apart from re-activating the
// theme the channel already runs, which is a no-op for the storefront.

import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_APPEARANCE_THEMES_LIST,
  ADMIN_APPEARANCE_THEME_DETAIL,
  ADMIN_APPEARANCE_THEME_IMPACT,
  ADMIN_APPEARANCE_THEME_ACTIVATE,
} from '../../../../graphql/Queries/admin/appearance/themes.queries';

test.describe.configure({ timeout: 60_000 });

async function safeJson(r: any) { try { return await r.json(); } catch { return null; } }

async function activeThemeCode(request: any): Promise<string | null> {
  const resp = await sendAdminGraphQLRequest(request, ADMIN_APPEARANCE_THEMES_LIST, {});
  const body = await safeJson(resp);
  const themes = body?.data?.adminAppearanceThemes ?? [];
  const active = themes.find((t: any) => t.status === 'active') ?? themes[0];

  return active?.code ?? null;
}

test.describe('Admin Appearance Themes GraphQL API', () => {
  test('listing returns the theme gallery', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_APPEARANCE_THEMES_LIST, {});
    expect(resp.status()).toBe(200);

    const body = await safeJson(resp);
    expect(body?.errors).toBeUndefined();

    const themes = body?.data?.adminAppearanceThemes;
    expect(Array.isArray(themes)).toBe(true);
    expect(themes.length).toBeGreaterThan(0);
    expect(typeof themes[0].code).toBe('string');
    expect(['active', 'installed', 'available']).toContain(themes[0].status);
  });

  test('detail resolves the active theme by code', async ({ request }) => {
    const code = await activeThemeCode(request);
    if (!code) { test.skip(true, 'no theme in the gallery'); return; }

    const resp = await sendAdminGraphQLRequest(request, ADMIN_APPEARANCE_THEME_DETAIL, { code });
    expect(resp.status()).toBe(200);

    const body = await safeJson(resp);
    expect(body?.errors).toBeUndefined();
    expect(body?.data?.adminAppearanceTheme?.code).toBe(code);
  });

  test('detail of an unknown code errors', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_APPEARANCE_THEME_DETAIL, { code: 'no_such_theme_e2e' });
    expect(resp.status()).toBe(200);

    const body = await safeJson(resp);
    expect(body?.errors).toBeDefined();
  });

  test('impact reports what activation would change', async ({ request }) => {
    const code = await activeThemeCode(request);
    if (!code) { test.skip(true, 'no theme in the gallery'); return; }

    const resp = await sendAdminGraphQLRequest(request, ADMIN_APPEARANCE_THEME_IMPACT, { code, channelIds: [1] });
    expect(resp.status()).toBe(200);

    const body = await safeJson(resp);
    expect(body?.errors).toBeUndefined();
    expect(Array.isArray(body?.data?.adminAppearanceThemeImpact?.impact)).toBe(true);
  });

  test('impact rejects an unknown channel', async ({ request }) => {
    const code = await activeThemeCode(request);
    if (!code) { test.skip(true, 'no theme in the gallery'); return; }

    const resp = await sendAdminGraphQLRequest(request, ADMIN_APPEARANCE_THEME_IMPACT, { code, channelIds: [99999999] });
    expect(resp.status()).toBe(200);

    const body = await safeJson(resp);
    expect(body?.errors).toBeDefined();
  });

  test('activating the theme the channel already runs is accepted', async ({ request }) => {
    const code = await activeThemeCode(request);
    if (!code) { test.skip(true, 'no theme in the gallery'); return; }

    const resp = await sendAdminGraphQLRequest(request, ADMIN_APPEARANCE_THEME_ACTIVATE, { code, channelIds: [1] });
    expect(resp.status()).toBe(200);

    const body = await safeJson(resp);
    expect(body?.errors).toBeUndefined();
    expect(body?.data?.createAdminAppearanceThemeActivate?.adminAppearanceThemeActivate?.code).toBe(code);
  });
});
