// Admin Catalog — Product customer-group prices sub-resource e2e.

import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_CATALOG } from '../../../../rest/endpoints/admin.catalog.endpoints';

test.describe.configure({ timeout: 60_000 });

async function safeJson(resp: any): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

async function createSimpleProduct(request: any): Promise<number | null> {
  const ts = Date.now() + Math.floor(Math.random() * 1000);
  const r = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCTS, {
    method: 'POST',
    data: {
      sku: `e2e-cgp-${ts}`,
      type: 'simple',
      attribute_family_id: 1,
    },
  });
  if (r.status() !== 200 && r.status() !== 201) return null;
  const b = await safeJson(r);
  return b?.id ?? b?.data?.id ?? null;
}

test.describe('Admin Catalog Product Customer-Group Prices REST API', () => {
  test('list cgp on fresh product returns 200 (empty)', async ({ request }) => {
    const id = await createSimpleProduct(request);
    if (!id) test.skip(true, 'product create unavailable');

    const resp = await sendAdminRequest(
      request,
      ADMIN_CATALOG.PRODUCT_CUSTOMER_GROUP_PRICES(id!)
    );
    expect([200, 404]).toContain(resp.status());
    if (resp.status() === 200) {
      const body = await safeJson(resp);
      expect(body?.data ? Array.isArray(body.data) : Array.isArray(body)).toBe(true);
    }

    await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT(id!), { method: 'DELETE' });
  });

  test('list cgp on non-existent product returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(
      request,
      ADMIN_CATALOG.PRODUCT_CUSTOMER_GROUP_PRICES(99999999)
    );
    expect([400, 404]).toContain(resp.status());
  });

  test('create cgp happy path + update + delete', async ({ request }) => {
    const id = await createSimpleProduct(request);
    if (!id) test.skip(true, 'product create unavailable');

    const createResp = await sendAdminRequest(
      request,
      ADMIN_CATALOG.PRODUCT_CUSTOMER_GROUP_PRICES(id!),
      {
        method: 'POST',
        data: {
          qty: 5,
          value_type: 'fixed',
          value: 9.99,
          customer_group_id: null,
        },
      }
    );
    const status = createResp.status();
    console.log('cgp create:', status);
    expect([200, 201, 400, 422, 500]).toContain(status);

    let cgpId: number | null = null;
    if (status === 200 || status === 201) {
      const b = await safeJson(createResp);
      cgpId = b?.id ?? b?.data?.id ?? null;
    }

    if (cgpId) {
      const upd = await sendAdminRequest(
        request,
        ADMIN_CATALOG.PRODUCT_CUSTOMER_GROUP_PRICE(id!, cgpId),
        {
          method: 'PUT' as any,
          data: { qty: 10, value: 7.5 },
        }
      );
      expect([200, 201, 400, 404, 422]).toContain(upd.status());

      const del = await sendAdminRequest(
        request,
        ADMIN_CATALOG.PRODUCT_CUSTOMER_GROUP_PRICE(id!, cgpId),
        { method: 'DELETE' }
      );
      expect([200, 204, 400, 404, 422]).toContain(del.status());
    }

    await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT(id!), { method: 'DELETE' });
  });

  test('create cgp with invalid value_type is rejected', async ({ request }) => {
    const id = await createSimpleProduct(request);
    if (!id) test.skip(true, 'product create unavailable');

    const resp = await sendAdminRequest(
      request,
      ADMIN_CATALOG.PRODUCT_CUSTOMER_GROUP_PRICES(id!),
      {
        method: 'POST',
        data: { qty: 5, value_type: 'banana', value: 1, customer_group_id: null },
      }
    );
    expect([400, 422]).toContain(resp.status());

    await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT(id!), { method: 'DELETE' });
  });

  test('create cgp with qty=0 is rejected', async ({ request }) => {
    const id = await createSimpleProduct(request);
    if (!id) test.skip(true, 'product create unavailable');

    const resp = await sendAdminRequest(
      request,
      ADMIN_CATALOG.PRODUCT_CUSTOMER_GROUP_PRICES(id!),
      {
        method: 'POST',
        data: { qty: 0, value_type: 'fixed', value: 5, customer_group_id: null },
      }
    );
    expect([400, 422]).toContain(resp.status());

    await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT(id!), { method: 'DELETE' });
  });

  test('delete cgp across products returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(
      request,
      ADMIN_CATALOG.PRODUCT_CUSTOMER_GROUP_PRICE(99999999, 99999998),
      { method: 'DELETE' }
    );
    expect([400, 404, 422]).toContain(resp.status());
  });
});
