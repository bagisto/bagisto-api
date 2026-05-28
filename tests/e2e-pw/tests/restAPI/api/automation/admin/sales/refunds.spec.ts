// tests/restAPI/api/automation/admin/sales/refunds.spec.ts
//
// W3 — Standalone refunds listing + detail.
// (Refund preview is in orderActions.spec.ts since it's per-order.)

import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_SALES } from '../../../../rest/endpoints/admin.sales.endpoints';

test.describe.configure({ timeout: 60_000 });

async function firstRefundId(request: any): Promise<number | null> {
  const resp = await sendAdminRequest(request, ADMIN_SALES.REFUNDS, {
    params: { per_page: '1' },
  });
  if (resp.status() !== 200) return null;
  const body = await resp.json();
  const rows = body.data ?? body;
  return rows.length ? rows[0].id : null;
}

test.describe('Admin Refunds REST API', () => {
  test('listing returns paginated envelope', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.REFUNDS, {
      params: { per_page: '5' },
    });
    const status = response.status();
    console.log('refunds listing:', status);
    expect(status).toBe(200);

    const body = await response.json();
    expect(body).toHaveProperty('data');
    expect(body).toHaveProperty('meta');
    console.log(`  total=${body.meta.total} returned=${body.data.length}`);
  });

  test('listing row shape', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.REFUNDS, {
      params: { per_page: '1' },
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    if (body.data.length === 0) {
      test.skip(true, 'no refunds in DB');
      return;
    }
    const row = body.data[0];
    expect(row).toHaveProperty('id');
    expect(row).toHaveProperty('orderId');
    expect(row).toHaveProperty('state');
    expect(row).toHaveProperty('baseGrandTotal');
    expect(row).toHaveProperty('billedTo');
  });

  test('filter state returns 200', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.REFUNDS, {
      params: { state: 'refunded', per_page: '3' },
    });
    expect(response.status()).toBe(200);
  });

  test('filter order_id returns 200', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.REFUNDS, {
      params: { order_id: '1', per_page: '3' },
    });
    expect(response.status()).toBe(200);
  });

  test('filter billed_to returns 200', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.REFUNDS, {
      params: { billed_to: 'admin', per_page: '3' },
    });
    expect(response.status()).toBe(200);
  });

  test('filter base_grand_total range returns 200', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.REFUNDS, {
      params: { base_grand_total_from: '0', base_grand_total_to: '99999', per_page: '3' },
    });
    expect(response.status()).toBe(200);
  });

  test('filter created_at range returns 200', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.REFUNDS, {
      params: { created_at_from: '2020-01-01', created_at_to: '2030-12-31', per_page: '3' },
    });
    expect(response.status()).toBe(200);
  });

  test('detail returns the refund', async ({ request }) => {
    const id = await firstRefundId(request);
    if (!id) {
      test.skip(true, 'no refunds in DB');
      return;
    }
    const response = await sendAdminRequest(request, ADMIN_SALES.REFUND(id));
    const status = response.status();
    console.log(`refund detail (${id}):`, status);
    expect(status).toBe(200);

    const body = await response.json();
    expect(body.id).toBe(id);
    expect(body).toHaveProperty('orderId');
    expect(body).toHaveProperty('state');
  });

  test('detail bogus id returns 404', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.REFUND(99999999));
    const status = response.status();
    console.log('refund detail bogus:', status);
    expect([404, 400]).toContain(status);
  });
});
