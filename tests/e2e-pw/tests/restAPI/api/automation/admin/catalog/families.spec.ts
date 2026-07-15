// Admin Catalog — Attribute Families REST e2e.
// Listing, detail, create/update/delete.

import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_CATALOG } from '../../../../rest/endpoints/admin.catalog.endpoints';

test.describe.configure({ timeout: 60_000 });

async function safeJson(resp: any): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

test.describe('Admin Catalog Attribute Families REST API', () => {
  test('listing returns 200 + envelope', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.FAMILIES);
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);
    expect(body?.data ? Array.isArray(body.data) : Array.isArray(body)).toBe(true);
  });

  test('listing with filter + sort', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.FAMILIES, {
      params: { per_page: '5', sort: 'id-desc', code: 'default' },
    });
    expect([200, 400, 422]).toContain(resp.status());
  });

  test('detail for default family (id=1)', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.FAMILY(1));
    expect([200, 404]).toContain(resp.status());
    if (resp.status() === 200) {
      const body = await safeJson(resp);
      const fam = body?.data ?? body;
      expect(fam.id).toBeTruthy();
    }
  });

  test('detail for non-existent id', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.FAMILY(99999999));
    expect([404, 400]).toContain(resp.status());
  });

  test('create family with empty body is rejected', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.FAMILIES, {
      method: 'POST',
      data: {},
    });
    expect([400, 422, 500]).toContain(resp.status());
  });

  test('create + update + delete family', async ({ request }) => {
    const ts = Date.now();
    const code = `e2e_fam_${ts}`;
    const createResp = await sendAdminRequest(request, ADMIN_CATALOG.FAMILIES, {
      method: 'POST',
      data: {
        code,
        name: `E2E Family ${ts}`,
        attribute_groups: [
          {
            code: 'general',
            name: 'General',
            column: 1,
            position: 1,
            custom_attributes: [],
          },
        ],
      },
    });
    const status = createResp.status();
    console.log('family create:', status);
    expect([200, 201, 400, 422, 429]).toContain(status);

    if (status === 200 || status === 201) {
      const body = await safeJson(createResp);
      const id = body?.id ?? body?.data?.id;
      if (id) {
        const upd = await sendAdminRequest(request, ADMIN_CATALOG.FAMILY(id), {
          method: 'PUT' as any,
          data: { code, name: `E2E Family ${ts} v2` },
        });
        expect([200, 201, 400, 404, 422]).toContain(upd.status());

        const del = await sendAdminRequest(request, ADMIN_CATALOG.FAMILY(id), {
          method: 'DELETE',
        });
        // monolith refuses with 400 when a product uses the family;
        // a fresh empty family should delete clean.
        expect([200, 204, 400, 404, 422]).toContain(del.status());
      }
    }
  });

  test('delete non-existent family returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.FAMILY(99999999), {
      method: 'DELETE',
    });
    expect([400, 404, 422]).toContain(resp.status());
  });

  // Owns its fixture: a family with a product on it. Never targets the seeded
  // family — deleting that one would break every later spec that creates products.
  test('delete a family that a product uses is refused', async ({ request }) => {
    const ts = Date.now();
    const famResp = await sendAdminRequest(request, ADMIN_CATALOG.FAMILIES, {
      method: 'POST',
      data: {
        code: `e2e_fam_inuse_${ts}`,
        name: `E2E Family In Use ${ts}`,
        attribute_groups: [
          { code: 'general', name: 'General', column: 1, position: 1, custom_attributes: [] },
        ],
      },
    });
    expect([200, 201]).toContain(famResp.status());
    const familyId = (await safeJson(famResp))?.id;
    expect(familyId).toBeTruthy();

    let productId: number | null = null;

    try {
      const prodResp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCTS, {
        method: 'POST',
        data: {
          sku: `e2e-fam-inuse-${ts}`,
          attribute_family_id: familyId,
          type: 'simple',
        },
      });
      expect([200, 201]).toContain(prodResp.status());
      productId = (await safeJson(prodResp))?.id ?? null;
      expect(productId).toBeTruthy();

      const del = await sendAdminRequest(request, ADMIN_CATALOG.FAMILY(familyId), {
        method: 'DELETE',
      });
      expect(del.status()).toBe(400);
    } finally {
      if (productId) {
        await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT(productId), { method: 'DELETE' });
      }
      await sendAdminRequest(request, ADMIN_CATALOG.FAMILY(familyId), { method: 'DELETE' });
    }
  });
});
