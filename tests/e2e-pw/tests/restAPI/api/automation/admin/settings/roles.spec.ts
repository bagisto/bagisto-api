import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_SETTINGS } from '../../../../rest/endpoints/admin.settings.endpoints';

test.describe.configure({ timeout: 60_000 });

const NOT_FOUND = 'Role not found.';
const DELETED = 'Role deleted successfully.';
const PERMISSIONS_REQUIRED = 'The permissions field is required when permission_type is custom.';

function uniqueName(): string {
  return `E2ERole${Date.now().toString(36)}${Math.floor(Math.random() * 1_000_000).toString(36)}`;
}

async function createRole(request: APIRequestContext, name: string): Promise<any> {
  const resp = await sendAdminRequest(request, ADMIN_SETTINGS.ROLES, {
    method: 'POST',
    data: {
      name,
      description: 'e2e generated',
      permission_type: 'all',
    },
  });
  const body = await resp.json();
  expect(resp.status(), JSON.stringify(body)).toBe(201);

  return body;
}

async function deleteRole(request: APIRequestContext, id: number): Promise<void> {
  await sendAdminRequest(request, ADMIN_SETTINGS.ROLE(id), { method: 'DELETE' });
}

async function nameMatches(request: APIRequestContext, name: string): Promise<number> {
  const resp = await sendAdminRequest(request, ADMIN_SETTINGS.ROLES, { params: { name } });
  expect(resp.status()).toBe(200);

  return (await resp.json()).data.length;
}

test.describe('admin settings roles rest api', () => {
  let name: string;

  test.beforeEach(() => {
    name = uniqueName();
  });

  test('listing returns 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.ROLES);
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(Array.isArray(body.data)).toBe(true);
    expect(typeof body.meta.total).toBe('number');
  });

  test('listing with name filter', async ({ request }) => {
    const created = await createRole(request, name);

    try {
      const resp = await sendAdminRequest(request, ADMIN_SETTINGS.ROLES, { params: { name } });
      expect(resp.status()).toBe(200);
      const body = await resp.json();
      expect(body.data).toHaveLength(1);
      expect(body.data[0].id).toBe(created.id);
      expect(body.data[0].name).toBe(name);
    } finally {
      await deleteRole(request, created.id);
    }
  });

  test('detail of a created role returns 200', async ({ request }) => {
    const created = await createRole(request, name);

    try {
      const resp = await sendAdminRequest(request, ADMIN_SETTINGS.ROLE(created.id));
      expect(resp.status()).toBe(200);
      const body = await resp.json();
      expect(body.id).toBe(created.id);
      expect(body.name).toBe(name);
      expect(body.description).toBe('e2e generated');
      expect(body.permissionType).toBe('all');
    } finally {
      await deleteRole(request, created.id);
    }
  });

  test('detail non-existent id returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.ROLE(99999999));
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe(NOT_FOUND);
  });

  test('create role with permission type all', async ({ request }) => {
    const created = await createRole(request, name);

    try {
      expect(typeof created.id).toBe('number');
      expect(created.name).toBe(name);
      expect(created.description).toBe('e2e generated');
      expect(created.permissionType).toBe('all');
      expect(await nameMatches(request, name)).toBe(1);
    } finally {
      await deleteRole(request, created.id);
    }
  });

  test('create role with permission type custom requires permissions', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.ROLES, {
      method: 'POST',
      data: {
        name,
        description: 'e2e',
        permission_type: 'custom',
      },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe(PERMISSIONS_REQUIRED);
    expect(await nameMatches(request, name)).toBe(0);
  });

  test('create role missing fields returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.ROLES, {
      method: 'POST',
      data: {},
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The name field is required.');
  });

  test('update role name', async ({ request }) => {
    const created = await createRole(request, name);
    const renamed = uniqueName();

    try {
      const resp = await sendAdminRequest(request, ADMIN_SETTINGS.ROLE(created.id), {
        method: 'PUT',
        data: { name: renamed, description: 'renamed', permission_type: 'all' },
      });
      expect(resp.status()).toBe(200);
      const body = await resp.json();
      expect(body.id).toBe(created.id);
      expect(body.name).toBe(renamed);
      expect(body.description).toBe('renamed');
      expect(body.permissionType).toBe('all');

      const detail = await sendAdminRequest(request, ADMIN_SETTINGS.ROLE(created.id));
      expect((await detail.json()).name).toBe(renamed);
    } finally {
      await deleteRole(request, created.id);
    }
  });

  test('delete fresh role returns 200', async ({ request }) => {
    const created = await createRole(request, name);

    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.ROLE(created.id), {
      method: 'DELETE',
    });
    expect(resp.status()).toBe(200);
    expect((await resp.json()).message).toBe(DELETED);

    const detail = await sendAdminRequest(request, ADMIN_SETTINGS.ROLE(created.id));
    expect(detail.status()).toBe(404);
  });

  test('should validate a role update before applying the last all-access guard', async ({ request }) => {
    const created = await createRole(request, name);

    try {
      const resp = await sendAdminRequest(request, ADMIN_SETTINGS.ROLE(created.id), {
        method: 'PUT',
        data: { name: uniqueName(), description: 'demoted without permissions', permission_type: 'custom' },
      });
      expect(resp.status()).toBe(422);
      expect((await resp.json()).detail).toBe(PERMISSIONS_REQUIRED);

      const detail = await sendAdminRequest(request, ADMIN_SETTINGS.ROLE(created.id));
      expect(detail.status()).toBe(200);
      const role = await detail.json();
      expect(role.permissionType).toBe('all');
      expect(role.name).toBe(name);
    } finally {
      await deleteRole(request, created.id);
    }
  });
});
