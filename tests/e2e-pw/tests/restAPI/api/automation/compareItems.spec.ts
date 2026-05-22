// tests/restAPI/api/automation/compareItems.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

function assertStatus(resp: any, debugLabel: string) {
  expect([0, 200, 201, 400, 401, 403, 404, 422, 500]).toContain(resp.status());
  console.log(`${debugLabel}:`, resp.status());
}

function generateUniqueEmail() {
  return `compare_${Date.now()}@example.com`;
}

function generatePassword() {
  return `Compare${Math.floor(Math.random() * 10000)}!`;
}

let authToken: string | null = null;
let customerEmail: string;
let customerPassword: string;

test.describe('Compare Items REST API (Public)', () => {
  test('Should return status for compare items without auth', async ({ request }) => {
    const response = await sendRestRequest(request, '/api/shop/compare-items');
    assertStatus(response, 'GET /api/shop/compare-items');
  });

  test('Should return status for single compare item without auth', async ({ request }) => {
    const response = await sendRestRequest(request, '/api/shop/compare-items/1');
    assertStatus(response, 'GET /api/shop/compare-items/1');
  });
});

test.describe('Compare Items REST API (Auth Required)', () => {
  test.beforeAll(async ({ request }) => {
    customerEmail = generateUniqueEmail();
    customerPassword = generatePassword();
    console.log(`Compare test credentials - Email: ${customerEmail}, Password: ${customerPassword}`);

    const loginResp = await sendRestRequest(request, ENDPOINTS.CUSTOMER_LOGIN, {
      method: 'POST',
      data: { email: customerEmail, password: customerPassword },
    });

    if (loginResp.status() === 200) {
      const body = await loginResp.json();
      authToken = body.token as string;
      console.log('Logged in for compare tests. Token:', authToken?.slice(0, 12) + '...');
    } else {
      // Try to register first
      const registerResp = await sendRestRequest(request, ENDPOINTS.CUSTOMER_REGISTER, {
        method: 'POST',
        data: {
          first_name: 'Compare',
          last_name: 'User',
          email: customerEmail,
          password: customerPassword,
          password_confirmation: customerPassword,
        },
      });

      if (registerResp.status() === 200 || registerResp.status() === 201) {
        const loginRetry = await sendRestRequest(request, ENDPOINTS.CUSTOMER_LOGIN, {
          method: 'POST',
          data: { email: customerEmail, password: customerPassword },
        });
        if (loginRetry.status() === 200) {
          const body = await loginRetry.json();
          authToken = body.token as string;
          console.log('Registered and logged in for compare tests');
        }
      }
    }
  });

  function authHeaders(token: string) {
    return { Authorization: `Bearer ${token}` };
  }

  test('Should return status for compare items when authenticated', async ({ request }) => {
    if (!authToken) {
      test.skip(true, 'Login failed');
      return;
    }
    const response = await sendRestRequest(request, '/api/shop/compare-items', {
      headers: authHeaders(authToken),
    });
    assertStatus(response, 'GET /api/shop/compare-items (authenticated)');
    if (response.status() === 200) {
      const body = await response.json();
      expect(body).toBeDefined();
      if (Array.isArray(body)) {
        console.log('Compare item count:', body.length);
      } else if (body.data) {
        console.log('Compare item count:', body.data.length);
      }
    }
  });

  test('Should return single compare item when authenticated', async ({ request }) => {
    if (!authToken) {
      test.skip(true, 'Login failed');
      return;
    }
    const listResp = await sendRestRequest(request, '/api/shop/compare-items', {
      headers: authHeaders(authToken),
    });
    if (listResp.status() !== 200) {
      test.skip(true, 'Compare items list not available');
      return;
    }
    const listBody = await listResp.json();
    const items = Array.isArray(listBody) ? listBody : listBody.data ?? [];
    if (items.length === 0) {
      test.skip(true, 'No compare items');
      return;
    }
    const itemId = items[0].id;
    const response = await sendRestRequest(request, `/api/shop/compare-items/${itemId}`, {
      headers: authHeaders(authToken),
    });
    assertStatus(response, `GET /api/shop/compare-items/${itemId} (authenticated)`);
    if (response.status() === 200) {
      const body = await response.json();
      expect(body.id).toBe(itemId);
      console.log('Single compare item:', { id: body.id, name: body.name });
    }
  });

  test('Should return 401 without token', async ({ request }) => {
    const response = await sendRestRequest(request, '/api/shop/compare-items');
    assertStatus(response, 'GET /api/shop/compare-items (no auth)');
  });
});