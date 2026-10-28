import { test, expect, APIRequestContext } from '@playwright/test';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../../graphql/helpers/testSupport';
import { SHOP_ACCOUNT_QUERIES as Q } from '../../../graphql/Queries/coverage/shopAccount.queries';
import { SHOP_ACCOUNT_MESSAGES as MSG } from '../../../../restAPI/rest/endpoints/coverage/shopAccount.endpoints';

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

async function gql(request: APIRequestContext, query: string, variables: Record<string, any> = {}, token?: string): Promise<any> {
  const response = await sendGraphQLRequest(request, query, variables, token ? bearer(token) : {});
  expect(response.status()).toBe(200);

  return response.json();
}

async function login(request: APIRequestContext, email: string, password: string): Promise<any> {
  const body = await gql(request, Q.loginCustomer, { email, password });
  expect(body.errors).toBeUndefined();

  return body.data.createCustomerLogin.customerLogin;
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
  expect(customer.email).toBe(email);

  const token = customer.token || (await login(request, email, password)).token;
  expect(typeof token).toBe('string');
  expect(token.length).toBeGreaterThan(0);

  return { id: Number(customer._id), email, password, token };
}

async function createAddress(request: APIRequestContext, customer: RegisteredCustomer, city: string): Promise<number> {
  const body = await gql(request, Q.createAddress, {
    input: {
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
  }, customer.token);

  expect(body.errors).toBeUndefined();
  const address = body.data.createAddUpdateCustomerAddress.addUpdateCustomerAddress;
  expect(address.city).toBe(city);
  expect(typeof address.addressId).toBe('number');

  return address.addressId;
}

test.describe('shop account social login graphql api', () => {
  test('should refuse a provider the store does not support', async ({ request }) => {
    const body = await gql(request, Q.socialLogin, { input: { provider: 'github', idToken: `e2e-${uniqueSuffix()}` } });

    expect(body.errors).toBeUndefined();
    const result = body.data.createSocialLogin.socialLogin;
    expect(result.success).toBe(false);
    expect(result.code).toBe('PROVIDER_NOT_SUPPORTED');
    expect(result.message).toBe(MSG.SOCIAL_PROVIDER_NOT_SUPPORTED);
    expect(result.token).toBeNull();
  });

  test('should refuse a google sign in that carries no token', async ({ request }) => {
    const body = await gql(request, Q.socialLogin, { input: { provider: 'google' } });

    expect(body.errors).toBeUndefined();
    const result = body.data.createSocialLogin.socialLogin;
    expect(result.success).toBe(false);

    const expectedMessages: Record<string, string> = {
      PROVIDER_DISABLED: MSG.SOCIAL_PROVIDER_DISABLED,
      SOCIAL_TOKEN_REQUIRED: MSG.SOCIAL_TOKEN_REQUIRED,
    };

    expect(Object.keys(expectedMessages)).toContain(result.code);
    expect(result.message).toBe(expectedMessages[result.code]);
    expect(result.token).toBeNull();
  });
});

test.describe('shop account reset password graphql api', () => {
  test('should refuse a reset token that was never issued and keep the old password', async ({ request }) => {
    const customer = await registerCustomer(request);

    const body = await gql(request, Q.resetPassword, {
      input: {
        email: customer.email,
        token: `not-issued-${uniqueSuffix()}`,
        password: 'NewPassw0rd!456',
        passwordConfirmation: 'NewPassw0rd!456',
      },
    });

    expect(body.errors).toBeUndefined();
    const result = body.data.createResetPassword.resetPassword;
    expect(result.success).toBe(false);
    expect(result.message).toBe(MSG.RESET_TOKEN_INVALID);

    const loginResult = await login(request, customer.email, customer.password);
    expect(loginResult.success).toBe(true);
  });

  test('should reject a confirmation that does not match the password', async ({ request }) => {
    const customer = await registerCustomer(request);

    const body = await gql(request, Q.resetPassword, {
      input: {
        email: customer.email,
        token: `not-issued-${uniqueSuffix()}`,
        password: 'NewPassw0rd!456',
        passwordConfirmation: 'Different!789',
      },
    });

    expect(body.errors).toBeUndefined();
    const result = body.data.createResetPassword.resetPassword;
    expect(result.success).toBe(false);
    expect(result.message).toBe(MSG.PASSWORD_CONFIRMATION_MISMATCH);
  });
});

test.describe('shop account verify account graphql api', () => {
  test('should refuse a verification token that was never issued', async ({ request }) => {
    const body = await gql(request, Q.verifyAccount, { token: `not-issued-${uniqueSuffix()}` });

    expect(body.errors).toBeUndefined();
    const result = body.data.createVerifyAccount.verifyAccount;
    expect(result.success).toBe(false);
    expect(result.message).toBe(MSG.VERIFY_FAILED);
  });

  test('should require a verification token', async ({ request }) => {
    const body = await gql(request, Q.verifyAccount, { token: '' });

    expect(body.errors).toBeUndefined();
    const result = body.data.createVerifyAccount.verifyAccount;
    expect(result.success).toBe(false);
    expect(result.message).toBe(MSG.TOKEN_FIELD_REQUIRED);
  });
});

test.describe('shop account resend verification email graphql api', () => {
  test('should answer a resend with the same success whether or not the address is registered', async ({ request }) => {
    const body = await gql(request, Q.resendVerificationEmail, { email: `shop-account-unknown-${uniqueSuffix()}@example.com` });

    expect(body.errors).toBeUndefined();
    const result = body.data.createResendVerificationEmail.resendVerificationEmail;
    expect(result.success).toBe(true);
    expect(result.message).toBe(MSG.VERIFICATION_SENT);
  });

  test('should reject an address that is not an email', async ({ request }) => {
    const body = await gql(request, Q.resendVerificationEmail, { email: 'not-an-email' });

    expect(body.errors).toBeUndefined();
    const result = body.data.createResendVerificationEmail.resendVerificationEmail;
    expect(result.success).toBe(false);
    expect(result.message).toBe(MSG.EMAIL_INVALID);
  });
});

test.describe('shop account newsletter unsubscription graphql api', () => {
  test('should answer an unsubscribe token with the generic success', async ({ request }) => {
    const body = await gql(request, Q.newsletterUnsubscription, { token: `e2e-${uniqueSuffix()}` });

    expect(body.errors).toBeUndefined();
    const result = body.data.createNewsletterUnsubscription.newsletterUnsubscription;
    expect(result.success).toBe(true);
    expect(result.message).toBe(MSG.UNSUBSCRIBED);
  });

  test('should require an unsubscribe token', async ({ request }) => {
    const body = await gql(request, Q.newsletterUnsubscription, { token: null });

    expect(body.data?.createNewsletterUnsubscription ?? null).toBeNull();
    expect(graphQLErrorMessages(body)).toContain(MSG.TOKEN_FIELD_REQUIRED);
  });
});

test.describe('shop account read customer address graphql api', () => {
  test('should read an address the customer owns', async ({ request }) => {
    const customer = await registerCustomer(request);
    const city = `E2E City ${uniqueSuffix()}`;
    const addressId = await createAddress(request, customer, city);

    const body = await gql(request, Q.readGetCustomerAddress, { id: String(addressId) }, customer.token);

    expect(body.errors).toBeUndefined();
    const address = body.data.readGetCustomerAddress;
    expect(address.addressId).toBe(addressId);
    expect(address.firstName).toBe('Shop');
    expect(address.lastName).toBe('Account');
    expect(address.city).toBe(city);
    expect(address.postcode).toBe('90001');
    expect(address.country).toBe('US');
    expect(address.defaultAddress).toBe(false);
  });

  test('should refuse an address another customer owns', async ({ request }) => {
    const owner = await registerCustomer(request);
    const addressId = await createAddress(request, owner, `E2E City ${uniqueSuffix()}`);
    const intruder = await registerCustomer(request);

    const body = await gql(request, Q.readGetCustomerAddress, { id: String(addressId) }, intruder.token);

    expect(body.data?.readGetCustomerAddress ?? null).toBeNull();
    expect(graphQLErrorMessages(body)).toContain(MSG.ADDRESS_NOT_FOUND);
  });

  test('should refuse an address read without a customer token', async ({ request }) => {
    const body = await gql(request, Q.readGetCustomerAddress, { id: '1' });

    expect(body.data?.readGetCustomerAddress ?? null).toBeNull();
    expect(graphQLErrorMessages(body)).toContain(MSG.ADDRESS_AUTH_REQUIRED);
  });
});

test.describe('shop account product review translation graphql api', () => {
  test('should refuse to translate a review that does not exist', async ({ request }) => {
    const body = await gql(request, Q.productReviewTranslation, { productId: 999999999, reviewId: 999999999 });

    expect(body.data?.productReviewTranslation ?? null).toBeNull();

    const messages = graphQLErrorMessages(body);
    expect(messages).toHaveLength(1);
    expect([MSG.REVIEW_TRANSLATION_FORBIDDEN, MSG.REVIEW_TRANSLATION_NOT_FOUND]).toContain(messages[0]);
  });
});
