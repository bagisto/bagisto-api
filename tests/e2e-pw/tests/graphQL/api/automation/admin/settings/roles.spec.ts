import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_ROLE_CREATE,
  ADMIN_ROLE_DETAIL,
  ADMIN_ROLE_UPDATE,
} from '../../../../graphql/Queries/admin/settings/roles.queries';

test.describe.configure({ timeout: 60_000 });

const ROLE_IRI = (id: number) => `/api/admin/settings/roles/${id}`;
const UNKNOWN_IRI = ROLE_IRI(999999999);

const NOT_FOUND = 'Role not found.';
const DELETED = 'Role deleted successfully.';
const PERMISSIONS_REQUIRED = 'The permissions field is required when permission_type is custom.';

const ROLES_LIST = `
  query adminSettingsRoles($first: Int, $name: String) {
    adminSettingsRoles(first: $first, name: $name) {
      totalCount
      edges { node { _id name description permissionType } }
    }
  }
`;

const ROLE_DELETE = `
  mutation deleteAdminSettingsRole($id: ID!) {
    deleteAdminSettingsRole(input: { id: $id }) { adminSettingsRole { _id name message } }
  }
`;

interface CreatedRole {
  _id: number;
  id: string;
  name: string;
}

function uniqueName(): string {
  return `e2e gql role ${Date.now().toString(36)}${Math.floor(Math.random() * 1_000_000).toString(36)}`;
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

async function createRole(request: APIRequestContext): Promise<CreatedRole> {
  const name = uniqueName();
  const role = dataOf(await gql(request, ADMIN_ROLE_CREATE, { name, description: 'e2e role', permissionType: 'all' }), 'createAdminSettingsRole')
    .adminSettingsRole;
  expect(role.name).toBe(name);
  expect(role.id).toBe(ROLE_IRI(role._id));

  return { _id: role._id, id: role.id, name };
}

async function roleCount(request: APIRequestContext, name: string): Promise<number> {
  return dataOf(await gql(request, ROLES_LIST, { first: 5, name }), 'adminSettingsRoles').totalCount;
}

test.describe('admin settings roles graphql api', () => {
  let created: CreatedRole[];

  test.beforeEach(() => {
    created = [];
  });

  test.afterEach(async ({ request }) => {
    for (const role of created) {
      await sendAdminGraphQLRequest(request, ROLE_DELETE, { id: role.id });
    }
  });

  test('listing filtered by name returns only the created role', async ({ request }) => {
    const role = await createRole(request);
    created.push(role);

    const connection = dataOf(await gql(request, ROLES_LIST, { first: 5, name: role.name }), 'adminSettingsRoles');
    expect(connection.totalCount).toBe(1);
    expect(connection.edges[0].node).toEqual({ _id: role._id, name: role.name, description: 'e2e role', permissionType: 'all' });
  });

  test('detail returns the created role', async ({ request }) => {
    const role = await createRole(request);
    created.push(role);

    const node = dataOf(await gql(request, ADMIN_ROLE_DETAIL, { id: role.id }), 'adminSettingsRole');
    expect(node).toEqual({ id: role.id, _id: role._id, name: role.name, description: 'e2e role', permissionType: 'all' });
  });

  test('detail of an unknown role reports not found', async ({ request }) => {
    const body = await gql(request, ADMIN_ROLE_DETAIL, { id: UNKNOWN_IRI });
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(body.data?.adminSettingsRole ?? null).toBeNull();
  });

  test('update renames the role and delete removes it', async ({ request }) => {
    const role = await createRole(request);
    created.push(role);
    const renamed = uniqueName();

    const updated = dataOf(
      await gql(request, ADMIN_ROLE_UPDATE, { id: role.id, name: renamed, description: 'e2e role renamed', permissionType: 'all' }),
      'updateAdminSettingsRole',
    ).adminSettingsRole;
    expect(updated).toEqual({ id: role.id, _id: role._id, name: renamed });

    const reread = dataOf(await gql(request, ADMIN_ROLE_DETAIL, { id: role.id }), 'adminSettingsRole');
    expect(reread).toEqual(expect.objectContaining({ name: renamed, description: 'e2e role renamed', permissionType: 'all' }));

    const deleted = dataOf(await gql(request, ROLE_DELETE, { id: role.id }), 'deleteAdminSettingsRole').adminSettingsRole;
    expect(deleted).toEqual({ _id: role._id, name: renamed, message: DELETED });
    created = [];

    expect(firstError(await gql(request, ADMIN_ROLE_DETAIL, { id: role.id }))).toBe(NOT_FOUND);
  });

  test('update without a description is rejected and keeps the role', async ({ request }) => {
    const role = await createRole(request);
    created.push(role);

    expect(firstError(await gql(request, ADMIN_ROLE_UPDATE, { id: role.id, name: uniqueName() }))).toBe('The description field is required.');
    expect(dataOf(await gql(request, ADMIN_ROLE_DETAIL, { id: role.id }), 'adminSettingsRole').name).toBe(role.name);
  });

  test('create with an invalid permission type is rejected', async ({ request }) => {
    const name = uniqueName();

    expect(firstError(await gql(request, ADMIN_ROLE_CREATE, { name, description: 'x', permissionType: 'invalid' }))).toBe(
      'The selected permission type is invalid.',
    );
    expect(await roleCount(request, name)).toBe(0);
  });

  test('create a custom role without permissions is rejected', async ({ request }) => {
    const name = uniqueName();

    expect(firstError(await gql(request, ADMIN_ROLE_CREATE, { name, description: 'x', permissionType: 'custom' }))).toBe(PERMISSIONS_REQUIRED);
    expect(await roleCount(request, name)).toBe(0);
  });

  test('update validates the payload before applying the last all-access guard', async ({ request }) => {
    const role = await createRole(request);
    created.push(role);

    const body = await gql(request, ADMIN_ROLE_UPDATE, {
      id: role.id,
      name: uniqueName(),
      description: 'demoted without permissions',
      permissionType: 'custom',
    });
    expect(firstError(body)).toBe(PERMISSIONS_REQUIRED);

    const reread = dataOf(await gql(request, ADMIN_ROLE_DETAIL, { id: role.id }), 'adminSettingsRole');
    expect(reread).toEqual(expect.objectContaining({ name: role.name, permissionType: 'all' }));
  });

  test('delete of an unknown role reports not found', async ({ request }) => {
    expect(firstError(await gql(request, ROLE_DELETE, { id: UNKNOWN_IRI }))).toBe(NOT_FOUND);
  });
});
