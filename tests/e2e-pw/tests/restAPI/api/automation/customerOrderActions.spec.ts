import { test, expect, APIRequestContext } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import {
  bearer,
  expectProblem,
  findSaleableSimpleProducts,
  placeCustomerOrder,
  readCart,
  readJson,
  registerShopCustomer,
  ShopCustomer,
} from '../../rest/helpers/coverage/shopCart.helpers';

const MSG = {
  UNAUTHENTICATED: 'Unauthenticated. Please login to perform this action',
  CANCEL_ORDER_ID_REQUIRED: 'Order ID is required to cancel an order',
  CANCEL_NOT_FOUND: (id: number) => `Order with ID "${id}" not found or does not belong to this customer`,
  CANCEL_SUCCESS: 'Order has been canceled successfully',
  REORDER_ORDER_ID_REQUIRED: 'Order ID is required to reorder',
  REORDER_NOT_FOUND: (id: number) => `Order with ID "${id}" not found or does not belong to this customer`,
  REORDER_SUCCESS: (count: number) => `${count} item(s) from your previous order have been added to your cart`,
};

const MISSING_ORDER_ID = 2147480000;

async function orderStatus(request: APIRequestContext, customer: ShopCustomer, orderId: number): Promise<string> {
  const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ORDER(orderId), { headers: bearer(customer.token) });
  const body = await readJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);

  return body.status;
}

test.describe('customer order cancel rest api', () => {
  let customer: ShopCustomer;

  test.beforeEach(async ({ request }) => {
    customer = await registerShopCustomer(request);
  });

  test('cancel without a bearer token is refused', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CANCEL_ORDER, {
      method: 'POST',
      data: { orderId: MISSING_ORDER_ID },
    });

    await expectProblem(response, 403, MSG.UNAUTHENTICATED);
  });

  test('cancel without an order id is rejected', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CANCEL_ORDER, {
      method: 'POST',
      data: {},
      headers: bearer(customer.token),
    });

    await expectProblem(response, 400, MSG.CANCEL_ORDER_ID_REQUIRED);
  });

  test('cancel of an order that does not exist is not found', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CANCEL_ORDER, {
      method: 'POST',
      data: { orderId: MISSING_ORDER_ID },
      headers: bearer(customer.token),
    });

    await expectProblem(response, 404, MSG.CANCEL_NOT_FOUND(MISSING_ORDER_ID));
  });

  test('cancel of another customer order is not found and leaves the order pending', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const order = await placeCustomerOrder(request, customer, productId);
    const intruder = await registerShopCustomer(request);

    const response = await sendRestRequest(request, ENDPOINTS.CANCEL_ORDER, {
      method: 'POST',
      data: { orderId: order.orderId },
      headers: bearer(intruder.token),
    });

    await expectProblem(response, 404, MSG.CANCEL_NOT_FOUND(order.orderId));
    expect(await orderStatus(request, customer, order.orderId)).toBe('pending');
  });

  test('cancel of an own pending order cancels it', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const order = await placeCustomerOrder(request, customer, productId);

    const response = await sendRestRequest(request, ENDPOINTS.CANCEL_ORDER, {
      method: 'POST',
      data: { orderId: order.orderId },
      headers: bearer(customer.token),
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.success).toBe(true);
    expect(body.message).toBe(MSG.CANCEL_SUCCESS);
    expect(body.orderId).toBe(order.orderId);
    expect(body.status).toBe('canceled');
    expect(await orderStatus(request, customer, order.orderId)).toBe('canceled');
  });
});

test.describe('customer reorder rest api', () => {
  let customer: ShopCustomer;

  test.beforeEach(async ({ request }) => {
    customer = await registerShopCustomer(request);
  });

  test('reorder without a bearer token is refused', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.REORDER_ORDER, {
      method: 'POST',
      data: { orderId: MISSING_ORDER_ID },
    });

    await expectProblem(response, 403, MSG.UNAUTHENTICATED);
  });

  test('reorder of an order that does not exist is not found', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.REORDER_ORDER, {
      method: 'POST',
      data: { orderId: MISSING_ORDER_ID },
      headers: bearer(customer.token),
    });

    await expectProblem(response, 404, MSG.REORDER_NOT_FOUND(MISSING_ORDER_ID));
  });

  test('reorder without an order id is rejected', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.REORDER_ORDER, {
      method: 'POST',
      data: {},
      headers: bearer(customer.token),
    });

    await expectProblem(response, 400, MSG.REORDER_ORDER_ID_REQUIRED);
  });

  test('reorder of an own order puts its product back in the cart', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const order = await placeCustomerOrder(request, customer, productId);

    const response = await sendRestRequest(request, ENDPOINTS.REORDER_ORDER, {
      method: 'POST',
      data: { orderId: order.orderId },
      headers: bearer(customer.token),
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.success).toBe(true);
    expect(body.orderId).toBe(order.orderId);
    expect(body.itemsAddedCount).toBe(1);
    expect(body.message).toBe(MSG.REORDER_SUCCESS(1));

    const cart = await readJson(await readCart(request, customer.token));
    expect((cart.items ?? []).map((item: any) => Number(item.productId))).toContain(productId);
  });
});

test.describe('customer order actions over get', () => {
  test('get on the cancel order path is not routed', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CANCEL_ORDER);

    expect(response.status()).toBe(404);
  });

  test('get on the reorder path is not routed', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.REORDER_ORDER);

    expect(response.status()).toBe(404);
  });
});
