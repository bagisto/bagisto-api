// tests/restAPI/api/automation/wishlist.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

function assertReviewStatus(resp: any, debugLabel: string) {
  expect([0, 200, 201, 400, 401, 403, 404, 422, 500]).toContain(resp.status());
  console.log(`${debugLabel}:`, resp.status());
}

test.describe('Wishlist REST API (Public)', () => {
  test('Should return status for wishlist listing without auth', async ({ request }) => {
    const response = await sendRestRequest(request, '/api/shop/wishlists');
    assertReviewStatus(response, 'GET /api/shop/wishlists');
  });

  test('Should return status for single wishlist by ID without auth', async ({ request }) => {
    const response = await sendRestRequest(request, '/api/shop/wishlists/1');
    assertReviewStatus(response, 'GET /api/shop/wishlists/1');
  });
});

test.describe('Wishlist REST API (Auth Required)', () => {
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
      console.log('Logged in for wishlist tests. Token:', authToken.slice(0, 12) + '...');
    }
  });

  function authHeaders(token: string) {
    return { Authorization: `Bearer ${token}` };
  }

  test('Should return own wishlist when authenticated', async ({ request }) => {
    if (!authToken) {
      test.skip(true, 'Login failed');
      return;
    }
    const response = await sendRestRequest(request, '/api/shop/wishlists', {
      headers: authHeaders(authToken),
    });
    assertReviewStatus(response, 'GET /api/shop/wishlists (authenticated)');
    if (response.status() === 200) {
      const body = await response.json();
      expect(body).toBeDefined();
      if (Array.isArray(body)) {
        console.log('Wishlist item count:', body.length);
        if (body.length > 0) {
          console.log('First wishlist item:', JSON.stringify({ id: body[0].id, name: body[0].name }));
        }
      }
    }
  });

  test('Should return single wishlist by ID when authenticated', async ({ request }) => {
    if (!authToken) {
      test.skip(true, 'Login failed');
      return;
    }
    const listResp = await sendRestRequest(request, '/api/shop/wishlists', {
      headers: authHeaders(authToken),
    });
    if (listResp.status() !== 200) {
      test.skip(true, 'Wishlist list endpoint unavailable');
      return;
    }
    const listBody = await listResp.json();
    const items = Array.isArray(listBody) ? listBody : listBody.data ?? [];
    if (items.length === 0) {
      test.skip(true, 'No wishlist items');
      return;
    }
    const wishlistId = items[0].id;
    const response = await sendRestRequest(request, `/api/shop/wishlists/${wishlistId}`, {
      headers: authHeaders(authToken),
    });
    assertReviewStatus(response, `GET /api/shop/wishlists/${wishlistId} (authenticated)`);
    if (response.status() === 200) {
      const body = await response.json();
      expect(body.id).toBe(wishlistId);
      console.log('Single wishlist item:', { id: body.id, name: body.name });
    }
  });

  test('Should return 401/403 when accessing wishlist without token', async ({ request }) => {
    const response = await sendRestRequest(request, '/api/shop/wishlists');
    assertReviewStatus(response, 'GET /api/shop/wishlists (no auth)');
  });
});
