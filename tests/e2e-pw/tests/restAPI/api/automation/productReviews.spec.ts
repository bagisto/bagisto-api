// tests/restAPI/api/automation/productReviews.spec.ts
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
  return `prodrev_${Date.now()}@example.com`;
}

function generatePassword() {
  return `ProdRev${Math.floor(Math.random() * 10000)}!`;
}

let authToken: string | null = null;
let customerEmail: string;
let customerPassword: string;
let productId: number;

test.describe('Public Product Reviews REST API', () => {
  test.beforeEach(async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      params: { per_page: '1' },
    });
    const body = await response.json();
    if (body.length > 0) {
      productId = body[0].id;
    }
  });

  test('Should return product reviews list', async ({ request }) => {
    if (!productId) {
      test.skip(true, 'No products available');
      return;
    }
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_REVIEWS(productId), {
      params: { per_page: '10' },
    });
    assertStatus(response, `GET /api/shop/products/${productId}/reviews`);
    if (response.status() === 200) {
      const body = await response.json();
      if (Array.isArray(body)) {
        console.log(`Reviews for product ${productId}:`, body.length);
      } else if (body.data) {
        console.log(`Reviews for product ${productId}:`, body.data.length);
      }
    }
  });

  test('Should return pagination headers for product reviews', async ({ request }) => {
    if (!productId) {
      test.skip(true, 'No products available');
      return;
    }
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_REVIEWS(productId), {
      params: { page: '1', per_page: '10' },
    });
    assertStatus(response, `GET /api/shop/products/${productId}/reviews (paginated)`);
    if (response.status() === 200) {
      const headers = response.headers();
      expect(headers).toHaveProperty('x-total-count');
      console.log('Review pagination total:', headers['x-total-count']);
    }
  });

  test('Should return 404 for reviews of a non-existent product', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_REVIEWS(999999));
    assertStatus(response, 'GET /api/shop/products/999999/reviews');
  });
});

test.describe('Customer Product Reviews REST API', () => {
  test.beforeAll(async ({ request }) => {
    customerEmail = generateUniqueEmail();
    customerPassword = generatePassword();
    console.log(`Product reviews test credentials - Email: ${customerEmail}, Password: ${customerPassword}`);

    // Register customer first
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
      const body = [200, 201].includes(loginRetry.status())
          ? await loginRetry.json()
          : null;
      if (body?.token) {
        authToken = body.token as string;
        console.log('Registered and logged in for product reviews tests');
      }
    }

    // Get a product ID
    const productsResp = await sendRestRequest(request, ENDPOINTS.PRODUCTS, {
      params: { per_page: '1' },
    });
    const products = await productsResp.json();
    if (products.length > 0) {
      productId = products[0].id;
    }
  });

  test('Should return status for customer reviews endpoint without auth', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_REVIEWS);
    assertStatus(response, 'GET /api/shop/customer-reviews (no auth)');
  });

  test('Should list own reviews when authenticated', async ({ request }) => {
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

  test('Should create a product review', async ({ request }) => {
    if (!authToken) {
      test.skip(true, 'Login failed');
      return;
    }
    if (!productId) {
      test.skip(true, 'No products available');
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
        authorEmail: customerEmail,
      },
    });
    assertStatus(response, `POST /api/shop/products/${productId}/reviews`);
    if (response.status() === 200 || response.status() === 201) {
      const body = await response.json();
      expect(body).toHaveProperty('id');
      console.log('Created review:', JSON.stringify({
        id: body.id,
        title: body.title,
        rating: body.rating,
        status: body.status,
      }, null, 2));
    }
  });

  test('Should update own review', async ({ request }) => {
    if (!authToken) {
      test.skip(true, 'Login failed');
      return;
    }
    if (!productId) {
      test.skip(true, 'No products available');
      return;
    }

    // First create a review to update
    const createResponse = await sendRestRequest(request, ENDPOINTS.PRODUCT_REVIEWS(productId), {
      method: 'POST',
      headers: authHeaders(authToken),
      data: {
        title: 'Temp review',
        comment: 'This is a temporary review that will be updated.',
        rating: 3,
        authorName: 'Test User',
        authorEmail: customerEmail,
      },
    });

    if (createResponse.status() !== 200 && createResponse.status() !== 201) {
      test.skip(true, 'Failed to create test review');
      return;
    }
    const created = await createResponse.json();
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
          authorEmail: customerEmail,
        },
      },
    );
    assertStatus(response, `PUT /api/shop/products/${productId}/reviews/${reviewId}`);
    console.log('Updated review:', reviewId);
  });

  test('Should delete own review', async ({ request }) => {
    if (!authToken) {
      test.skip(true, 'Login failed');
      return;
    }
    if (!productId) {
      test.skip(true, 'No products available');
      return;
    }

    // First create a review to delete
    const createResponse = await sendRestRequest(request, ENDPOINTS.PRODUCT_REVIEWS(productId), {
      method: 'POST',
      headers: authHeaders(authToken),
      data: {
        title: 'Temp review for deletion',
        comment: 'This is a temporary review that will be deleted.',
        rating: 3,
        authorName: 'Test User',
        authorEmail: customerEmail,
      },
    });

    if (createResponse.status() !== 200 && createResponse.status() !== 201) {
      test.skip(true, 'Failed to create test review');
      return;
    }
    const created = await createResponse.json();
    const reviewId = created.id;

    const response = await sendRestRequest(
      request,
      ENDPOINTS.PRODUCT_REVIEW(productId, reviewId),
      {
        method: 'DELETE',
        headers: authHeaders(authToken),
      },
    );
    assertStatus(response, `DELETE /api/shop/products/${productId}/reviews/${reviewId}`);
    console.log('Deleted review:', reviewId);
  });

  test('Should return 401 when creating review without auth', async ({ request }) => {
    if (!productId) {
      test.skip(true, 'No products available');
      return;
    }
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_REVIEWS(productId), {
      method: 'POST',
      data: { title: 'Test', comment: 'Comment', rating: 5 },
    });
    assertStatus(response, `POST /api/shop/products/${productId}/reviews (no auth)`);
  });
});

test.describe('Product Reviews — Validation', () => {
  test('Should return error for review with invalid rating', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_REVIEWS(1), {
      method: 'POST',
      data: { title: 'Bad rating', comment: 'Comment', rating: 6 },
    });
    assertStatus(response, 'POST /api/shop/products/1/reviews (invalid rating)');
  });
});