// Admin Appearance — Themes REST e2e. Read-only apart from re-activating the theme
// the channel already runs, which leaves the storefront exactly as it was.

import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_APPEARANCE } from '../../../../rest/endpoints/admin.appearance.endpoints';

test.describe.configure({ timeout: 60_000 });

async function safeJson(resp: any): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

function rows(body: any): any[] {
  if (Array.isArray(body)) return body;

  return Array.isArray(body?.data) ? body.data : [];
}

async function activeThemeCode(request: any): Promise<string | null> {
  const resp = await sendAdminRequest(request, ADMIN_APPEARANCE.THEMES);
  const list = rows(await safeJson(resp));
  const active = list.find((t: any) => t.status === 'active') ?? list[0];

  return active?.code ?? null;
}

test.describe('Admin Appearance Themes REST API', () => {
  test('listing returns the theme gallery', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_APPEARANCE.THEMES);
    expect(resp.status()).toBe(200);

    const list = rows(await safeJson(resp));
    expect(list.length).toBeGreaterThan(0);
    expect(typeof list[0].code).toBe('string');
    expect(['active', 'installed', 'available']).toContain(list[0].status);
  });

  test('detail resolves by code', async ({ request }) => {
    const code = await activeThemeCode(request);
    if (!code) { test.skip(true, 'no theme in the gallery'); return; }

    const resp = await sendAdminRequest(request, ADMIN_APPEARANCE.THEME(code));
    expect(resp.status()).toBe(200);

    const body = await safeJson(resp);
    expect(body?.code).toBe(code);
  });

  test('detail of an unknown code returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_APPEARANCE.THEME('no_such_theme_e2e'));
    expect(resp.status()).toBe(404);
  });

  test('impact reports what activation would change', async ({ request }) => {
    const code = await activeThemeCode(request);
    if (!code) { test.skip(true, 'no theme in the gallery'); return; }

    const resp = await sendAdminRequest(request, ADMIN_APPEARANCE.THEME_IMPACT(code), {
      params: { 'channel_ids[]': '1' },
    });
    expect(resp.status()).toBe(200);

    const body = await safeJson(resp);
    expect(Array.isArray(body?.impact)).toBe(true);
  });

  test('impact without channels returns 422', async ({ request }) => {
    const code = await activeThemeCode(request);
    if (!code) { test.skip(true, 'no theme in the gallery'); return; }

    const resp = await sendAdminRequest(request, ADMIN_APPEARANCE.THEME_IMPACT(code));
    expect([400, 422]).toContain(resp.status());
  });

  test('activating the theme the channel already runs is accepted', async ({ request }) => {
    const code = await activeThemeCode(request);
    if (!code) { test.skip(true, 'no theme in the gallery'); return; }

    const resp = await sendAdminRequest(request, ADMIN_APPEARANCE.THEME_ACTIVATE(code), {
      method: 'POST',
      data: { channelIds: [1] },
    });
    expect([200, 201]).toContain(resp.status());

    const body = await safeJson(resp);
    expect(body?.code).toBe(code);
  });

  test('activating an unknown theme returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_APPEARANCE.THEME_ACTIVATE('no_such_theme_e2e'), {
      method: 'POST',
      data: { channelIds: [1] },
    });
    expect(resp.status()).toBe(404);
  });

  test('activating without channels returns 422', async ({ request }) => {
    const code = await activeThemeCode(request);
    if (!code) { test.skip(true, 'no theme in the gallery'); return; }

    const resp = await sendAdminRequest(request, ADMIN_APPEARANCE.THEME_ACTIVATE(code), {
      method: 'POST',
      data: {},
    });
    expect([400, 422]).toContain(resp.status());
  });
});
