import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_CUSTOMER_GROUPS_LIST,
  ADMIN_CUSTOMER_GROUP_DETAIL,
  ADMIN_CUSTOMER_GROUP_CREATE,
  ADMIN_CUSTOMER_GROUP_UPDATE,
} from '../../../../graphql/Queries/admin/customers/customerGroups.queries';

test.describe.configure({ timeout: 60_000 });

const GROUP_NOT_FOUND = 'Customer group not found.';

const SYSTEM_GROUP_UNDELETABLE = 'System customer groups cannot be deleted.';

const DELETE_GROUP = `
  mutation deleteAdminCustomerGroup($id: ID!) {
    deleteAdminCustomerGroup(input: { id: $id }) {
      adminCustomerGroup {
        _id
        code
        message
      }
    }
  }
`;

const MASS_DELETE_GROUPS = `
  mutation createAdminCustomerGroupMassDelete($indices: Iterable!) {
    createAdminCustomerGroupMassDelete(input: { indices: $indices }) {
      adminCustomerGroupMassDelete {
        deleted
        skipped
        message
      }
    }
  }
`;

interface CreatedGroup {
  _id: number;
  code: string;
  name: string;
}

function uniqueCode(): string {
  return `e2egqlgrp${Date.now()}${Math.floor(Math.random() * 100000)}`;
}

function groupIri(id: number): string {
  return `/api/admin/customers/groups/${id}`;
}

async function gql(request: APIRequestContext, query: string, variables: Record<string, any> = {}): Promise<any> {
  const response = await sendAdminGraphQLRequest(request, query, variables);
  expect(response.status()).toBe(200);

  return response.json();
}

function expectGraphQLError(body: any, field: string, message: string): void {
  expect(body.data?.[field] ?? null).toBeNull();
  expect(body.errors?.[0]?.message).toBe(message);
}

async function createGroup(request: APIRequestContext): Promise<CreatedGroup> {
  const code = uniqueCode();
  const name = `E2E GQL Group ${code}`;
  const body = await gql(request, ADMIN_CUSTOMER_GROUP_CREATE, { code, name });
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

  const created = body.data.createAdminCustomerGroup.adminCustomerGroup;
  expect(created._id).toBeGreaterThan(0);
  expect(created.code).toBe(code);
  expect(created.name).toBe(name);

  return created;
}

async function dropGroup(request: APIRequestContext, id: number): Promise<void> {
  await sendAdminGraphQLRequest(request, DELETE_GROUP, { id: groupIri(id) });
}

async function groupDetail(request: APIRequestContext, id: number): Promise<any> {
  const body = await gql(request, ADMIN_CUSTOMER_GROUP_DETAIL, { id: groupIri(id) });
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

  return body.data.adminCustomerGroup;
}

async function codesMatching(request: APIRequestContext, code: string, isUserDefined: number): Promise<string[]> {
  const body = await gql(request, ADMIN_CUSTOMER_GROUPS_LIST, { code, is_user_defined: isUserDefined });
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

  return body.data.adminCustomerGroups.edges.map((edge: any) => edge.node.code);
}

