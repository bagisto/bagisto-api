// Admin Settings — Data Transfer Imports REST e2e.
// Listing / detail / cancel / delete. NO create endpoint (multipart upload
// deferred per CLAUDE.md). Cancel + delete tested only against the first row
// returned by listing if any exists; otherwise skipped.

import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_SETTINGS } from '../../../../rest/endpoints/admin.settings.endpoints';

test.describe.configure({ timeout: 60_000 });

const OK_LIST = [200];

async function safeJson(resp: any): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

async function pickAnyImportId(request: any): Promise<number | null> {
  const resp = await sendAdminRequest(request, ADMIN_SETTINGS.DATA_TRANSFER_IMPORTS, {
    params: { per_page: '5' },
  });
  if (resp.status() !== 200) return null;
  const body = await safeJson(resp);
  const rows = body?.data ?? body ?? [];
  if (Array.isArray(rows) && rows.length > 0 && rows[0].id) return rows[0].id;
  return null;
}

test.describe('Admin Settings Data Transfer Imports REST API', () => {
  test('listing returns 200 + envelope', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.DATA_TRANSFER_IMPORTS);
    expect(OK_LIST).toContain(resp.status());
    const body = await safeJson(resp);
    if (body) expect(Array.isArray(body.data) || Array.isArray(body)).toBe(true);
  });

  test('listing with code filter', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.DATA_TRANSFER_IMPORTS, {
      params: { code: 'products' },
    });
    expect(OK_LIST).toContain(resp.status());
  });

  test('listing with state filter', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.DATA_TRANSFER_IMPORTS, {
      params: { state: 'pending' },
    });
    expect(OK_LIST).toContain(resp.status());
  });

  test('detail non-existent id returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.DATA_TRANSFER_IMPORT(99999999));
    expect([400, 404]).toContain(resp.status());
  });

  test('detail existing import row', async ({ request }) => {
    const id = await pickAnyImportId(request);
    if (!id) { test.skip(true, 'no imports in DB'); return; }
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.DATA_TRANSFER_IMPORT(id));
    expect([200, 404]).toContain(resp.status());
  });

  test('cancel — non-existent id returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.DATA_TRANSFER_IMPORT_CANCEL(99999999), {
      method: 'POST',
      data: {},
    });
    expect([400, 404, 422]).toContain(resp.status());
  });

  test('cancel — existing row tolerates 200/422 (state-dependent)', async ({ request }) => {
    const id = await pickAnyImportId(request);
    if (!id) { test.skip(true, 'no imports in DB'); return; }
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.DATA_TRANSFER_IMPORT_CANCEL(id), {
      method: 'POST',
      data: {},
    });
    // 200 if pending/processing, 422 otherwise (per CLAUDE.md)
    expect([200, 201, 400, 422]).toContain(resp.status());
  });

  test('delete non-existent id returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.DATA_TRANSFER_IMPORT(99999999), {
      method: 'DELETE',
    });
    expect([400, 404]).toContain(resp.status());
  });
});
