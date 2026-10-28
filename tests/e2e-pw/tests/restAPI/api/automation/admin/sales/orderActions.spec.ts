import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_SALES } from '../../../../rest/endpoints/admin.sales.endpoints';
import {
  ADMIN_SALES_COVERAGE,
  UNKNOWN_ID,
  expectProblem,
  invoiceOrder,
  placeOrder,
  safeJson,
} from '../../../../rest/helpers/coverage/adminSales.helpers';

test.describe.configure({ timeout: 240_000 });

const REORDER_SLUG = 'sales.order_settings.reorder';

const REORDER_ADMIN_KEY = 'sales.order_settings.reorder.admin';

const MSG = {
  ORDER_NOT_FOUND: 'Order not found.',
  REORDER_SUCCESS: 'Reorder successful. A new draft cart has been created.',
  CANCEL_NOTHING: 'There is nothing to cancel on this order.',
  COMMENT_EMPTY: 'Comment is required.',
  INVOICE_ITEMS_REQUIRED: 'At least one item with a positive quantity is required.',
  SHIPMENT_SOURCE_REQUIRED: 'Inventory source is required.',
  REFUND_NOTHING: 'There is nothing left to refund on this order.',
};

async function readOrder(request: APIRequestContext, orderId: number): Promise<any> {
  const response = await sendAdminRequest(request, ADMIN_SALES.ORDER(orderId));
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);

  return body;
}

async function adminReorderSetting(request: APIRequestContext): Promise<string | null> {
  const response = await sendAdminRequest(request, ADMIN_SALES_COVERAGE.CONFIGURATION, {
    params: { slug: REORDER_SLUG, channel: 'default' },
  });
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);

  const payload = Array.isArray(body) ? body[0] : body;

  return payload?.values?.[REORDER_ADMIN_KEY] ?? null;
}

async function setAdminReorderSetting(request: APIRequestContext, value: string): Promise<void> {
  const response = await sendAdminRequest(request, ADMIN_SALES_COVERAGE.CONFIGURATION, {
    method: 'POST',
    data: {
      slug: REORDER_SLUG,
      channel: 'default',
      locale: 'en',
      values: { [REORDER_ADMIN_KEY]: value },
    },
  });

  expect(response.status(), await response.text()).toBe(200);
  expect((await adminReorderSetting(request)) ?? '').toBe(value);
}

