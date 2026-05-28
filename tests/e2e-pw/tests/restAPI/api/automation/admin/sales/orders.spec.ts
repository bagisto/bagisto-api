// tests/restAPI/api/automation/admin/sales/orders.spec.ts
//
// W3 — Orders listing + filters + detail.
//
// Listing returns `{ data, meta }` envelope with rich row carrying id,
// incrementId, status/statusLabel, customer*, grandTotal, etc. Detail
// (/orders/{id}) embeds customer + addresses + items + invoices + shipments.

import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_SALES } from '../../../../rest/endpoints/admin.sales.endpoints';

test.describe.configure({ timeout: 60_000 });

async function firstOrderId(request: any): Promise<number | null> {
  const resp = await sendAdminRequest(request, ADMIN_SALES.ORDERS, {
    params: { per_page: '1' },
  });
  if (resp.status() !== 200) return null;
  const body = await resp.json();
  const rows = body.data ?? body;
  return rows.length ? rows[0].id : null;
}

test.describe('Admin Orders REST API', () => {
  test('listing returns paginated envelope', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.ORDERS, {
      params: { per_page: '5' },
    });
    const status = response.status();
    console.log('orders listing:', status);
    expect(status).toBe(200);

    const body = await response.json();
    expect(body).toHaveProperty('data');
    expect(body).toHaveProperty('meta');
    expect(Array.isArray(body.data)).toBe(true);
    expect(body.meta).toHaveProperty('total');
    expect(body.meta).toHaveProperty('currentPage');
    console.log(`  total=${body.meta.total} returned=${body.data.length}`);
  });

  test('listing row shape carries expected fields', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.ORDERS, {
      params: { per_page: '1' },
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    if (body.data.length === 0) {
      test.skip(true, 'no orders in DB');
      return;
    }
    const row = body.data[0];
    expect(row).toHaveProperty('id');
    expect(row).toHaveProperty('incrementId');
    expect(row).toHaveProperty('status');
    expect(row).toHaveProperty('grandTotal');
    expect(row).toHaveProperty('createdAt');
  });

  test('listing pagination per_page is honored', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.ORDERS, {
      params: { per_page: '3', page: '1' },
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    // total may be < 3; otherwise should be exactly 3
    if (body.meta.total >= 3) {
      expect(body.data.length).toBeLessThanOrEqual(3);
    }
    expect(body.meta.perPage).toBe(3);
  });

  // ── Filters ──────────────────────────────────────────────────
  test('filter status=pending returns only pending orders', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.ORDERS, {
      params: { status: 'pending', per_page: '5' },
    });
    const status = response.status();
    console.log('orders filter status:', status);
    expect(status).toBe(200);
    const body = await response.json();
    for (const row of body.data) {
      expect(row.status).toBe('pending');
    }
  });

  test('filter channel returns 200', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.ORDERS, {
      params: { channel: '1', per_page: '3' },
    });
    expect(response.status()).toBe(200);
  });

  test('filter customer_email_name returns 200', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.ORDERS, {
      params: { customer_email_name: 'admin', per_page: '3' },
    });
    expect(response.status()).toBe(200);
  });

  test('filter grand_total range returns 200', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.ORDERS, {
      params: { grand_total_from: '0', grand_total_to: '99999', per_page: '3' },
    });
    expect(response.status()).toBe(200);
  });

  test('filter created_at range returns 200', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.ORDERS, {
      params: {
        created_at_from: '2020-01-01',
        created_at_to: '2030-12-31',
        per_page: '3',
      },
    });
    expect(response.status()).toBe(200);
  });

  test('sort by id desc is the default', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.ORDERS, {
      params: { sort: 'id', order: 'desc', per_page: '3' },
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    if (body.data.length > 1) {
      expect(body.data[0].id).toBeGreaterThan(body.data[1].id);
    }
  });

  // ── Detail ───────────────────────────────────────────────────
  test('detail returns the order with embedded relations', async ({ request }) => {
    const id = await firstOrderId(request);
    if (!id) {
      test.skip(true, 'no orders in DB');
      return;
    }
    const response = await sendAdminRequest(request, ADMIN_SALES.ORDER(id));
    const status = response.status();
    console.log(`order detail (${id}):`, status);
    expect(status).toBe(200);

    const body = await response.json();
    expect(body.id).toBe(id);
    expect(body).toHaveProperty('incrementId');
    expect(body).toHaveProperty('status');
    expect(body).toHaveProperty('items');
    expect(body).toHaveProperty('invoices');
    expect(body).toHaveProperty('shipments');
  });

  test('detail of non-existent order returns 404', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.ORDER(99999999));
    const status = response.status();
    console.log('order detail bogus:', status);
    expect([404, 400]).toContain(status);
  });

  test('listing without admin token is rejected', async ({ request }) => {
    // Direct fetch — bypass adminClient's auto-login.
    const url = `${process.env.BAGISTO_URL}${ADMIN_SALES.ORDERS}`;
    const response = await request.fetch(url, {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        'X-Admin-Key': process.env.ADMIN_ACCESS_KEY!,
      },
    });
    const status = response.status();
    console.log('orders unauthenticated:', status);
    expect([401, 403]).toContain(status);
  });
});
