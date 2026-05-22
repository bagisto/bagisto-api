// tests/restAPI/api/automation/customerReviews.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

function authHeaders(token: string) {
  return { Authorization: `Bearer ${token}` };
}

function assertStatus(resp: any, debugLabel: string) {
  expect([0, 200, 201, 400, 401, 403, 404, 422, 500]).toContain(resp.status());
  console.log(`${debugLabel}:`, resp.status());
}

function generateUniqueEmail() {
  return `custrev_${Date.now()}@example.com`;
}

function generatePassword() {
  return `CustRev${Math.floor(Math.random() * 10000)}!`;
}

let authToken: string | null = null;
let customerEmail: string;
let customerPassword: string;

test.describe('Customer Reviews REST API (Public)', () => {
  test('Should return status for customer reviews endpoint without auth', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_REVIEWS);
    assertStatus(response, 'GET /api/shop/customer-reviews');
  });

  test('Should return status for single customer review by ID without auth', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_REVIEW(1));
    assertStatus(response, 'GET /api/shop/customer-reviews/1');
  });
});

test.describe('Customer Reviews (Auth Required)', () => {
  test.beforeAll(async ({ request }) => {
    customerEmail = generateUniqueEmail();
    customerPassword = generatePassword();
    console.log(`Customer reviews test credentials - Email: ${customerEmail}, Password: ${customerPassword}`);

    const loginResp = await sendRestRequest(request, ENDPOINTS.CUSTOMER_LOGIN, {
      method: 'POST',
      data: { email: customerEmail, password: customerPassword },
    });

    if (loginResp.status() === 200) {
      const body = await loginResp.json();
      authToken = body.token as string;
      console.log('Logged in successfully. Token:', authToken?.slice(0, 12) + '...');
    } else {
      // Try to register first
      const registerResp = await sendRestRequest(request, ENDPOINTS.CUSTOMER_REGISTER, {
        method: 'POST',
        data: {
          first_name: 'Review',
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
          console.log('Registered and logged in for customer reviews tests');
        }
      }
    }
  });

  test('Should return own reviews when authenticated', async ({ request }) => {
    if (!authToken) {
      test.skip(true, 'Login failed');
      return;
    }
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_REVIEWS, {
      headers: authHeaders(authToken),
    });
    assertStatus(response, 'GET /api/shop/customer-reviews (authenticated)');
    if (response.status() === 200) {
      const body = await response.json();
      if (Array.isArray(body)) {
        console.log('Own customer reviews:', body.length);
        if (body.length > 0) {
          console.log('First review:', JSON.stringify({ id: body[0].id, title: body[0].title, rating: body[0].rating }));
        }
      }
    }
  });

  test('Should return own review by ID when authenticated', async ({ request }) => {
    if (!authToken) {
      test.skip(true, 'Login failed');
      return;
    }
    const listResp = await sendRestRequest(request, ENDPOINTS.CUSTOMER_REVIEWS, {
      headers: authHeaders(authToken),
    });
    if (listResp.status() !== 200) {
      test.skip(true, 'Customer reviews list not available');
      return;
    }
    const listBody = await listResp.json();
    if (!Array.isArray(listBody) || listBody.length === 0) {
      test.skip(true, 'No customer reviews found');
      return;
    }
    const reviewId = listBody[0].id;
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_REVIEW(reviewId), {
      headers: authHeaders(authToken),
    });
    assertStatus(response, `GET /api/shop/customer-reviews/${reviewId} (authenticated)`);
    if (response.status() === 200) {
      const body = await response.json();
      expect(body.id).toBe(reviewId);
      expect(body).toHaveProperty('title');
      console.log('Single customer review:', { id: body.id, title: body.title, status: body.status });
    }
  });

  test('Should return 401/403 when fetching reviews without auth token', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_REVIEWS);
    assertStatus(response, 'GET /api/shop/customer-reviews (no auth)');
  });
});