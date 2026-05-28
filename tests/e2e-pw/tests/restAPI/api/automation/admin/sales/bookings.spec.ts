// tests/restAPI/api/automation/admin/sales/bookings.spec.ts
//
// W3 — Bookings listing + detail (one row per booking line on an order).

import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_SALES } from '../../../../rest/endpoints/admin.sales.endpoints';

test.describe.configure({ timeout: 60_000 });

async function firstBookingId(request: any): Promise<number | null> {
  const resp = await sendAdminRequest(request, ADMIN_SALES.BOOKINGS, {
    params: { per_page: '1' },
  });
  if (resp.status() !== 200) return null;
  const body = await resp.json();
  const rows = body.data ?? body;
  return rows.length ? rows[0].id : null;
}

test.describe('Admin Bookings REST API', () => {
  test('listing returns paginated envelope', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.BOOKINGS, {
      params: { per_page: '5' },
    });
    const status = response.status();
    console.log('bookings listing:', status);
    expect(status).toBe(200);

    const body = await response.json();
    expect(body).toHaveProperty('data');
    expect(body).toHaveProperty('meta');
    console.log(`  total=${body.meta.total} returned=${body.data.length}`);
  });

  test('listing row shape carries from/to in both raw + formatted forms', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.BOOKINGS, {
      params: { per_page: '1' },
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    if (body.data.length === 0) {
      test.skip(true, 'no bookings in DB');
      return;
    }
    const row = body.data[0];
    expect(row).toHaveProperty('id');
    expect(row).toHaveProperty('orderId');
    expect(row).toHaveProperty('productSku');
    expect(row).toHaveProperty('qty');
    expect(row).toHaveProperty('from');
    expect(row).toHaveProperty('to');
    expect(row).toHaveProperty('fromFormatted');
    expect(row).toHaveProperty('toFormatted');
  });

  test('filter order_id returns 200', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.BOOKINGS, {
      params: { order_id: '1', per_page: '3' },
    });
    expect(response.status()).toBe(200);
  });

  test('filter product_id returns 200', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.BOOKINGS, {
      params: { product_id: '1', per_page: '3' },
    });
    expect(response.status()).toBe(200);
  });

  test('filter qty returns 200', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.BOOKINGS, {
      params: { qty: '1', per_page: '3' },
    });
    expect(response.status()).toBe(200);
  });

  test('filter from/to ISO date range returns 200', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.BOOKINGS, {
      params: { from: '2020-01-01', to: '2030-12-31', per_page: '3' },
    });
    expect(response.status()).toBe(200);
  });

  test('detail returns the booking with embedded order summary', async ({ request }) => {
    const id = await firstBookingId(request);
    if (!id) {
      test.skip(true, 'no bookings in DB');
      return;
    }
    const response = await sendAdminRequest(request, ADMIN_SALES.BOOKING(id));
    const status = response.status();
    console.log(`booking detail (${id}):`, status);
    expect(status).toBe(200);

    const body = await response.json();
    expect(body.id).toBe(id);
    expect(body).toHaveProperty('orderId');
    expect(body).toHaveProperty('bookingType');
    expect(body).toHaveProperty('productSku');
    expect(body).toHaveProperty('order');
  });

  test('detail bogus id returns 404', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.BOOKING(99999999));
    const status = response.status();
    console.log('booking detail bogus:', status);
    expect([404, 400]).toContain(status);
  });
});
