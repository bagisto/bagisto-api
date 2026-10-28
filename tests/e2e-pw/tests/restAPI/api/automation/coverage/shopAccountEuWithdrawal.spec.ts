import { test, expect, APIRequestContext } from '@playwright/test';
import { randomUUID } from 'crypto';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import { SHOP_ACCOUNT, SHOP_ACCOUNT_MESSAGES as MSG } from '../../../rest/endpoints/coverage/shopAccount.endpoints';
import { e2eFixtures, missingFixtureReason, E2eCustomerFixture } from '../../../../fixtures/e2eFixtures';

const UUID_PATTERN = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;

const WITHDRAWAL_STATUSES = ['received', 'refunded', 'declined'];

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

async function withdrawalEnabled(request: APIRequestContext): Promise<boolean> {
  const { status, body } = await call(request, SHOP_ACCOUNT.EU_WITHDRAWAL_STATUS);
  expect(status).toBe(200);

  return body[0].enabled === true;
}

test.describe('shop account eu withdrawal status rest api', () => {
  test('should report the eu withdrawal setting of the current channel', async ({ request }) => {
    const { status, body } = await call(request, SHOP_ACCOUNT.EU_WITHDRAWAL_STATUS);

    expect(status).toBe(200);
    expect(Array.isArray(body)).toBe(true);
    expect(body).toHaveLength(1);
    expect(typeof body[0].channel).toBe('string');
    expect(body[0].id).toBe(body[0].channel);
    expect(typeof body[0].enabled).toBe('boolean');
  });
});

