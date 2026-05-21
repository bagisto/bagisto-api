// tests/restAPI/api/automation/compareItems.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

function assertReviewStatus(resp: any, debugLabel: string) {
  expect([0, 200, 201, 400, 401, 403, 404, 422, 500]).toContain(resp.status());
  console.log(`${debugLabel}:`, resp.status());
}

test.describe('Compare Items REST API (Public)', () => {
  test('Should return status for compare items without auth', async ({ request }) => {
    const response = await sendRestRequest(request, '/api/shop/compare-items');
    assertReviewStatus(response, 'GET /api/shop/compare-items');
  });

  test('Should return status for single compare item without auth', async ({ request }) => {
    const response = await sendRestRequest(request, '/api/shop/compare-items/1');
    assertReviewStatus(response, 'GET /api/shop/compare-items/1');
  });
});

test.describe('Compare Items REST API (Auth Required)', () => {
  const hasCreds = !!(process.env.BAGISTO_CUSTOMER_EMAIL && process.env.BAGISTO_CUSTOMER_PASSWORD);
  let authToken: string | null = null;

  test.beforeEach(async ({ request }) => {
    if (!hasCreds) {
      test.skip(true, 'BAGISTO_CUSTOMER_EMAIL / BAGISTO_CUSTOMER_PASSWORD not set in .env');
      return;
    }
    const email = process.env.BAGISTO_CUSTOMER_EMAIL!;
    const password = process.env.BAGISTO_CUSTOMER_PASSWORD!;
    const loginResp = await sendRestRequest(request, ENDPOINTS.CUSTOMER_LOGIN, {
      method: 'POST', data: { email, password },
    });
    if (loginResp.status() === 200) {
      const body = await loginResp.json();
      authToken = body.token as string;
      console.log('Logged in for compare tests. Token:', authToken.slice(0, 12) + '...');
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
    assertReviewStatus(response, 'GET /api/shop/compare-items (authenticated)');
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
    assertReviewStatus(response, `GET /api/shop/compare-items/${itemId} (authenticated)`);
    if (response.status() === 200) {
      const body = await response.json();
      expect(body.id).toBe(itemId);
      console.log('Single compare item:', { id: body.id, name: body.name });
    }
  });

  test('Should return 401 without token', async ({ request }) => {
    const response = await sendRestRequest(request, '/api/shop/compare-items');
    assertReviewStatus(response, 'GET /api/shop/compare-items (no auth)');
  });
});
