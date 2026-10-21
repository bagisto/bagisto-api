import { test, expect, APIRequestContext } from '@playwright/test';
import { randomUUID } from 'crypto';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../../graphql/helpers/testSupport';
import { SHOP_ACCOUNT_QUERIES as Q } from '../../../graphql/Queries/coverage/shopAccount.queries';
import { SHOP_ACCOUNT_MESSAGES as MSG } from '../../../../restAPI/rest/endpoints/coverage/shopAccount.endpoints';
import { e2eFixtures, missingFixtureReason, E2eCustomerFixture } from '../../../../fixtures/e2eFixtures';

const UUID_PATTERN = /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;

const WITHDRAWAL_STATUSES = ['received', 'refunded', 'declined'];

function uniqueSuffix(): string {
  return `${Date.now()}-${Math.floor(Math.random() * 1_000_000)}`;
}

function bearer(token: string): Record<string, string> {
  return { Authorization: `Bearer ${token}` };
}

function withdrawalIri(id: number): string {
  return `/api/shop/eu-withdrawals/${id}`;
}

async function gql(request: APIRequestContext, query: string, variables: Record<string, any> = {}, token?: string): Promise<any> {
  const response = await sendGraphQLRequest(request, query, variables, token ? bearer(token) : {});
  expect(response.status()).toBe(200);

  return response.json();
}

async function loginToken(request: APIRequestContext, customer: E2eCustomerFixture): Promise<string> {
  const body = await gql(request, Q.loginCustomer, { email: customer.email, password: customer.password });
  expect(body.errors).toBeUndefined();

  const login = body.data.createCustomerLogin.customerLogin;
  expect(login.success).toBe(true);

  return login.token;
}

async function registerToken(request: APIRequestContext): Promise<string> {
  const email = `shop-account-${uniqueSuffix()}@example.com`;
  const password = 'Passw0rd!123';

  const body = await gql(request, Q.registerCustomer, {
    input: {
      firstName: 'Shop',
      lastName: 'Account',
      email,
      password,
      confirmPassword: password,
      status: '1',
      isVerified: '1',
      isSuspended: '0',
      subscribedToNewsLetter: false,
    },
  });

  expect(body.errors).toBeUndefined();
  const token = body.data.createCustomer.customer.token;

  if (token) {
    return token;
  }

  const loginBody = await gql(request, Q.loginCustomer, { email, password });

  return loginBody.data.createCustomerLogin.customerLogin.token;
}

function expectError(body: any, field: string, message: string): void {
  expect(body.data?.[field] ?? null).toBeNull();
  expect(graphQLErrorMessages(body)).toContain(message);
}

async function withdrawalEnabled(request: APIRequestContext): Promise<boolean> {
  const body = await gql(request, Q.euWithdrawalStatus);
  expect(body.errors).toBeUndefined();

  return body.data.euWithdrawalStatus.enabled === true;
}

test.describe('shop account eu withdrawal status graphql api', () => {
  test('should report the eu withdrawal setting of the current channel', async ({ request }) => {
    const body = await gql(request, Q.euWithdrawalStatus);

    expect(body.errors).toBeUndefined();
    expect(typeof body.data.euWithdrawalStatus.channel).toBe('string');
    expect(body.data.euWithdrawalStatus.channel.length).toBeGreaterThan(0);
    expect(typeof body.data.euWithdrawalStatus.enabled).toBe('boolean');
  });
});

