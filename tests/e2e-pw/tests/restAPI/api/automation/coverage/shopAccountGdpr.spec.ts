import { test, expect, APIRequestContext } from '@playwright/test';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import { SHOP_ACCOUNT, SHOP_ACCOUNT_MESSAGES as MSG } from '../../../rest/endpoints/coverage/shopAccount.endpoints';
import { e2eFixtures, missingFixtureReason, E2eCustomerFixture } from '../../../../fixtures/e2eFixtures';

interface ApiResult {
  status: number;
  body: any;
}

interface RegisteredCustomer {
  id: number;
  email: string;
  token: string;
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

async function registerCustomer(request: APIRequestContext): Promise<RegisteredCustomer> {
  const email = `shop-account-${uniqueSuffix()}@example.com`;
  const password = 'Passw0rd!123';

  const response = await sendRestRequest(request, SHOP_ACCOUNT.CUSTOMER_REGISTER, {
    method: 'POST',
    data: { firstName: 'Shop', lastName: 'Account', email, password, confirmPassword: password },
  });

  expect(response.status()).toBe(201);
  const body = await response.json();
  expect(typeof body.token).toBe('string');

  return { id: Number(body.id), email, token: body.token };
}

async function call(
  request: APIRequestContext,
  endpoint: string,
  options: { method?: 'GET' | 'POST' | 'DELETE'; token?: string; data?: Record<string, any> } = {}
): Promise<ApiResult> {
  const response = await sendRestRequest(request, endpoint, {
    method: options.method ?? 'GET',
    data: options.data,
    headers: options.token ? bearer(options.token) : {},
  });

  const text = await response.text();

  return { status: response.status(), body: text ? JSON.parse(text) : null };
}

async function gdprEnabled(request: APIRequestContext): Promise<boolean> {
  const { status, body } = await call(request, SHOP_ACCOUNT.GDPR_STATUS);
  expect(status).toBe(200);

  return body[0].enabled === true;
}

async function raiseRequest(request: APIRequestContext, customer: RegisteredCustomer): Promise<any> {
  const message = `e2e gdpr update ${uniqueSuffix()}`;

  const { status, body } = await call(request, SHOP_ACCOUNT.GDPR_REQUESTS, {
    method: 'POST',
    token: customer.token,
    data: { type: 'update', message },
  });

  expect(status).toBe(201);
  expect(typeof body.id).toBe('number');
  expect(body.type).toBe('update');
  expect(body.status).toBe('pending');
  expect(body.message).toBe(message);
  expect(body.email).toBe(customer.email);
  expect(body.revokedAt ?? null).toBeNull();
  expect(body.successMessage).toBe(MSG.GDPR_RAISED);

  return body;
}

test.describe('shop account gdpr status rest api', () => {
  test('should report the gdpr setting of the current channel', async ({ request }) => {
    const { status, body } = await call(request, SHOP_ACCOUNT.GDPR_STATUS);

    expect(status).toBe(200);
    expect(Array.isArray(body)).toBe(true);
    expect(body).toHaveLength(1);
    expect(typeof body[0].channel).toBe('string');
    expect(body[0].id).toBe(body[0].channel);
    expect(typeof body[0].enabled).toBe('boolean');
  });
});

test.describe('shop account gdpr requests rest api', () => {
  test('should raise a gdpr request and read it back', async ({ request }) => {
    test.skip(!(await gdprEnabled(request)), 'GDPR is disabled in the admin configuration.');

    const customer = await registerCustomer(request);
    const raised = await raiseRequest(request, customer);

    const list = await call(request, SHOP_ACCOUNT.GDPR_REQUESTS, { token: customer.token });
    expect(list.status).toBe(200);
    expect(list.body.map((row: any) => row.id)).toEqual([raised.id]);

    const detail = await call(request, SHOP_ACCOUNT.GDPR_REQUEST(raised.id), { token: customer.token });
    expect(detail.status).toBe(200);
    expect(detail.body.id).toBe(raised.id);
    expect(detail.body.type).toBe('update');
    expect(detail.body.status).toBe('pending');
    expect(detail.body.message).toBe(raised.message);
  });

  test('should revoke a pending gdpr request and refuse a second revoke', async ({ request }) => {
    test.skip(!(await gdprEnabled(request)), 'GDPR is disabled in the admin configuration.');

    const customer = await registerCustomer(request);
    const raised = await raiseRequest(request, customer);

    const revoke = await call(request, SHOP_ACCOUNT.GDPR_REQUEST_REVOKE(raised.id), { method: 'POST', token: customer.token, data: {} });
    expect(revoke.status).toBe(200);
    expect(revoke.body.id).toBe(raised.id);
    expect(revoke.body.status).toBe('revoked');
    expect(typeof revoke.body.revokedAt).toBe('string');

    const again = await call(request, SHOP_ACCOUNT.GDPR_REQUEST_REVOKE(raised.id), { method: 'POST', token: customer.token, data: {} });
    expect(again.status).toBe(422);
    expect(again.body.detail).toBe(MSG.GDPR_REVOKE_NOT_ALLOWED);

    const detail = await call(request, SHOP_ACCOUNT.GDPR_REQUEST(raised.id), { token: customer.token });
    expect(detail.body.status).toBe('revoked');
  });

  test('should delete a gdpr request the customer raised', async ({ request }) => {
    test.skip(!(await gdprEnabled(request)), 'GDPR is disabled in the admin configuration.');

    const customer = await registerCustomer(request);
    const raised = await raiseRequest(request, customer);

    const removal = await call(request, SHOP_ACCOUNT.GDPR_REQUEST(raised.id), { method: 'DELETE', token: customer.token });
    expect(removal.status).toBe(204);

    const detail = await call(request, SHOP_ACCOUNT.GDPR_REQUEST(raised.id), { token: customer.token });
    expect(detail.status).toBe(404);
    expect(detail.body.detail).toBe(MSG.GDPR_NOT_FOUND);

    const list = await call(request, SHOP_ACCOUNT.GDPR_REQUESTS, { token: customer.token });
    expect(list.body).toEqual([]);
  });

  test('should keep a gdpr request out of reach of another customer', async ({ request }) => {
    test.skip(!(await gdprEnabled(request)), 'GDPR is disabled in the admin configuration.');

    const owner = await registerCustomer(request);
    const raised = await raiseRequest(request, owner);
    const intruder = await registerCustomer(request);

    const detail = await call(request, SHOP_ACCOUNT.GDPR_REQUEST(raised.id), { token: intruder.token });
    expect(detail.status).toBe(404);
    expect(detail.body.detail).toBe(MSG.GDPR_NOT_FOUND);

    const revoke = await call(request, SHOP_ACCOUNT.GDPR_REQUEST_REVOKE(raised.id), { method: 'POST', token: intruder.token, data: {} });
    expect(revoke.status).toBe(404);
    expect(revoke.body.detail).toBe(MSG.GDPR_NOT_FOUND);

    const removal = await call(request, SHOP_ACCOUNT.GDPR_REQUEST(raised.id), { method: 'DELETE', token: intruder.token });
    expect(removal.status).toBe(404);
    expect(removal.body.detail).toBe(MSG.GDPR_NOT_FOUND);

    const list = await call(request, SHOP_ACCOUNT.GDPR_REQUESTS, { token: intruder.token });
    expect(list.body).toEqual([]);

    const ownerView = await call(request, SHOP_ACCOUNT.GDPR_REQUEST(raised.id), { token: owner.token });
    expect(ownerView.status).toBe(200);
    expect(ownerView.body.status).toBe('pending');
  });

  test('should reject a gdpr request with an unknown type and create nothing', async ({ request }) => {
    test.skip(!(await gdprEnabled(request)), 'GDPR is disabled in the admin configuration.');

    const customer = await registerCustomer(request);

    const { status, body } = await call(request, SHOP_ACCOUNT.GDPR_REQUESTS, {
      method: 'POST',
      token: customer.token,
      data: { type: 'export', message: 'e2e gdpr' },
    });

    expect(status).toBe(400);
    expect(body.detail).toBe(MSG.GDPR_TYPE_INVALID);

    const list = await call(request, SHOP_ACCOUNT.GDPR_REQUESTS, { token: customer.token });
    expect(list.body).toEqual([]);
  });

  test('should require a type and a message for a gdpr request', async ({ request }) => {
    test.skip(!(await gdprEnabled(request)), 'GDPR is disabled in the admin configuration.');

    const customer = await registerCustomer(request);

    const missingType = await call(request, SHOP_ACCOUNT.GDPR_REQUESTS, { method: 'POST', token: customer.token, data: { message: 'e2e gdpr' } });
    expect(missingType.status).toBe(400);
    expect(missingType.body.detail).toBe(MSG.GDPR_TYPE_REQUIRED);

    const missingMessage = await call(request, SHOP_ACCOUNT.GDPR_REQUESTS, { method: 'POST', token: customer.token, data: { type: 'delete', message: '   ' } });
    expect(missingMessage.status).toBe(400);
    expect(missingMessage.body.detail).toBe(MSG.GDPR_MESSAGE_REQUIRED);

    const list = await call(request, SHOP_ACCOUNT.GDPR_REQUESTS, { token: customer.token });
    expect(list.body).toEqual([]);
  });

  test('should refuse gdpr requests without a customer token', async ({ request }) => {
    test.skip(!(await gdprEnabled(request)), 'GDPR is disabled in the admin configuration.');

    const list = await call(request, SHOP_ACCOUNT.GDPR_REQUESTS);
    expect(list.status).toBe(403);
    expect(list.body.detail).toBe(MSG.GDPR_UNAUTHENTICATED);

    const create = await call(request, SHOP_ACCOUNT.GDPR_REQUESTS, { method: 'POST', data: { type: 'update', message: 'e2e gdpr' } });
    expect(create.status).toBe(403);
    expect(create.body.detail).toBe(MSG.GDPR_UNAUTHENTICATED);

    const detail = await call(request, SHOP_ACCOUNT.GDPR_REQUEST(1));
    expect(detail.status).toBe(403);
    expect(detail.body.detail).toBe(MSG.GDPR_UNAUTHENTICATED);
  });

  test('should answer gdpr requests with the disabled message while gdpr is off', async ({ request }) => {
    test.skip(await gdprEnabled(request), 'GDPR is enabled in the admin configuration.');

    const customer = await registerCustomer(request);

    const list = await call(request, SHOP_ACCOUNT.GDPR_REQUESTS, { token: customer.token });
    expect(list.status).toBe(400);
    expect(list.body.detail).toBe(MSG.GDPR_DISABLED);

    const create = await call(request, SHOP_ACCOUNT.GDPR_REQUESTS, {
      method: 'POST',
      token: customer.token,
      data: { type: 'update', message: 'e2e gdpr' },
    });
    expect(create.status).toBe(400);
    expect(create.body.detail).toBe(MSG.GDPR_DISABLED);

    const detail = await call(request, SHOP_ACCOUNT.GDPR_REQUEST(1), { token: customer.token });
    expect(detail.status).toBe(400);
    expect(detail.body.detail).toBe(MSG.GDPR_DISABLED);
  });
});

test.describe('shop account gdpr data export rest api', () => {
  test('should export only the authenticated customer data', async ({ request }) => {
    test.skip(!(await gdprEnabled(request)), 'GDPR is disabled in the admin configuration.');

    const customer = await registerCustomer(request);
    const city = `E2E City ${uniqueSuffix()}`;

    const address = await call(request, SHOP_ACCOUNT.CUSTOMER_ADDRESSES, {
      method: 'POST',
      token: customer.token,
      data: {
        firstName: 'Shop',
        lastName: 'Account',
        email: customer.email,
        phone: '5551234567',
        address1: '1 E2E Street',
        city,
        state: 'CA',
        country: 'US',
        postcode: '90001',
        defaultAddress: false,
      },
    });
    expect(address.status).toBe(201);

    const { status, body } = await call(request, SHOP_ACCOUNT.GDPR_DATA, { token: customer.token });

    expect(status).toBe(200);
    expect(body.customer.id).toBe(customer.id);
    expect(body.customer.email).toBe(customer.email);
    expect(body.customer.firstName).toBe('Shop');
    expect(body.addresses).toHaveLength(1);
    expect(body.addresses[0].city).toBe(city);
    expect(body.orders).toEqual([]);
  });

  test('should export the orders of the customer', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));
    test.skip(!(await gdprEnabled(request)), 'GDPR is disabled in the admin configuration.');

    const token = await loginToken(request, fx.customer!);
    const { status, body } = await call(request, SHOP_ACCOUNT.GDPR_DATA, { token });

    expect(status).toBe(200);
    expect(body.customer.id).toBe(fx.customer!.id);

    const order = body.orders.find((row: any) => row.id === fx.order!.id);
    expect(order).toBeTruthy();
    expect(order.increment_id).toBe(fx.order!.incrementId);
    expect(Array.isArray(order.items)).toBe(true);
    expect(order.items.length).toBeGreaterThan(0);
  });

  test('should download the gdpr export as a pdf', async ({ request }) => {
    test.skip(!(await gdprEnabled(request)), 'GDPR is disabled in the admin configuration.');

    const customer = await registerCustomer(request);

    const response = await sendRestRequest(request, SHOP_ACCOUNT.GDPR_DATA_PDF, { headers: bearer(customer.token) });

    expect(response.status()).toBe(200);
    expect(response.headers()['content-type']).toContain('application/pdf');
    expect(response.headers()['content-disposition']).toContain('customerInfo.pdf');

    const bytes = await response.body();
    expect(bytes.subarray(0, 4).toString('latin1')).toBe('%PDF');
  });

  test('should refuse the gdpr export without a customer token', async ({ request }) => {
    const data = await call(request, SHOP_ACCOUNT.GDPR_DATA);
    expect(data.status).toBe(401);
    expect(data.body.detail).toBe(MSG.CART_AUTH_REQUIRED);

    const pdf = await sendRestRequest(request, SHOP_ACCOUNT.GDPR_DATA_PDF);
    expect(pdf.status()).toBe(401);
    expect(await pdf.text()).toContain(MSG.CART_AUTH_REQUIRED);
  });

  test('should answer the gdpr export with not found while gdpr is off', async ({ request }) => {
    test.skip(await gdprEnabled(request), 'GDPR is enabled in the admin configuration.');

    const customer = await registerCustomer(request);

    const data = await call(request, SHOP_ACCOUNT.GDPR_DATA, { token: customer.token });
    expect(data.status).toBe(404);
    expect(data.body.detail).toBe(MSG.GDPR_DISABLED);

    const pdf = await sendRestRequest(request, SHOP_ACCOUNT.GDPR_DATA_PDF, { headers: bearer(customer.token) });
    expect(pdf.status()).toBe(404);
    expect(await pdf.text()).toContain(MSG.GDPR_DISABLED);
  });
});
