// tests/restAPI/api/automation/admin/sales/shipments.spec.ts
//
// W3 — Standalone shipments listing + detail.

import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_SALES } from '../../../../rest/endpoints/admin.sales.endpoints';

test.describe.configure({ timeout: 60_000 });

async function firstShipmentId(request: any): Promise<number | null> {
  const resp = await sendAdminRequest(request, ADMIN_SALES.SHIPMENTS, {
    params: { per_page: '1' },
  });
  if (resp.status() !== 200) return null;
  const body = await resp.json();
  const rows = body.data ?? body;
  return rows.length ? rows[0].id : null;
}

test.describe('Admin Shipments REST API', () => {
  test('listing returns paginated envelope', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.SHIPMENTS, {
      params: { per_page: '5' },
    });
    const status = response.status();
    console.log('shipments listing:', status);
    expect(status).toBe(200);

    const body = await response.json();
    expect(body).toHaveProperty('data');
    expect(body).toHaveProperty('meta');
    console.log(`  total=${body.meta.total} returned=${body.data.length}`);
  });

  test('listing row shape', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.SHIPMENTS, {
      params: { per_page: '1' },
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    if (body.data.length === 0) {
      test.skip(true, 'no shipments in DB');
      return;
    }
    const row = body.data[0];
    expect(row).toHaveProperty('id');
    expect(row).toHaveProperty('orderId');
    expect(row).toHaveProperty('totalQty');
    expect(row).toHaveProperty('inventorySourceName');
    expect(row).toHaveProperty('shippedTo');
  });

  test('filter order_id returns 200', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.SHIPMENTS, {
      params: { order_id: '1', per_page: '3' },
    });
    expect(response.status()).toBe(200);
  });

  test('filter billed_to returns 200', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.SHIPMENTS, {
      params: { shipped_to: 'admin', per_page: '3' },
    });
    expect(response.status()).toBe(200);
  });

  test('filter created_at range returns 200', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.SHIPMENTS, {
      params: { created_at_from: '2020-01-01', created_at_to: '2030-12-31', per_page: '3' },
    });
    expect(response.status()).toBe(200);
  });

  test('sort id desc default', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.SHIPMENTS, {
      params: { sort: 'id', order: 'desc', per_page: '3' },
    });
    expect(response.status()).toBe(200);
  });

  test('detail returns the shipment with items', async ({ request }) => {
    const id = await firstShipmentId(request);
    if (!id) {
      test.skip(true, 'no shipments in DB');
      return;
    }
    const response = await sendAdminRequest(request, ADMIN_SALES.SHIPMENT(id));
    const status = response.status();
    console.log(`shipment detail (${id}):`, status);
    expect(status).toBe(200);

    const body = await response.json();
    expect(body.id).toBe(id);
    expect(body).toHaveProperty('orderId');
    expect(body).toHaveProperty('items');
    expect(body).toHaveProperty('inventorySourceName');
  });

  test('detail bogus id returns 404', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.SHIPMENT(99999999));
    const status = response.status();
    console.log('shipment detail bogus:', status);
    expect([404, 400]).toContain(status);
  });
});