test.describe('shop account eu withdrawals rest api', () => {
  test('should refuse the withdrawal list without a customer token', async ({ request }) => {
    const { status, body } = await call(request, SHOP_ACCOUNT.EU_WITHDRAWALS);

    expect(status).toBe(403);
    expect(body.detail).toBe(MSG.UNAUTHENTICATED);
  });

  test('should list no withdrawals for a customer without orders', async ({ request }) => {
    const token = await registerToken(request);

    const { status, body } = await call(request, SHOP_ACCOUNT.EU_WITHDRAWALS, { token });

    expect(status).toBe(200);
    expect(body).toEqual([]);
  });

  test('should file a withdrawal for the customer order once and read it back', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));
    test.skip(!(await withdrawalEnabled(request)), 'EU withdrawal is disabled on the current channel.');

    const token = await loginToken(request, fx.customer!);
    const data = { order_id: fx.order!.id, reason_text: `e2e withdrawal ${uniqueSuffix()}` };

    const first = await call(request, SHOP_ACCOUNT.EU_WITHDRAWALS, { method: 'POST', token, data });
    expect(first.status).toBe(201);
    expect(typeof first.body.id).toBe('number');
    expect(first.body.orderId).toBe(fx.order!.id);
    expect(first.body.orderIncrementId).toBe(fx.order!.incrementId);
    expect(first.body.isGuest).toBe(false);
    expect(first.body.customerEmail).toBe(fx.customer!.email);
    expect(first.body.uuid).toMatch(UUID_PATTERN);
    expect(WITHDRAWAL_STATUSES).toContain(first.body.status);

    const second = await call(request, SHOP_ACCOUNT.EU_WITHDRAWALS, { method: 'POST', token, data });
    expect(second.status).toBe(201);
    expect(second.body.id).toBe(first.body.id);
    expect(second.body.uuid).toBe(first.body.uuid);

    const detail = await call(request, SHOP_ACCOUNT.EU_WITHDRAWAL(first.body.id), { token });
    expect(detail.status).toBe(200);
    expect(detail.body.id).toBe(first.body.id);
    expect(detail.body.uuid).toBe(first.body.uuid);
    expect(detail.body.orderId).toBe(fx.order!.id);
    expect(typeof detail.body.receivedAt).toBe('string');

    const list = await call(request, SHOP_ACCOUNT.EU_WITHDRAWALS, { token, params: { per_page: '50' } });
    expect(list.status).toBe(200);
    expect(list.body.map((row: any) => row.id)).toContain(first.body.id);
  });

  test('should refuse a withdrawal while the channel has it disabled', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));
    test.skip(await withdrawalEnabled(request), 'EU withdrawal is enabled on the current channel.');

    const token = await loginToken(request, fx.customer!);

    const { status, body } = await call(request, SHOP_ACCOUNT.EU_WITHDRAWALS, {
      method: 'POST',
      token,
      data: { order_id: fx.order!.id, reason_text: 'e2e withdrawal' },
    });

    expect(status).toBe(404);
    expect(body.detail).toBe(MSG.EU_ORDER_NOT_FOUND);
  });

  test('should refuse a withdrawal for another customer order', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.otherCustomer, missingFixtureReason('otherCustomer'));

    const token = await loginToken(request, fx.otherCustomer!);
    const before = await call(request, SHOP_ACCOUNT.EU_WITHDRAWALS, { token, params: { per_page: '50' } });

    const { status, body } = await call(request, SHOP_ACCOUNT.EU_WITHDRAWALS, {
      method: 'POST',
      token,
      data: { order_id: fx.order!.id, reason_text: 'e2e withdrawal' },
    });

    expect(status).toBe(404);
    expect(body.detail).toBe(MSG.EU_ORDER_NOT_FOUND);

    const after = await call(request, SHOP_ACCOUNT.EU_WITHDRAWALS, { token, params: { per_page: '50' } });
    expect(after.body.map((row: any) => row.id)).toEqual(before.body.map((row: any) => row.id));
  });

  test('should hide a customer withdrawal from another customer', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.customer, missingFixtureReason('customer'));
    test.skip(!fx.otherCustomer, missingFixtureReason('otherCustomer'));

    const ownerToken = await loginToken(request, fx.customer!);
    const owned = await call(request, SHOP_ACCOUNT.EU_WITHDRAWALS, { token: ownerToken });
    expect(owned.status).toBe(200);
    test.skip(owned.body.length === 0, 'The fixture customer has filed no withdrawal.');

    const withdrawalId = owned.body[0].id;
    const otherToken = await loginToken(request, fx.otherCustomer!);

    const detail = await call(request, SHOP_ACCOUNT.EU_WITHDRAWAL(withdrawalId), { token: otherToken });
    expect(detail.status).toBe(404);
    expect(detail.body.detail).toBe(MSG.EU_NOT_FOUND);

    const list = await call(request, SHOP_ACCOUNT.EU_WITHDRAWALS, { token: otherToken, params: { per_page: '50' } });
    expect(list.body.map((row: any) => row.id)).not.toContain(withdrawalId);
  });

  test('should return 404 for a withdrawal id that does not exist', async ({ request }) => {
    const token = await registerToken(request);

    const { status, body } = await call(request, SHOP_ACCOUNT.EU_WITHDRAWAL(999999999), { token });

    expect(status).toBe(404);
    expect(body.detail).toBe(MSG.EU_NOT_FOUND);
  });

  test('should refuse a single withdrawal without a customer token', async ({ request }) => {
    const { status, body } = await call(request, SHOP_ACCOUNT.EU_WITHDRAWAL(1));

    expect(status).toBe(403);
    expect(body.detail).toBe(MSG.UNAUTHENTICATED);
  });

  test('should reject a withdrawal reason longer than 5000 characters', async ({ request }) => {
    const token = await registerToken(request);

    const { status, body } = await call(request, SHOP_ACCOUNT.EU_WITHDRAWALS, {
      method: 'POST',
      token,
      data: { order_id: 999999999, reason_text: 'x'.repeat(5001) },
    });

    expect(status).toBe(422);
    expect(body.detail).toBe(MSG.EU_REASON_TOO_LONG);
  });

  test('should refuse to file a withdrawal without a customer token', async ({ request }) => {
    const { status, body } = await call(request, SHOP_ACCOUNT.EU_WITHDRAWALS, { method: 'POST', data: { order_id: 1 } });

    expect(status).toBe(403);
    expect(body.detail).toBe(MSG.UNAUTHENTICATED);
  });
});

