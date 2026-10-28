import { test, expect, APIRequestContext } from '@playwright/test';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import { SHOP_ACCOUNT, SHOP_ACCOUNT_MESSAGES as MSG } from '../../../rest/endpoints/coverage/shopAccount.endpoints';

interface RegisteredCustomer {
  id: number;
  email: string;
  password: string;
  token: string;
}

function uniqueSuffix(): string {
  return `${Date.now()}-${Math.floor(Math.random() * 1_000_000)}`;
}

function bearer(token: string): Record<string, string> {
  return { Authorization: `Bearer ${token}` };
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
  expect(body.email).toBe(email);
  expect(typeof body.token).toBe('string');
  expect(body.token.length).toBeGreaterThan(0);

  return { id: Number(body.id), email, password, token: body.token };
}

async function login(request: APIRequestContext, email: string, password: string) {
  const response = await sendRestRequest(request, SHOP_ACCOUNT.CUSTOMER_LOGIN, {
    method: 'POST',
    data: { email, password },
  });

  expect(response.status()).toBe(201);

  return response.json();
}

async function expectProfileReadable(request: APIRequestContext, customer: RegisteredCustomer): Promise<void> {
  const response = await sendRestRequest(request, SHOP_ACCOUNT.CUSTOMER_PROFILE, { headers: bearer(customer.token) });
  expect(response.status()).toBe(200);
  const body = await response.json();
  expect(Array.isArray(body)).toBe(true);
  expect(body[0].email).toBe(customer.email);
}

async function expectTokenRejected(request: APIRequestContext, token: string): Promise<void> {
  const response = await sendRestRequest(request, SHOP_ACCOUNT.CUSTOMER_PROFILE, { headers: bearer(token) });
  expect(response.status()).toBe(401);
  const body = await response.json();
  expect(body.error).toBe('unauthenticated');
  expect(body.message).toBe(MSG.PROFILE_INVALID_TOKEN);
}

test.describe('shop account logout rest api', () => {
  test('should revoke the bearer token on logout', async ({ request }) => {
    const customer = await registerCustomer(request);
    await expectProfileReadable(request, customer);

    const response = await sendRestRequest(request, SHOP_ACCOUNT.CUSTOMER_LOGOUT, {
      method: 'POST',
      data: {},
      headers: bearer(customer.token),
    });

    expect(response.status()).toBe(201);
    const body = await response.json();
    expect(body.success).toBe(true);
    expect(body.message).toBe(MSG.LOGGED_OUT);

    await expectTokenRejected(request, customer.token);
  });

  test('should refuse a logout without a bearer token', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_ACCOUNT.CUSTOMER_LOGOUT, { method: 'POST', data: {} });

    expect(response.status()).toBe(201);
    const body = await response.json();
    expect(body.success).toBe(false);
    expect(body.message).toBe(MSG.UNAUTHENTICATED);
  });
});

test.describe('shop account profile delete rest api', () => {
  test('should delete the account when the current password is given', async ({ request }) => {
    const customer = await registerCustomer(request);

    const response = await sendRestRequest(request, SHOP_ACCOUNT.CUSTOMER_PROFILE_DELETE(customer.id), {
      method: 'POST',
      data: { password: customer.password },
      headers: bearer(customer.token),
    });

    expect([201, 204]).toContain(response.status());

    await expectTokenRejected(request, customer.token);

    const loginResponse = await sendRestRequest(request, SHOP_ACCOUNT.CUSTOMER_LOGIN, {
      method: 'POST',
      data: { email: customer.email, password: customer.password },
    });
    expect(loginResponse.status()).toBe(401);
    const loginBody = await loginResponse.json();
    expect(loginBody.error).toBe('invalid_credentials');
    expect(loginBody.message).toBe(MSG.INVALID_CREDENTIALS);
  });

  test('should keep the account when the password is wrong', async ({ request }) => {
    const customer = await registerCustomer(request);

    const response = await sendRestRequest(request, SHOP_ACCOUNT.CUSTOMER_PROFILE_DELETE(customer.id), {
      method: 'POST',
      data: { password: 'Wrong-Passw0rd!' },
      headers: bearer(customer.token),
    });

    expect(response.status()).toBe(422);
    const body = await response.json();
    expect(body.detail).toBe(MSG.DELETE_PASSWORD_INCORRECT);

    await expectProfileReadable(request, customer);
  });

  test('should require the password to delete the account', async ({ request }) => {
    const customer = await registerCustomer(request);

    const response = await sendRestRequest(request, SHOP_ACCOUNT.CUSTOMER_PROFILE_DELETE(customer.id), {
      method: 'POST',
      data: {},
      headers: bearer(customer.token),
    });

    expect(response.status()).toBe(422);
    const body = await response.json();
    expect(body.detail).toBe(MSG.DELETE_PASSWORD_REQUIRED);

    await expectProfileReadable(request, customer);
  });

  test('should refuse an account delete without a bearer token', async ({ request }) => {
    const customer = await registerCustomer(request);

    const response = await sendRestRequest(request, SHOP_ACCOUNT.CUSTOMER_PROFILE_DELETE(customer.id), {
      method: 'POST',
      data: { password: customer.password },
    });

    expect(response.status()).toBe(401);
    const body = await response.json();
    expect(body.detail).toBe(MSG.TOKEN_REQUIRED);

    await expectProfileReadable(request, customer);
  });
});

