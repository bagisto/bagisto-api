import { test, expect, APIRequestContext } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { sendAdminRequest } from '../../rest/helpers/adminClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import { ADMIN_CUSTOMERS } from '../../rest/endpoints/admin.customers.endpoints';
import {
  UNKNOWN_ID,
  adminConfigValue,
  createAdminProduct,
  deleteAdminProducts,
  expectDetail,
  safeJson,
  uniqueStamp,
} from '../../rest/helpers/coverage/restGaps.helpers';
import { ShopCustomer, bearer, registerShopCustomer } from '../../rest/helpers/coverage/shopCart.helpers';

test.describe.configure({ timeout: 120_000 });

const REVIEW_SLUG = 'catalog.products.review';

const MESSAGES = {
  REVIEWS_DISABLED: 'Product reviews are currently disabled',
  GUEST_REVIEWS_DISABLED: 'Guest reviews are not allowed. Please login to submit a review',
  RATING_INVALID: 'Rating must be between 1 and 5',
  NOT_OWNED: 'This review was not written by you and cannot be modified or deleted',
  UNAUTHENTICATED: 'Unauthenticated. Please login to perform this action',
  CUSTOMER_REVIEW_NOT_FOUND: (id: number) => `Customer review with ID "${id}" not found`,
};

interface ReviewSettings {
  customerReviews: boolean;
  guestReviews: boolean;
}

function isEnabled(value: string | null, fallback: boolean): boolean {
  if (value === null) {
    return fallback;
  }

  return value === '1' || value === 'true';
}

async function reviewSettings(request: APIRequestContext): Promise<ReviewSettings> {
  return {
    customerReviews: isEnabled(await adminConfigValue(request, REVIEW_SLUG, `${REVIEW_SLUG}.customer_review`), true),
    guestReviews: isEnabled(await adminConfigValue(request, REVIEW_SLUG, `${REVIEW_SLUG}.guest_review`), false),
  };
}

async function createReview(
  request: APIRequestContext,
  customer: ShopCustomer,
  productId: number,
  title: string
): Promise<any> {
  const response = await sendRestRequest(request, ENDPOINTS.REVIEWS, {
    method: 'POST',
    headers: bearer(customer.token),
    data: { productId, title, comment: 'E2E review comment', rating: 4 },
  });
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(201);
  expect(typeof body?.id, JSON.stringify(body)).toBe('number');

  return body;
}

async function customerReviews(request: APIRequestContext, customer: ShopCustomer): Promise<any[]> {
  const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_REVIEWS, { headers: bearer(customer.token) });
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);
  expect(Array.isArray(body), JSON.stringify(body)).toBe(true);

  return body;
}

async function approveReview(request: APIRequestContext, reviewId: number): Promise<void> {
  const response = await sendAdminRequest(request, ADMIN_CUSTOMERS.REVIEW(reviewId), {
    method: 'PUT',
    data: { status: 'approved' },
  });
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);
  expect(body.status).toBe('approved');
}

test.describe('public product reviews rest api', () => {
  let createdIds: number[];
  let productId: number;

  test.beforeEach(async ({ request }) => {
    createdIds = [];
    productId = (await createAdminProduct(request, 'simple')).id;
    createdIds.push(productId);
  });

  test.afterEach(async ({ request }) => {
    await deleteAdminProducts(request, createdIds);
  });

  test('should return an empty review list with pagination headers for a product without reviews', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_REVIEWS(productId), {
      params: { page: '1', per_page: '10' },
    });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body).toEqual([]);

    const headers = response.headers();

    expect(headers['x-total-count']).toBe('0');
    expect(headers['x-page']).toBe('1');
    expect(headers['x-per-page']).toBe('10');
  });

  test('should list only approved reviews of a product', async ({ request }) => {
    const settings = await reviewSettings(request);

    test.skip(!settings.customerReviews, 'customer reviews are disabled on this store');

    const customer = await registerShopCustomer(request);
    const title = `E2E Approved Review ${uniqueStamp()}`;
    const review = await createReview(request, customer, productId, title);

    const pending = await sendRestRequest(request, ENDPOINTS.PRODUCT_REVIEWS(productId));

    expect(pending.status()).toBe(200);
    expect(await safeJson(pending)).toEqual([]);
    expect(pending.headers()['x-total-count']).toBe('0');

    await approveReview(request, review.id);

    const approved = await sendRestRequest(request, ENDPOINTS.PRODUCT_REVIEWS(productId), {
      params: { page: '1', per_page: '10' },
    });
    const body = await safeJson(approved);

    expect(approved.status(), JSON.stringify(body)).toBe(200);
    expect(body).toHaveLength(1);
    expect(body[0].id).toBe(review.id);
    expect(body[0].title).toBe(title);
    expect(body[0].rating).toBe(4);
    expect(approved.headers()['x-total-count']).toBe('1');
  });
});

test.describe('public product reviews of a missing product rest api', () => {
  test('should return an empty review list for a product that does not exist', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_REVIEWS(UNKNOWN_ID));
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body).toEqual([]);
    expect(response.headers()['x-total-count']).toBe('0');
  });

  test('should refuse the customer review listing without a customer token', async ({ request }) => {
    await expectDetail(await sendRestRequest(request, ENDPOINTS.CUSTOMER_REVIEWS), 403, MESSAGES.UNAUTHENTICATED);
  });
});

