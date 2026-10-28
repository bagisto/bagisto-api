import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../graphql/helpers/adminGraphqlClient';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import { env } from '../../../config/env';
import {
  ADMIN_RMA_REASONS_QUERY,
  ADMIN_RMA_REASON_QUERY,
  CREATE_ADMIN_RMA_REASON,
  UPDATE_ADMIN_RMA_REASON,
  DELETE_ADMIN_RMA_REASON,
  ADMIN_RMA_REASON_MASS_DELETE,
  ADMIN_RMA_REASON_MASS_UPDATE_STATUS,
  ADMIN_RETURN_REASONS_QUERY,
} from '../../../graphql/Queries/coverage/adminRma.queries';

test.describe.configure({ timeout: 60_000 });

const UNKNOWN_IRI = '/api/admin/rma/reasons/999999999';

const NOT_FOUND = 'Return reason not found.';

function uniqueTitle(): string {
  return `e2e gql rma reason ${Date.now()}-${Math.floor(Math.random() * 1_000_000)}`;
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

async function createReason(request: APIRequestContext, variables: Record<string, any>): Promise<any> {
  return dataOf(await gql(request, CREATE_ADMIN_RMA_REASON, variables), 'createAdminRmaReason').adminRmaReason;
}

async function reasonById(request: APIRequestContext, id: string): Promise<any> {
  return gql(request, ADMIN_RMA_REASON_QUERY, { id });
}

test.describe('admin rma reasons graphql', () => {
  let created: string[];

  test.beforeEach(() => {
    created = [];
  });

  test.afterEach(async ({ request }) => {
    for (const id of created) {
      await sendAdminGraphQLRequest(request, DELETE_ADMIN_RMA_REASON, { id });
    }
  });

  test('should refuse the reason listing without an admin token', async ({ request }) => {
    const resp = await sendGraphQLRequest(request, ADMIN_RMA_REASONS_QUERY, { first: 1 }, {}, env.adminGraphqlEndpoint);
    expect(resp.status()).toBe(401);
    expect((await resp.json())?.message).toBe('Unauthenticated.');
  });

  test('should create a reason with its resolution types', async ({ request }) => {
    const title = uniqueTitle();
    const reason = await createReason(request, { title, status: 0, position: 4, resolutionType: ['return', 'cancel_items'] });
    created.push(reason.id);

    expect(reason.id).toBe(`/api/admin/rma/reasons/${reason._id}`);
    expect(reason).toEqual(expect.objectContaining({ title, status: 0, position: 4, isAdmin: 0 }));
    expect([...reason.resolutionType].sort()).toEqual(['cancel_items', 'return']);
  });

  test('should reject a reason without a resolution type', async ({ request }) => {
    const title = uniqueTitle();
    const body = await gql(request, CREATE_ADMIN_RMA_REASON, { title, status: 1, position: 1 });
    expect(firstError(body)).toBe('The resolution type field is required.');

    const connection = dataOf(await gql(request, ADMIN_RMA_REASONS_QUERY, { title, first: 5 }), 'adminRmaReasons');
    expect(connection.totalCount).toBe(0);
  });

  test('should list only the reason matching the title filter', async ({ request }) => {
    const title = uniqueTitle();
    const reason = await createReason(request, { title, status: 0, position: 2, resolutionType: ['return'] });
    created.push(reason.id);

    const connection = dataOf(await gql(request, ADMIN_RMA_REASONS_QUERY, { title, first: 5 }), 'adminRmaReasons');
    expect(connection.totalCount).toBe(1);
    expect(connection.edges[0].node).toEqual(expect.objectContaining({ _id: reason._id, title, status: 0, position: 2 }));
  });

  test('should return a single reason by iri', async ({ request }) => {
    const reason = await createReason(request, { title: uniqueTitle(), status: 1, position: 3, resolutionType: ['cancel_items'] });
    created.push(reason.id);

    const node = dataOf(await reasonById(request, reason.id), 'adminRmaReason');
    expect(node).toEqual(expect.objectContaining({ _id: reason._id, title: reason.title, position: 3 }));
    expect(node.resolutionType).toEqual(['cancel_items']);
  });

  test('should report a reason that does not exist', async ({ request }) => {
    const body = await reasonById(request, UNKNOWN_IRI);
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(body.data?.adminRmaReason ?? null).toBeNull();
  });

  test('should update a reason and replace its resolution types', async ({ request }) => {
    const reason = await createReason(request, { title: uniqueTitle(), status: 1, position: 1, resolutionType: ['return', 'cancel_items'] });
    created.push(reason.id);
    const title = uniqueTitle();

    const updated = dataOf(
      await gql(request, UPDATE_ADMIN_RMA_REASON, { id: reason.id, title, status: 0, position: 8, resolutionType: ['cancel_items'] }),
      'updateAdminRmaReason',
    ).adminRmaReason;
    expect(updated).toEqual(expect.objectContaining({ _id: reason._id, title, status: 0, position: 8 }));
    expect(updated.resolutionType).toEqual(['cancel_items']);

    expect(dataOf(await reasonById(request, reason.id), 'adminRmaReason').resolutionType).toEqual(['cancel_items']);
  });

  test('should report an update of a reason that does not exist', async ({ request }) => {
    const body = await gql(request, UPDATE_ADMIN_RMA_REASON, {
      id: UNKNOWN_IRI,
      title: uniqueTitle(),
      status: 1,
      position: 1,
      resolutionType: ['return'],
    });
    expect(firstError(body)).toBe(NOT_FOUND);
  });

  test('should delete a reason the test created and confirm it with a message', async ({ request }) => {
    const reason = await createReason(request, { title: uniqueTitle(), status: 0, position: 1, resolutionType: ['return'] });

    const deleted = dataOf(await gql(request, DELETE_ADMIN_RMA_REASON, { id: reason.id }), 'deleteAdminRmaReason').adminRmaReason;
    expect(deleted).toEqual(expect.objectContaining({ _id: reason._id, message: 'Return reason deleted successfully.' }));

    expect(firstError(await reasonById(request, reason.id))).toBe(NOT_FOUND);
  });

  test('should report a delete of a reason that does not exist', async ({ request }) => {
    expect(firstError(await gql(request, DELETE_ADMIN_RMA_REASON, { id: UNKNOWN_IRI }))).toBe(NOT_FOUND);
  });

  test('should mass update the active flag of the reasons that exist', async ({ request }) => {
    const first = await createReason(request, { title: uniqueTitle(), status: 0, position: 1, resolutionType: ['return'] });
    const second = await createReason(request, { title: uniqueTitle(), status: 0, position: 1, resolutionType: ['return'] });
    created.push(first.id, second.id);

    const result = dataOf(
      await gql(request, ADMIN_RMA_REASON_MASS_UPDATE_STATUS, { indices: [first._id, second._id, 999999999], value: 1 }),
      'createAdminRmaReasonMassUpdateStatus',
    ).adminRmaReasonMassUpdateStatus;
    expect(result.updated).toEqual([first._id, second._id]);
    expect(result.message).toBe('Selected items updated.');
    expect(dataOf(await reasonById(request, second.id), 'adminRmaReason').status).toBe(1);
  });

  test('should reject a mass reason update without ids', async ({ request }) => {
    expect(firstError(await gql(request, ADMIN_RMA_REASON_MASS_UPDATE_STATUS, { indices: [], value: 1 }))).toBe('At least one id is required.');
  });

  test('should mass delete the reasons the test created', async ({ request }) => {
    const first = await createReason(request, { title: uniqueTitle(), status: 0, position: 1, resolutionType: ['return'] });
    const second = await createReason(request, { title: uniqueTitle(), status: 0, position: 1, resolutionType: ['cancel_items'] });

    const result = dataOf(
      await gql(request, ADMIN_RMA_REASON_MASS_DELETE, { indices: [first._id, second._id] }),
      'createAdminRmaReasonMassDelete',
    ).adminRmaReasonMassDelete;
    expect(result.deleted).toEqual([first._id, second._id]);
    expect(result.message).toBe('Selected items deleted.');
    expect(firstError(await reasonById(request, second.id))).toBe(NOT_FOUND);
  });

  test('should reject a mass reason delete without ids', async ({ request }) => {
    expect(firstError(await gql(request, ADMIN_RMA_REASON_MASS_DELETE, { indices: [] }))).toBe('At least one id is required.');
  });

  test('should offer an active reason only for the resolution types it carries', async ({ request }) => {
    const active = await createReason(request, { title: uniqueTitle(), status: 1, position: 6, resolutionType: ['cancel_items'] });
    const inactive = await createReason(request, { title: uniqueTitle(), status: 0, position: 6, resolutionType: ['cancel_items'] });
    created.push(active.id, inactive.id);

    const cancel = dataOf(await gql(request, ADMIN_RETURN_REASONS_QUERY, { resolutionType: 'cancel_items' }), 'adminReturnReasons');
    expect(cancel.find((row: any) => row._id === active._id)).toEqual({ _id: active._id, title: active.title, position: 6 });
    expect(cancel.map((row: any) => row._id)).not.toContain(inactive._id);

    const returns = dataOf(await gql(request, ADMIN_RETURN_REASONS_QUERY, { resolutionType: 'return' }), 'adminReturnReasons');
    expect(returns.map((row: any) => row._id)).not.toContain(active._id);
  });

  test('should reject a resolution reason lookup for an unknown resolution type', async ({ request }) => {
    const body = await gql(request, ADMIN_RETURN_REASONS_QUERY, { resolutionType: 'refund' });
    expect(firstError(body)).toBe('Resolution type must be "return" or "cancel_items".');
  });
});
