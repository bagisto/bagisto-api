import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_SALES } from '../../../../rest/endpoints/admin.sales.endpoints';
import {
  UNKNOWN_ID,
  expectProblem,
  invoiceOrder,
  placeOrder,
  safeJson,
} from '../../../../rest/helpers/coverage/adminSales.helpers';

test.describe.configure({ timeout: 240_000 });

const INVOICE_NOT_FOUND = 'Invoice not found.';

async function createInvoice(request: APIRequestContext): Promise<{ invoiceId: number; orderId: number; incrementId: string }> {
  const order = await placeOrder(request);
  const invoice = await invoiceOrder(request, order.orderId);

  return { invoiceId: invoice.id, orderId: order.orderId, incrementId: order.incrementId };
}

test.describe('admin invoices rest api', () => {
  test('listing returns paginated envelope', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.INVOICES, {
      params: { per_page: '5' },
    });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(Array.isArray(body.data)).toBe(true);
    expect(body.data.length).toBeLessThanOrEqual(5);
    expect(body).toHaveProperty('meta');
  });

  test('listing row shape carries slim fields', async ({ request }) => {
    const { invoiceId, orderId, incrementId } = await createInvoice(request);

    const response = await sendAdminRequest(request, ADMIN_SALES.INVOICES, {
      params: { order_id: incrementId, per_page: '50' },
    });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);

    const row = body.data.find((entry: any) => entry.id === invoiceId);
    expect(row, JSON.stringify(body.data)).toBeDefined();
    expect(row.order.id).toBe(orderId);
    expect(row).toHaveProperty('state');
    expect(row).toHaveProperty('baseGrandTotal');
    expect(row).toHaveProperty('formattedBaseGrandTotal');
  });

  test('filter state returns 200', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.INVOICES, {
      params: { state: 'paid', per_page: '3' },
    });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    for (const row of body.data) {
      expect(row.state).toBe('paid');
    }
  });

  test('filter order_id returns 200', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.INVOICES, {
      params: { order_id: '1', per_page: '3' },
    });
    expect(response.status()).toBe(200);
  });

  test('filter base_grand_total range returns 200', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.INVOICES, {
      params: { base_grand_total_from: '0', base_grand_total_to: '99999', per_page: '3' },
    });
    expect(response.status()).toBe(200);
  });

  test('filter created_at range returns 200', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.INVOICES, {
      params: { created_at_from: '2020-01-01', created_at_to: '2030-12-31', per_page: '3' },
    });
    expect(response.status()).toBe(200);
  });

  test('sort id desc default', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.INVOICES, {
      params: { sort: 'id', order: 'desc', per_page: '3' },
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    if (body.data.length > 1) {
      expect(body.data[0].id).toBeGreaterThanOrEqual(body.data[1].id);
    }
  });

  test('detail returns the invoice with items', async ({ request }) => {
    const { invoiceId } = await createInvoice(request);

    const response = await sendAdminRequest(request, ADMIN_SALES.INVOICE(invoiceId));
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body.id).toBe(invoiceId);
    expect(body).toHaveProperty('state');
    expect(body.items).toHaveLength(1);
    expect(body).toHaveProperty('grandTotal');
  });

  test('detail bogus id returns 404', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.INVOICE(UNKNOWN_ID));

    await expectProblem(response, 404, INVOICE_NOT_FOUND);
  });

  test('print returns the invoice as a pdf download', async ({ request }) => {
    const { invoiceId } = await createInvoice(request);

    const response = await sendAdminRequest(request, ADMIN_SALES.INVOICE_PRINT(invoiceId), {
      headers: { Accept: 'application/pdf' },
    });
    const headers = response.headers();

    expect(response.status(), await response.text()).toBe(200);
    expect(headers['content-type']).toContain('application/pdf');
    expect(headers['content-disposition']).toContain(`invoice-${invoiceId}.pdf`);
    expect((await response.body()).subarray(0, 4).toString()).toBe('%PDF');
  });

  test('print bogus id returns 404', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.INVOICE_PRINT(UNKNOWN_ID), {
      headers: { Accept: 'application/pdf' },
    });

    expect(response.status(), await response.text()).toBe(404);
  });
});
