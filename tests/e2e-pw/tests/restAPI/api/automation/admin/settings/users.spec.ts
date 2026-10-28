import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_SETTINGS } from '../../../../rest/endpoints/admin.settings.endpoints';

test.describe.configure({ timeout: 60_000 });

const ADMINISTRATOR_ROLE_ID = 1;
const NOT_FOUND = 'Admin user not found.';
const DELETED = 'Admin user deleted successfully.';

interface UserInput {
  name: string;
  email: string;
}

function userInput(): UserInput {
  const suffix = `${Date.now().toString(36)}${Math.floor(Math.random() * 1_000_000).toString(36)}`;

  return {
    name: `E2E User ${suffix}`,
    email: `e2e_${suffix}@example.com`,
  };
}

async function createUser(request: APIRequestContext, input: UserInput): Promise<any> {
  const resp = await sendAdminRequest(request, ADMIN_SETTINGS.USERS, {
    method: 'POST',
    data: {
      ...input,
      password: 'e2epassword123',
      role_id: ADMINISTRATOR_ROLE_ID,
      status: 1,
    },
  });
  const body = await resp.json();
  expect(resp.status(), JSON.stringify(body)).toBe(201);

  return body;
}

async function deleteUser(request: APIRequestContext, id: number): Promise<void> {
  await sendAdminRequest(request, ADMIN_SETTINGS.USER(id), { method: 'DELETE' });
}

async function emailMatches(request: APIRequestContext, email: string): Promise<number> {
  const resp = await sendAdminRequest(request, ADMIN_SETTINGS.USERS, { params: { email } });
  expect(resp.status()).toBe(200);

  return (await resp.json()).data.length;
}

test.describe('admin settings users rest api', () => {
  let input: UserInput;

  test.beforeEach(() => {
    input = userInput();
  });

  test('listing returns 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.USERS);
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(Array.isArray(body.data)).toBe(true);
    expect(typeof body.meta.total).toBe('number');
  });

  test('listing with email filter', async ({ request }) => {
    const created = await createUser(request, input);

    try {
      const resp = await sendAdminRequest(request, ADMIN_SETTINGS.USERS, { params: { email: input.email } });
      expect(resp.status()).toBe(200);
      const body = await resp.json();
      expect(body.data).toHaveLength(1);
      expect(body.data[0].id).toBe(created.id);
      expect(body.data[0].email).toBe(input.email);
    } finally {
      await deleteUser(request, created.id);
    }
  });

  test('detail of a created user returns 200', async ({ request }) => {
    const created = await createUser(request, input);

    try {
      const resp = await sendAdminRequest(request, ADMIN_SETTINGS.USER(created.id));
      expect(resp.status()).toBe(200);
      const body = await resp.json();
      expect(body.id).toBe(created.id);
      expect(body.name).toBe(input.name);
      expect(body.email).toBe(input.email);
      expect(body.roleId).toBe(ADMINISTRATOR_ROLE_ID);
      expect(body.status).toBe(1);
    } finally {
      await deleteUser(request, created.id);
    }
  });

  test('detail non-existent id returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.USER(99999999));
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe(NOT_FOUND);
  });

  test('create user happy path', async ({ request }) => {
    const created = await createUser(request, input);

    try {
      expect(typeof created.id).toBe('number');
      expect(created.name).toBe(input.name);
      expect(created.email).toBe(input.email);
      expect(created.roleId).toBe(ADMINISTRATOR_ROLE_ID);
      expect(created.status).toBe(1);
      expect(created).not.toHaveProperty('password');
      expect(await emailMatches(request, input.email)).toBe(1);
    } finally {
      await deleteUser(request, created.id);
    }
  });

  test('create user missing fields returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.USERS, {
      method: 'POST',
      data: {},
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The name field is required.');
  });

  test('create user short password returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.USERS, {
      method: 'POST',
      data: {
        name: input.name,
        email: input.email,
        password: 'x',
        role_id: ADMINISTRATOR_ROLE_ID,
      },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The password field must be at least 6 characters.');
    expect(await emailMatches(request, input.email)).toBe(0);
  });

  test('update user partial name only', async ({ request }) => {
    const created = await createUser(request, input);

    try {
      const resp = await sendAdminRequest(request, ADMIN_SETTINGS.USER(created.id), {
        method: 'PUT',
        data: { name: 'Renamed E2E User' },
      });
      expect(resp.status()).toBe(200);
      const body = await resp.json();
      expect(body.id).toBe(created.id);
      expect(body.name).toBe('Renamed E2E User');
      expect(body.email).toBe(input.email);
      expect(body.roleId).toBe(ADMINISTRATOR_ROLE_ID);
      expect(body.status).toBe(1);

      const detail = await sendAdminRequest(request, ADMIN_SETTINGS.USER(created.id));
      expect((await detail.json()).name).toBe('Renamed E2E User');
    } finally {
      await deleteUser(request, created.id);
    }
  });

  test('delete fresh user returns 200', async ({ request }) => {
    const created = await createUser(request, input);

    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.USER(created.id), {
      method: 'DELETE',
    });
    expect(resp.status()).toBe(200);
    expect((await resp.json()).message).toBe(DELETED);

    const detail = await sendAdminRequest(request, ADMIN_SETTINGS.USER(created.id));
    expect(detail.status()).toBe(404);
  });

  test('should validate a user update before applying the last all-access guard', async ({ request }) => {
    const created = await createUser(request, input);

    try {
      const resp = await sendAdminRequest(request, ADMIN_SETTINGS.USER(created.id), {
        method: 'PUT',
        data: { name: 'Invalid Update', email: 'not-an-email', role_id: ADMINISTRATOR_ROLE_ID, status: 0 },
      });
      expect(resp.status()).toBe(422);
      expect((await resp.json()).detail).toBe('The email field must be a valid email address.');

      const detail = await sendAdminRequest(request, ADMIN_SETTINGS.USER(created.id));
      expect(detail.status()).toBe(200);
      const user = await detail.json();
      expect(user.email).toBe(input.email);
      expect(user.name).toBe(input.name);
      expect(user.status).toBe(1);
    } finally {
      await deleteUser(request, created.id);
    }
  });
});
