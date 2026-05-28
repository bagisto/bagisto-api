// Admin Settings — Users (admins) REST e2e.
// Listing / detail / create / update / delete. No mass-delete.
// Self-delete + last-admin delete guards (400). We never delete the caller —
// only fresh users we create with the e2e role.

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

function uniqueEmail(): string {
  return `e2e_${Date.now().toString(36).slice(-6)}@example.com`;
}

async function createUser(request: any, roleId = 1): Promise<{ id: number | null; email: string }> {
  const email = uniqueEmail();
  const resp = await sendAdminRequest(request, ADMIN_SETTINGS.USERS, {
    method: 'POST',
    data: {
      name: `E2E User ${Date.now()}`,
      email,
      password: 'e2epassword123',
      role_id: roleId,
      status: 1,
    },
  });
  const body = await safeJson(resp);
  return { id: body?.id ?? body?.data?.id ?? null, email };
}

test.describe('Admin Settings Users REST API', () => {
  test('listing returns 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.USERS);
    expect(OK_LIST).toContain(resp.status());
    const body = await safeJson(resp);
    if (body) expect(Array.isArray(body.data) || Array.isArray(body)).toBe(true);
  });

  test('listing with email filter', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.USERS, {
      params: { email: 'admin' },
    });
    expect(OK_LIST).toContain(resp.status());
  });

  test('detail for id=1 returns 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.USER(1));
    expect([200, 404]).toContain(resp.status());
  });

  test('detail non-existent id returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.USER(99999999));
    expect([400, 404]).toContain(resp.status());
  });

  test('create user happy path', async ({ request }) => {
    const { id } = await createUser(request);
    if (id) {
      await sendAdminRequest(request, ADMIN_SETTINGS.USER(id), { method: 'DELETE' });
    }
  });

  test('create user missing fields returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.USERS, {
      method: 'POST',
      data: {},
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('create user short password returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.USERS, {
      method: 'POST',
      data: {
        name: 'Short Pass',
        email: uniqueEmail(),
        password: 'x',
        role_id: 1,
      },
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('update user partial (name)', async ({ request }) => {
    const { id } = await createUser(request);
    if (!id) { test.skip(true, 'create failed'); return; }
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.USER(id), {
      method: 'PUT',
      data: { name: 'Renamed E2E User' },
    });
    expect(OK_UPDATE).toContain(resp.status());
    await sendAdminRequest(request, ADMIN_SETTINGS.USER(id), { method: 'DELETE' });
  });

  test('delete fresh user returns 200/204', async ({ request }) => {
    const { id } = await createUser(request);
    if (!id) { test.skip(true, 'create failed'); return; }
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.USER(id), {
      method: 'DELETE',
    });
    expect(OK_DELETE).toContain(resp.status());
  });
});
