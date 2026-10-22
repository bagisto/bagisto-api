import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../rest/helpers/adminClient';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import { ENDPOINTS } from '../../../rest/endpoints/endpoints';
import { env } from '../../../config/env';

test.describe.configure({ timeout: 60_000 });

const UNAUTHENTICATED = { message: 'Unauthenticated.', error: 'unauthenticated' };

test.describe('admin auth rest api', () => {
  test('profile read returns the authenticated admin', async ({ request }) => {
    const response = await sendAdminRequest(request, ENDPOINTS.ADMIN_PROFILE);
    expect(response.status()).toBe(200);

    const body = await response.json();
    const admin = Array.isArray(body) ? body[0] : (body?.email ? body : (body?.data ?? body));
    expect(admin.email).toBe(env.adminEmail);
  });

  test('profile read without bearer token returns 401', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.ADMIN_PROFILE, {
      method: 'GET',
    });
    expect(response.status()).toBe(401);
    expect(await response.json()).toEqual(UNAUTHENTICATED);
  });

  test('profile read with garbage bearer token returns 401', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.ADMIN_PROFILE, {
      method: 'GET',
      headers: { 'Authorization': 'Bearer this-is-not-a-real-token' },
    });
    expect(response.status()).toBe(401);
    expect(await response.json()).toEqual(UNAUTHENTICATED);
  });

  test('removed login endpoint is no longer routable', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.ADMIN_LOGIN, {
      method: 'POST',
      data: { email: env.adminEmail, password: 'whatever' },
    });
    expect(response.status()).toBe(405);
    expect(response.headers()['allow']).toBe('GET, HEAD');

    const body = await response.json();
    expect(body).not.toHaveProperty('token');
    expect(body.message).toBe('The POST method is not supported for route api/admin/login. Supported methods: GET, HEAD.');
  });

  test('admin endpoint accepts bearer token without x-admin-key', async ({ request }) => {
    const response = await sendAdminRequest(request, '/api/admin/customers', {
      method: 'GET',
      params: { per_page: '1' },
    });
    expect(response.status()).toBe(200);
    expect(Array.isArray((await response.json()).data)).toBe(true);
  });
});