test.describe('shop account guest eu withdrawals rest api', () => {
  test('should answer an unknown guest order with the constant not found response', async ({ request }) => {
    const { status, body } = await call(request, SHOP_ACCOUNT.EU_WITHDRAWAL_GUEST, {
      method: 'POST',
      data: {
        order_increment_id: `E2E-${uniqueSuffix()}`,
        email: `shop-account-guest-${uniqueSuffix()}@example.com`,
        reason_text: 'e2e guest withdrawal',
      },
    });

    expect(status).toBe(404);
    expect(body.detail).toBe(MSG.EU_ORDER_NOT_FOUND);
  });

  test('should refuse a guest withdrawal for a customer order', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const { status, body } = await call(request, SHOP_ACCOUNT.EU_WITHDRAWAL_GUEST, {
      method: 'POST',
      data: { order_increment_id: fx.order!.incrementId, email: fx.customer!.email },
    });

    expect(status).toBe(404);
    expect(body.detail).toBe(MSG.EU_ORDER_NOT_FOUND);
  });

  test('should require the order number for a guest withdrawal', async ({ request }) => {
    const { status, body } = await call(request, SHOP_ACCOUNT.EU_WITHDRAWAL_GUEST, {
      method: 'POST',
      data: { email: `shop-account-guest-${uniqueSuffix()}@example.com` },
    });

    expect(status).toBe(422);
    expect(body.detail).toBe(MSG.EU_ORDER_INCREMENT_REQUIRED);
  });

  test('should reject a guest withdrawal with an invalid email', async ({ request }) => {
    const { status, body } = await call(request, SHOP_ACCOUNT.EU_WITHDRAWAL_GUEST, {
      method: 'POST',
      data: { order_increment_id: `E2E-${uniqueSuffix()}`, email: 'not-an-email' },
    });

    expect(status).toBe(422);
    expect(body.detail).toBe(MSG.EMAIL_INVALID);
  });

  test('should answer an unknown guest confirmation with not found', async ({ request }) => {
    const { status, body } = await call(request, SHOP_ACCOUNT.EU_WITHDRAWAL_GUEST_CONFIRMATION(randomUUID()), {
      params: { email: `shop-account-guest-${uniqueSuffix()}@example.com` },
    });

    expect(status).toBe(404);
    expect(body.detail).toBe(MSG.EU_NOT_FOUND);
  });

  test('should not reveal a customer withdrawal through the guest confirmation', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const token = await loginToken(request, fx.customer!);
    const owned = await call(request, SHOP_ACCOUNT.EU_WITHDRAWALS, { token });
    test.skip(owned.body.length === 0, 'The fixture customer has filed no withdrawal.');

    const { status, body } = await call(request, SHOP_ACCOUNT.EU_WITHDRAWAL_GUEST_CONFIRMATION(owned.body[0].uuid), {
      params: { email: fx.customer!.email },
    });

    expect(status).toBe(404);
    expect(body.detail).toBe(MSG.EU_NOT_FOUND);
  });

  test('should reject a guest confirmation lookup with a malformed uuid', async ({ request }) => {
    const { status, body } = await call(request, SHOP_ACCOUNT.EU_WITHDRAWAL_GUEST_CONFIRMATION('not-a-uuid'), {
      params: { email: `shop-account-guest-${uniqueSuffix()}@example.com` },
    });

    expect(status).toBe(422);
    expect(body.detail).toBe(MSG.UUID_INVALID);
  });

  test('should require the order email for a guest confirmation lookup', async ({ request }) => {
    const { status, body } = await call(request, SHOP_ACCOUNT.EU_WITHDRAWAL_GUEST_CONFIRMATION(randomUUID()));

    expect(status).toBe(422);
    expect(body.detail).toBe(MSG.EMAIL_REQUIRED);
  });
});
