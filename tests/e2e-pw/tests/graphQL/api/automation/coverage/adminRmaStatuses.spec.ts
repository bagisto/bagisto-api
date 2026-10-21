import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../graphql/helpers/adminGraphqlClient';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import { env } from '../../../config/env';
import {
  ADMIN_RMA_STATUSES_QUERY,
  ADMIN_RMA_STATUS_QUERY,
  CREATE_ADMIN_RMA_STATUS,
  UPDATE_ADMIN_RMA_STATUS,
  DELETE_ADMIN_RMA_STATUS,
  ADMIN_RMA_STATUS_MASS_DELETE,
  ADMIN_RMA_STATUS_MASS_UPDATE_STATUS,
} from '../../../graphql/Queries/coverage/adminRma.queries';

test.describe.configure({ timeout: 60_000 });

const UNKNOWN_IRI = '/api/admin/rma/statuses/999999999';

const DEFAULT_STATUS_IRI = '/api/admin/rma/statuses/1';

const NOT_FOUND = 'Return status not found.';

function uniqueTitle(): string {
  return `e2e gql rma status ${Date.now()}-${Math.floor(Math.random() * 1_000_000)}`;
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

async function createStatus(request: APIRequestContext, variables: Record<string, any>): Promise<any> {
  return dataOf(await gql(request, CREATE_ADMIN_RMA_STATUS, variables), 'createAdminRmaStatus').adminRmaStatus;
}

async function statusById(request: APIRequestContext, id: string): Promise<any> {
  return gql(request, ADMIN_RMA_STATUS_QUERY, { id });
}

test.describe('admin rma statuses graphql', () => {
  let created: string[];

  test.beforeEach(() => {
    created = [];
  });

  test.afterEach(async ({ request }) => {
    for (const id of created) {
      await sendAdminGraphQLRequest(request, DELETE_ADMIN_RMA_STATUS, { id });
    }
  });

  test('should refuse the status listing without an admin token', async ({ request }) => {
    const resp = await sendGraphQLRequest(request, ADMIN_RMA_STATUSES_QUERY, { first: 1 }, {}, env.adminGraphqlEndpoint);
    expect(resp.status()).toBe(401);
    expect((await resp.json())?.message).toBe('Unauthenticated.');
  });

  test('should refuse to create a status without an admin token', async ({ request }) => {
    const resp = await sendGraphQLRequest(request, CREATE_ADMIN_RMA_STATUS, { title: uniqueTitle(), status: 1 }, {}, env.adminGraphqlEndpoint);
    expect(resp.status()).toBe(401);
    expect((await resp.json())?.message).toBe('Unauthenticated.');
  });

  test('should create a status and return it with default off', async ({ request }) => {
    const title = uniqueTitle();
    const status = await createStatus(request, { title, status: 1, color: '#FDB022' });
    created.push(status.id);

    expect(status.id).toBe(`/api/admin/rma/statuses/${status._id}`);
    expect(status).toEqual(expect.objectContaining({ title, status: 1, color: '#FDB022', default: 0 }));
    expect(typeof status.createdAt).toBe('string');
  });

  test('should reject a status whose title is already taken', async ({ request }) => {
    const title = uniqueTitle();
    created.push((await createStatus(request, { title, status: 0 })).id);

    const body = await gql(request, CREATE_ADMIN_RMA_STATUS, { title, status: 0 });
    expect(firstError(body)).toBe('The title has already been taken.');
    expect(body.data?.createAdminRmaStatus ?? null).toBeNull();
  });

  test('should list only the status matching the title filter', async ({ request }) => {
    const title = uniqueTitle();
    const status = await createStatus(request, { title, status: 0, color: '#12B76A' });
    created.push(status.id);

    const connection = dataOf(await gql(request, ADMIN_RMA_STATUSES_QUERY, { title, first: 10 }), 'adminRmaStatuses');
    expect(connection.totalCount).toBe(1);
    expect(connection.edges.map((edge: any) => edge.node)).toEqual([
      expect.objectContaining({ _id: status._id, title, status: 0, color: '#12B76A', default: 0 }),
    ]);

    const active = dataOf(await gql(request, ADMIN_RMA_STATUSES_QUERY, { title, status: 1, first: 10 }), 'adminRmaStatuses');
    expect(active.totalCount).toBe(0);
  });

  test('should return a single status by iri', async ({ request }) => {
    const title = uniqueTitle();
    const status = await createStatus(request, { title, status: 1, color: '#000000' });
    created.push(status.id);

    const node = dataOf(await statusById(request, status.id), 'adminRmaStatus');
    expect(node).toEqual(expect.objectContaining({ id: status.id, _id: status._id, title, status: 1, color: '#000000' }));
  });

  test('should report a status that does not exist', async ({ request }) => {
    const body = await statusById(request, UNKNOWN_IRI);
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(body.data?.adminRmaStatus ?? null).toBeNull();
  });

  test('should update the title, active flag and colour of a status', async ({ request }) => {
    const status = await createStatus(request, { title: uniqueTitle(), status: 1, color: '#FDB022' });
    created.push(status.id);
    const title = uniqueTitle();

    const updated = dataOf(
      await gql(request, UPDATE_ADMIN_RMA_STATUS, { id: status.id, title, status: 0, color: '#12B76A' }),
      'updateAdminRmaStatus',
    ).adminRmaStatus;
    expect(updated).toEqual(expect.objectContaining({ _id: status._id, title, status: 0, color: '#12B76A' }));

    const node = dataOf(await statusById(request, status.id), 'adminRmaStatus');
    expect(node).toEqual(expect.objectContaining({ title, status: 0, color: '#12B76A' }));
  });

  test('should report an update of a status that does not exist', async ({ request }) => {
    const body = await gql(request, UPDATE_ADMIN_RMA_STATUS, { id: UNKNOWN_IRI, title: uniqueTitle(), status: 1 });
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(body.data?.updateAdminRmaStatus ?? null).toBeNull();
  });

  test('should delete a status the test created and confirm it with a message', async ({ request }) => {
    const status = await createStatus(request, { title: uniqueTitle(), status: 0 });

    const deleted = dataOf(await gql(request, DELETE_ADMIN_RMA_STATUS, { id: status.id }), 'deleteAdminRmaStatus').adminRmaStatus;
    expect(deleted).toEqual(expect.objectContaining({ _id: status._id, title: status.title, message: 'Return status deleted successfully.' }));

    expect(firstError(await statusById(request, status.id))).toBe(NOT_FOUND);
  });

  test('should refuse to delete a default status', async ({ request }) => {
    const body = await gql(request, DELETE_ADMIN_RMA_STATUS, { id: DEFAULT_STATUS_IRI });
    expect(firstError(body)).toBe('A default return status cannot be deleted.');

    const node = dataOf(await statusById(request, DEFAULT_STATUS_IRI), 'adminRmaStatus');
    expect(node.default).toBe(1);
  });

  test('should report a delete of a status that does not exist', async ({ request }) => {
    const body = await gql(request, DELETE_ADMIN_RMA_STATUS, { id: UNKNOWN_IRI });
    expect(firstError(body)).toBe(NOT_FOUND);
  });

  test('should mass update the active flag of the statuses that exist', async ({ request }) => {
    const first = await createStatus(request, { title: uniqueTitle(), status: 1 });
    const second = await createStatus(request, { title: uniqueTitle(), status: 1 });
    created.push(first.id, second.id);

    const result = dataOf(
      await gql(request, ADMIN_RMA_STATUS_MASS_UPDATE_STATUS, { indices: [first._id, second._id, 999999999], value: 0 }),
      'createAdminRmaStatusMassUpdateStatus',
    ).adminRmaStatusMassUpdateStatus;
    expect(result.updated).toEqual([first._id, second._id]);
    expect(result.message).toBe('Selected items updated.');

    for (const id of [first.id, second.id]) {
      expect(dataOf(await statusById(request, id), 'adminRmaStatus').status).toBe(0);
    }
  });

  test('should reject a mass status update with a value other than zero or one', async ({ request }) => {
    const status = await createStatus(request, { title: uniqueTitle(), status: 1 });
    created.push(status.id);

    const body = await gql(request, ADMIN_RMA_STATUS_MASS_UPDATE_STATUS, { indices: [status._id], value: 4 });
    expect(firstError(body)).toBe('Status value must be 0 or 1.');
    expect(dataOf(await statusById(request, status.id), 'adminRmaStatus').status).toBe(1);
  });

  test('should mass delete the created statuses and skip a default one', async ({ request }) => {
    const first = await createStatus(request, { title: uniqueTitle(), status: 0 });
    const second = await createStatus(request, { title: uniqueTitle(), status: 0 });

    const result = dataOf(
      await gql(request, ADMIN_RMA_STATUS_MASS_DELETE, { indices: [1, first._id, second._id] }),
      'createAdminRmaStatusMassDelete',
    ).adminRmaStatusMassDelete;
    expect(result.deleted).toEqual([first._id, second._id]);
    expect(result.message).toBe('Selected items deleted.');

    expect(firstError(await statusById(request, first.id))).toBe(NOT_FOUND);
    expect(dataOf(await statusById(request, DEFAULT_STATUS_IRI), 'adminRmaStatus')._id).toBe(1);
  });

  test('should reject a mass status delete without ids', async ({ request }) => {
    const body = await gql(request, ADMIN_RMA_STATUS_MASS_DELETE, { indices: [] });
    expect(firstError(body)).toBe('At least one id is required.');
  });
});
