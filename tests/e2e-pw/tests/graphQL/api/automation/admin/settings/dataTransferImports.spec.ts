import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  createProductImport,
  deleteImport,
  productImportCsv,
  uniqueSku,
} from '../../../../../restAPI/rest/helpers/coverage/restGaps.helpers';

test.describe.configure({ timeout: 90_000 });

const IMPORT_IRI = (id: number) => `/api/admin/settings/data-transfer/imports/${id}`;
const UNKNOWN_ID = 999999999;

const NOT_FOUND = 'Import not found.';
const CANCELLED = 'Import cancelled successfully.';
const DELETED = 'Import deleted successfully.';
const CANNOT_CANCEL = (state: string) => `Only pending or processing imports can be cancelled. Current state: ${state}.`;

const IMPORTS_LIST = `
  query adminSettingsDataTransferImports($first: Int, $code: String, $state: String, $sort: String, $order: String) {
    adminSettingsDataTransferImports(first: $first, code: $code, state: $state, sort: $sort, order: $order) {
      edges { node { _id code action state } }
    }
  }
`;

const IMPORT_DETAIL = `
  query adminSettingsDataTransferImport($id: ID!) {
    adminSettingsDataTransferImport(id: $id) { id _id code action state }
  }
`;

const IMPORT_CANCEL = `
  mutation cancelAdminSettingsDataTransferImportCancel($id: ID!, $importId: Int) {
    cancelAdminSettingsDataTransferImportCancel(input: { id: $id, importId: $importId }) {
      adminSettingsDataTransferImportCancel { _id state success message }
    }
  }
`;

const IMPORT_DELETE = `
  mutation deleteAdminSettingsDataTransferImport($id: ID!) {
    deleteAdminSettingsDataTransferImport(input: { id: $id }) {
      adminSettingsDataTransferImport { _id code message }
    }
  }
`;

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

function cancelVariables(importId: number): Record<string, any> {
  return { id: `${IMPORT_IRI(importId)}/cancel`, importId };
}

test.describe('admin settings data transfer imports graphql api', () => {
  let importId: number;

  test.beforeEach(async ({ request }) => {
    const sku = uniqueSku('e2e-gql-dti');
    importId = (await createProductImport(request, sku, productImportCsv(sku, 'simple'))).id;
  });

  test.afterEach(async ({ request }) => {
    if (importId) {
      await deleteImport(request, importId);
    }
  });

  test('listing returns the created pending import', async ({ request }) => {
    const connection = dataOf(
      await gql(request, IMPORTS_LIST, { first: 50, code: 'products', state: 'pending', sort: 'id', order: 'desc' }),
      'adminSettingsDataTransferImports',
    );
    const node = connection.edges.map((edge: any) => edge.node).find((row: any) => row._id === importId);
    expect(node).toEqual({ _id: importId, code: 'products', action: 'append', state: 'pending' });
  });

  test('detail returns the created import by iri', async ({ request }) => {
    const node = dataOf(await gql(request, IMPORT_DETAIL, { id: IMPORT_IRI(importId) }), 'adminSettingsDataTransferImport');
    expect(node).toEqual(expect.objectContaining({ _id: importId, code: 'products', action: 'append', state: 'pending' }));
  });

  test('detail of an unknown import reports not found', async ({ request }) => {
    const body = await gql(request, IMPORT_DETAIL, { id: IMPORT_IRI(UNKNOWN_ID) });
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(body.data?.adminSettingsDataTransferImport ?? null).toBeNull();
  });

  test('cancel moves a pending import to cancelled and refuses a second cancel', async ({ request }) => {
    const result = dataOf(await gql(request, IMPORT_CANCEL, cancelVariables(importId)), 'cancelAdminSettingsDataTransferImportCancel')
      .adminSettingsDataTransferImportCancel;
    expect(result).toEqual({ _id: importId, state: 'cancelled', success: true, message: CANCELLED });

    expect(dataOf(await gql(request, IMPORT_DETAIL, { id: IMPORT_IRI(importId) }), 'adminSettingsDataTransferImport').state).toBe('cancelled');

    expect(firstError(await gql(request, IMPORT_CANCEL, cancelVariables(importId)))).toBe(CANNOT_CANCEL('cancelled'));
  });

  test('cancel of an unknown import reports not found', async ({ request }) => {
    const body = await gql(request, IMPORT_CANCEL, cancelVariables(UNKNOWN_ID));
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(body.data?.cancelAdminSettingsDataTransferImportCancel ?? null).toBeNull();
  });

  test('delete removes the created import', async ({ request }) => {
    const deleted = dataOf(await gql(request, IMPORT_DELETE, { id: IMPORT_IRI(importId) }), 'deleteAdminSettingsDataTransferImport')
      .adminSettingsDataTransferImport;
    expect(deleted).toEqual({ _id: importId, code: 'products', message: DELETED });

    expect(firstError(await gql(request, IMPORT_DETAIL, { id: IMPORT_IRI(importId) }))).toBe(NOT_FOUND);
    importId = 0;
  });

  test('delete of an unknown import reports not found', async ({ request }) => {
    const body = await gql(request, IMPORT_DELETE, { id: IMPORT_IRI(UNKNOWN_ID) });
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(body.data?.deleteAdminSettingsDataTransferImport ?? null).toBeNull();
  });
});
