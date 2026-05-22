// tests/api/automation/createProductReview.spec.ts
import { test, expect } from '@playwright/test';
import { getCustomerAuthHeaders } from '../../config/auth';
import { CREATE_PRODUCT_REVIEW } from '../../graphql/Queries/productReviews.queries';
import { SHOP_DOCS_QUERIES } from '../../graphql/Queries/shopDocs.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../graphql/helpers/testSupport';

async function getFirstProductId(request: any): Promise<number> {
  const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getProducts, { first: 1 });
  const body = await response.json();
  const node = body.data?.products?.edges?.[0]?.node;
  const numericId = Number(String(node?.id ?? '').split('/').pop());
  expect(numericId > 0, 'test store must have at least one product available').toBeTruthy();
  return numericId;
}

test.describe('Create Product Review - Basic', () => {
  test.slow();

  test('Should create a product review as an authenticated customer', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);
    const productId = await getFirstProductId(request);

    const input = {
      productId,
      title: 'Excellent quality and very stylish',
      comment:
        'Very impressed with the product. The fabric feels premium and soft, the fitting is perfect, and the design adds a classy look.',
      rating: 5,
      name: 'Playwright Reviewer',
      email: 'playwright.reviewer@playwrighttest.local',
      status: 0,
    };

    const response = await sendGraphQLRequest(request, CREATE_PRODUCT_REVIEW, { input }, headers);
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(
      body.errors,
      `create review errored: ${graphQLErrorMessages(body).join(' | ')}`
    ).toBeUndefined();

    const review = body.data?.createProductReview?.productReview;
    expect(review).toBeTruthy();
    expect(review.title).toBe(input.title);
    expect(review.comment).toBe(input.comment);
    expect(review.rating).toBe(input.rating);
  });

  test('Should create a minimal product review as an authenticated customer', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);
    const productId = await getFirstProductId(request);

    const input = {
      productId,
      title: 'Good product',
      comment: 'I liked this product. Quality is good for the price.',
      rating: 4,
      name: 'Playwright Reviewer Minimal',
      email: 'playwright.reviewer.min@playwrighttest.local',
      status: 0,
    };

    const response = await sendGraphQLRequest(request, CREATE_PRODUCT_REVIEW, { input }, headers);
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(
      body.errors,
      `create review errored: ${graphQLErrorMessages(body).join(' | ')}`
    ).toBeUndefined();

    const review = body.data?.createProductReview?.productReview;
    expect(review).toBeTruthy();
    expect(review.title).toBe(input.title);
    expect(review.rating).toBe(input.rating);
  });
});
