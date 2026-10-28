import { test, expect, APIRequestContext } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import { addCartLine, bearer, createGuestCart, findSaleableSimpleProducts, readJson, uniqueSuffix } from '../../rest/helpers/coverage/shopCart.helpers';

const REFRESH = '/api/shop/customer/refresh';
const LOGOUT = '/api/shop/customer/logout';
const LOGOUT_ALL = '/api/shop/customer/logout-all';
const SESSIONS = '/api/shop/customer/sessions';

interface Credentials {
  email: string;
  password: string;
}

async function registeredCredentials(request: APIRequestContext): Promise<Credentials> {
  const suffix = uniqueSuffix();
  const credentials = { email: `rest.auth.${suffix}@example.com`, password: `Auth${suffix}!Aa` };

  const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_REGISTER, {
    method: 'POST',
    data: { firstName: 'Rest', lastName: 'Auth', ...credentials, confirmPassword: credentials.password },
  });

  expect(response.status(), await response.text()).toBe(201);

  return credentials;
}

async function login(request: APIRequestContext, credentials: Credentials, extra: Record<string, any> = {}): Promise<any> {
  const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_LOGIN, {
    method: 'POST',
    data: { ...credentials, device_name: 'Playwright', client_type: 'web', ...extra },
  });
  const body = await readJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(201);

  return body;
}

async function refresh(request: APIRequestContext, refreshToken: string) {
  return sendRestRequest(request, REFRESH, { method: 'POST', data: { refresh_token: refreshToken } });
}

async function profileStatus(request: APIRequestContext, accessToken: string): Promise<number> {
  return (await sendRestRequest(request, ENDPOINTS.CUSTOMER_PROFILE, { headers: bearer(accessToken) })).status();
}

test.describe('customer authentication rest api', () => {
  test('login returns a token pair whose access token authenticates', async ({ request }) => {
    const credentials = await registeredCredentials(request);

    const body = await login(request, credentials);

    expect(body.tokenType).toBe('Bearer');
    expect(String(body.accessToken).split('.')).toHaveLength(3);
    expect(body.token).toBe(body.accessToken);
    expect(typeof body.refreshToken).toBe('string');
    expect(Number(body.expiresIn)).toBeGreaterThan(0);
    expect(body.session.name).toBe('Playwright');
    expect(body.session.clientType).toBe('web');
    expect(body.customer.email).toBe(credentials.email);
    expect(body.cartId).toBeNull();

    expect(await profileStatus(request, body.accessToken)).toBe(200);
  });

  test('a refused login is an error with its code', async ({ request }) => {
    const credentials = await registeredCredentials(request);

    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_LOGIN, {
      method: 'POST',
      data: { email: credentials.email, password: 'Wrong-password-1' },
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(401);
    expect(body.error).toBe('invalid_credentials');
  });

  test('refresh rotates the pair and a reused refresh token ends the session', async ({ request }) => {
    const first = await login(request, await registeredCredentials(request));

    const rotated = await refresh(request, first.refreshToken);
    const second = await readJson(rotated);

    expect(rotated.status(), JSON.stringify(second)).toBe(200);
    expect(second.session.id).toBe(first.session.id);
    expect(second.refreshToken).not.toBe(first.refreshToken);
    expect(await profileStatus(request, second.accessToken)).toBe(200);

    const reused = await refresh(request, first.refreshToken);

    expect(reused.status()).toBe(401);
    expect((await readJson(reused)).error).toBe('refresh_token_invalid');

    const afterReuse = await sendRestRequest(request, ENDPOINTS.CUSTOMER_PROFILE, { headers: bearer(second.accessToken) });

    expect(afterReuse.status()).toBe(401);
    expect((await readJson(afterReuse)).error).toBe('unauthenticated');
  });

  test('sessions are listed and one is ended', async ({ request }) => {
    const credentials = await registeredCredentials(request);
    const current = await login(request, credentials);
    const other = await login(request, credentials, { device_name: 'Other device' });

    const list = await sendRestRequest(request, SESSIONS, { headers: bearer(current.accessToken) });
    const sessions = await readJson(list);

    expect(list.status(), JSON.stringify(sessions)).toBe(200);

    const rows = Array.isArray(sessions) ? sessions : sessions.data;
    const ids = rows.map((row: any) => Number(row.id));

    expect(ids).toEqual(expect.arrayContaining([Number(current.session.id), Number(other.session.id)]));
    expect(rows.find((row: any) => Number(row.id) === Number(current.session.id)).isCurrent).toBe(true);

    const revoke = await sendRestRequest(request, `${SESSIONS}/${other.session.id}`, {
      method: 'DELETE',
      headers: bearer(current.accessToken),
    });

    expect(revoke.status()).toBe(204);
    expect(await profileStatus(request, other.accessToken)).toBe(401);
    expect(await profileStatus(request, current.accessToken)).toBe(200);
  });

  test('logout ends this session and logout-all ends every session', async ({ request }) => {
    const credentials = await registeredCredentials(request);
    const first = await login(request, credentials);
    const second = await login(request, credentials);
    const third = await login(request, credentials);

    const logout = await sendRestRequest(request, LOGOUT, { method: 'POST', data: {}, headers: bearer(first.accessToken) });

    expect(logout.status()).toBeLessThan(300);
    expect(await profileStatus(request, first.accessToken)).toBe(401);
    expect(await profileStatus(request, second.accessToken)).toBe(200);

    const logoutAll = await sendRestRequest(request, LOGOUT_ALL, { method: 'POST', data: {}, headers: bearer(second.accessToken) });
    const body = await readJson(logoutAll);

    expect(logoutAll.status(), JSON.stringify(body)).toBeLessThan(300);
    expect(Number(body.revoked)).toBeGreaterThanOrEqual(2);
    expect(await profileStatus(request, third.accessToken)).toBe(401);
  });

  test('a forged customer token is refused with its code before the operation runs', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      headers: bearer('eyJhbGciOiJFUzI1NiJ9.eyJ0eXAiOiJjdXN0b21lcl9hY2Nlc3MiLCJzdWIiOjF9.c2lnbmF0dXJl'),
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(401);
    expect(body.error).toBe('unauthenticated');
  });

  test('login with a guest cart token merges the guest cart into the customer cart', async ({ request }) => {
    const credentials = await registeredCredentials(request);

    const [productId] = await findSaleableSimpleProducts(request, 1);

    test.skip(!productId, 'no saleable simple product to put in a guest cart');

    const guest = await createGuestCart(request);
    const line = await addCartLine(request, guest.token, productId, 1);
    const guestToken = guest.token;
    const guestCartId = line.cartId;

    const body = await login(request, credentials, { guest_cart_token: guestToken });

    expect(Number(body.cartId)).toBe(guestCartId);

    const customerCart = await readJson(await sendRestRequest(request, '/api/shop/cart', {
      method: 'POST',
      data: {},
      headers: bearer(body.accessToken),
    }));

    expect(Number(customerCart.id)).toBe(guestCartId);
    expect(customerCart.isGuest).toBe(false);
  });
});
