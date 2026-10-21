import { test, expect, APIRequestContext } from '@playwright/test';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import { SHOP_ACCOUNT, SHOP_ACCOUNT_MESSAGES as MSG } from '../../../rest/endpoints/coverage/shopAccount.endpoints';
import { e2eFixtures, missingFixtureReason, E2eCustomerFixture } from '../../../../fixtures/e2eFixtures';

const STATUS_PENDING = 1;
const STATUS_SOLVED = 6;
const STATUS_CANCELED = 9;

interface ApiResult {
  status: number;
  body: any;
}

function uniqueSuffix(): string {
  return `${Date.now()}-${Math.floor(Math.random() * 1_000_000)}`;
}

function bearer(token: string): Record<string, string> {
  return { Authorization: `Bearer ${token}` };
}

async function loginToken(request: APIRequestContext, customer: E2eCustomerFixture): Promise<string> {
  const response = await sendRestRequest(request, SHOP_ACCOUNT.CUSTOMER_LOGIN, {
    method: 'POST',
    data: { email: customer.email, password: customer.password },
  });

  expect(response.status()).toBe(201);
  const body = await response.json();
  expect(body.success).toBe(true);

  return body.token;
}

async function registerToken(request: APIRequestContext): Promise<string> {
  const email = `shop-account-${uniqueSuffix()}@example.com`;
  const password = 'Passw0rd!123';

  const response = await sendRestRequest(request, SHOP_ACCOUNT.CUSTOMER_REGISTER, {
    method: 'POST',
    data: { firstName: 'Shop', lastName: 'Account', email, password, confirmPassword: password },
  });

  expect(response.status()).toBe(201);
  const body = await response.json();
  expect(typeof body.token).toBe('string');

  return body.token;
}

async function call(
  request: APIRequestContext,
  endpoint: string,
  options: { method?: 'GET' | 'POST'; token?: string; data?: Record<string, any>; params?: Record<string, string> } = {}
): Promise<ApiResult> {
  const response = await sendRestRequest(request, endpoint, {
    method: options.method ?? 'GET',
    data: options.data,
    params: options.params,
    headers: options.token ? bearer(options.token) : {},
  });

  return { status: response.status(), body: await response.json() };
}

async function requiredCustomAnswers(request: APIRequestContext, token: string): Promise<Record<string, any>> {
  const { status, body } = await call(request, SHOP_ACCOUNT.RETURN_CUSTOM_FIELDS, { token });
  expect(status).toBe(200);

  const answers: Record<string, any> = {};

  for (const field of body.filter((row: any) => row.isRequired)) {
    const firstOption = field.options?.[0]?.value;

    if (['select', 'radio'].includes(field.type)) {
      answers[String(field.id)] = firstOption;
    } else if (['multiselect', 'checkbox'].includes(field.type)) {
      answers[String(field.id)] = [firstOption];
    } else {
      answers[String(field.id)] = 'e2e answer';
    }
  }

  return answers;
}

async function buildReturnPayload(request: APIRequestContext, token: string, orderId: number): Promise<Record<string, any> | null> {
  const items = await call(request, SHOP_ACCOUNT.RETURNABLE_ITEMS, { token, params: { order_id: String(orderId) } });
  expect(items.status).toBe(200);

  const item = items.body.find((row: any) => row.currentQuantity >= 1 && row.forReturnQuantity >= 1);

  const reasons = await call(request, SHOP_ACCOUNT.RETURN_REASONS, { token, params: { resolution_type: 'return' } });
  expect(reasons.status).toBe(200);

  if (
    !item
    || reasons.body.length === 0
  ) {
    return null;
  }

  return {
    order_id: orderId,
    order_item_id: item.orderItemId,
    rma_qty: 1,
    resolution_type: 'return',
    rma_reason_id: reasons.body[0].id,
    information: `e2e return ${uniqueSuffix()}`,
    package_condition: 'open',
    custom_attributes: await requiredCustomAnswers(request, token),
    agreement: true,
  };
}

