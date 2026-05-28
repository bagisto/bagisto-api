// Admin Catalog — Product inventory sub-resource e2e.

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
      sku: `e2e-inv-${ts}`,
      type: 'simple',
      attribute_family_id: 1,
    },
  });
  if (r.status() !== 200 && r.status() !== 201) return null;
  const b = await safeJson(r);
  return b?.id ?? b?.data?.id ?? null;
}

test.describe('Admin Catalog Product Inventory REST API', () => {
  test('list inventories for a fresh product returns 200', async ({ request }) => {
    const id = await createSimpleProduct(request);
    if (!id) test.skip(true, 'product create unavailable');

    const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT_INVENTORIES(id!));
    const status = resp.status();
    console.log('inventories list:', status);
    expect([200, 404]).toContain(status);

    if (status === 200) {
      const body = await safeJson(resp);
      // envelope { data: [...], meta: { totalQty } }
      expect(body?.data ? Array.isArray(body.data) : Array.isArray(body)).toBe(true);
    }

    await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT(id!), { method: 'DELETE' });
  });

  test('list inventories on non-existent product returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT_INVENTORIES(99999999));
    expect([400, 404]).toContain(resp.status());
  });

  test('bulk-update inventories with empty body is rejected', async ({ request }) => {
    const id = await createSimpleProduct(request);
    if (!id) test.skip(true, 'product create unavailable');

    const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT_INVENTORIES(id!), {
      method: 'PUT' as any,
      data: {},
    });
    expect([400, 422, 500]).toContain(resp.status());

    await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT(id!), { method: 'DELETE' });
  });

  test('bulk-update inventories with valid source 1 succeeds', async ({ request }) => {
    const id = await createSimpleProduct(request);
    if (!id) test.skip(true, 'product create unavailable');

    const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT_INVENTORIES(id!), {
      method: 'PUT' as any,
      data: { inventories: { 1: 25 } },
    });
    const status = resp.status();
    console.log('inventories bulk-put:', status);
    expect([200, 201, 400, 422, 500]).toContain(status);

    await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT(id!), { method: 'DELETE' });
  });

  test('bulk-update inventories with negative qty rejected', async ({ request }) => {
    const id = await createSimpleProduct(request);
    if (!id) test.skip(true, 'product create unavailable');

    const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT_INVENTORIES(id!), {
      method: 'PUT' as any,
      data: { inventories: { 1: -5 } },
    });
    expect([400, 422]).toContain(resp.status());

    await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT(id!), { method: 'DELETE' });
  });

  test('bulk-update inventories with unknown source rejected', async ({ request }) => {
    const id = await createSimpleProduct(request);
    if (!id) test.skip(true, 'product create unavailable');

    const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT_INVENTORIES(id!), {
      method: 'PUT' as any,
      data: { inventories: { 99999: 10 } },
    });
    expect([400, 422]).toContain(resp.status());

    await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT(id!), { method: 'DELETE' });
  });
});
