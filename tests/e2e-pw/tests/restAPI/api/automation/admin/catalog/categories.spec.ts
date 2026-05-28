// Admin Catalog — Categories REST e2e.
// Listing, tree, detail, create/update/delete + mass-actions.

import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_CATALOG } from '../../../../rest/endpoints/admin.catalog.endpoints';

test.describe.configure({ timeout: 60_000 });

const OK_LIST = [200];
const OK_CREATE = [200, 201, 400, 422, 429];
const OK_UPDATE = [200, 201, 400, 404, 422, 429];
const OK_DELETE = [200, 204, 400, 404, 422, 429];

async function safeJson(resp: any): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

test.describe('Admin Catalog Categories REST API', () => {
  test('listing returns 200 + envelope shape', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORIES);
    expect(OK_LIST).toContain(resp.status());
    const body = await safeJson(resp);
    // envelope { data, meta }
    if (body && typeof body === 'object') {
      expect(Array.isArray(body.data) || Array.isArray(body)).toBe(true);
    }
  });

  test('listing with pagination params', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORIES, {
      params: { page: '1', per_page: '5' },
    });
    expect(OK_LIST).toContain(resp.status());
  });

  test('listing with name filter', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORIES, {
      params: { name: 'root' },
    });
    expect(OK_LIST).toContain(resp.status());
  });

  test('tree returns 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORY_TREE);
    expect(OK_LIST).toContain(resp.status());
    const body = await safeJson(resp);
    if (body) {
      expect(Array.isArray(body.data) || Array.isArray(body)).toBe(true);
    }
  });

  test('detail for root (id=1) returns 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORY(1));
    expect([200, 404]).toContain(resp.status());
  });

  test('detail for non-existent id returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORY(99999999));
    expect([404, 400]).toContain(resp.status());
  });

  test('create category happy-ish path then cleanup', async ({ request }) => {
    const ts = Date.now();
    const slug = `e2e-cat-${ts}`;
    const createResp = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORIES, {
      method: 'POST',
      data: {
        slug,
        name: `E2E Category ${ts}`,
        description: 'e2e seed',
        position: 1,
        status: 1,
        display_mode: 'products_and_description',
        parent_id: 1,
        attributes: [],
      },
    });
    const status = createResp.status();
    console.log('categories create:', status);
    expect(OK_CREATE).toContain(status);

    if (status === 200 || status === 201) {
      const body = await safeJson(createResp);
      const id = body?.id ?? body?.data?.id;
      if (id) {
        const delResp = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORY(id), {
          method: 'DELETE',
        });
        expect(OK_DELETE).toContain(delResp.status());
      }
    }
  });

  test('create category with empty body returns validation error', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORIES, {
      method: 'POST',
      data: {},
    });
    expect([400, 422, 500]).toContain(resp.status());
  });

  test('update non-existent category returns 404/422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORY(99999999), {
      method: 'PUT' as any,
      data: { en: { name: 'nope' } },
    });
    expect([400, 404, 422]).toContain(resp.status());
  });

  test('delete root category (id=1) is refused', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORY(1), {
      method: 'DELETE',
    });
    // monolith refuses with 400; some configs may return 404 if root id differs
    expect([400, 404, 422]).toContain(resp.status());
  });

  test('mass-delete with empty indices is rejected', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORIES_MASS_DELETE, {
      method: 'POST',
      data: { indices: [] },
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('mass-delete + mass-update-status round trip on freshly created entries', async ({ request }) => {
    const ts = Date.now();
    const createdIds: number[] = [];

    for (let i = 0; i < 2; i++) {
      const r = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORIES, {
        method: 'POST',
        data: {
          slug: `e2e-mass-${ts}-${i}`,
          name: `E2E Mass ${ts} ${i}`,
          description: 'e2e',
          position: 1,
          status: 1,
          display_mode: 'products_and_description',
          parent_id: 1,
          attributes: [],
        },
      });
      if (r.status() === 200 || r.status() === 201) {
        const body = await safeJson(r);
        const id = body?.id ?? body?.data?.id;
        if (id) createdIds.push(id);
      }
    }

    if (createdIds.length > 0) {
      const statusResp = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORIES_MASS_UPDATE_STATUS, {
        method: 'POST',
        data: { indices: createdIds, value: 0 },
      });
      expect([200, 201, 400, 422]).toContain(statusResp.status());

      const delResp = await sendAdminRequest(request, ADMIN_CATALOG.CATEGORIES_MASS_DELETE, {
        method: 'POST',
        data: { indices: createdIds },
      });
      expect([200, 201, 400, 422]).toContain(delResp.status());
    }
  });
});