test.describe('customer product reviews rest api', () => {
  let createdIds: number[];
  let productId: number;
  let settings: ReviewSettings;
  let customer: ShopCustomer;

  test.beforeEach(async ({ request }) => {
    settings = await reviewSettings(request);

    test.skip(!settings.customerReviews, 'customer reviews are disabled on this store');

    createdIds = [];
    productId = (await createAdminProduct(request, 'simple')).id;
    createdIds.push(productId);

    customer = await registerShopCustomer(request);
  });

  test.afterEach(async ({ request }) => {
    await deleteAdminProducts(request, createdIds ?? []);
  });

  test('should create a pending review in the name of the customer', async ({ request }) => {
    const title = `E2E Created Review ${uniqueStamp()}`;
    const review = await createReview(request, customer, productId, title);

    expect(review.title).toBe(title);
    expect(review.comment).toBe('E2E review comment');
    expect(review.rating).toBe(4);
    expect(review.status).toBe('pending');
    expect(review.name).toBe(`${customer.firstName} ${customer.lastName}`);
  });

  test('should list own reviews when authenticated', async ({ request }) => {
    const title = `E2E Own Review ${uniqueStamp()}`;
    const review = await createReview(request, customer, productId, title);

    const rows = await customerReviews(request, customer);

    expect(rows).toHaveLength(1);
    expect(rows[0].id).toBe(review.id);
    expect(rows[0].title).toBe(title);
    expect(rows[0].status).toBe('pending');
  });

  test('should update own review', async ({ request }) => {
    const review = await createReview(request, customer, productId, `E2E Review To Update ${uniqueStamp()}`);
    const updatedTitle = `E2E Updated Review ${uniqueStamp()}`;

    const response = await sendRestRequest(request, ENDPOINTS.REVIEW(review.id), {
      method: 'PATCH',
      headers: { ...bearer(customer.token), 'Content-Type': 'application/merge-patch+json' },
      data: { title: updatedTitle, comment: 'Updated comment', rating: 5 },
    });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body.id).toBe(review.id);
    expect(body.title).toBe(updatedTitle);
    expect(body.rating).toBe(5);
    expect(body.status).toBe('pending');

    const [stored] = await customerReviews(request, customer);

    expect(stored.title).toBe(updatedTitle);
    expect(stored.rating).toBe(5);
  });

  test('should refuse to update a review of another customer', async ({ request }) => {
    const title = `E2E Foreign Review ${uniqueStamp()}`;
    const review = await createReview(request, customer, productId, title);
    const other = await registerShopCustomer(request);

    const response = await sendRestRequest(request, ENDPOINTS.REVIEW(review.id), {
      method: 'PATCH',
      headers: { ...bearer(other.token), 'Content-Type': 'application/merge-patch+json' },
      data: { title: 'Hijacked title' },
    });

    await expectDetail(response, 403, MESSAGES.NOT_OWNED);

    const [stored] = await customerReviews(request, customer);

    expect(stored.title).toBe(title);
  });

  test('should delete own review', async ({ request }) => {
    const review = await createReview(request, customer, productId, `E2E Review To Delete ${uniqueStamp()}`);

    const response = await sendRestRequest(request, ENDPOINTS.REVIEW(review.id), {
      method: 'DELETE',
      headers: bearer(customer.token),
    });

    expect(response.status()).toBe(204);
    expect(await customerReviews(request, customer)).toEqual([]);

    await expectDetail(
      await sendRestRequest(request, ENDPOINTS.CUSTOMER_REVIEW(review.id), { headers: bearer(customer.token) }),
      404,
      MESSAGES.CUSTOMER_REVIEW_NOT_FOUND(review.id)
    );
  });

  test('should reject a review with an invalid rating', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.REVIEWS, {
      method: 'POST',
      headers: bearer(customer.token),
      data: { productId, title: 'Bad rating', comment: 'Comment', rating: 6 },
    });

    await expectDetail(response, 400, MESSAGES.RATING_INVALID);
    expect(await customerReviews(request, customer)).toEqual([]);
  });
});

test.describe('guest product reviews rest api', () => {
  let createdIds: number[];
  let productId: number;

  test.beforeEach(async ({ request }) => {
    createdIds = [];
    productId = (await createAdminProduct(request, 'simple')).id;
    createdIds.push(productId);
  });

  test.afterEach(async ({ request }) => {
    await deleteAdminProducts(request, createdIds);
  });

  test('should apply the review settings to a review without a customer token', async ({ request }) => {
    const settings = await reviewSettings(request);

    const response = await sendRestRequest(request, ENDPOINTS.REVIEWS, {
      method: 'POST',
      data: { productId, title: 'Guest review', comment: 'Guest comment', rating: 5, name: 'E2E Guest' },
    });

    if (!settings.customerReviews) {
      await expectDetail(response, 403, MESSAGES.REVIEWS_DISABLED);

      return;
    }

    if (!settings.guestReviews) {
      await expectDetail(response, 403, MESSAGES.GUEST_REVIEWS_DISABLED);

      return;
    }

    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.status).toBe('pending');
    expect(body.name).toBe('E2E Guest');
  });
});