async function createReturn(request: APIRequestContext, token: string, payload: Record<string, any>): Promise<any> {
  const { status, body } = await call(request, SHOP_ACCOUNT.RETURNS, { method: 'POST', token, data: payload });

  expect(status).toBe(201);
  expect(typeof body.id).toBe('number');
  expect(body.orderId).toBe(payload.order_id);
  expect(body.statusId).toBe(STATUS_PENDING);
  expect(body.information).toBe(payload.information);
  expect(body.packageCondition).toBe('open');
  expect(body.item.order_item_id).toBe(payload.order_item_id);
  expect(body.item.quantity).toBe(1);
  expect(body.item.resolution).toBe('return');
  expect(body.item.reason_id).toBe(payload.rma_reason_id);

  return body;
}

async function readReturn(request: APIRequestContext, token: string, id: number): Promise<any> {
  const { status, body } = await call(request, SHOP_ACCOUNT.RETURN(id), { token });
  expect(status).toBe(200);

  return body;
}

test.describe('shop account return lookups rest api', () => {
  test('should list the return reasons of a resolution type', async ({ request }) => {
    const token = await registerToken(request);

    for (const resolutionType of ['return', 'cancel_items']) {
      const { status, body } = await call(request, SHOP_ACCOUNT.RETURN_REASONS, { token, params: { resolution_type: resolutionType } });

      expect(status).toBe(200);
      expect(Array.isArray(body)).toBe(true);
      for (const reason of body) {
        expect(typeof reason.id).toBe('number');
        expect(typeof reason.title).toBe('string');
      }
    }
  });

  test('should reject an unknown resolution type for return reasons', async ({ request }) => {
    const token = await registerToken(request);

    const { status, body } = await call(request, SHOP_ACCOUNT.RETURN_REASONS, { token, params: { resolution_type: 'exchange' } });

    expect(status).toBe(400);
    expect(body.detail).toBe(MSG.RETURN_INVALID_RESOLUTION);
  });

  test('should refuse the return reasons without a customer token', async ({ request }) => {
    const { status, body } = await call(request, SHOP_ACCOUNT.RETURN_REASONS, { params: { resolution_type: 'return' } });

    expect(status).toBe(403);
    expect(body.detail).toBe(MSG.UNAUTHENTICATED);
  });

  test('should list the active return form custom fields', async ({ request }) => {
    const token = await registerToken(request);

    const { status, body } = await call(request, SHOP_ACCOUNT.RETURN_CUSTOM_FIELDS, { token });

    expect(status).toBe(200);
    expect(Array.isArray(body)).toBe(true);
    for (const field of body) {
      expect(typeof field.id).toBe('number');
      expect(typeof field.type).toBe('string');
      expect(typeof field.isRequired).toBe('boolean');
      expect(Array.isArray(field.options)).toBe(true);
    }
  });

  test('should refuse the return custom fields without a customer token', async ({ request }) => {
    const { status, body } = await call(request, SHOP_ACCOUNT.RETURN_CUSTOM_FIELDS);

    expect(status).toBe(403);
    expect(body.detail).toBe(MSG.UNAUTHENTICATED);
  });

  test('should list the returnable items of the customer order with consistent quantities', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const token = await loginToken(request, fx.customer!);
    const { status, body } = await call(request, SHOP_ACCOUNT.RETURNABLE_ITEMS, { token, params: { order_id: String(fx.order!.id) } });

    expect(status).toBe(200);
    expect(Array.isArray(body)).toBe(true);
    for (const item of body) {
      expect(typeof item.orderItemId).toBe('number');
      expect(item.currentQuantity).toBe(item.qtyOrdered - item.rmaQuantity);
      expect(item.forReturnQuantity).toBeGreaterThanOrEqual(0);
    }
  });

  test('should refuse the returnable items of another customer order', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.otherCustomer, missingFixtureReason('otherCustomer'));

    const token = await loginToken(request, fx.otherCustomer!);
    const { status, body } = await call(request, SHOP_ACCOUNT.RETURNABLE_ITEMS, { token, params: { order_id: String(fx.order!.id) } });

    expect(status).toBe(404);
    expect(body.detail).toBe(MSG.RETURN_INVALID_ORDER);
  });

  test('should require an order id for returnable items', async ({ request }) => {
    const token = await registerToken(request);

    const { status, body } = await call(request, SHOP_ACCOUNT.RETURNABLE_ITEMS, { token });

    expect(status).toBe(404);
    expect(body.detail).toBe(MSG.RETURN_INVALID_ORDER);
  });

  test('should refuse the returnable items without a customer token', async ({ request }) => {
    const { status, body } = await call(request, SHOP_ACCOUNT.RETURNABLE_ITEMS, { params: { order_id: '1' } });

    expect(status).toBe(403);
    expect(body.detail).toBe(MSG.UNAUTHENTICATED);
  });

  test('should list no returnable orders for a customer without orders', async ({ request }) => {
    const token = await registerToken(request);

    const { status, body } = await call(request, SHOP_ACCOUNT.RETURNABLE_ORDERS, { token });

    expect(status).toBe(200);
    expect(body).toEqual([]);
  });

  test('should only list returnable orders that still hold quantity to return', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const token = await loginToken(request, fx.customer!);
    const { status, body } = await call(request, SHOP_ACCOUNT.RETURNABLE_ORDERS, { token });

    expect(status).toBe(200);
    expect(Array.isArray(body)).toBe(true);
    for (const order of body) {
      expect(typeof order.incrementId).toBe('string');
      expect(order.returnableQty).toBeGreaterThan(0);
      expect(order.returnableQty).toBe(order.totalQtyOrdered - order.totalReturnedQty);
    }
  });

  test('should not list another customer order as returnable', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.otherCustomer, missingFixtureReason('otherCustomer'));

    const token = await loginToken(request, fx.otherCustomer!);
    const { status, body } = await call(request, SHOP_ACCOUNT.RETURNABLE_ORDERS, { token, params: { increment_id: fx.order!.incrementId } });

    expect(status).toBe(200);
    expect(body.map((order: any) => order.id)).not.toContain(fx.order!.id);
  });

  test('should refuse the returnable orders without a customer token', async ({ request }) => {
    const { status, body } = await call(request, SHOP_ACCOUNT.RETURNABLE_ORDERS);

    expect(status).toBe(403);
    expect(body.detail).toBe(MSG.UNAUTHENTICATED);
  });
});