test.describe('shop account eu withdrawals graphql api', () => {
  test('should refuse the withdrawal list without a customer token', async ({ request }) => {
    expectError(await gql(request, Q.euWithdrawals, { first: 10 }), 'euWithdrawals', MSG.UNAUTHENTICATED);
  });

  test('should list no withdrawals for a customer without orders', async ({ request }) => {
    const token = await registerToken(request);

    const body = await gql(request, Q.euWithdrawals, { first: 10 }, token);

    expect(body.errors).toBeUndefined();
    expect(body.data.euWithdrawals.edges).toEqual([]);
  });

  test('should file a withdrawal for the customer order once and read it back', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));
    test.skip(!(await withdrawalEnabled(request)), 'EU withdrawal is disabled on the current channel.');

    const token = await loginToken(request, fx.customer!);
    const variables = { orderId: fx.order!.id, reasonText: `e2e withdrawal ${uniqueSuffix()}` };

    const first = await gql(request, Q.createEuWithdrawal, variables, token);
    expect(first.errors).toBeUndefined();

    const filed = first.data.createEuWithdrawal.euWithdrawal;
    expect(typeof filed._id).toBe('number');
    expect(filed.orderId).toBe(fx.order!.id);
    expect(filed.orderIncrementId).toBe(fx.order!.incrementId);
    expect(filed.isGuest).toBe(false);
    expect(filed.uuid).toMatch(UUID_PATTERN);
    expect(WITHDRAWAL_STATUSES).toContain(filed.status);

    const second = await gql(request, Q.createEuWithdrawal, variables, token);
    expect(second.errors).toBeUndefined();
    expect(second.data.createEuWithdrawal.euWithdrawal._id).toBe(filed._id);

    const detail = await gql(request, Q.euWithdrawal, { id: withdrawalIri(filed._id) }, token);
    expect(detail.errors).toBeUndefined();
    expect(detail.data.euWithdrawal._id).toBe(filed._id);
    expect(detail.data.euWithdrawal.uuid).toBe(filed.uuid);
    expect(typeof detail.data.euWithdrawal.receivedAt).toBe('string');

    const list = await gql(request, Q.euWithdrawals, { first: 50 }, token);
    expect(list.errors).toBeUndefined();
    expect(list.data.euWithdrawals.edges.map((edge: any) => edge.node._id)).toContain(filed._id);
  });

  test('should refuse a withdrawal while the channel has it disabled', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));
    test.skip(await withdrawalEnabled(request), 'EU withdrawal is enabled on the current channel.');

    const token = await loginToken(request, fx.customer!);

    expectError(
      await gql(request, Q.createEuWithdrawal, { orderId: fx.order!.id, reasonText: 'e2e withdrawal' }, token),
      'createEuWithdrawal',
      MSG.EU_ORDER_NOT_FOUND
    );
  });

  test('should refuse a withdrawal for another customer order', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.otherCustomer, missingFixtureReason('otherCustomer'));

    const token = await loginToken(request, fx.otherCustomer!);

    expectError(
      await gql(request, Q.createEuWithdrawal, { orderId: fx.order!.id, reasonText: 'e2e withdrawal' }, token),
      'createEuWithdrawal',
      MSG.EU_ORDER_NOT_FOUND
    );
  });

  test('should hide a customer withdrawal from another customer', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.customer, missingFixtureReason('customer'));
    test.skip(!fx.otherCustomer, missingFixtureReason('otherCustomer'));

    const ownerToken = await loginToken(request, fx.customer!);
    const owned = await gql(request, Q.euWithdrawals, { first: 1 }, ownerToken);
    expect(owned.errors).toBeUndefined();
    test.skip(owned.data.euWithdrawals.edges.length === 0, 'The fixture customer has filed no withdrawal.');

    const withdrawalId = owned.data.euWithdrawals.edges[0].node._id;
    const otherToken = await loginToken(request, fx.otherCustomer!);

    expectError(await gql(request, Q.euWithdrawal, { id: withdrawalIri(withdrawalId) }, otherToken), 'euWithdrawal', MSG.EU_NOT_FOUND);
  });

  test('should reject a withdrawal reason longer than 5000 characters', async ({ request }) => {
    const token = await registerToken(request);

    expectError(
      await gql(request, Q.createEuWithdrawal, { orderId: 999999999, reasonText: 'x'.repeat(5001) }, token),
      'createEuWithdrawal',
      MSG.EU_REASON_TOO_LONG
    );
  });

  test('should refuse to file a withdrawal without a customer token', async ({ request }) => {
    expectError(await gql(request, Q.createEuWithdrawal, { orderId: 1 }), 'createEuWithdrawal', MSG.UNAUTHENTICATED);
  });
});

test.describe('shop account guest eu withdrawals graphql api', () => {
  test('should answer an unknown guest order with the constant not found response', async ({ request }) => {
    expectError(
      await gql(request, Q.createGuestEuWithdrawal, {
        orderIncrementId: `E2E-${uniqueSuffix()}`,
        email: `shop-account-guest-${uniqueSuffix()}@example.com`,
        reasonText: 'e2e guest withdrawal',
      }),
      'createGuestEuWithdrawal',
      MSG.EU_ORDER_NOT_FOUND
    );
  });

  test('should require the order number for a guest withdrawal', async ({ request }) => {
    expectError(
      await gql(request, Q.createGuestEuWithdrawal, { email: `shop-account-guest-${uniqueSuffix()}@example.com` }),
      'createGuestEuWithdrawal',
      MSG.EU_ORDER_INCREMENT_REQUIRED
    );
  });

  test('should reject a guest withdrawal with an invalid email', async ({ request }) => {
    expectError(
      await gql(request, Q.createGuestEuWithdrawal, { orderIncrementId: `E2E-${uniqueSuffix()}`, email: 'not-an-email' }),
      'createGuestEuWithdrawal',
      MSG.EMAIL_INVALID
    );
  });

  test('should answer an unknown guest confirmation with not found', async ({ request }) => {
    expectError(
      await gql(request, Q.guestEuWithdrawalConfirmation, { uuid: randomUUID(), email: `shop-account-guest-${uniqueSuffix()}@example.com` }),
      'guestEuWithdrawalConfirmation',
      MSG.EU_NOT_FOUND
    );
  });

  test('should reject a guest confirmation lookup with a malformed uuid', async ({ request }) => {
    expectError(
      await gql(request, Q.guestEuWithdrawalConfirmation, { uuid: 'not-a-uuid', email: `shop-account-guest-${uniqueSuffix()}@example.com` }),
      'guestEuWithdrawalConfirmation',
      MSG.UUID_INVALID
    );
  });

  test('should reject a guest confirmation lookup with an invalid email', async ({ request }) => {
    expectError(
      await gql(request, Q.guestEuWithdrawalConfirmation, { uuid: randomUUID(), email: 'not-an-email' }),
      'guestEuWithdrawalConfirmation',
      MSG.EMAIL_INVALID
    );
  });
});
