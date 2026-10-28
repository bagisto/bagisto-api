import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import { ADMIN_ROLE_CREATE } from '../../../../graphql/Queries/admin/settings/roles.queries';
import {
  ADMIN_USER_CREATE,
  ADMIN_USER_DETAIL,
  ADMIN_USER_UPDATE,
} from '../../../../graphql/Queries/admin/settings/users.queries';

test.describe.configure({ timeout: 60_000 });

const USER_IRI = (id: number) => `/api/admin/settings/users/${id}`;
const ROLE_IRI = (id: number) => `/api/admin/settings/roles/${id}`;
const UNKNOWN_IRI = USER_IRI(999999999);

const NOT_FOUND = 'Admin user not found.';
const DELETED = 'Admin user deleted successfully.';

const USERS_LIST = `
  query adminSettingsUsers($first: Int, $email: String) {
    adminSettingsUsers(first: $first, email: $email) {
      totalCount
      edges { node { _id name email roleId roleName status } }
    }
  }
`;

const USER_DELETE = `
  mutation deleteAdminSettingsUser($id: ID!) {
    deleteAdminSettingsUser(input: { id: $id }) { adminSettingsUser { _id email message } }
  }
`;

const ROLE_DELETE = `
  mutation deleteAdminSettingsRole($id: ID!) {
    deleteAdminSettingsRole(input: { id: $id }) { adminSettingsRole { _id } }
  }
`;

interface CreatedUser {
  _id: number;
  id: string;
  email: string;
}

function uniqueSuffix(): string {
  return `${Date.now().toString(36)}${Math.floor(Math.random() * 1_000_000).toString(36)}`;
}

function uniqueEmail(): string {
  return `e2e-gql-user-${uniqueSuffix()}@example.com`;
}

async function gql(request: APIRequestContext, query: string, variables: Record<string, any> = {}): Promise<any> {
  const resp = await sendAdminGraphQLRequest(request, query, variables);
  expect(resp.status()).toBe(200);
  return resp.json();
}

function dataOf(body: any, field: string): any {
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
  return body.data[field];
}

function firstError(body: any): string | undefined {
  return body.errors?.[0]?.message;
}

async function userCount(request: APIRequestContext, email: string): Promise<number> {
  return dataOf(await gql(request, USERS_LIST, { first: 5, email }), 'adminSettingsUsers').totalCount;
}

test.describe('admin settings users graphql api', () => {
  let roleId: number;
  let roleName: string;
  let created: CreatedUser[];

  test.beforeEach(async ({ request }) => {
    created = [];
    roleName = `e2e gql user role ${uniqueSuffix()}`;
    roleId = dataOf(
      await gql(request, ADMIN_ROLE_CREATE, { name: roleName, description: 'e2e user role', permissionType: 'all' }),
      'createAdminSettingsRole',
    ).adminSettingsRole._id;
  });

  test.afterEach(async ({ request }) => {
    for (const user of created) {
      await sendAdminGraphQLRequest(request, USER_DELETE, { id: user.id });
    }

    await sendAdminGraphQLRequest(request, ROLE_DELETE, { id: ROLE_IRI(roleId) });
  });

  async function createUser(request: APIRequestContext): Promise<CreatedUser> {
    const email = uniqueEmail();
    const user = dataOf(
      await gql(request, ADMIN_USER_CREATE, { name: 'e2e gql user', email, password: 'secret123', roleId, status: 1 }),
      'createAdminSettingsUser',
    ).adminSettingsUser;
    expect(user).toEqual(expect.objectContaining({ name: 'e2e gql user', email }));
    expect(user.id).toBe(USER_IRI(user._id));

    const createdUser = { _id: user._id, id: user.id, email };
    created.push(createdUser);

    return createdUser;
  }

  test('listing filtered by email returns only the created user', async ({ request }) => {
    const user = await createUser(request);

    const connection = dataOf(await gql(request, USERS_LIST, { first: 5, email: user.email }), 'adminSettingsUsers');
    expect(connection.totalCount).toBe(1);
    expect(connection.edges[0].node).toEqual({
      _id: user._id,
      name: 'e2e gql user',
      email: user.email,
      roleId,
      roleName,
      status: 1,
    });
  });

  test('detail returns the created user', async ({ request }) => {
    const user = await createUser(request);

    const node = dataOf(await gql(request, ADMIN_USER_DETAIL, { id: user.id }), 'adminSettingsUser');
    expect(node).toEqual({ id: user.id, _id: user._id, name: 'e2e gql user', email: user.email, roleId, status: 1 });
  });

  test('detail of an unknown user reports not found', async ({ request }) => {
    const body = await gql(request, ADMIN_USER_DETAIL, { id: UNKNOWN_IRI });
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(body.data?.adminSettingsUser ?? null).toBeNull();
  });

  test('update renames the user and delete removes it', async ({ request }) => {
    const user = await createUser(request);
    const renamed = `e2e gql renamed ${user._id}`;

    const updated = dataOf(await gql(request, ADMIN_USER_UPDATE, { id: user.id, name: renamed }), 'updateAdminSettingsUser').adminSettingsUser;
    expect(updated).toEqual({ id: user.id, _id: user._id, name: renamed });

    const reread = dataOf(await gql(request, ADMIN_USER_DETAIL, { id: user.id }), 'adminSettingsUser');
    expect(reread).toEqual(expect.objectContaining({ name: renamed, email: user.email, roleId, status: 1 }));

    const deleted = dataOf(await gql(request, USER_DELETE, { id: user.id }), 'deleteAdminSettingsUser').adminSettingsUser;
    expect(deleted).toEqual({ _id: user._id, email: user.email, message: DELETED });
    created = [];

    expect(firstError(await gql(request, ADMIN_USER_DETAIL, { id: user.id }))).toBe(NOT_FOUND);
  });

  test('create with an empty password is rejected', async ({ request }) => {
    const email = uniqueEmail();

    expect(firstError(await gql(request, ADMIN_USER_CREATE, { name: 'e2e gql user', email, password: '', roleId, status: 1 }))).toBe(
      'The password field is required.',
    );
    expect(await userCount(request, email)).toBe(0);
  });

  test('create with a taken email is rejected', async ({ request }) => {
    const user = await createUser(request);

    expect(firstError(await gql(request, ADMIN_USER_CREATE, { name: 'e2e gql twin', email: user.email, password: 'secret123', roleId, status: 1 }))).toBe(
      'The email has already been taken.',
    );
    expect(await userCount(request, user.email)).toBe(1);
  });

  test('update of an unknown user reports not found', async ({ request }) => {
    expect(firstError(await gql(request, ADMIN_USER_UPDATE, { id: UNKNOWN_IRI, name: 'e2e gql nobody' }))).toBe(NOT_FOUND);
  });
});