test.describe('shop account returns rest api', () => {
  test('should refuse the return list without a customer token', async ({ request }) => {
    const { status, body } = await call(request, SHOP_ACCOUNT.RETURNS);

    expect(status).toBe(403);
    expect(body.detail).toBe(MSG.UNAUTHENTICATED);
  });

  test('should list no returns for a customer without orders', async ({ request }) => {
    const token = await registerToken(request);

    const { status, body } = await call(request, SHOP_ACCOUNT.RETURNS, { token });

    expect(status).toBe(200);
    expect(body).toEqual([]);
  });

  test('should list and return the customer return by id', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.rma, missingFixtureReason('rma'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const token = await loginToken(request, fx.customer!);

    const list = await call(request, SHOP_ACCOUNT.RETURNS, { token, params: { per_page: '50' } });
    expect(list.status).toBe(200);
    expect(list.body.map((row: any) => row.id)).toContain(fx.rma!.id);

    const detail = await readReturn(request, token, fx.rma!.id);
    expect(detail.id).toBe(fx.rma!.id);
    expect(detail.orderId).toBe(fx.rma!.orderId);
    expect(typeof detail.statusId).toBe('number');
    expect(typeof detail.statusTitle).toBe('string');
    expect(typeof detail.canClose).toBe('boolean');
    expect(typeof detail.canReopen).toBe('boolean');
    expect(typeof detail.isExpired).toBe('boolean');
    expect(Array.isArray(detail.images)).toBe(true);
    expect(detail.images.map((image: any) => image.id)).toContain(fx.rma!.imageId);
    expect(detail.messagesCount).toBeGreaterThan(0);
  });

  test('should hide the customer return from another customer', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.rma, missingFixtureReason('rma'));
    test.skip(!fx.otherCustomer, missingFixtureReason('otherCustomer'));

    const token = await loginToken(request, fx.otherCustomer!);

    const detail = await call(request, SHOP_ACCOUNT.RETURN(fx.rma!.id), { token });
    expect(detail.status).toBe(404);
    expect(detail.body.detail).toBe(MSG.RETURN_NOT_FOUND);

    const list = await call(request, SHOP_ACCOUNT.RETURNS, { token, params: { per_page: '50' } });
    expect(list.status).toBe(200);
    expect(list.body.map((row: any) => row.id)).not.toContain(fx.rma!.id);
  });

  test('should return 404 for a return id that does not exist', async ({ request }) => {
    const token = await registerToken(request);

    const { status, body } = await call(request, SHOP_ACCOUNT.RETURN(999999999), { token });

    expect(status).toBe(404);
    expect(body.detail).toBe(MSG.RETURN_NOT_FOUND);
  });
});

