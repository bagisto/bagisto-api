import { test, expect, APIRequestContext } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import { bearer, expectProblem, readJson, registerShopCustomer, ShopCustomer, uniqueSuffix } from '../../rest/helpers/coverage/shopCart.helpers';
import { e2eFixtures, missingFixtureReason } from '../../../fixtures/e2eFixtures';

const MSG = {
  UNAUTHENTICATED: 'Unauthenticated. Please login to perform this action',
  REVIEW_NOT_FOUND: (id: number) => `Customer review with ID "${id}" not found`,
};

interface CreatedReview {
  id: number;
  title: string;
  comment: string;
  rating: number;
}

async function createReview(request: APIRequestContext, customer: ShopCustomer, productId: number): Promise<CreatedReview> {
  const suffix = uniqueSuffix();
  const title = `customer review ${suffix}`;
  const comment = `customer review comment ${suffix}`;
  const rating = 4;

  const response = await sendRestRequest(request, ENDPOINTS.REVIEWS, {
    method: 'POST',
    data: { product_id: productId, title, comment, rating, name: customer.firstName },
    headers: bearer(customer.token),
  });
  const body = await readJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(201);

  return { id: Number(body.id), title, comment, rating };
}

function reviewedProductId(): number {
  const fx = e2eFixtures();
  test.skip(!fx.order, missingFixtureReason('order'));

  return fx.order!.productId;
}

test.describe('customer reviews rest api without a token', () => {
  test('customer reviews list without a bearer token is refused', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_REVIEWS);

    await expectProblem(response, 403, MSG.UNAUTHENTICATED);
  });

  test('customer review detail without a bearer token is refused', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_REVIEW(1));

    await expectProblem(response, 403, MSG.UNAUTHENTICATED);
  });
});

test.describe('customer reviews rest api', () => {
  let customer: ShopCustomer;

  test.beforeEach(async ({ request }) => {
    customer = await registerShopCustomer(request);
  });

  test('customer reviews list holds exactly the reviews the customer wrote', async ({ request }) => {
    const productId = reviewedProductId();
    const review = await createReview(request, customer, productId);

    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_REVIEWS, { headers: bearer(customer.token) });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(Array.isArray(body)).toBe(true);
    expect(body).toHaveLength(1);
    expect(body[0].id).toBe(review.id);
    expect(body[0].title).toBe(review.title);
    expect(body[0].comment).toBe(review.comment);
    expect(body[0].rating).toBe(review.rating);
    expect(body[0].status).toBe('pending');
  });

  test('customer review detail returns the own review', async ({ request }) => {
    const productId = reviewedProductId();
    const review = await createReview(request, customer, productId);

    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_REVIEW(review.id), { headers: bearer(customer.token) });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body.id).toBe(review.id);
    expect(body.title).toBe(review.title);
    expect(body.rating).toBe(review.rating);
    expect(body.status).toBe('pending');
  });

  test('customer review detail of another customer review is not found', async ({ request }) => {
    const productId = reviewedProductId();
    const review = await createReview(request, customer, productId);
    const intruder = await registerShopCustomer(request);

    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_REVIEW(review.id), { headers: bearer(intruder.token) });

    await expectProblem(response, 404, MSG.REVIEW_NOT_FOUND(review.id));
  });

  test('customer reviews list of a customer without reviews is empty', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CUSTOMER_REVIEWS, { headers: bearer(customer.token) });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body).toEqual([]);
  });
});
