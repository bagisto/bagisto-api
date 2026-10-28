import { test, expect, APIResponse } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_SETTINGS } from '../../../../rest/endpoints/admin.settings.endpoints';
import {
  createProductImport,
  deleteImport,
  productImportCsv,
  readImport,
  uniqueSku,
} from '../../../../rest/helpers/coverage/restGaps.helpers';

test.describe.configure({ timeout: 120_000 });

const UNKNOWN_ID = 99999999;

const MSG = {
  NOT_FOUND: 'Import not found.',
  CANCELLED: 'Import cancelled successfully.',
  CANNOT_CANCEL_CANCELLED: 'Only pending or processing imports can be cancelled. Current state: cancelled.',
};

async function jsonOf(response: APIResponse): Promise<any> {
  return JSON.parse(await response.text());
}

function problemDetail(body: any): string {
  return String(body?.detail ?? body?.message ?? '');
}

async function expectNotFound(response: APIResponse): Promise<void> {
  const body = await jsonOf(response);

  expect(response.status(), JSON.stringify(body)).toBe(404);
  expect(problemDetail(body)).toBe(MSG.NOT_FOUND);
}

test.describe('admin settings data transfer imports rest api', () => {
  test('listing returns 200 + envelope', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.DATA_TRANSFER_IMPORTS);
    const body = await jsonOf(resp);

    expect(resp.status(), JSON.stringify(body)).toBe(200);
    expect(Array.isArray(body.data)).toBe(true);
    expect(body).toHaveProperty('meta');
  });

  test('listing with code filter', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.DATA_TRANSFER_IMPORTS, {
      params: { code: 'products' },
    });
    const body = await jsonOf(resp);

    expect(resp.status(), JSON.stringify(body)).toBe(200);
    for (const row of body.data) {
      expect(row.code).toBe('products');
    }
  });

  test('listing with state filter', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.DATA_TRANSFER_IMPORTS, {
      params: { state: 'pending' },
    });
    const body = await jsonOf(resp);

    expect(resp.status(), JSON.stringify(body)).toBe(200);
    for (const row of body.data) {
      expect(row.state).toBe('pending');
    }
  });

  test('detail non-existent id returns 404', async ({ request }) => {
    await expectNotFound(await sendAdminRequest(request, ADMIN_SETTINGS.DATA_TRANSFER_IMPORT(UNKNOWN_ID)));
  });

  test.describe('with an uploaded import', () => {
    let importId: number;

    test.beforeEach(async ({ request }) => {
      importId = 0;
      const sku = uniqueSku('e2e-import-settings');
      importId = (await createProductImport(request, sku, productImportCsv(sku, 'simple'))).id;
    });

    test.afterEach(async ({ request }) => {
      if (importId) {
        await deleteImport(request, importId);
      }
    });

    test('detail returns the pending import', async ({ request }) => {
      const resp = await sendAdminRequest(request, ADMIN_SETTINGS.DATA_TRANSFER_IMPORT(importId));
      const body = await jsonOf(resp);

      expect(resp.status(), JSON.stringify(body)).toBe(200);
      expect(body.id).toBe(importId);
      expect(body.code).toBe('products');
      expect(body.state).toBe('pending');
    });

    test('cancel moves a pending import to cancelled and refuses a second cancel', async ({ request }) => {
      const resp = await sendAdminRequest(request, ADMIN_SETTINGS.DATA_TRANSFER_IMPORT_CANCEL(importId), {
        method: 'POST',
        data: {},
      });
      const body = await jsonOf(resp);

      expect(resp.status(), JSON.stringify(body)).toBe(200);
      expect(body.id).toBe(importId);
      expect(body.state).toBe('cancelled');
      expect(body.success).toBe(true);
      expect(body.message).toBe(MSG.CANCELLED);
      expect((await readImport(request, importId)).state).toBe('cancelled');

      const again = await sendAdminRequest(request, ADMIN_SETTINGS.DATA_TRANSFER_IMPORT_CANCEL(importId), {
        method: 'POST',
        data: {},
      });
      const againBody = await jsonOf(again);

      expect(again.status(), JSON.stringify(againBody)).toBe(422);
      expect(problemDetail(againBody)).toBe(MSG.CANNOT_CANCEL_CANCELLED);
    });
  });

  test('cancel non-existent id returns 404', async ({ request }) => {
    await expectNotFound(await sendAdminRequest(request, ADMIN_SETTINGS.DATA_TRANSFER_IMPORT_CANCEL(UNKNOWN_ID), {
      method: 'POST',
      data: {},
    }));
  });

  test('delete non-existent id returns 404', async ({ request }) => {
    await expectNotFound(await sendAdminRequest(request, ADMIN_SETTINGS.DATA_TRANSFER_IMPORT(UNKNOWN_ID), {
      method: 'DELETE',
    }));
  });
});
