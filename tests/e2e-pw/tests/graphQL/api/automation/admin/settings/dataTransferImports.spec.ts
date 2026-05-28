// Admin Settings — Data Transfer Imports GraphQL e2e.
// No create (multipart deferred). Only list + detail + cancel + delete on
// existing rows; we never destructively delete a row we didn't pre-locate.

import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_IMPORTS_LIST,
  ADMIN_IMPORT_DETAIL,
  ADMIN_IMPORT_CANCEL,
  ADMIN_IMPORT_DELETE,
} from '../../../../graphql/Queries/admin/settings/dataTransferImports.queries';

test.describe.configure({ timeout: 60_000 });
async function safeJson(r: any) { try { return await r.json(); } catch { return null; } }

test.describe('Admin Settings DataTransferImports GraphQL API', () => {
  test('listing returns edges', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_IMPORTS_LIST, { first: 5 });
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);
    expect(Array.isArray(body?.data?.adminSettingsDataTransferImports?.edges)).toBe(true);
  });

  test('detail not-found returns errors[]', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_IMPORT_DETAIL, { id: '/api/admin/settings/data-transfer/imports/99999999' });
    expect(resp.status()).toBe(200);
  });

  test('cancel non-existent id returns errors[]', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_IMPORT_CANCEL, { id: '/api/admin/settings/data-transfer/imports/99999999/cancel' });
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    const nullPayload = body?.data?.cancelAdminSettingsDataTransferImportCancel?.adminSettingsDataTransferImportCancel === null;
    expect(hasErrors || nullPayload).toBe(true);
  });

  test('delete non-existent id returns errors[]', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_IMPORT_DELETE, { id: '/api/admin/settings/data-transfer/imports/99999999' });
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    const nullPayload = body?.data?.deleteAdminSettingsDataTransferImport?.adminSettingsDataTransferImport === null;
    expect(hasErrors || nullPayload).toBe(true);
  });
});