test.describe('admin order actions rest api', () => {
  test('reorder creates a draft cart for a customer order', async ({ request }) => {
    const order = await placeOrder(request);
    const original = await adminReorderSetting(request);

    if (original !== '1') {
      await setAdminReorderSetting(request, '1');
    }

    try {
      const response = await sendAdminRequest(request, ADMIN_SALES.ORDER_REORDER(order.orderId), {
        method: 'POST',
        data: {},
      });
      const body = await safeJson(response);

      expect(response.status(), JSON.stringify(body)).toBe(201);
      expect(body.success).toBe(true);
      expect(body.message).toBe(MSG.REORDER_SUCCESS);
      expect(typeof body.cartId).toBe('number');
      expect(body.cartId).not.toBe(order.cartId);
    } finally {
      if (original !== '1') {
        await setAdminReorderSetting(request, original ?? '0');
      }
    }
  });

  test('reorder on non-existent order returns 404', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.ORDER_REORDER(UNKNOWN_ID), {
      method: 'POST',
      data: {},
    });

    await expectProblem(response, 404, MSG.ORDER_NOT_FOUND);
  });

  test('cancel cancels a pending order and refuses a second cancel', async ({ request }) => {
    const order = await placeOrder(request);

    const response = await sendAdminRequest(request, ADMIN_SALES.ORDER_CANCEL(order.orderId), {
      method: 'POST',
      data: {},
    });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.id).toBe(order.orderId);
    expect(body.status).toBe('canceled');
    expect((await readOrder(request, order.orderId)).status).toBe('canceled');

    const again = await sendAdminRequest(request, ADMIN_SALES.ORDER_CANCEL(order.orderId), {
      method: 'POST',
      data: {},
    });

    await expectProblem(again, 422, MSG.CANCEL_NOTHING);
  });

  test('cancel non-existent order returns 404', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_SALES.ORDER_CANCEL(UNKNOWN_ID), {
      method: 'POST',
      data: {},
    });

    await expectProblem(response, 404, MSG.ORDER_NOT_FOUND);
  });

  test('list comments returns paginated envelope', async ({ request }) => {
    const order = await placeOrder(request);

    const response = await sendAdminRequest(request, ADMIN_SALES.ORDER_COMMENTS(order.orderId));
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(Array.isArray(body.data)).toBe(true);
    expect(body).toHaveProperty('meta');
  });

  test('add comment creates a new row', async ({ request }) => {
    const order = await placeOrder(request);
    const comment = `E2E comment ${Date.now()}`;

    const response = await sendAdminRequest(request, ADMIN_SALES.ORDER_COMMENTS(order.orderId), {
      method: 'POST',
      data: { comment, customerNotified: false },
    });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(typeof body.id).toBe('number');
    expect(body.comment).toBe(comment);

    const list = await sendAdminRequest(request, ADMIN_SALES.ORDER_COMMENTS(order.orderId));
    const listBody = await safeJson(list);

    expect(list.status(), JSON.stringify(listBody)).toBe(200);
    expect(listBody.data.map((row: any) => row.comment)).toContain(comment);
  });

  test('add comment with empty body is rejected', async ({ request }) => {
    const order = await placeOrder(request);

    const response = await sendAdminRequest(request, ADMIN_SALES.ORDER_COMMENTS(order.orderId), {
      method: 'POST',
      data: { comment: '' },
    });

    await expectProblem(response, 422, MSG.COMMENT_EMPTY);

    const list = await sendAdminRequest(request, ADMIN_SALES.ORDER_COMMENTS(order.orderId));
    const listBody = await safeJson(list);

    expect(list.status(), JSON.stringify(listBody)).toBe(200);
    expect(listBody.data).toHaveLength(0);
  });

  test('invoice create rejects an empty item list and leaves the order invoiceable', async ({ request }) => {
    const order = await placeOrder(request);

    const response = await sendAdminRequest(request, ADMIN_SALES.ORDER_INVOICES(order.orderId), {
      method: 'POST',
      data: { invoice: { items: {} } },
    });

    await expectProblem(response, 422, MSG.INVOICE_ITEMS_REQUIRED);

    const invoice = await invoiceOrder(request, order.orderId);
    expect(invoice.id).toBeGreaterThan(0);
  });

  test('shipment create without a top level source is rejected', async ({ request }) => {
    const order = await placeOrder(request);

    const response = await sendAdminRequest(request, ADMIN_SALES.ORDER_SHIPMENTS(order.orderId), {
      method: 'POST',
      data: { shipment: { source: 1, items: {} } },
    });

    await expectProblem(response, 422, MSG.SHIPMENT_SOURCE_REQUIRED);
  });

  test('refund create on an uninvoiced order is refused', async ({ request }) => {
    const order = await placeOrder(request);

    const response = await sendAdminRequest(request, ADMIN_SALES.ORDER_REFUNDS(order.orderId), {
      method: 'POST',
      data: { items: {}, shippingAmount: 0, adjustmentRefund: 0, adjustmentFee: 0 },
    });

    await expectProblem(response, 422, MSG.REFUND_NOTHING);
    expect((await readOrder(request, order.orderId)).grandTotalRefunded).toBe(0);
  });

  test('refund preview returns totals without writing', async ({ request }) => {
    const order = await placeOrder(request);
    await invoiceOrder(request, order.orderId);

    const detail = await readOrder(request, order.orderId);
    const [item] = detail.items;

    const response = await sendAdminRequest(request, ADMIN_SALES.ORDER_REFUND_PREVIEW(order.orderId), {
      method: 'POST',
      data: { items: [{ orderItemId: item.id, quantity: 1 }], shipping: 0, adjustmentRefund: 0, adjustmentFee: 0 },
    });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.orderId).toBe(order.orderId);
    expect(body.subtotal).toBeGreaterThan(0);
    expect(body.shipping).toBe(0);
    expect(body.grandTotal).toBeCloseTo(body.subtotal + body.tax - body.discount, 2);
    expect(typeof body.formattedGrandTotal).toBe('string');
    expect((await readOrder(request, order.orderId)).grandTotalRefunded).toBe(0);
  });
});
