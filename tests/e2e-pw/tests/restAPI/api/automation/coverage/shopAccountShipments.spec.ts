import { test, expect, APIRequestContext } from '@playwright/test';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import { SHOP_ACCOUNT, SHOP_ACCOUNT_MESSAGES as MSG } from '../../../rest/endpoints/coverage/shopAccount.endpoints';
import { e2eFixtures, missingFixtureReason, E2eCustomerFixture } from '../../../../fixtures/e2eFixtures';

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
  const email = `shop-account-${Date.now()}-${Math.floor(Math.random() * 1_000_000)}@example.com`;
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

function refersTo(value: any, id: number): boolean {
  if (typeof value === 'string') {
    return value.endsWith(`/${id}`);
  }

  return Number(value?.id) === id || String(value?.['@id'] ?? '').endsWith(`/${id}`);
}

async function getJson(request: APIRequestContext, endpoint: string, token?: string, params?: Record<string, string>) {
  const response = await sendRestRequest(request, endpoint, {
    headers: token ? bearer(token) : {},
    params,
  });

  return { status: response.status(), body: await response.json() };
}

test.describe('shop account order shipments rest api', () => {
  test('should refuse the shipment list without a customer token', async ({ request }) => {
    const { status, body } = await getJson(request, SHOP_ACCOUNT.SHIPMENTS);

    expect(status).toBe(403);
    expect(body.detail).toBe(MSG.UNAUTHENTICATED);
  });

  test('should refuse a single shipment without a customer token', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));

    const { status, body } = await getJson(request, SHOP_ACCOUNT.SHIPMENT(fx.order!.shipmentId));

    expect(status).toBe(403);
    expect(body.detail).toBe(MSG.UNAUTHENTICATED);
  });

  test('should list the shipment of the customer order filtered by order id', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const token = await loginToken(request, fx.customer!);
    const { status, body } = await getJson(request, SHOP_ACCOUNT.SHIPMENTS, token, { order_id: String(fx.order!.id) });

    expect(status).toBe(200);
    expect(Array.isArray(body)).toBe(true);

    const shipment = body.find((row: any) => row.id === fx.order!.shipmentId);
    expect(shipment).toBeTruthy();
    expect(typeof shipment.shippingNumber).toBe('string');
    expect(Array.isArray(shipment.items)).toBe(true);
    expect(shipment.items).toHaveLength(fx.order!.shipmentItemIds.length);
  });

  test('should return the customer shipment by id with its items', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const token = await loginToken(request, fx.customer!);
    const { status, body } = await getJson(request, SHOP_ACCOUNT.SHIPMENT(fx.order!.shipmentId), token);

    expect(status).toBe(200);
    expect(body.id).toBe(fx.order!.shipmentId);
    expect(typeof body.totalQty).toBe('number');
    expect(body.totalQty).toBeGreaterThan(0);
    expect(Array.isArray(body.items)).toBe(true);
    expect(body.items).toHaveLength(fx.order!.shipmentItemIds.length);

    for (const itemId of fx.order!.shipmentItemIds) {
      expect(body.items.some((item: any) => refersTo(item, itemId))).toBe(true);
    }
  });

  test('should hide the customer shipment from another customer', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.otherCustomer, missingFixtureReason('otherCustomer'));

    const token = await loginToken(request, fx.otherCustomer!);

    const single = await getJson(request, SHOP_ACCOUNT.SHIPMENT(fx.order!.shipmentId), token);
    expect(single.status).toBe(404);
    expect(single.body.detail).toBe(MSG.SHIPMENT_NOT_FOUND(fx.order!.shipmentId));

    const list = await getJson(request, SHOP_ACCOUNT.SHIPMENTS, token, { order_id: String(fx.order!.id) });
    expect(list.status).toBe(200);
    expect(list.body).toEqual([]);
  });

  test('should return 404 for a shipment id that does not exist', async ({ request }) => {
    const token = await registerToken(request);

    const { status, body } = await getJson(request, SHOP_ACCOUNT.SHIPMENT(999999999), token);

    expect(status).toBe(404);
    expect(body.detail).toBe(MSG.SHIPMENT_NOT_FOUND(999999999));
  });

  test('should list no shipments for a customer without orders', async ({ request }) => {
    const token = await registerToken(request);

    const { status, body } = await getJson(request, SHOP_ACCOUNT.SHIPMENTS, token);

    expect(status).toBe(200);
    expect(body).toEqual([]);
  });
});

test.describe('shop account order shipment items rest api', () => {
  test('should refuse the shipment item list without a customer token', async ({ request }) => {
    const { status, body } = await getJson(request, SHOP_ACCOUNT.SHIPMENT_ITEMS);

    expect(status).toBe(403);
    expect(body.detail).toBe(MSG.UNAUTHENTICATED);
  });

  test('should refuse a single shipment item without a customer token', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));

    const { status, body } = await getJson(request, SHOP_ACCOUNT.SHIPMENT_ITEM(fx.order!.shipmentItemIds[0]));

    expect(status).toBe(403);
    expect(body.detail).toBe(MSG.UNAUTHENTICATED);
  });

  test('should list the shipped items of the customer', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const token = await loginToken(request, fx.customer!);
    const { status, body } = await getJson(request, SHOP_ACCOUNT.SHIPMENT_ITEMS, token);

    expect(status).toBe(200);
    expect(Array.isArray(body)).toBe(true);

    const ids = body.map((row: any) => row.id);
    for (const itemId of fx.order!.shipmentItemIds) {
      expect(ids).toContain(itemId);
    }
  });

  test('should return a shipped item by id with its shipment', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const token = await loginToken(request, fx.customer!);
    const itemId = fx.order!.shipmentItemIds[0];
    const { status, body } = await getJson(request, SHOP_ACCOUNT.SHIPMENT_ITEM(itemId), token);

    expect(status).toBe(200);
    expect(body.id).toBe(itemId);
    expect(typeof body.sku).toBe('string');
    expect(typeof body.qty).toBe('number');
    expect(body.qty).toBeGreaterThan(0);
    expect(refersTo(body.shipment, fx.order!.shipmentId)).toBe(true);
  });

  test('should hide the shipped items from another customer', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.otherCustomer, missingFixtureReason('otherCustomer'));

    const token = await loginToken(request, fx.otherCustomer!);
    const itemId = fx.order!.shipmentItemIds[0];

    const single = await getJson(request, SHOP_ACCOUNT.SHIPMENT_ITEM(itemId), token);
    expect(single.status).toBe(404);
    expect(single.body.detail).toBe(MSG.SHIPMENT_ITEM_NOT_FOUND(itemId));

    const list = await getJson(request, SHOP_ACCOUNT.SHIPMENT_ITEMS, token);
    expect(list.status).toBe(200);
    const ids = list.body.map((row: any) => row.id);
    for (const ownedId of fx.order!.shipmentItemIds) {
      expect(ids).not.toContain(ownedId);
    }
  });
});