test.describe('shop account return create rest api', () => {
  const basePayload = () => ({
    order_id: 999999999,
    order_item_id: 999999999,
    rma_qty: 1,
    resolution_type: 'return',
    rma_reason_id: 1,
    information: `e2e return ${uniqueSuffix()}`,
    agreement: true,
  });

  test('should reject a return without the policy agreement and create nothing', async ({ request }) => {
    const token = await registerToken(request);

    const { status, body } = await call(request, SHOP_ACCOUNT.RETURNS, { method: 'POST', token, data: { ...basePayload(), agreement: false } });

    expect(status).toBe(400);
    expect(body.detail).toBe(MSG.RETURN_AGREEMENT_REQUIRED);

    const list = await call(request, SHOP_ACCOUNT.RETURNS, { token });
    expect(list.body).toEqual([]);
  });

  test('should reject a return with an unknown resolution type', async ({ request }) => {
    const token = await registerToken(request);

    const { status, body } = await call(request, SHOP_ACCOUNT.RETURNS, { method: 'POST', token, data: { ...basePayload(), resolution_type: 'exchange' } });

    expect(status).toBe(400);
    expect(body.detail).toBe(MSG.RETURN_INVALID_INPUT);
  });

  test('should reject a return with an unknown package condition', async ({ request }) => {
    const token = await registerToken(request);

    const { status, body } = await call(request, SHOP_ACCOUNT.RETURNS, { method: 'POST', token, data: { ...basePayload(), package_condition: 'broken' } });

    expect(status).toBe(400);
    expect(body.detail).toBe(MSG.RETURN_INVALID_PACKAGE_CONDITION);
  });

  test('should reject an answer to a custom field that does not exist', async ({ request }) => {
    const token = await registerToken(request);

    const { status, body } = await call(request, SHOP_ACCOUNT.RETURNS, {
      method: 'POST',
      token,
      data: { ...basePayload(), custom_attributes: { '999999999': 'e2e' } },
    });

    expect(status).toBe(400);
    expect(body.detail).toBe(MSG.RETURN_UNKNOWN_CUSTOM_FIELD('999999999'));
  });

  test('should refuse a return on another customer order and create nothing', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.otherCustomer, missingFixtureReason('otherCustomer'));

    const token = await loginToken(request, fx.otherCustomer!);
    const before = await call(request, SHOP_ACCOUNT.RETURNS, { token, params: { per_page: '50' } });

    const { status, body } = await call(request, SHOP_ACCOUNT.RETURNS, {
      method: 'POST',
      token,
      data: { ...basePayload(), order_id: fx.order!.id, custom_attributes: await requiredCustomAnswers(request, token) },
    });

    expect(status).toBe(400);
    expect(body.detail).toBe(MSG.RETURN_INVALID_ORDER);

    const after = await call(request, SHOP_ACCOUNT.RETURNS, { token, params: { per_page: '50' } });
    expect(after.body.map((row: any) => row.id)).toEqual(before.body.map((row: any) => row.id));
  });

  test('should refuse a return without a customer token', async ({ request }) => {
    const { status, body } = await call(request, SHOP_ACCOUNT.RETURNS, { method: 'POST', data: basePayload() });

    expect(status).toBe(403);
    expect(body.detail).toBe(MSG.UNAUTHENTICATED);
  });
});

