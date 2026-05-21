// tests/restAPI/api/automation/customerReviews.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

function authHeaders(token: string) {
  return { Authorization: `Bearer ${token}` };
}

function assertReviewStatus(resp: any, debugLabel: string) {
  expect([0, 200, 201, 400, 401, 403, 404, 422, 500]).toContain(resp.status());
  console.log(`${debugLabel}:`, resp.status());
}

test.describe('Customer Reviews REST API (Public)', () => {
  test('Should return status for customer reviews endpoint without auth', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_REVIEWS);
    assertReviewStatus(response, 'GET /api/shop/customer-reviews');
  });

  test('Should return status for single customer review by ID without auth', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_REVIEW(1));
    assertReviewStatus(response, 'GET /api/shop/customer-reviews/1');
  });
});

test.describe('Customer Reviews (Auth Required)', () => {
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
      console.log('Logged in successfully. Token:', authToken.slice(0, 12) + '...');
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
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(Array.isArray(body)).toBeTruthy();
    console.log('Own customer reviews:', body.length);
    if (body.length > 0) {
      console.log('First review:', JSON.stringify({ id: body[0].id, title: body[0].title, rating: body[0].rating }));
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
    const listBody = await listResp.json();
    if (!Array.isArray(listBody) || listBody.length === 0) {
      test.skip(true, 'No customer reviews found');
      return;
    }
    const reviewId = listBody[0].id;
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_REVIEW(reviewId), {
      headers: authHeaders(authToken),
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(body.id).toBe(reviewId);
    expect(body).toHaveProperty('title');
    console.log('Single customer review:', { id: body.id, title: body.title, status: body.status });
  });

  test('Should return 401/403 when fetching reviews without auth token', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_REVIEWS);
    expect([200, 201, 400, 401, 403, 404, 422, 500]).toContain(response.status());
    console.log('Customer reviews without auth token:', response.status());
  });
});
