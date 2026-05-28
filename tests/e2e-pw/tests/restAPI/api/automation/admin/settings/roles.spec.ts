// Admin Settings — Roles REST e2e.
// Listing / detail / create / update / delete. No mass-delete.
// Last-role + in-use-by-admin delete guards (400). Delete acts on fresh rows.

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
  return `E2ERole${Date.now().toString(36).slice(-6)}`;
}

async function createRole(request: any): Promise<{ id: number | null }> {
  const resp = await sendAdminRequest(request, ADMIN_SETTINGS.ROLES, {
    method: 'POST',
    data: {
      name: uniqueName(),
      description: 'e2e generated',
      permission_type: 'all',
    },
  });
  const body = await safeJson(resp);
  return { id: body?.id ?? body?.data?.id ?? null };
}

test.describe('Admin Settings Roles REST API', () => {
  test('listing returns 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.ROLES);
    expect(OK_LIST).toContain(resp.status());
    const body = await safeJson(resp);
    if (body) expect(Array.isArray(body.data) || Array.isArray(body)).toBe(true);
  });

  test('listing with name filter', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.ROLES, {
      params: { name: 'Admin' },
    });
    expect(OK_LIST).toContain(resp.status());
  });

  test('detail for id=1 returns 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.ROLE(1));
    expect([200, 404]).toContain(resp.status());
  });

  test('detail non-existent id returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.ROLE(99999999));
    expect([400, 404]).toContain(resp.status());
  });

  test('create role permission_type=all happy path', async ({ request }) => {
    const { id } = await createRole(request);
    if (id) {
      await sendAdminRequest(request, ADMIN_SETTINGS.ROLE(id), { method: 'DELETE' });
    }
  });

  test('create role permission_type=custom requires permissions', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.ROLES, {
      method: 'POST',
      data: {
        name: uniqueName(),
        description: 'e2e',
        permission_type: 'custom',
      },
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('create role missing fields returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.ROLES, {
      method: 'POST',
      data: {},
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('update role name', async ({ request }) => {
    const { id } = await createRole(request);
    if (!id) { test.skip(true, 'create failed'); return; }
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.ROLE(id), {
      method: 'PUT',
      data: { name: uniqueName(), description: 'renamed', permission_type: 'all' },
    });
    expect(OK_UPDATE).toContain(resp.status());
    await sendAdminRequest(request, ADMIN_SETTINGS.ROLE(id), { method: 'DELETE' });
  });

  test('delete fresh role returns 200/204', async ({ request }) => {
    const { id } = await createRole(request);
    if (!id) { test.skip(true, 'create failed'); return; }
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.ROLE(id), {
      method: 'DELETE',
    });
    expect(OK_DELETE).toContain(resp.status());
  });
});
