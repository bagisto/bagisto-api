import { test, expect, APIRequestContext } from '@playwright/test';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';

const REGISTER = `
  mutation register($input: createCustomerInput!) {
    createCustomer(input: $input) {
      customer { _id accessToken refreshToken }
    }
  }
`;

const LOGIN = `
  mutation login($input: createCustomerLoginInput!) {
    createCustomerLogin(input: $input) {
      customerLogin { accessToken refreshToken expiresIn session customer cartId }
    }
  }
`;

const REFRESH = `
  mutation refresh($input: createCustomerTokenRefreshInput!) {
    createCustomerTokenRefresh(input: $input) {
      customerTokenRefresh { accessToken refreshToken session }
    }
  }
`;

const SESSIONS = `{ customerSessions { _id name clientType isCurrent } }`;

const LOGOUT_ALL = `
  mutation { createCustomerLogoutAll(input: {}) { customerLogoutAll { revoked success } } }
`;

const PROFILE = `{ readCustomerProfile { email } }`;

function suffix(): string {
  return `${Date.now()}${Math.floor(Math.random() * 100000)}`;
}

function bearer(token: string): Record<string, string> {
  return { Authorization: `Bearer ${token}` };
}

async function json(request: APIRequestContext, query: string, variables: Record<string, any> = {}, headers: Record<string, string> = {}) {
  return (await sendGraphQLRequest(request, query, variables, headers)).json();
}

async function registered(request: APIRequestContext): Promise<{ email: string; password: string }> {
  const id = suffix();
  const credentials = { email: `graphql.auth.${id}@example.com`, password: `Auth${id}!Aa` };

  const body = await json(request, REGISTER, {
    input: { firstName: 'Graph', lastName: 'Auth', ...credentials, confirmPassword: credentials.password },
  });

  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

  return credentials;
}

async function login(request: APIRequestContext, credentials: { email: string; password: string }): Promise<any> {
  const body = await json(request, LOGIN, {
    input: { ...credentials, deviceName: 'Playwright GraphQL', clientType: 'mobile' },
  });

  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

  return body.data.createCustomerLogin.customerLogin;
}

test.describe('customer authentication graphql api', () => {
  test('createCustomerLogin returns a token pair whose access token authenticates', async ({ request }) => {
    const credentials = await registered(request);
    const pair = await login(request, credentials);

    expect(String(pair.accessToken).split('.')).toHaveLength(3);
    expect(typeof pair.refreshToken).toBe('string');
    expect(pair.expiresIn).toBeGreaterThan(0);
    expect(pair.session.clientType).toBe('mobile');
    expect(pair.cartId).toBeNull();

    const profile = await json(request, PROFILE, {}, bearer(pair.accessToken));

    expect(profile.data.readCustomerProfile.email).toBe(credentials.email);
  });

  test('a refused login carries its code', async ({ request }) => {
    const credentials = await registered(request);

    const body = await json(request, LOGIN, { input: { email: credentials.email, password: 'Wrong-password-1' } });

    expect(body.errors[0].extensions.code).toBe('invalid_credentials');
  });

  test('createCustomerTokenRefresh rotates the pair and refuses a reused refresh token', async ({ request }) => {
    const first = await login(request, await registered(request));

    const rotated = await json(request, REFRESH, { input: { refreshToken: first.refreshToken } });

    expect(rotated.errors, JSON.stringify(rotated.errors)).toBeUndefined();

    const second = rotated.data.createCustomerTokenRefresh.customerTokenRefresh;

    expect(second.session.id).toBe(first.session.id);
    expect(second.refreshToken).not.toBe(first.refreshToken);

    const reused = await json(request, REFRESH, { input: { refreshToken: first.refreshToken } });

    expect(reused.errors[0].extensions.code).toBe('refresh_token_invalid');
  });

  test('customerSessions lists the sessions and createCustomerLogoutAll ends them', async ({ request }) => {
    const credentials = await registered(request);
    const current = await login(request, credentials);
    await login(request, credentials);

    const list = await json(request, SESSIONS, {}, bearer(current.accessToken));

    expect(list.errors, JSON.stringify(list.errors)).toBeUndefined();
    expect(list.data.customerSessions.length).toBeGreaterThanOrEqual(2);
    expect(list.data.customerSessions.find((row: any) => row._id === current.session.id).isCurrent).toBe(true);

    const logoutAll = await json(request, LOGOUT_ALL, {}, bearer(current.accessToken));

    expect(logoutAll.errors, JSON.stringify(logoutAll.errors)).toBeUndefined();
    expect(logoutAll.data.createCustomerLogoutAll.customerLogoutAll.revoked).toBeGreaterThanOrEqual(2);

    const response = await sendGraphQLRequest(request, PROFILE, {}, bearer(current.accessToken));

    expect(response.status()).toBe(401);
    expect((await response.json()).errors[0].extensions.code).toBe('unauthenticated');
  });
});
