// Admin Settings — Roles GraphQL e2e. No mass-delete.

import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_ROLES_LIST,
  ADMIN_ROLE_DETAIL,
  ADMIN_ROLE_CREATE,
  ADMIN_ROLE_UPDATE,
  ADMIN_ROLE_DELETE,
} from '../../../../graphql/Queries/admin/settings/roles.queries';

test.describe.configure({ timeout: 60_000 });
async function safeJson(r: any) { try { return await r.json(); } catch { return null; } }
function parseId(iri: any): number | null {
  if (typeof iri === 'number') return iri;
  if (typeof iri === 'string' && iri.includes('/')) return parseInt(iri.split('/').pop() || '0', 10);
  return null;
}
function unique(): string { return `E2E Role ${Date.now().toString(36).slice(-6)}${Math.floor(Math.random()*100)}`; }

async function createRole(request: any): Promise<{ id: number | null; name: string }> {
  const name = unique();
  const resp = await sendAdminGraphQLRequest(request, ADMIN_ROLE_CREATE, {
    name, description: 'e2e role', permissionType: 'all',
  });
  const body = await safeJson(resp);
  const id = parseId(body?.data?.createAdminSettingsRole?.adminSettingsRole?._id
    ?? body?.data?.createAdminSettingsRole?.adminSettingsRole?.id);
  return { id, name };
}

test.describe('Admin Settings Roles GraphQL API', () => {
  test('listing returns edges', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_ROLES_LIST, { first: 5 });
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);
    expect(Array.isArray(body?.data?.adminSettingsRoles?.edges)).toBe(true);
  });

  test('detail for role id=1', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_ROLE_DETAIL, { id: '/api/admin/settings/roles/1' });
    expect(resp.status()).toBe(200);
  });

  test('create + update + delete lifecycle', async ({ request }) => {
    const { id } = await createRole(request);
    if (!id) return;
    const iri = `/api/admin/settings/roles/${id}`;
    const upd = await sendAdminGraphQLRequest(request, ADMIN_ROLE_UPDATE, { id: iri, name: unique() });
    expect(upd.status()).toBe(200);
    const del = await sendAdminGraphQLRequest(request, ADMIN_ROLE_DELETE, { id: iri });
    expect(del.status()).toBe(200);
  });

  test('create with invalid permission_type is rejected', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_ROLE_CREATE, {
      name: unique(), description: 'x', permissionType: 'invalid',
    });
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    const nullPayload = body?.data?.createAdminSettingsRole?.adminSettingsRole === null;
    expect(hasErrors || nullPayload).toBe(true);
  });
});
