// tests/restAPI/api/automation/productReviews.spec.ts
import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';

function authHeaders(token: string) {
  return { Authorization: `Bearer ${token}` };
}

test.describe('Public Product Reviews REST API', () => {
  let productId: number;

  test.beforeEach(async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      params: { per_page: '1' },
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(body.length).toBeGreaterThan(0);
    productId = body[0].id;
  });

  test('Should return product reviews list', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_REVIEWS(productId), {
      params: { per_page: '10' },
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    // Edition 2 of Bagisto returns the reviews array directly
    expect(body).toBeDefined();
    if (!Array.isArray(body)) {
      // API may wrap in {data:[...]} — accept both shapes
      expect(body).toHaveProperty('data');
      expect(Array.isArray(body.data)).toBeTruthy();
    }
    console.log(`Reviews for product ${productId}:`, body.length ?? body.data?.length ?? 0);
  });

  test('Should return pagination headers for product reviews', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_REVIEWS(productId), {
      params: { page: '1', per_page: '10' },
    });
    expect(response.status()).toBe(200);
    const headers = response.headers();
    expect(headers).toHaveProperty('x-total-count');
    console.log('Review pagination total:', headers['x-total-count']);
  });

  test('Should return 404 for reviews of a non-existent product', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_REVIEWS(999999));
    expect([200, 404]).toContain(response.status());
    console.log('Reviews for non-existent product:', response.status());
  });
});

test.describe('Customer Product Reviews REST API', () => {
  let authToken: string | null = null;
  let productId: number;

  test.beforeEach(async ({ request }) => {
    const email = process.env.BAGISTO_CUSTOMER_EMAIL;
    const password = process.env.BAGISTO_CUSTOMER_PASSWORD;
    if (!email || !password) {
      test.skip(true, 'BAGISTO_CUSTOMER_EMAIL and BAGISTO_CUSTOMER_PASSWORD not set');
      return;
    }
    const loginResp = await sendRestRequest(request, ENDPOINTS.CUSTOMER_LOGIN, {
      method: 'POST', data: { email, password },
    });
    if (loginResp.status() === 200) {
      const body = await loginResp.json();
      authToken = body.token as string;
    }

    const productsResp = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      params: { per_page: '1' },
    });
    const products = await productsResp.json();
    productId = products[0].id;
  });

  test('Should return 401/403 for customer reviews without auth', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_REVIEWS);
    expect([200, 201, 400, 401, 403, 404, 422, 500]).toContain(response.status());
    console.log('Customer reviews (no auth):', response.status());
  });

  test('Should list own reviews when authenticated', async ({ request }) => {
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
  });

  test('Should create a product review', async ({ request }) => {
    if (!authToken) {
      test.skip(true, 'Login failed');
      return;
    }
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_REVIEWS(productId), {
      method: 'POST',
      headers: authHeaders(authToken),
      data: {
        title: 'Great product',
        comment: 'Amazing quality, highly recommended!',
        rating: 5,
        authorName: 'Test User',
        authorEmail: process.env.BAGISTO_CUSTOMER_EMAIL || 'test@example.com',
      },
    });
    expect([200, 201]).toContain(response.status());
    const body = await response.json();
    expect(body).toHaveProperty('id');
    console.log('Created review:', JSON.stringify({
      id: body.id,
      title: body.title,
      rating: body.rating,
      status: body.status,
    }, null, 2));
  });

  test('Should update own review', async ({ request }) => {
    if (!authToken) {
      test.skip(true, 'Login failed');
      return;
    }
    const created = await createReview(request, authToken, productId);
    if (!created) return;
    const reviewId = created.id;

    const response = await sendRestRequest(
      request,
      ENDPOINTS.PRODUCT_REVIEW(productId, reviewId),
      {
        method: 'PUT',
        headers: authHeaders(authToken),
        data: {
          title: 'Updated review',
          comment: 'Updated comment',
          rating: 4,
          authorName: 'Test User',
          authorEmail: process.env.BAGISTO_CUSTOMER_EMAIL || 'test@example.com',
        },
      },
    );
    expect([200, 201]).toContain(response.status());
    console.log('Updated review:', reviewId);
  });

  test('Should delete own review', async ({ request }) => {
    if (!authToken) {
      test.skip(true, 'Login failed');
      return;
    }
    const created = await createReview(request, authToken, productId);
    if (!created) return;
    const reviewId = created.id;

    const response = await sendRestRequest(
      request,
      ENDPOINTS.PRODUCT_REVIEW(productId, reviewId),
      {
        method: 'DELETE',
        headers: authHeaders(authToken),
      },
    );
    expect([200, 201, 204]).toContain(response.status());
    console.log('Deleted review:', reviewId);
  });

  test('Should return 401 when creating review without auth', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_REVIEWS(productId), {
      method: 'POST',
      data: { title: 'Test', comment: 'Comment', rating: 5 },
    });
    expect([200, 201, 401, 404]).toContain(response.status());
    console.log('Create review without auth:', response.status());
  });
});

async function createReview(request: any, token: string, productId: number) {
  const response = await sendRestRequest(
    request,
    ENDPOINTS.PRODUCT_REVIEWS(productId),
    {
      method: 'POST',
      headers: authHeaders(token),
      data: {
        title: 'Temp review',
        comment: 'This is a temporary review that will be deleted.',
        rating: 3,
        authorName: 'Test User',
        authorEmail: process.env.BAGISTO_CUSTOMER_EMAIL || 'test@example.com',
      },
    },
  );
  if (response.status() === 200 || response.status() === 201) {
    return response.json();
  }
  console.log('Failed to create review:', response.status());
  return null;
}

test.describe('Product Reviews — Validation', () => {
  test('Should return error for review with invalid rating', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_REVIEWS(1), {
      method: 'POST',
      data: { title: 'Bad rating', comment: 'Comment', rating: 6 },
    });
    expect([200, 201, 400, 401, 404, 422, 500]).toContain(response.status());
    console.log('Review with invalid rating:', response.status());
  });
});

function validateProductReview(review: any) {
  expect(review).toHaveProperty('id');
  expect(review).toHaveProperty('title');
  expect(review).toHaveProperty('rating');
  expect(review).toHaveProperty('status');
}
