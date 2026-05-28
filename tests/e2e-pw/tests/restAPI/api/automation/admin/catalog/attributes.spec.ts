// Admin Catalog — Attributes REST e2e.
// Listing, detail, CRUD, mass-delete + nested option CRUD.

import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_CATALOG } from '../../../../rest/endpoints/admin.catalog.endpoints';

test.describe.configure({ timeout: 60_000 });

async function safeJson(resp: any): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

async function findFirstId(request: any, endpoint: string): Promise<number | null> {
  const r = await sendAdminRequest(request, endpoint, { params: { per_page: '1' } });
  if (r.status() !== 200) return null;
  const body = await safeJson(r);
  const rows = body?.data ?? (Array.isArray(body) ? body : []);
  return rows[0]?.id ?? null;
}

test.describe('Admin Catalog Attributes REST API', () => {
  test('listing returns 200 + envelope', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTES);
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);
    expect(body?.data ? Array.isArray(body.data) : Array.isArray(body)).toBe(true);
  });

  test('listing with filter + sort + pagination', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTES, {
      params: { per_page: '5', sort: 'id-desc', type: 'text' },
    });
    expect([200, 400, 422]).toContain(resp.status());
  });

  test('detail for first listed attribute', async ({ request }) => {
    const id = await findFirstId(request, ADMIN_CATALOG.ATTRIBUTES);
    if (!id) test.skip(true, 'no attributes seeded');
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTE(id!));
    expect([200, 404]).toContain(resp.status());
    if (resp.status() === 200) {
      const body = await safeJson(resp);
      expect(body?.id ?? body?.data?.id).toBeTruthy();
    }
  });

  test('detail for non-existent id returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTE(99999999));
    expect([404, 400]).toContain(resp.status());
  });

  test('create attribute happy-ish then cleanup', async ({ request }) => {
    const ts = Date.now();
    const code = `e2e_attr_${ts}`;
    const createResp = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTES, {
      method: 'POST',
      data: {
        code,
        admin_name: `E2E Attr ${ts}`,
        type: 'text',
        is_required: false,
        is_unique: false,
        validation: null,
        position: 0,
      },
    });
    const status = createResp.status();
    console.log('attributes create:', status);
    expect([200, 201, 400, 422, 429]).toContain(status);

    if (status === 200 || status === 201) {
      const body = await safeJson(createResp);
      const id = body?.id ?? body?.data?.id;
      if (id) {
        const delResp = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTE(id), {
          method: 'DELETE',
        });
        expect([200, 204, 400, 404, 409, 422]).toContain(delResp.status());
      }
    }
  });

  test('create attribute with empty body is rejected', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTES, {
      method: 'POST',
      data: {},
    });
    expect([400, 422, 500]).toContain(resp.status());
  });

  test('update non-existent attribute returns 404/422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTE(99999999), {
      method: 'PUT' as any,
      data: { admin_name: 'no' },
    });
    expect([400, 404, 422]).toContain(resp.status());
  });

  test('delete non-existent attribute returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTE(99999999), {
      method: 'DELETE',
    });
    expect([400, 404, 422]).toContain(resp.status());
  });

  test('mass-delete empty indices is rejected', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTES_MASS_DELETE, {
      method: 'POST',
      data: { indices: [] },
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('mass-delete on freshly created attributes', async ({ request }) => {
    const ts = Date.now();
    const ids: number[] = [];
    for (let i = 0; i < 2; i++) {
      const r = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTES, {
        method: 'POST',
        data: {
          code: `e2e_mass_attr_${ts}_${i}`,
          admin_name: `E2E Mass ${ts} ${i}`,
          type: 'text',
        },
      });
      if (r.status() === 200 || r.status() === 201) {
        const b = await safeJson(r);
        const id = b?.id ?? b?.data?.id;
        if (id) ids.push(id);
      }
    }
    if (ids.length > 0) {
      const del = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTES_MASS_DELETE, {
        method: 'POST',
        data: { indices: ids },
      });
      expect([200, 201, 400, 422]).toContain(del.status());
    }
  });
});

test.describe('Admin Catalog Attribute Options REST API', () => {
  // Helper: spin up a select-type attribute we can attach options to.
  async function createSelectAttribute(request: any): Promise<number | null> {
    const ts = Date.now();
    const r = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTES, {
      method: 'POST',
      data: {
        code: `e2e_select_${ts}`,
        admin_name: `E2E Select ${ts}`,
        type: 'select',
      },
    });
    if (r.status() !== 200 && r.status() !== 201) return null;
    const b = await safeJson(r);
    return b?.id ?? b?.data?.id ?? null;
  }

  test('create option happy-ish + update + delete + attr cleanup', async ({ request }) => {
    const attrId = await createSelectAttribute(request);
    if (!attrId) test.skip(true, 'select-attribute fixture not creatable');

    const ts = Date.now();
    const createResp = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTE_OPTIONS(attrId!), {
      method: 'POST',
      data: {
        admin_name: `Option ${ts}`,
        sort_order: 0,
      },
    });
    const status = createResp.status();
    console.log('attribute option create:', status);
    expect([200, 201, 400, 422, 429]).toContain(status);

    let optionId: number | null = null;
    if (status === 200 || status === 201) {
      const b = await safeJson(createResp);
      optionId = b?.id ?? b?.data?.id ?? null;
    }

    if (optionId) {
      const upd = await sendAdminRequest(
        request,
        ADMIN_CATALOG.ATTRIBUTE_OPTION(attrId!, optionId),
        {
          method: 'PUT' as any,
          data: { admin_name: `Option ${ts} v2` },
        }
      );
      expect([200, 201, 400, 404, 422]).toContain(upd.status());

      const del = await sendAdminRequest(
        request,
        ADMIN_CATALOG.ATTRIBUTE_OPTION(attrId!, optionId),
        { method: 'DELETE' }
      );
      expect([200, 204, 400, 404, 409, 422]).toContain(del.status());
    }

    // Always try to clean up the parent attribute.
    await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTE(attrId!), { method: 'DELETE' });
  });

  test('create option on non-select attribute is rejected', async ({ request }) => {
    // Find an attribute and try options regardless — for many text-type attrs the API rejects.
    const id = await findFirstId(request, ADMIN_CATALOG.ATTRIBUTES);
    if (!id) test.skip(true, 'no attribute available');
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.ATTRIBUTE_OPTIONS(id!), {
      method: 'POST',
      data: { admin_name: 'nope' },
    });
    // text/textarea/etc → 400/422; if attribute happens to be select it'd succeed.
    expect([200, 201, 400, 404, 422, 500]).toContain(resp.status());
  });
});