test.describe('admin customer groups graphql api', () => {
  test('list returns the seeded system groups', async ({ request }) => {
    const body = await gql(request, ADMIN_CUSTOMER_GROUPS_LIST, { first: 50, is_user_defined: 0 });

    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
    const codes = body.data.adminCustomerGroups.edges.map((edge: any) => edge.node.code);
    expect(codes).toEqual(expect.arrayContaining(['guest', 'general', 'wholesale']));
  });

  test('list filter by is_user_defined separates a created group from system groups', async ({ request }) => {
    const created = await createGroup(request);

    try {
      expect(await codesMatching(request, created.code, 1)).toEqual([created.code]);
      expect(await codesMatching(request, created.code, 0)).toEqual([]);

      const system = await gql(request, ADMIN_CUSTOMER_GROUPS_LIST, { first: 50, is_user_defined: 0 });
      const nodes = system.data.adminCustomerGroups.edges.map((edge: any) => edge.node);
      expect(nodes.length).toBeGreaterThan(0);
      expect(nodes.every((node: any) => node.isUserDefined === 0)).toBe(true);
    } finally {
      await dropGroup(request, created._id);
    }
  });

  test('create detail and delete round trip', async ({ request }) => {
    const created = await createGroup(request);

    const detail = await groupDetail(request, created._id);
    expect(detail._id).toBe(created._id);
    expect(detail.code).toBe(created.code);
    expect(detail.name).toBe(created.name);
    expect(detail.isUserDefined).toBe(1);
    expect(detail.customersCount).toBe(0);

    const removal = await gql(request, DELETE_GROUP, { id: groupIri(created._id) });
    expect(removal.errors, JSON.stringify(removal.errors)).toBeUndefined();
    expect(removal.data.deleteAdminCustomerGroup.adminCustomerGroup._id).toBe(created._id);
    expect(removal.data.deleteAdminCustomerGroup.adminCustomerGroup.message).toBe('Customer group deleted successfully.');

    expectGraphQLError(await gql(request, ADMIN_CUSTOMER_GROUP_DETAIL, { id: groupIri(created._id) }), 'adminCustomerGroup', GROUP_NOT_FOUND);
  });

  test('create rejects a duplicate code', async ({ request }) => {
    const first = await createGroup(request);

    try {
      const duplicate = await gql(request, ADMIN_CUSTOMER_GROUP_CREATE, { code: first.code, name: 'Duplicate' });

      expectGraphQLError(duplicate, 'createAdminCustomerGroup', 'The code has already been taken.');
      expect(await codesMatching(request, first.code, 1)).toEqual([first.code]);
    } finally {
      await dropGroup(request, first._id);
    }
  });

  test('create rejects a code that does not start with a letter', async ({ request }) => {
    const code = `123-bad-code-${Date.now()}`;
    const body = await gql(request, ADMIN_CUSTOMER_GROUP_CREATE, { code, name: 'Bad' });

    expectGraphQLError(body, 'createAdminCustomerGroup', 'The code must be valid.');
    expect(await codesMatching(request, code, 1)).toEqual([]);
  });

  test('update renames a user defined group', async ({ request }) => {
    const created = await createGroup(request);

    try {
      const newName = `Renamed ${Date.now()}`;
      const body = await gql(request, ADMIN_CUSTOMER_GROUP_UPDATE, {
        id: groupIri(created._id),
        code: created.code,
        name: newName,
      });

      expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
      expect(body.data.updateAdminCustomerGroup.adminCustomerGroup.name).toBe(newName);
      expect(body.data.updateAdminCustomerGroup.adminCustomerGroup.code).toBe(created.code);
      expect((await groupDetail(request, created._id)).name).toBe(newName);
    } finally {
      await dropGroup(request, created._id);
    }
  });

  test('system guard refuses to change the code of the general group', async ({ request }) => {
    const body = await gql(request, ADMIN_CUSTOMER_GROUP_UPDATE, {
      id: groupIri(2),
      code: 'renamed_general',
      name: 'General',
    });

    expectGraphQLError(body, 'updateAdminCustomerGroup', 'The code of a system customer group cannot be changed.');
    expect((await groupDetail(request, 2)).code).toBe('general');
  });

  test('system guard refuses to delete the guest group', async ({ request }) => {
    const body = await gql(request, DELETE_GROUP, { id: groupIri(1) });

    expectGraphQLError(body, 'deleteAdminCustomerGroup', SYSTEM_GROUP_UNDELETABLE);
    expect((await groupDetail(request, 1)).code).toBe('guest');
  });

  test('mass delete removes user defined groups and skips the system group', async ({ request }) => {
    const a = await createGroup(request);
    const b = await createGroup(request);

    const body = await gql(request, MASS_DELETE_GROUPS, { indices: [a._id, b._id, 1] });

    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
    const result = body.data.createAdminCustomerGroupMassDelete.adminCustomerGroupMassDelete;
    expect(result.deleted).toEqual([a._id, b._id]);
    expect(result.skipped).toEqual([{ id: 1, reason: SYSTEM_GROUP_UNDELETABLE }]);
    expect(result.message).toBe('Customer groups deleted.');

    expectGraphQLError(await gql(request, ADMIN_CUSTOMER_GROUP_DETAIL, { id: groupIri(a._id) }), 'adminCustomerGroup', GROUP_NOT_FOUND);
    expectGraphQLError(await gql(request, ADMIN_CUSTOMER_GROUP_DETAIL, { id: groupIri(b._id) }), 'adminCustomerGroup', GROUP_NOT_FOUND);
    expect((await groupDetail(request, 1))._id).toBe(1);
  });

  test('mass delete rejects empty indices', async ({ request }) => {
    const body = await gql(request, MASS_DELETE_GROUPS, { indices: [] });

    expectGraphQLError(body, 'createAdminCustomerGroupMassDelete', 'At least one customer group id is required.');
  });
});
