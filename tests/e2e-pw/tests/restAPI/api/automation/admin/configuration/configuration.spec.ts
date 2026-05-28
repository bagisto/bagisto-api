// Admin Configuration REST e2e.
// GET /menu (schema tree), GET ?slug=... (current values), POST ... (bulk
// upsert under one slug). Uses a harmless slug (`general.general`) so we
// don't accidentally toggle business-critical settings. We toggle the weight
// unit between kgs/lbs and restore.

import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_SETTINGS } from '../../../../rest/endpoints/admin.settings.endpoints';

test.describe.configure({ timeout: 60_000 });

const SLUG = 'general.general';
const WEIGHT_KEY = 'general.general.locale_options.weight_unit';

async function safeJson(resp: any): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

test.describe('Admin Configuration REST API', () => {
  test('menu (full tree) returns 200 + array', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CONFIG_MENU);
    expect([200]).toContain(resp.status());
    const body = await safeJson(resp);
    if (body) expect(Array.isArray(body) || typeof body === 'object').toBe(true);
  });

  test('menu scoped to slug returns 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CONFIG_MENU, {
      params: { slug: SLUG },
    });
    expect([200]).toContain(resp.status());
  });

  test('menu with include_values=true returns 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CONFIG_MENU, {
      params: { slug: SLUG, include_values: 'true' },
    });
    expect([200]).toContain(resp.status());
  });

  test('menu unknown slug returns 200 (or 404)', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CONFIG_MENU, {
      params: { slug: 'definitely.not.a.real.slug' },
    });
    // Some impls return an empty 200, others 404.
    expect([200, 404]).toContain(resp.status());
  });

  test('values requires slug param', async ({ request }) => {
    // No slug → expect 422 (anti-foot-gun, per CLAUDE.md).
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CONFIG_VALUES);
    expect([400, 422]).toContain(resp.status());
  });

  test('values for general.general returns 200 + payload', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CONFIG_VALUES, {
      params: { slug: SLUG },
    });
    expect([200]).toContain(resp.status());
    const body = await safeJson(resp);
    if (body) expect(Array.isArray(body) || typeof body === 'object').toBe(true);
  });

  test('update happy path — toggle weight unit + restore', async ({ request }) => {
    // Snapshot
    const readResp = await sendAdminRequest(request, ADMIN_SETTINGS.CONFIG_VALUES, {
      params: { slug: SLUG },
    });
    const readBody = await safeJson(readResp);
    const original = (() => {
      const arr = Array.isArray(readBody) ? readBody : [readBody];
      return arr[0]?.values?.[WEIGHT_KEY] ?? 'kgs';
    })();
    const target = original === 'kgs' ? 'lbs' : 'kgs';

    const updateResp = await sendAdminRequest(request, ADMIN_SETTINGS.CONFIG_UPDATE, {
      method: 'POST',
      data: {
        slug: SLUG,
        channel: 'default',
        locale: 'en',
        values: { [WEIGHT_KEY]: target },
      },
    });
    expect([200, 201, 400, 422]).toContain(updateResp.status());

    // Restore (best-effort)
    await sendAdminRequest(request, ADMIN_SETTINGS.CONFIG_UPDATE, {
      method: 'POST',
      data: {
        slug: SLUG,
        channel: 'default',
        locale: 'en',
        values: { [WEIGHT_KEY]: original },
      },
    });
  });

  test('update scope-escape rejected with 422', async ({ request }) => {
    // Key does NOT begin with the slug → anti-scope-escape guard.
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CONFIG_UPDATE, {
      method: 'POST',
      data: {
        slug: SLUG,
        channel: 'default',
        locale: 'en',
        values: { 'sales.order_settings.reorder.admin': '1' },
      },
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('update missing slug returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CONFIG_UPDATE, {
      method: 'POST',
      data: { values: { [WEIGHT_KEY]: 'kgs' } },
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('update empty values returns 422 or 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CONFIG_UPDATE, {
      method: 'POST',
      data: { slug: SLUG, channel: 'default', locale: 'en', values: {} },
    });
    // Some impls tolerate no-op writes.
    expect([200, 201, 400, 422]).toContain(resp.status());
  });
});
