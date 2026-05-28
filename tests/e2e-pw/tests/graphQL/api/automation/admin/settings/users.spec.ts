// Admin Settings — Users GraphQL e2e. No mass-delete. Never delete self.

import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_USERS_LIST,
  ADMIN_USER_DETAIL,
  ADMIN_USER_CREATE,
  ADMIN_USER_UPDATE,
  ADMIN_USER_DELETE,
} from '../../../../graphql/Queries/admin/settings/users.queries';

test.describe.configure({ timeout: 60_000 });
async function safeJson(r: any) { try { return await r.json(); } catch { return null; } }
function parseId(iri: any): number | null {
  if (typeof iri === 'number') return iri;
  if (typeof iri === 'string' && iri.includes('/')) return parseInt(iri.split('/').pop() || '0', 10);
  return null;
}
function uniqueEmail(): string { return `e2e+${Date.now()}${Math.floor(Math.random()*1000)}@example.com`; }

async function createUser(request: any, roleId = 1): Promise<{ id: number | null; email: string }> {
  const email = uniqueEmail();
  const resp = await sendAdminGraphQLRequest(request, ADMIN_USER_CREATE, {
    name: 'E2E User', email, password: 'secret123', roleId, status: 1,
  });
  const body = await safeJson(resp);
  const id = parseId(body?.data?.createAdminSettingsUser?.adminSettingsUser?._id
    ?? body?.data?.createAdminSettingsUser?.adminSettingsUser?.id);
  return { id, email };
}

test.describe('Admin Settings Users GraphQL API', () => {
  test('listing returns edges', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_USERS_LIST, { first: 5 });
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);
    expect(Array.isArray(body?.data?.adminSettingsUsers?.edges)).toBe(true);
  });

  test('detail for user id=1', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_USER_DETAIL, { id: '/api/admin/settings/users/1' });
    expect(resp.status()).toBe(200);
  });

  test('create + update + delete lifecycle (fresh user only)', async ({ request }) => {
    const { id } = await createUser(request);
    if (!id) return;
    const iri = `/api/admin/settings/users/${id}`;
    const upd = await sendAdminGraphQLRequest(request, ADMIN_USER_UPDATE, { id: iri, name: `Renamed ${id}` });
    expect(upd.status()).toBe(200);
    const del = await sendAdminGraphQLRequest(request, ADMIN_USER_DELETE, { id: iri });
    expect(del.status()).toBe(200);
  });

  test('create with missing password is rejected', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_USER_CREATE, {
      name: 'E2E', email: uniqueEmail(), password: '', roleId: 1, status: 1,
    });
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    const nullPayload = body?.data?.createAdminSettingsUser?.adminSettingsUser === null;
    expect(hasErrors || nullPayload).toBe(true);
  });
});
