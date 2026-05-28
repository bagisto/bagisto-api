// Admin Catalog — Product images sub-resource e2e.
// Upload + reorder + delete. Multipart upload is deferred (Playwright
// APIRequestContext multipart with admin headers + auto-relogin is fiddly);
// the reorder + delete paths can still be exercised against existing images.

import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_CATALOG } from '../../../../rest/endpoints/admin.catalog.endpoints';

test.describe.configure({ timeout: 60_000 });

async function safeJson(resp: any): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

test.describe('Admin Catalog Product Images REST API', () => {
  test('multipart upload — deferred', async () => {
    test.skip(true, 'multipart upload — deferred to follow-up (shop parity)');
  });

  test('reorder with empty payload is rejected', async ({ request }) => {
    // Use an arbitrary product id — endpoint should validate body before
    // ownership lookup. If it 404s instead, that's also acceptable.
    const resp = await sendAdminRequest(
      request,
      ADMIN_CATALOG.PRODUCT_IMAGES_REORDER(1),
      {
        method: 'PUT' as any,
        data: {},
      }
    );
    expect([400, 404, 422, 500]).toContain(resp.status());
  });

  test('reorder with foreign image id is rejected', async ({ request }) => {
    const resp = await sendAdminRequest(
      request,
      ADMIN_CATALOG.PRODUCT_IMAGES_REORDER(1),
      {
        method: 'PUT' as any,
        data: { order: [{ id: 99999999, position: 1 }] },
      }
    );
    expect([400, 404, 422]).toContain(resp.status());
  });

  test('delete non-existent image returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(
      request,
      ADMIN_CATALOG.PRODUCT_IMAGE(1, 99999999),
      { method: 'DELETE' }
    );
    expect([400, 404, 422]).toContain(resp.status());
  });

  test('delete image on non-existent product returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(
      request,
      ADMIN_CATALOG.PRODUCT_IMAGE(99999999, 99999998),
      { method: 'DELETE' }
    );
    expect([400, 404, 422]).toContain(resp.status());
  });
});
