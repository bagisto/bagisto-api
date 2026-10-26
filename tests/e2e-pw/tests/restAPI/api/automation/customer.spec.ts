import { test, expect, APIRequestContext } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import { bearer, expectProblem, readJson, uniqueSuffix } from '../../rest/helpers/coverage/shopCart.helpers';

const CUSTOMER_ADDRESSES = '/api/shop/customer-addresses';
const VERIFY_TOKENS = '/api/shop/verify-tokens';

const MSG = {
  LOGIN_SUCCESS: 'You have logged in successfully',
  INVALID_CREDENTIALS: 'Invalid email or password',
  LOGIN_FIELDS_REQUIRED: 'The email field is required. The password field is required.',
  PROFILE_TOKEN_REQUIRED: 'Authentication token is required. Please provide token in query input',
  PROFILE_UPDATED: 'Customer profile updated successfully',
  CURRENT_PASSWORD_INCORRECT: 'The current password you entered is incorrect',
  UNAUTHENTICATED: 'Unauthenticated. Please login to perform this action',
  ADDRESS_TOKEN_REQUIRED: 'Authentication token is required. Please provide the token in the Authorization header as "Bearer <token>"',
};

interface RegisteredCustomer {
  id: number;
  email: string;
  password: string;
  firstName: string;
  lastName: string;
  token: string;
}

function newCredentials(): { email: string; password: string; firstName: string; lastName: string } {
  const suffix = uniqueSuffix();

  return {
    email: `rest.customer.${suffix}@example.com`,
    password: `Cust${suffix}!Aa`,
    firstName: 'Rest',
    lastName: 'Customer',
  };
}

async function registerCustomer(request: APIRequestContext): Promise<RegisteredCustomer> {
  const credentials = newCredentials();

  const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_REGISTER, {
    method: 'POST',
    data: { ...credentials, confirmPassword: credentials.password },
  });
  const body = await readJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(201);
  expectAccessToken(body);

  return { id: Number(body.id), ...credentials, token: body.accessToken };
}

async function login(request: APIRequestContext, email: string, password: string, status = 201): Promise<any> {
  const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_LOGIN, {
    method: 'POST',
    data: { email, password },
  });
  const body = await readJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(status);

  return body;
}

function expectAccessToken(body: any): void {
  expect(String(body.accessToken).split('.'), JSON.stringify(body)).toHaveLength(3);
  expect(typeof body.refreshToken).toBe('string');
  expect(Number(body.expiresIn)).toBeGreaterThan(0);
}

function expectTokenPair(body: any): void {
  expect(body.tokenType, JSON.stringify(body)).toBe('Bearer');
  expect(String(body.accessToken).split('.')).toHaveLength(3);
  expect(typeof body.refreshToken).toBe('string');
  expect(Number(body.expiresIn)).toBeGreaterThan(0);
  expect(Number(body.session?.id)).toBeGreaterThan(0);
}

async function readProfile(request: APIRequestContext, token: string): Promise<any> {
  const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_PROFILE, { headers: bearer(token) });
  const body = await readJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);
  expect(Array.isArray(body)).toBe(true);
  expect(body).toHaveLength(1);

  return body[0];
}

test.describe('customer registration rest api', () => {
  test('registers a new customer and issues a token', async ({ request }) => {
    const credentials = newCredentials();
    const phone = `${Date.now()}${Math.floor(Math.random() * 10)}`;

    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_REGISTER, {
      method: 'POST',
      data: { ...credentials, confirmPassword: credentials.password, phone },
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(Number(body.id)).toBeGreaterThan(0);
    expect(body.email).toBe(credentials.email);
    expect(body.firstName).toBe(credentials.firstName);
    expect(body.lastName).toBe(credentials.lastName);
    expect(body.phone).toBe(phone);
    expectAccessToken(body);

    const profile = await readProfile(request, body.accessToken);
    expect(profile.email).toBe(credentials.email);
    expect(profile.phone).toBe(phone);
  });
});

test.describe('customer login rest api', () => {
  test('logs in with the registered credentials', async ({ request }) => {
    const customer = await registerCustomer(request);

    const body = await login(request, customer.email, customer.password);

    expect(body.success).toBe(true);
    expect(body.message).toBe(MSG.LOGIN_SUCCESS);
    expect(Number(body.id)).toBe(customer.id);
    expectTokenPair(body);
    expect(body.token).toBe(body.accessToken);

    const profile = await readProfile(request, body.accessToken);
    expect(profile.email).toBe(customer.email);
  });

  test('refuses credentials that match no customer without issuing a token', async ({ request }) => {
    const body = await login(request, `nobody.${uniqueSuffix()}@example.com`, 'WrongPassword123!', 401);

    expect(body.error).toBe('invalid_credentials');
    expect(body.message).toBe(MSG.INVALID_CREDENTIALS);
    expect(body.accessToken).toBeUndefined();
  });

  test('rejects empty credentials', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_LOGIN, {
      method: 'POST',
      data: {},
    });

    await expectProblem(response, 400, MSG.LOGIN_FIELDS_REQUIRED);
  });
});

