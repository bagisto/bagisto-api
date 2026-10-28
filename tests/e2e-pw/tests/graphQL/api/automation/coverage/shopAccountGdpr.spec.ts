import { test, expect, APIRequestContext } from '@playwright/test';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../../graphql/helpers/testSupport';
import { SHOP_ACCOUNT_QUERIES as Q } from '../../../graphql/Queries/coverage/shopAccount.queries';
import { SHOP_ACCOUNT_MESSAGES as MSG } from '../../../../restAPI/rest/endpoints/coverage/shopAccount.endpoints';
import { e2eFixtures, missingFixtureReason, E2eCustomerFixture } from '../../../../fixtures/e2eFixtures';

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

function gdprIri(id: number): string {
  return `/api/shop/gdpr-requests/${id}`;
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

async function registerCustomer(request: APIRequestContext): Promise<RegisteredCustomer> {
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
  const customer = body.data.createCustomer.customer;
  let token = customer.token;

  if (!token) {
    const loginBody = await gql(request, Q.loginCustomer, { email, password });
    token = loginBody.data.createCustomerLogin.customerLogin.token;
  }

  expect(typeof token).toBe('string');

  return { id: Number(customer._id), email, token };
}

function expectError(body: any, field: string, message: string): void {
  expect(body.data?.[field] ?? null).toBeNull();
  expect(graphQLErrorMessages(body)).toContain(message);
}

async function gdprEnabled(request: APIRequestContext): Promise<boolean> {
  const body = await gql(request, Q.gdprStatus);
  expect(body.errors).toBeUndefined();

  return body.data.gdprStatus.enabled === true;
}

async function raiseRequest(request: APIRequestContext, customer: RegisteredCustomer): Promise<any> {
  const message = `e2e gdpr update ${uniqueSuffix()}`;

  const body = await gql(request, Q.createGdprRequest, { type: 'update', message }, customer.token);
  expect(body.errors).toBeUndefined();

  const raised = body.data.createGdprRequest.gdprRequest;
  expect(typeof raised._id).toBe('number');
  expect(raised.type).toBe('update');
  expect(raised.status).toBe('pending');
  expect(raised.message).toBe(message);
  expect(raised.email).toBe(customer.email);
  expect(raised.successMessage).toBe(MSG.GDPR_RAISED);

  return raised;
}

async function requestIds(request: APIRequestContext, token: string): Promise<number[]> {
  const body = await gql(request, Q.gdprRequests, { first: 50 }, token);
  expect(body.errors).toBeUndefined();

  return body.data.gdprRequests.edges.map((edge: any) => edge.node._id);
}

test.describe('shop account gdpr status graphql api', () => {
  test('should report the gdpr setting of the current channel', async ({ request }) => {
    const body = await gql(request, Q.gdprStatus);

    expect(body.errors).toBeUndefined();
    expect(typeof body.data.gdprStatus.channel).toBe('string');
    expect(body.data.gdprStatus.channel.length).toBeGreaterThan(0);
    expect(typeof body.data.gdprStatus.enabled).toBe('boolean');
  });
});

test.describe('shop account gdpr requests graphql api', () => {
  test('should raise a gdpr request and read it back', async ({ request }) => {
    test.skip(!(await gdprEnabled(request)), 'GDPR is disabled in the admin configuration.');

    const customer = await registerCustomer(request);
    const raised = await raiseRequest(request, customer);

    expect(await requestIds(request, customer.token)).toEqual([raised._id]);

    const detail = await gql(request, Q.gdprRequest, { id: gdprIri(raised._id) }, customer.token);
    expect(detail.errors).toBeUndefined();
    expect(detail.data.gdprRequest._id).toBe(raised._id);
    expect(detail.data.gdprRequest.status).toBe('pending');
    expect(detail.data.gdprRequest.message).toBe(raised.message);
  });

  test('should revoke a pending gdpr request and refuse a second revoke', async ({ request }) => {
    test.skip(!(await gdprEnabled(request)), 'GDPR is disabled in the admin configuration.');

    const customer = await registerCustomer(request);
    const raised = await raiseRequest(request, customer);

    const revoke = await gql(request, Q.revokeGdprRequest, { id: gdprIri(raised._id) }, customer.token);
    expect(revoke.errors).toBeUndefined();

    const revoked = revoke.data.revokeGdprRequest.gdprRequest;
    expect(revoked._id).toBe(raised._id);
    expect(revoked.status).toBe('revoked');
    expect(typeof revoked.revokedAt).toBe('string');

    expectError(await gql(request, Q.revokeGdprRequest, { id: gdprIri(raised._id) }, customer.token), 'revokeGdprRequest', MSG.GDPR_REVOKE_NOT_ALLOWED);

    const detail = await gql(request, Q.gdprRequest, { id: gdprIri(raised._id) }, customer.token);
    expect(detail.data.gdprRequest.status).toBe('revoked');
  });

  test('should delete a gdpr request the customer raised', async ({ request }) => {
    test.skip(!(await gdprEnabled(request)), 'GDPR is disabled in the admin configuration.');

    const customer = await registerCustomer(request);
    const raised = await raiseRequest(request, customer);

    const removal = await gql(request, Q.deleteGdprRequest, { id: gdprIri(raised._id) }, customer.token);
    expect(removal.errors).toBeUndefined();
    expect(removal.data.deleteGdprRequest.gdprRequest).toBeTruthy();

    expect(await requestIds(request, customer.token)).toEqual([]);
    expectError(await gql(request, Q.gdprRequest, { id: gdprIri(raised._id) }, customer.token), 'gdprRequest', MSG.GDPR_NOT_FOUND);
  });

  test('should keep a gdpr request out of reach of another customer', async ({ request }) => {
    test.skip(!(await gdprEnabled(request)), 'GDPR is disabled in the admin configuration.');

    const owner = await registerCustomer(request);
    const raised = await raiseRequest(request, owner);
    const intruder = await registerCustomer(request);

    expectError(await gql(request, Q.gdprRequest, { id: gdprIri(raised._id) }, intruder.token), 'gdprRequest', MSG.GDPR_NOT_FOUND);

    const revoke = await gql(request, Q.revokeGdprRequest, { id: gdprIri(raised._id) }, intruder.token);
    expect(revoke.data?.revokeGdprRequest ?? null).toBeNull();
    expect(graphQLErrorMessages(revoke).length).toBeGreaterThan(0);

    const removal = await gql(request, Q.deleteGdprRequest, { id: gdprIri(raised._id) }, intruder.token);
    expect(removal.data?.deleteGdprRequest ?? null).toBeNull();
    expect(graphQLErrorMessages(removal).length).toBeGreaterThan(0);

    expect(await requestIds(request, intruder.token)).toEqual([]);

    const ownerView = await gql(request, Q.gdprRequest, { id: gdprIri(raised._id) }, owner.token);
    expect(ownerView.errors).toBeUndefined();
    expect(ownerView.data.gdprRequest.status).toBe('pending');
  });

  test('should reject a gdpr request with an unknown type or no message', async ({ request }) => {
    test.skip(!(await gdprEnabled(request)), 'GDPR is disabled in the admin configuration.');

    const customer = await registerCustomer(request);

    expectError(await gql(request, Q.createGdprRequest, { type: 'export', message: 'e2e gdpr' }, customer.token), 'createGdprRequest', MSG.GDPR_TYPE_INVALID);
    expectError(await gql(request, Q.createGdprRequest, { type: 'delete', message: '' }, customer.token), 'createGdprRequest', MSG.GDPR_MESSAGE_REQUIRED);

    expect(await requestIds(request, customer.token)).toEqual([]);
  });

  test('should refuse gdpr requests without a customer token', async ({ request }) => {
    test.skip(!(await gdprEnabled(request)), 'GDPR is disabled in the admin configuration.');

    expectError(await gql(request, Q.gdprRequests, { first: 10 }), 'gdprRequests', MSG.GDPR_UNAUTHENTICATED);
    expectError(await gql(request, Q.gdprRequest, { id: gdprIri(1) }), 'gdprRequest', MSG.GDPR_UNAUTHENTICATED);
    expectError(await gql(request, Q.createGdprRequest, { type: 'update', message: 'e2e gdpr' }), 'createGdprRequest', MSG.GDPR_UNAUTHENTICATED);
  });

  test('should answer gdpr requests with the disabled message while gdpr is off', async ({ request }) => {
    test.skip(await gdprEnabled(request), 'GDPR is enabled in the admin configuration.');

    const customer = await registerCustomer(request);

    expectError(await gql(request, Q.gdprRequests, { first: 10 }, customer.token), 'gdprRequests', MSG.GDPR_DISABLED);
    expectError(await gql(request, Q.gdprRequest, { id: gdprIri(1) }, customer.token), 'gdprRequest', MSG.GDPR_DISABLED);
    expectError(await gql(request, Q.createGdprRequest, { type: 'update', message: 'e2e gdpr' }, customer.token), 'createGdprRequest', MSG.GDPR_DISABLED);
  });
});

test.describe('shop account gdpr data export graphql api', () => {
  test('should export only the authenticated customer data', async ({ request }) => {
    test.skip(!(await gdprEnabled(request)), 'GDPR is disabled in the admin configuration.');

    const customer = await registerCustomer(request);
    const body = await gql(request, Q.gdprData, {}, customer.token);

    expect(body.errors).toBeUndefined();
    expect(body.data.gdprData.customer.id).toBe(customer.id);
    expect(body.data.gdprData.customer.email).toBe(customer.email);
    expect(body.data.gdprData.addresses).toEqual([]);
    expect(body.data.gdprData.orders).toEqual([]);
  });

  test('should export the orders of the customer', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));
    test.skip(!(await gdprEnabled(request)), 'GDPR is disabled in the admin configuration.');

    const token = await loginToken(request, fx.customer!);
    const body = await gql(request, Q.gdprData, {}, token);

    expect(body.errors).toBeUndefined();
    expect(body.data.gdprData.customer.id).toBe(fx.customer!.id);
    expect(body.data.gdprData.orders.map((order: any) => order.id)).toContain(fx.order!.id);
  });

  test('should return the gdpr export as a base64 pdf', async ({ request }) => {
    test.skip(!(await gdprEnabled(request)), 'GDPR is disabled in the admin configuration.');

    const customer = await registerCustomer(request);
    const body = await gql(request, Q.gdprDataPdf, {}, customer.token);

    expect(body.errors).toBeUndefined();
    const pdf = body.data.gdprDataPdf;
    expect(pdf.fileName).toBe('customerInfo.pdf');
    expect(pdf.mimeType).toBe('application/pdf');

    const bytes = Buffer.from(pdf.content, 'base64');
    expect(bytes.length).toBe(pdf.size);
    expect(bytes.subarray(0, 4).toString('latin1')).toBe('%PDF');
  });

  test('should refuse the gdpr export without a customer token', async ({ request }) => {
    expectError(await gql(request, Q.gdprData), 'gdprData', MSG.CART_AUTH_REQUIRED);
    expectError(await gql(request, Q.gdprDataPdf), 'gdprDataPdf', MSG.CART_AUTH_REQUIRED);
  });

  test('should refuse the gdpr export while gdpr is off', async ({ request }) => {
    test.skip(await gdprEnabled(request), 'GDPR is enabled in the admin configuration.');

    const customer = await registerCustomer(request);

    expectError(await gql(request, Q.gdprData, {}, customer.token), 'gdprData', MSG.GDPR_DISABLED);
    expectError(await gql(request, Q.gdprDataPdf, {}, customer.token), 'gdprDataPdf', MSG.GDPR_DISABLED);
  });
});
