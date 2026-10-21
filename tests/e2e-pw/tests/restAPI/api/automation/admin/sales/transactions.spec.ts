import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_SALES } from '../../../../rest/endpoints/admin.sales.endpoints';
import {
  CoverageOrder,
  UNKNOWN_ID,
  expectProblem,
  invoiceOrder,
  placeOrder,
  safeJson,
  setInvoiceState,
} from '../../../../rest/helpers/coverage/adminSales.helpers';

test.describe.configure({ timeout: 240_000 });

const TRANSACTION_NOT_FOUND = 'Transaction not found.';

interface RecordedPayment {
  order: CoverageOrder;
  invoiceId: number;
  amount: number;
  transaction: any;
}

async function recordPayment(request: APIRequestContext): Promise<RecordedPayment> {
  const order = await placeOrder(request);
  const invoice = await invoiceOrder(request, order.orderId);

  await setInvoiceState(request, [invoice.id], 'pending');

  const response = await sendAdminRequest(request, ADMIN_SALES.TRANSACTIONS, {
    method: 'POST',
    data: { invoiceId: invoice.id, paymentMethod: 'cashondelivery', amount: invoice.baseGrandTotal },
  });
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(201);
  expect(typeof body.id).toBe('number');

  return { order, invoiceId: invoice.id, amount: invoice.baseGrandTotal, transaction: body };
}

test.describe('admin transactions rest api', () => {
  test('listing returns paginated envelope', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.TRANSACTIONS, {
      params: { per_page: '5' },
    });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(Array.isArray(body.data)).toBe(true);
    expect(body).toHaveProperty('meta');
  });

  test('listing row shape', async ({ request }) => {
    const payment = await recordPayment(request);

    const response = await sendAdminRequest(request, ADMIN_SALES.TRANSACTIONS, {
      params: { order_id: payment.order.incrementId, per_page: '50' },
    });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    const row = body.data.find((entry: any) => entry.id === payment.transaction.id);
    expect(row, JSON.stringify(body.data)).toBeDefined();
    expect(row).toHaveProperty('transactionId');
    expect(row).toHaveProperty('status');
    expect(row).toHaveProperty('type');
  });

  test('filter status returns 200', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.TRANSACTIONS, {
      params: { status: 'paid', per_page: '3' },
    });
    expect(response.status()).toBe(200);
  });

  test('filter transaction_id returns 200', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.TRANSACTIONS, {
      params: { transaction_id: '0', per_page: '3' },
    });
    expect(response.status()).toBe(200);
  });

  test('filter order_id returns 200', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.TRANSACTIONS, {
      params: { order_id: '1', per_page: '3' },
    });
    expect(response.status()).toBe(200);
  });

  test('filter created_at range returns 200', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.TRANSACTIONS, {
      params: { created_at_from: '2020-01-01', created_at_to: '2030-12-31', per_page: '3' },
    });
    expect(response.status()).toBe(200);
  });

  test('detail returns a recorded transaction', async ({ request }) => {
    const payment = await recordPayment(request);

    const response = await sendAdminRequest(request, ADMIN_SALES.TRANSACTION(payment.transaction.id));
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body.id).toBe(payment.transaction.id);
    expect(body.transactionId).toBe(payment.transaction.transactionId);
    expect(body.transactionId).toMatch(/^[0-9a-f]{40}$/);
    expect(body.invoiceId).toBe(payment.invoiceId);
    expect(body.orderId).toBe(payment.order.orderId);
    expect(body.orderIncrementId).toBe(payment.order.incrementId);
    expect(body.amount).toBeCloseTo(payment.amount, 2);
    expect(body.status).toBe('paid');
    expect(body.paymentMethod).toBe('cashondelivery');
  });

  test('detail bogus id returns 404', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.TRANSACTION(UNKNOWN_ID));

    await expectProblem(response, 404, TRANSACTION_NOT_FOUND);
  });
});
