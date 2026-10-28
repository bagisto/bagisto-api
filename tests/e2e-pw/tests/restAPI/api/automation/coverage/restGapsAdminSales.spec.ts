import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../rest/helpers/adminClient';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import {
  ADMIN_SALES_COVERAGE,
  createCouponRule,
  deleteCartRule,
  invoiceOrder,
  placeOrder,
} from '../../../rest/helpers/coverage/adminSales.helpers';
import {
  REST_GAPS,
  REST_GAPS_MESSAGES as MSG,
  UNKNOWN_ID,
  expectAdminUnauthenticated,
  expectDetail,
  rowsOf,
  safeJson,
  uniqueStamp,
} from '../../../rest/helpers/coverage/restGaps.helpers';

test.describe.configure({ timeout: 180_000 });

async function readCartRule(request: APIRequestContext, id: number): Promise<any> {
  const response = await sendAdminRequest(request, ADMIN_SALES_COVERAGE.CART_RULE(id));
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);

  return body;
}

async function cartRuleIdsNamed(request: APIRequestContext, name: string): Promise<number[]> {
  const response = await sendAdminRequest(request, ADMIN_SALES_COVERAGE.CART_RULES, { params: { per_page: '50', sort: 'id', order: 'desc' } });
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);

  return rowsOf(body).filter((row: any) => row.name === name).map((row: any) => Number(row.id));
}

test.describe('admin marketing cart rule copy rest api', () => {
  let createdIds: number[];

  test.beforeEach(() => {
    createdIds = [];
  });

  test.afterEach(async ({ request }) => {
    for (const id of createdIds) {
      await deleteCartRule(request, id);
    }
  });

  test('should copy the rule inactive under a copy of name with its channels and groups but no coupon', async ({ request }) => {
    const source = await createCouponRule(request);
    createdIds.push(source.id);

    const original = await readCartRule(request, source.id);

    const response = await sendAdminRequest(request, REST_GAPS.CART_RULE_COPY(source.id), { method: 'POST', data: {} });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(typeof body.id).toBe('number');
    createdIds.push(body.id);

    expect(body.id).not.toBe(source.id);
    expect(body.name).toBe(MSG.CART_RULE_COPY_OF(original.name));
    expect(body.status).toBe(0);
    expect(body.actionType).toBe(original.actionType);
    expect(body.discountAmount).toBe(original.discountAmount);
    expect(body.couponType).toBe(original.couponType);
    expect(body.couponCode ?? null).toBeNull();
    expect(body.channels.map((channel: any) => channel.id).sort()).toEqual(original.channels.map((channel: any) => channel.id).sort());
    expect(body.customerGroups.map((group: any) => group.id).sort()).toEqual(original.customerGroups.map((group: any) => group.id).sort());

    const stored = await readCartRule(request, body.id);
    expect(stored.name).toBe(MSG.CART_RULE_COPY_OF(original.name));
    expect(stored.status).toBe(0);

    const source2 = await readCartRule(request, source.id);
    expect(source2.name).toBe(original.name);
    expect(source2.status).toBe(original.status);
    expect(source2.couponCode).toBe(source.code);
  });

  test('should report an unknown cart rule and create no copy', async ({ request }) => {
    await expectDetail(
      await sendAdminRequest(request, REST_GAPS.CART_RULE_COPY(UNKNOWN_ID), { method: 'POST', data: {} }),
      404,
      MSG.CART_RULE_NOT_FOUND
    );
  });

  test('should refuse to copy a cart rule without an admin token', async ({ request }) => {
    const source = await createCouponRule(request);
    createdIds.push(source.id);

    const original = await readCartRule(request, source.id);

    await expectAdminUnauthenticated(
      await sendRestRequest(request, REST_GAPS.CART_RULE_COPY(source.id), { method: 'POST', data: {} })
    );

    expect(await cartRuleIdsNamed(request, MSG.CART_RULE_COPY_OF(original.name))).toEqual([]);
  });
});

test.describe('admin invoice send duplicate rest api', () => {
  async function invoicedOrder(request: APIRequestContext) {
    const order = await placeOrder(request);
    const invoice = await invoiceOrder(request, order.orderId);

    return { order, invoice };
  }

  test('should send the invoice to the requested address', async ({ request }) => {
    const { invoice } = await invoicedOrder(request);
    const email = `duplicate_${uniqueStamp()}@example.com`;

    const response = await sendAdminRequest(request, REST_GAPS.INVOICE_SEND_DUPLICATE(invoice.id), {
      method: 'POST',
      data: { email },
    });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body.id).toBe(invoice.id);
    expect(body.email).toBe(email);
    expect(body.success).toBe(true);
    expect(body.message).toBe(MSG.INVOICE_DUPLICATE_SENT(email));
  });

  test('should default the recipient to the order customer', async ({ request }) => {
    const { order, invoice } = await invoicedOrder(request);

    const response = await sendAdminRequest(request, REST_GAPS.INVOICE_SEND_DUPLICATE(invoice.id), {
      method: 'POST',
      data: {},
    });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body.email).toBe(order.customer.email);
    expect(body.message).toBe(MSG.INVOICE_DUPLICATE_SENT(order.customer.email));
  });

  test('should report an invalid recipient address', async ({ request }) => {
    const { invoice } = await invoicedOrder(request);

    await expectDetail(
      await sendAdminRequest(request, REST_GAPS.INVOICE_SEND_DUPLICATE(invoice.id), {
        method: 'POST',
        data: { email: 'not-an-email' },
      }),
      422,
      MSG.INVOICE_EMAIL_INVALID
    );
  });

  test('should report an unknown invoice', async ({ request }) => {
    await expectDetail(
      await sendAdminRequest(request, REST_GAPS.INVOICE_SEND_DUPLICATE(UNKNOWN_ID), {
        method: 'POST',
        data: { email: 'someone@example.com' },
      }),
      404,
      MSG.INVOICE_NOT_FOUND
    );
  });

  test('should refuse to send a duplicate without an admin token', async ({ request }) => {
    await expectAdminUnauthenticated(
      await sendRestRequest(request, REST_GAPS.INVOICE_SEND_DUPLICATE(UNKNOWN_ID), {
        method: 'POST',
        data: { email: 'someone@example.com' },
      })
    );
  });
});