test.describe('shop account social login rest api', () => {
  test('should refuse a provider the store does not support', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_ACCOUNT.SOCIAL_LOGIN, {
      method: 'POST',
      data: { provider: 'github', idToken: `e2e-${uniqueSuffix()}` },
    });

    expect(response.status()).toBe(201);
    const body = await response.json();
    expect(body.success).toBe(false);
    expect(body.code).toBe('PROVIDER_NOT_SUPPORTED');
    expect(body.message).toBe(MSG.SOCIAL_PROVIDER_NOT_SUPPORTED);
    expect(body.token ?? null).toBeNull();
  });

  test('should refuse a google sign in that carries no token', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_ACCOUNT.SOCIAL_LOGIN, {
      method: 'POST',
      data: { provider: 'google' },
    });

    expect(response.status()).toBe(201);
    const body = await response.json();
    expect(body.success).toBe(false);

    const expectedMessages: Record<string, string> = {
      PROVIDER_DISABLED: MSG.SOCIAL_PROVIDER_DISABLED,
      SOCIAL_TOKEN_REQUIRED: MSG.SOCIAL_TOKEN_REQUIRED,
    };

    expect(Object.keys(expectedMessages)).toContain(body.code);
    expect(body.message).toBe(expectedMessages[body.code]);
    expect(body.token ?? null).toBeNull();
  });
});

test.describe('shop account forgot password rest api', () => {
  test('should send a reset link to a registered customer', async ({ request }) => {
    const customer = await registerCustomer(request);

    const response = await sendRestRequest(request, SHOP_ACCOUNT.FORGOT_PASSWORDS, {
      method: 'POST',
      data: { email: customer.email },
    });

    expect(response.status()).toBe(201);
    const body = await response.json();
    expect(body.success).toBe(true);
    expect(body.message).toBe(MSG.RESET_LINK_SENT);
  });

  test('should report an address that is not registered', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_ACCOUNT.FORGOT_PASSWORDS, {
      method: 'POST',
      data: { email: `shop-account-missing-${uniqueSuffix()}@example.com` },
    });

    expect(response.status()).toBe(201);
    const body = await response.json();
    expect(body.success).toBe(false);
    expect(body.message).toBe(MSG.RESET_EMAIL_NOT_FOUND);
  });

  test('should require an email address', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_ACCOUNT.FORGOT_PASSWORDS, {
      method: 'POST',
      data: { email: '' },
    });

    expect(response.status()).toBe(201);
    const body = await response.json();
    expect(body.success).toBe(false);
    expect(body.message).toBe(MSG.RESET_EMAIL_REQUIRED);
  });
});