test.describe('shop account return actions rest api', () => {
  test('should cancel a return the customer raised and refuse a second cancel', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const token = await loginToken(request, fx.customer!);
    const payload = await buildReturnPayload(request, token, fx.order!.id);
    test.skip(!payload, 'The fixture order has no quantity left to return.');

    const created = await createReturn(request, token, payload!);

    const list = await call(request, SHOP_ACCOUNT.RETURNS, { token });
    expect(list.status).toBe(200);

    const listed = list.body.find((row: any) => row.id === created.id);
    expect(listed, JSON.stringify(list.body)).toBeTruthy();
    expect(listed.statusId).toBe(STATUS_PENDING);

    const cancel = await call(request, SHOP_ACCOUNT.RETURN_CANCEL(created.id), { method: 'POST', token, data: {} });
    expect(cancel.status).toBe(201);
    expect(cancel.body.id).toBe(created.id);
    expect(cancel.body.statusId).toBe(STATUS_CANCELED);
    expect(cancel.body.canClose).toBe(false);

    const again = await call(request, SHOP_ACCOUNT.RETURN_CANCEL(created.id), { method: 'POST', token, data: {} });
    expect(again.status).toBe(400);
    expect(again.body.detail).toBe(MSG.RETURN_ALREADY_CANCELED);

    expect((await readReturn(request, token, created.id)).statusId).toBe(STATUS_CANCELED);
  });

  test('should reopen a canceled return only when the store allows it', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const token = await loginToken(request, fx.customer!);
    const payload = await buildReturnPayload(request, token, fx.order!.id);
    test.skip(!payload, 'The fixture order has no quantity left to return.');

    const created = await createReturn(request, token, payload!);

    const cancel = await call(request, SHOP_ACCOUNT.RETURN_CANCEL(created.id), { method: 'POST', token, data: {} });
    expect(cancel.status).toBe(201);
    expect(cancel.body.statusId).toBe(STATUS_CANCELED);

    const reopen = await call(request, SHOP_ACCOUNT.RETURN_REOPEN(created.id), { method: 'POST', token, data: {} });

    if (cancel.body.canReopen) {
      expect(reopen.status).toBe(201);
      expect(reopen.body.id).toBe(created.id);
      expect(reopen.body.statusId).toBe(STATUS_PENDING);
      expect(reopen.body.canClose).toBe(true);

      const release = await call(request, SHOP_ACCOUNT.RETURN_CANCEL(created.id), { method: 'POST', token, data: {} });
      expect(release.status).toBe(201);
      expect(release.body.statusId).toBe(STATUS_CANCELED);
    } else {
      expect(reopen.status).toBe(400);
      expect(reopen.body.detail).toBe(MSG.RETURN_REOPEN_NOT_ALLOWED);
      expect((await readReturn(request, token, created.id)).statusId).toBe(STATUS_CANCELED);
    }
  });

  test('should close a pending return and refuse to close it again', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const token = await loginToken(request, fx.customer!);
    const payload = await buildReturnPayload(request, token, fx.order!.id);
    test.skip(!payload, 'The fixture order has no quantity left to return.');

    const created = await createReturn(request, token, payload!);
    expect(created.canClose).toBe(true);

    const close = await call(request, SHOP_ACCOUNT.RETURN_CLOSE(created.id), { method: 'POST', token, data: {} });
    expect(close.status).toBe(201);
    expect(close.body.id).toBe(created.id);
    expect(close.body.statusId).toBe(STATUS_SOLVED);
    expect(close.body.canClose).toBe(false);
    expect(close.body.canReopen).toBe(false);

    const again = await call(request, SHOP_ACCOUNT.RETURN_CLOSE(created.id), { method: 'POST', token, data: {} });
    expect(again.status).toBe(400);
    expect(again.body.detail).toBe(MSG.RETURN_CLOSE_NOT_ALLOWED);
  });

  test('should refuse to cancel, close or reopen another customer return', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.rma, missingFixtureReason('rma'));
    test.skip(!fx.customer, missingFixtureReason('customer'));
    test.skip(!fx.otherCustomer, missingFixtureReason('otherCustomer'));

    const ownerToken = await loginToken(request, fx.customer!);
    const before = await readReturn(request, ownerToken, fx.rma!.id);

    const otherToken = await loginToken(request, fx.otherCustomer!);

    for (const endpoint of [SHOP_ACCOUNT.RETURN_CANCEL(fx.rma!.id), SHOP_ACCOUNT.RETURN_CLOSE(fx.rma!.id), SHOP_ACCOUNT.RETURN_REOPEN(fx.rma!.id)]) {
      const { status, body } = await call(request, endpoint, { method: 'POST', token: otherToken, data: {} });

      expect(status).toBe(404);
      expect(body.detail).toBe(MSG.RETURN_NOT_FOUND);
    }

    const after = await readReturn(request, ownerToken, fx.rma!.id);
    expect(after.statusId).toBe(before.statusId);
    expect(after.updatedAt).toBe(before.updatedAt);
  });

  test('should refuse return actions without a customer token', async ({ request }) => {
    for (const endpoint of [SHOP_ACCOUNT.RETURN_CANCEL(1), SHOP_ACCOUNT.RETURN_CLOSE(1), SHOP_ACCOUNT.RETURN_REOPEN(1)]) {
      const { status, body } = await call(request, endpoint, { method: 'POST', data: {} });

      expect(status).toBe(403);
      expect(body.detail).toBe(MSG.UNAUTHENTICATED);
    }
  });
});

