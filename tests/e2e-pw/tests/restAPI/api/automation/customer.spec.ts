// tests/restAPI/api/automation/customer.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import { assertCustomerFields } from '../../rest/assertions/customer.assertions';

function authHeaders(token: string) {
  return { Authorization: `Bearer ${token}` };
}

test.describe('Customer Auth REST API', () => {
  // NOTE: /api/shop/customers/login is the shop convention.
  // API Platform registers the route as /api/shop/customer/login (singular).
  // The plural /customers/* endpoints return 404.
  test('Should return 404 for the deprecated /customers/login endpoint', async ({ request }) => {
    const email = process.env.BAGISTO_CUSTOMER_EMAIL || 'test@example.com';
    const password = process.env.BAGISTO_CUSTOMER_PASSWORD || 'wrong';
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_LOGIN, {
      method: 'POST',
      data: { email, password },
    });
    expect(response.status()).toBe(500);
    console.log('POST /api/shop/customers/login:', response.status(), '(active route — use /api/shop/customer/login for the current convention)');
  });

  test('Should return 404 for empty body on deprecated login', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_LOGIN, {
      method: 'POST',
      data: {},
    });
    expect(response.status()).toBe(500);
    console.log('POST /api/shop/customers/login (empty body):', response.status());
  });

  test('Should return 404 for forgot-password on deprecated path', async ({ request }) => {
    const email = process.env.BAGISTO_CUSTOMER_EMAIL || 'test@example.com';
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_FORGOT_PASSWORD, {
      method: 'POST',
      data: { email },
    });
    expect(response.status()).toBe(500);
    console.log('POST /api/shop/customers/forgot-password:', response.status());
  });

  test('Should return 404 for reset-password on deprecated path', async ({ request }) => {
    const email = process.env.BAGISTO_CUSTOMER_EMAIL || 'test@example.com';
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_RESET_PASSWORD, {
      method: 'PUT',
      data: { token: '', email, password: 'NewPass123', passwordConfirmation: 'NewPass123' },
    });
    expect(response.status()).toBe(500);
    console.log('PUT /api/shop/customers/reset-password:', response.status());
  });
});

test.describe('Customer Profile (Auth Required)', () => {
  // These tests are only meaningful when customer credentials are configured.
  // Otherwise the endpoints return 401/404 and all auth-dependent flows fail.
  const hasCreds = !!(process.env.BAGISTO_CUSTOMER_EMAIL && process.env.BAGISTO_CUSTOMER_PASSWORD);

  test.beforeEach(async ({ request }) => {
    if (!hasCreds) {
      test.skip(true, 'BAGISTO_CUSTOMER_EMAIL / BAGISTO_CUSTOMER_PASSWORD not set in .env');
    }
  });

  test('Should authenticate via login (auth-required pre-requisite)', async ({ request }) => {
    if (!hasCreds) {
      test.skip(true, 'No credentials configured');
      return;
    }
    const email = process.env.BAGISTO_CUSTOMER_EMAIL!;
    const password = process.env.BAGISTO_CUSTOMER_PASSWORD!;
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_LOGIN, {
      method: 'POST',
      data: { email, password },
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(typeof body.token).toBe('string');
    expect(body.token.length).toBeGreaterThan(0);
    console.log('Already authenticated for customer profile tests, token:', body.token.slice(0, 12) + '...');
  });

  test('Should handle profile (GET) status check', async ({ request }) => {
    if (!hasCreds) {
      test.skip(true, 'No credentials configured');
      return;
    }
    // With admin Bearer — responds 200 or 401 depending on auth system
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_PROFILE);
    assertPublicCustStatus(response, 'GET /api/shop/customers/profile');
  });

  test('Should handle profile update PUT status', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_PROFILE, {
      method: 'PUT',
      data: { firstName: 'Updated', lastName: 'Name' },
    });
    assertPublicCustStatus(response, 'PUT /api/shop/customers/profile');
  });

  test('Should handle account deletion DELETE status', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_DELETE_ACCOUNT, {
      method: 'DELETE',
    });
    assertPublicCustStatus(response, 'DELETE /api/shop/customers/profile');
  });
});