test.describe('shop account reset password rest api', () => {
  test('should refuse a reset token that was never issued and keep the old password', async ({ request }) => {
    const customer = await registerCustomer(request);

    const response = await sendRestRequest(request, SHOP_ACCOUNT.RESET_PASSWORDS, {
      method: 'POST',
      data: {
        token: `not-issued-${uniqueSuffix()}`,
        email: customer.email,
        password: 'NewPassw0rd!456',
        password_confirmation: 'NewPassw0rd!456',
      },
    });

    expect(response.status()).toBe(201);
    const body = await response.json();
    expect(body.success).toBe(false);
    expect(body.message).toBe(MSG.RESET_TOKEN_INVALID);

    const loginBody = await login(request, customer.email, customer.password);
    expect(loginBody.success).toBe(true);
  });

  test('should refuse a reset for an address that is not registered', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_ACCOUNT.RESET_PASSWORDS, {
      method: 'POST',
      data: {
        token: `not-issued-${uniqueSuffix()}`,
        email: `shop-account-missing-${uniqueSuffix()}@example.com`,
        password: 'NewPassw0rd!456',
        password_confirmation: 'NewPassw0rd!456',
      },
    });

    expect(response.status()).toBe(201);
    const body = await response.json();
    expect(body.success).toBe(false);
    expect(body.message).toBe(MSG.RESET_USER_NOT_FOUND);
  });

  test('should reject a confirmation that does not match the password', async ({ request }) => {
    const customer = await registerCustomer(request);

    const response = await sendRestRequest(request, SHOP_ACCOUNT.RESET_PASSWORDS, {
      method: 'POST',
      data: {
        token: `not-issued-${uniqueSuffix()}`,
        email: customer.email,
        password: 'NewPassw0rd!456',
        password_confirmation: 'Different!789',
      },
    });

    expect(response.status()).toBe(201);
    const body = await response.json();
    expect(body.success).toBe(false);
    expect(body.message).toBe(MSG.PASSWORD_CONFIRMATION_MISMATCH);
  });
});

test.describe('shop account verification email rest api', () => {
  test('should send a verification email to a registered address', async ({ request }) => {
    const customer = await registerCustomer(request);

    const response = await sendRestRequest(request, SHOP_ACCOUNT.VERIFICATION_EMAILS, {
      method: 'POST',
      data: { email: customer.email },
    });

    expect(response.status()).toBe(201);
    const body = await response.json();
    expect(body.success).toBe(true);
    expect(body.message).toBe(MSG.VERIFICATION_SENT);
  });

  test('should reject an address that is not an email', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_ACCOUNT.VERIFICATION_EMAILS, {
      method: 'POST',
      data: { email: 'not-an-email' },
    });

    expect(response.status()).toBe(201);
    const body = await response.json();
    expect(body.success).toBe(false);
    expect(body.message).toBe(MSG.EMAIL_INVALID);
  });
});

test.describe('shop account verify account rest api', () => {
  test('should refuse a verification token that was never issued', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_ACCOUNT.VERIFY_ACCOUNTS, {
      method: 'POST',
      data: { token: `not-issued-${uniqueSuffix()}` },
    });

    expect(response.status()).toBe(201);
    const body = await response.json();
    expect(body.success).toBe(false);
    expect(body.message).toBe(MSG.VERIFY_FAILED);
  });

  test('should require a verification token', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_ACCOUNT.VERIFY_ACCOUNTS, {
      method: 'POST',
      data: { token: '' },
    });

    expect(response.status()).toBe(201);
    const body = await response.json();
    expect(body.success).toBe(false);
    expect(body.message).toBe(MSG.TOKEN_FIELD_REQUIRED);
  });
});

test.describe('shop account newsletter unsubscribe rest api', () => {
  test('should answer an unsubscribe token with the generic success', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_ACCOUNT.NEWSLETTER_UNSUBSCRIBE, {
      method: 'POST',
      data: { token: `e2e-${uniqueSuffix()}` },
    });

    expect(response.status()).toBe(201);
    const body = await response.json();
    expect(body.success).toBe(true);
    expect(body.message).toBe(MSG.UNSUBSCRIBED);
  });

  test('should require an unsubscribe token', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_ACCOUNT.NEWSLETTER_UNSUBSCRIBE, {
      method: 'POST',
      data: {},
    });

    expect(response.status()).toBe(400);
    const body = await response.json();
    expect(body.detail).toBe(MSG.TOKEN_FIELD_REQUIRED);
  });

  test('should reject an unsubscribe token longer than 255 characters', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_ACCOUNT.NEWSLETTER_UNSUBSCRIBE, {
      method: 'POST',
      data: { token: 'x'.repeat(256) },
    });

    expect(response.status()).toBe(400);
    const body = await response.json();
    expect(body.detail).toBe(MSG.TOKEN_FIELD_TOO_LONG);
  });
});
