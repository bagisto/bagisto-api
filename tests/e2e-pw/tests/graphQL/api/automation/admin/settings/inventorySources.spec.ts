import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_INV_SOURCE_CREATE,
  ADMIN_INV_SOURCE_UPDATE,
} from '../../../../graphql/Queries/admin/settings/inventorySources.queries';

test.describe.configure({ timeout: 60_000 });

const SOURCE_IRI = (id: number) => `/api/admin/settings/inventory-sources/${id}`;
const UNKNOWN_IRI = SOURCE_IRI(999999999);

const NOT_FOUND = 'Inventory source not found.';
const DELETED = 'Inventory source deleted successfully.';
const MASS_DELETED = 'Inventory sources deleted successfully.';
const CODE_TAKEN = 'An inventory source with this code already exists.';

const SOURCES_LIST = `
  query adminSettingsInventorySources($first: Int, $code: String) {
    adminSettingsInventorySources(first: $first, code: $code) {
      totalCount
      edges { node { _id code name status country } }
    }
  }
`;

const SOURCE_DETAIL = `
  query adminSettingsInventorySource($id: ID!) {
    adminSettingsInventorySource(id: $id) { id _id code name contactName contactEmail country state city postcode status }
  }
`;

const SOURCE_DELETE = `
  mutation deleteAdminSettingsInventorySource($id: ID!) {
    deleteAdminSettingsInventorySource(input: { id: $id }) { adminSettingsInventorySource { _id code message } }
  }
`;

const SOURCE_MASS_DELETE = `
  mutation createAdminSettingsInventorySourceMassDelete($indices: Iterable!) {
    createAdminSettingsInventorySourceMassDelete(input: { indices: $indices }) {
      adminSettingsInventorySourceMassDelete { deleted message }
    }
  }
`;

interface CreatedSource {
  _id: number;
  id: string;
  code: string;
}

function uniqueCode(): string {
  return `e2e-gql-inv-${Date.now().toString(36)}${Math.floor(Math.random() * 1_000_000).toString(36)}`;
}

function sourceVariables(code: string): Record<string, any> {
  return {
    code,
    name: `e2e gql source ${code}`,
    contactName: 'Tester',
    contactEmail: 'tester@example.com',
    contactNumber: '5555555555',
    country: 'US',
    state: 'NY',
    city: 'New York',
    street: '1 Test St',
    postcode: '10001',
    priority: 0,
    status: 1,
  };
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

async function createSource(request: APIRequestContext): Promise<CreatedSource> {
  const code = uniqueCode();
  const source = dataOf(await gql(request, ADMIN_INV_SOURCE_CREATE, sourceVariables(code)), 'createAdminSettingsInventorySource')
    .adminSettingsInventorySource;
  expect(source.code).toBe(code);
  expect(source.id).toBe(SOURCE_IRI(source._id));

  return { _id: source._id, id: source.id, code };
}

test.describe('admin settings inventory sources graphql api', () => {
  let created: CreatedSource[];

  test.beforeEach(() => {
    created = [];
  });

  test.afterEach(async ({ request }) => {
    for (const source of created) {
      await sendAdminGraphQLRequest(request, SOURCE_DELETE, { id: source.id });
    }
  });

  test('listing filtered by code returns only the created source', async ({ request }) => {
    const source = await createSource(request);
    created.push(source);

    const connection = dataOf(await gql(request, SOURCES_LIST, { code: source.code, first: 5 }), 'adminSettingsInventorySources');
    expect(connection.totalCount).toBe(1);
    expect(connection.edges[0].node).toEqual({
      _id: source._id,
      code: source.code,
      name: `e2e gql source ${source.code}`,
      status: 1,
      country: 'US',
    });
  });

  test('detail returns the created source with its contact fields', async ({ request }) => {
    const source = await createSource(request);
    created.push(source);

    const node = dataOf(await gql(request, SOURCE_DETAIL, { id: source.id }), 'adminSettingsInventorySource');
    expect(node).toEqual(expect.objectContaining({
      _id: source._id,
      code: source.code,
      contactName: 'Tester',
      contactEmail: 'tester@example.com',
      country: 'US',
      state: 'NY',
      city: 'New York',
      postcode: '10001',
      status: 1,
    }));
  });

  test('detail of an unknown source reports not found', async ({ request }) => {
    const body = await gql(request, SOURCE_DETAIL, { id: UNKNOWN_IRI });
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(body.data?.adminSettingsInventorySource ?? null).toBeNull();
  });

  test('create with a taken code is rejected', async ({ request }) => {
    const source = await createSource(request);
    created.push(source);

    expect(firstError(await gql(request, ADMIN_INV_SOURCE_CREATE, sourceVariables(source.code)))).toBe(CODE_TAKEN);

    const connection = dataOf(await gql(request, SOURCES_LIST, { code: source.code, first: 5 }), 'adminSettingsInventorySources');
    expect(connection.totalCount).toBe(1);
  });

  test('create with an invalid contact email is rejected', async ({ request }) => {
    const code = uniqueCode();
    const body = await gql(request, ADMIN_INV_SOURCE_CREATE, { ...sourceVariables(code), contactEmail: 'not-an-email' });
    expect(firstError(body)).toBe('The contact email field must be a valid email address.');

    const connection = dataOf(await gql(request, SOURCES_LIST, { code, first: 5 }), 'adminSettingsInventorySources');
    expect(connection.totalCount).toBe(0);
  });

  test('update renames the source and delete removes it', async ({ request }) => {
    const source = await createSource(request);
    created.push(source);
    const renamed = `e2e gql renamed ${source.code}`;

    const updated = dataOf(await gql(request, ADMIN_INV_SOURCE_UPDATE, { id: source.id, name: renamed }), 'updateAdminSettingsInventorySource')
      .adminSettingsInventorySource;
    expect(updated).toEqual(expect.objectContaining({ _id: source._id, name: renamed }));

    const reread = dataOf(await gql(request, SOURCE_DETAIL, { id: source.id }), 'adminSettingsInventorySource');
    expect(reread).toEqual(expect.objectContaining({ name: renamed, code: source.code, contactEmail: 'tester@example.com' }));

    const deleted = dataOf(await gql(request, SOURCE_DELETE, { id: source.id }), 'deleteAdminSettingsInventorySource').adminSettingsInventorySource;
    expect(deleted).toEqual({ _id: source._id, code: source.code, message: DELETED });
    created = [];

    expect(firstError(await gql(request, SOURCE_DETAIL, { id: source.id }))).toBe(NOT_FOUND);
  });

  test('update of an unknown source reports not found', async ({ request }) => {
    expect(firstError(await gql(request, ADMIN_INV_SOURCE_UPDATE, { id: UNKNOWN_IRI, name: 'e2e gql nobody' }))).toBe(NOT_FOUND);
  });

  test('mass-delete removes the freshly created source', async ({ request }) => {
    const source = await createSource(request);
    created.push(source);

    const result = dataOf(
      await gql(request, SOURCE_MASS_DELETE, { indices: [source._id] }),
      'createAdminSettingsInventorySourceMassDelete',
    ).adminSettingsInventorySourceMassDelete;
    expect(result).toEqual({ deleted: [source._id], message: MASS_DELETED });
    created = [];

    expect(firstError(await gql(request, SOURCE_DETAIL, { id: source.id }))).toBe(NOT_FOUND);
  });

  test('mass-delete without indices is rejected', async ({ request }) => {
    expect(firstError(await gql(request, SOURCE_MASS_DELETE, { indices: [] }))).toBe(
      'The indices field is required and must be a non-empty array.',
    );
  });
});