test.describe('customer profile rest api', () => {
  let customer: RegisteredCustomer;

  test.beforeEach(async ({ request }) => {
    customer = await registerCustomer(request);
  });

  test('reads the profile of the token owner', async ({ request }) => {
    const profile = await readProfile(request, customer.token);

    expect(String(profile.id)).toBe(String(customer.id));
    expect(profile.email).toBe(customer.email);
    expect(profile.firstName).toBe(customer.firstName);
    expect(profile.lastName).toBe(customer.lastName);
  });

  test('refuses the profile without a token', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_PROFILE);

    await expectProblem(response, 401, MSG.PROFILE_TOKEN_REQUIRED);
  });

  test('updates the profile names and reads them back', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_PROFILE_UPDATE(customer.id), {
      method: 'PUT',
      data: { firstName: 'Updated', lastName: 'Name' },
      headers: bearer(customer.token),
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body.success).toBe(true);
    expect(body.message).toBe(MSG.PROFILE_UPDATED);
    expect(body.firstName).toBe('Updated');
    expect(body.lastName).toBe('Name');

    const profile = await readProfile(request, customer.token);
    expect(profile.firstName).toBe('Updated');
    expect(profile.lastName).toBe('Name');
    expect(profile.email).toBe(customer.email);
  });
});

test.describe('customer token verification rest api', () => {
  test('reports a malformed bearer token as unauthenticated', async ({ request }) => {
    const response = await sendRestRequest(request, VERIFY_TOKENS, {
      method: 'POST',
      data: {},
      headers: bearer('invalid-token'),
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.isValid).toBe(false);
    expect(body.id).toBe(0);
    expect(body.email).toBe('');
    expect(body.message).toBe(MSG.UNAUTHENTICATED);
  });
});

test.describe('customer address rest api', () => {
  let customer: RegisteredCustomer;

  test.beforeEach(async ({ request }) => {
    customer = await registerCustomer(request);
  });

  test('creates an address for the token owner', async ({ request }) => {
    const response = await sendRestRequest(request, CUSTOMER_ADDRESSES, {
      method: 'POST',
      headers: bearer(customer.token),
      data: {
        firstName: 'Test',
        lastName: 'Address',
        address1: '123 Test St',
        city: 'Test City',
        country: 'US',
        state: 'CA',
        postcode: '90210',
        phone: '5550123456',
      },
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.id).toBeGreaterThan(0);
    expect(body.firstName).toBe('Test');
    expect(body.lastName).toBe('Address');
    expect(body.address1).toBe('123 Test St');
    expect(body.city).toBe('Test City');
    expect(body.country).toBe('US');
    expect(body.state).toBe('CA');
    expect(body.postcode).toBe('90210');
  });

  test('lists only the addresses of the token owner', async ({ request }) => {
    const created = await sendRestRequest(request, CUSTOMER_ADDRESSES, {
      method: 'POST',
      headers: bearer(customer.token),
      data: { firstName: 'List', lastName: 'Address', address1: '9 List Rd', city: 'Listville', country: 'US', state: 'CA', postcode: '90001' },
    });
    const createdBody = await readJson(created);
    expect(created.status(), JSON.stringify(createdBody)).toBe(201);

    const response = await sendRestRequest(request, CUSTOMER_ADDRESSES, { headers: bearer(customer.token) });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(Array.isArray(body)).toBe(true);
    expect(body).toHaveLength(1);
    expect(body[0].id).toBe(createdBody.id);
    expect(body[0].city).toBe('Listville');
  });

  test('refuses the address list without a token', async ({ request }) => {
    const response = await sendRestRequest(request, CUSTOMER_ADDRESSES);

    await expectProblem(response, 401, MSG.ADDRESS_TOKEN_REQUIRED);
  });
});

test.describe('customer orders rest api', () => {
  test('lists no orders for a customer who has not ordered', async ({ request }) => {
    const customer = await registerCustomer(request);

    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ORDERS, { headers: bearer(customer.token) });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body).toEqual([]);
  });

  test('refuses the order list without a token', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_ORDERS);

    await expectProblem(response, 403, MSG.UNAUTHENTICATED);
  });
});

test.describe('customer password change rest api', () => {
  test('changes the password when the current password is correct', async ({ request }) => {
    const customer = await registerCustomer(request);
    const newPassword = `New${uniqueSuffix()}!Aa`;

    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_PROFILE_UPDATE(customer.id), {
      method: 'PUT',
      data: { currentPassword: customer.password, password: newPassword, confirmPassword: newPassword },
      headers: bearer(customer.token),
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body.success).toBe(true);
    expect(body.message).toBe(MSG.PROFILE_UPDATED);

    const withNew = await login(request, customer.email, newPassword);
    expect(withNew.success).toBe(true);
    expectTokenPair(withNew);

    const withOld = await login(request, customer.email, customer.password, 401);
    expect(withOld.error).toBe('invalid_credentials');
    expect(withOld.message).toBe(MSG.INVALID_CREDENTIALS);
  });

  test('rejects a password change with a wrong current password and keeps the old one', async ({ request }) => {
    const customer = await registerCustomer(request);

    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_PROFILE_UPDATE(customer.id), {
      method: 'PUT',
      data: {
        currentPassword: 'definitely-not-the-current-password',
        password: 'BogusPass1234!',
        confirmPassword: 'BogusPass1234!',
      },
      headers: bearer(customer.token),
    });

    await expectProblem(response, 400, MSG.CURRENT_PASSWORD_INCORRECT);

    const withOld = await login(request, customer.email, customer.password);
    expect(withOld.success).toBe(true);

    const withBogus = await login(request, customer.email, 'BogusPass1234!', 401);
    expect(withBogus.error).toBe('invalid_credentials');
  });
});
