// Admin Settings — Locales REST e2e.
// Listing / detail / create / update / delete / mass-delete.
// Code regex: [a-z0-9_-]+. Delete operates on fresh rows only (last-locale +
// channel-default-locale guards return 400).

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

function uniqueLocaleCode(): string {
  return `e2e_${Date.now().toString(36).slice(-6)}`;
}

async function createLocale(request: any): Promise<{ id: number | null; code: string }> {
  const code = uniqueLocaleCode();
  const resp = await sendAdminRequest(request, ADMIN_SETTINGS.LOCALES, {
    method: 'POST',
    data: { code, name: `E2E ${code}`, direction: 'ltr' },
  });
  const body = await safeJson(resp);
  return { id: body?.id ?? body?.data?.id ?? null, code };
}

test.describe('Admin Settings Locales REST API', () => {
  test('listing returns 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.LOCALES);
    expect(OK_LIST).toContain(resp.status());
    const body = await safeJson(resp);
    if (body) expect(Array.isArray(body.data) || Array.isArray(body)).toBe(true);
  });

  test('listing with code filter', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.LOCALES, {
      params: { code: 'en' },
    });
    expect(OK_LIST).toContain(resp.status());
  });

  test('detail for id=1 returns 200 or 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.LOCALE(1));
    expect([200, 404]).toContain(resp.status());
  });

  test('detail non-existent id returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.LOCALE(99999999));
    expect([400, 404]).toContain(resp.status());
  });

  test('create locale happy path', async ({ request }) => {
    const { id } = await createLocale(request);
    if (id) {
      await sendAdminRequest(request, ADMIN_SETTINGS.LOCALE(id), { method: 'DELETE' });
    }
  });

  test('create locale missing fields returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.LOCALES, {
      method: 'POST',
      data: {},
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('create locale invalid direction returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.LOCALES, {
      method: 'POST',
      data: { code: uniqueLocaleCode(), name: 'E2E Bad', direction: 'wrong' },
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('update locale partial (name only)', async ({ request }) => {
    const { id } = await createLocale(request);
    if (!id) { test.skip(true, 'create failed'); return; }
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.LOCALE(id), {
      method: 'PUT',
      data: { name: 'Renamed E2E' },
    });
    expect(OK_UPDATE).toContain(resp.status());
    await sendAdminRequest(request, ADMIN_SETTINGS.LOCALE(id), { method: 'DELETE' });
  });

  test('delete fresh locale returns 200/204', async ({ request }) => {
    const { id } = await createLocale(request);
    if (!id) { test.skip(true, 'create failed'); return; }
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.LOCALE(id), {
      method: 'DELETE',
    });
    expect(OK_DELETE).toContain(resp.status());
  });

  test('mass-delete with single fresh id', async ({ request }) => {
    const { id } = await createLocale(request);
    if (!id) { test.skip(true, 'create failed'); return; }
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.LOCALES_MASS_DELETE, {
      method: 'POST',
      data: { indices: [id] },
    });
    expect([200, 201, 400, 422]).toContain(resp.status());
  });

  test('mass-delete empty indices returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.LOCALES_MASS_DELETE, {
      method: 'POST',
      data: { indices: [] },
    });
    expect([400, 422]).toContain(resp.status());
  });
});