test.describe('shop account return messages rest api', () => {
  test('should post a message on the customer return and list it first', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.rma, missingFixtureReason('rma'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const token = await loginToken(request, fx.customer!);
    const message = `e2e message ${uniqueSuffix()}`;

    const created = await call(request, SHOP_ACCOUNT.RETURN_MESSAGES, { method: 'POST', token, data: { return_id: fx.rma!.id, message } });
    expect(created.status).toBe(201);
    expect(typeof created.body.id).toBe('number');
    expect(created.body.rmaId).toBe(fx.rma!.id);
    expect(created.body.message).toBe(message);
    expect(created.body.isAdmin).toBe(false);

    const list = await call(request, SHOP_ACCOUNT.RETURN_MESSAGES, { token, params: { return_id: String(fx.rma!.id) } });
    expect(list.status).toBe(200);
    expect(list.body[0].id).toBe(created.body.id);
    expect(list.body[0].message).toBe(message);
    expect(list.body.map((row: any) => row.id)).toContain(fx.rma!.messageId);
  });

  test('should hide the return conversation from another customer', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.rma, missingFixtureReason('rma'));
    test.skip(!fx.customer, missingFixtureReason('customer'));
    test.skip(!fx.otherCustomer, missingFixtureReason('otherCustomer'));

    const otherToken = await loginToken(request, fx.otherCustomer!);
    const message = `e2e intrusion ${uniqueSuffix()}`;

    const list = await call(request, SHOP_ACCOUNT.RETURN_MESSAGES, { token: otherToken, params: { return_id: String(fx.rma!.id) } });
    expect(list.status).toBe(404);
    expect(list.body.detail).toBe(MSG.RETURN_NOT_FOUND);

    const post = await call(request, SHOP_ACCOUNT.RETURN_MESSAGES, { method: 'POST', token: otherToken, data: { return_id: fx.rma!.id, message } });
    expect(post.status).toBe(404);
    expect(post.body.detail).toBe(MSG.RETURN_NOT_FOUND);

    const ownerToken = await loginToken(request, fx.customer!);
    const ownerList = await call(request, SHOP_ACCOUNT.RETURN_MESSAGES, { token: ownerToken, params: { return_id: String(fx.rma!.id) } });
    expect(ownerList.body.map((row: any) => row.message)).not.toContain(message);
  });

  test('should reject a message without a return id', async ({ request }) => {
    const token = await registerToken(request);

    const { status, body } = await call(request, SHOP_ACCOUNT.RETURN_MESSAGES, { method: 'POST', token, data: { message: 'e2e' } });

    expect(status).toBe(400);
    expect(body.detail).toBe(MSG.RETURN_INVALID_INPUT);
  });

  test('should refuse the return conversation without a customer token', async ({ request }) => {
    const list = await call(request, SHOP_ACCOUNT.RETURN_MESSAGES, { params: { return_id: '1' } });
    expect(list.status).toBe(403);
    expect(list.body.detail).toBe(MSG.UNAUTHENTICATED);

    const post = await call(request, SHOP_ACCOUNT.RETURN_MESSAGES, { method: 'POST', data: { return_id: 1, message: 'e2e' } });
    expect(post.status).toBe(403);
    expect(post.body.detail).toBe(MSG.UNAUTHENTICATED);
  });
});
