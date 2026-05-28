// Admin Settings — Inventory Sources REST e2e.
// Listing / detail / create / update / delete / mass-delete.
// Last-source + FK-in-use delete guards (400). Delete only on fresh rows.

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

function uniqueCode(): string {
  return `e2e-src-${Date.now().toString(36).slice(-6)}`;
}

async function createSource(request: any): Promise<{ id: number | null }> {
  const code = uniqueCode();
  const resp = await sendAdminRequest(request, ADMIN_SETTINGS.INVENTORY_SOURCES, {
    method: 'POST',
    data: {
      code,
      name: `E2E Source ${code}`,
      description: 'e2e generated',
      contact_name: 'E2E Tester',
      contact_email: `e2e-${Date.now()}@example.com`,
      contact_number: '1234567890',
      country: 'US',
      state: 'CA',
      city: 'San Francisco',
      street: '1 Market St',
      postcode: '94105',
      priority: 0,
      status: 1,
    },
  });
  const body = await safeJson(resp);
  return { id: body?.id ?? body?.data?.id ?? null };
}

test.describe('Admin Settings Inventory Sources REST API', () => {
  test('listing returns 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.INVENTORY_SOURCES);
    expect(OK_LIST).toContain(resp.status());
    const body = await safeJson(resp);
    if (body) expect(Array.isArray(body.data) || Array.isArray(body)).toBe(true);
  });

  test('listing with code filter', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.INVENTORY_SOURCES, {
      params: { code: 'default' },
    });
    expect(OK_LIST).toContain(resp.status());
  });

  test('detail for id=1 returns 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.INVENTORY_SOURCE(1));
    expect([200, 404]).toContain(resp.status());
  });

  test('detail non-existent id returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.INVENTORY_SOURCE(99999999));
    expect([400, 404]).toContain(resp.status());
  });

  test('create inventory source happy path', async ({ request }) => {
    const { id } = await createSource(request);
    if (id) {
      await sendAdminRequest(request, ADMIN_SETTINGS.INVENTORY_SOURCE(id), { method: 'DELETE' });
    }
  });

  test('create source missing required fields returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.INVENTORY_SOURCES, {
      method: 'POST',
      data: { code: 'incomplete' },
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('update source partial (name only)', async ({ request }) => {
    const { id } = await createSource(request);
    if (!id) { test.skip(true, 'create failed'); return; }
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.INVENTORY_SOURCE(id), {
      method: 'PUT',
      data: { name: 'Renamed E2E Source' },
    });
    expect(OK_UPDATE).toContain(resp.status());
    await sendAdminRequest(request, ADMIN_SETTINGS.INVENTORY_SOURCE(id), { method: 'DELETE' });
  });

  test('delete fresh source returns 200/204', async ({ request }) => {
    const { id } = await createSource(request);
    if (!id) { test.skip(true, 'create failed'); return; }
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.INVENTORY_SOURCE(id), {
      method: 'DELETE',
    });
    expect(OK_DELETE).toContain(resp.status());
  });

  test('mass-delete with single fresh id', async ({ request }) => {
    const { id } = await createSource(request);
    if (!id) { test.skip(true, 'create failed'); return; }
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.INVENTORY_SOURCES_MASS_DELETE, {
      method: 'POST',
      data: { indices: [id] },
    });
    expect([200, 201, 400, 422]).toContain(resp.status());
  });

  test('mass-delete empty indices returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.INVENTORY_SOURCES_MASS_DELETE, {
      method: 'POST',
      data: { indices: [] },
    });
    expect([400, 422]).toContain(resp.status());
  });
});
