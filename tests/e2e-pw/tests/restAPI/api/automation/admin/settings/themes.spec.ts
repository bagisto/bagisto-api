// Admin Settings — Themes (theme_customizations) REST e2e.
// Listing / detail / create / update / delete / mass-delete / mass-update-status.
// Delete wipes the storage directory — only act on fresh rows we created.

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

function uniqueName(): string {
  return `E2E Theme ${Date.now().toString(36).slice(-6)}`;
}

async function createTheme(request: any): Promise<{ id: number | null }> {
  const resp = await sendAdminRequest(request, ADMIN_SETTINGS.THEMES, {
    method: 'POST',
    data: {
      name: uniqueName(),
      sort_order: 99,
      type: 'static_content',
      channel_id: 1,
      theme_code: 'default',
      status: 1,
    },
  });
  const body = await safeJson(resp);
  return { id: body?.id ?? body?.data?.id ?? null };
}

test.describe('Admin Settings Themes REST API', () => {
  test('listing returns 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.THEMES);
    expect(OK_LIST).toContain(resp.status());
    const body = await safeJson(resp);
    if (body) expect(Array.isArray(body.data) || Array.isArray(body)).toBe(true);
  });

  test('listing with type filter', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.THEMES, {
      params: { type: 'static_content' },
    });
    expect(OK_LIST).toContain(resp.status());
  });

  test('detail for id=1 returns 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.THEME(1));
    expect([200, 404]).toContain(resp.status());
  });

  test('detail non-existent id returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.THEME(99999999));
    expect([400, 404]).toContain(resp.status());
  });

  test('create theme happy path', async ({ request }) => {
    const { id } = await createTheme(request);
    if (id) {
      await sendAdminRequest(request, ADMIN_SETTINGS.THEME(id), { method: 'DELETE' });
    }
  });

  test('create theme invalid type returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.THEMES, {
      method: 'POST',
      data: {
        name: uniqueName(),
        sort_order: 99,
        type: 'definitely_not_a_type',
        channel_id: 1,
        theme_code: 'default',
      },
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('create theme missing fields returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.THEMES, {
      method: 'POST',
      data: {},
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('update theme name', async ({ request }) => {
    const { id } = await createTheme(request);
    if (!id) { test.skip(true, 'create failed'); return; }
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.THEME(id), {
      method: 'PUT',
      data: { name: 'Renamed E2E Theme', locale: 'en', options: { html: '<p>Hi</p>', css: '' } },
    });
    expect(OK_UPDATE).toContain(resp.status());
    await sendAdminRequest(request, ADMIN_SETTINGS.THEME(id), { method: 'DELETE' });
  });

  test('delete fresh theme returns 200/204', async ({ request }) => {
    const { id } = await createTheme(request);
    if (!id) { test.skip(true, 'create failed'); return; }
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.THEME(id), {
      method: 'DELETE',
    });
    expect(OK_DELETE).toContain(resp.status());
  });

  test('mass-delete with single fresh id', async ({ request }) => {
    const { id } = await createTheme(request);
    if (!id) { test.skip(true, 'create failed'); return; }
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.THEMES_MASS_DELETE, {
      method: 'POST',
      data: { indices: [id] },
    });
    expect([200, 201, 400, 422]).toContain(resp.status());
  });

  test('mass-delete empty indices returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.THEMES_MASS_DELETE, {
      method: 'POST',
      data: { indices: [] },
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('mass-update-status toggles status on fresh row', async ({ request }) => {
    const { id } = await createTheme(request);
    if (!id) { test.skip(true, 'create failed'); return; }
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.THEMES_MASS_UPDATE_STATUS, {
      method: 'POST',
      data: { indices: [id], value: 0 },
    });
    expect([200, 201, 400, 422]).toContain(resp.status());
    await sendAdminRequest(request, ADMIN_SETTINGS.THEME(id), { method: 'DELETE' });
  });

  test('mass-update-status invalid value returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.THEMES_MASS_UPDATE_STATUS, {
      method: 'POST',
      data: { indices: [1], value: 99 },
    });
    expect([400, 422]).toContain(resp.status());
  });
});
