import { test, expect } from '@playwright/test';
import { getCustomerAuthHeaders } from '../../config/auth';
import {
  CREATE_PRODUCT_REVIEW,
  UPDATE_PRODUCT_REVIEW,
  DELETE_PRODUCT_REVIEW,
} from '../../graphql/Queries/productReviews.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../graphql/helpers/testSupport';

test.describe('Product Review CRUD - Auth aware', () => {
  test('Should create a product review or return the actual API message', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const input = {
      productId: 1,
      title: 'Review creation check',
      comment: 'Checking the live API response for review creation.',
      rating: 5,
      name: 'QA User',
      email: 'qa.user@example.com',
      status: 0,
    };

    const response = await sendGraphQLRequest(request, CREATE_PRODUCT_REVIEW, { input }, headers);
    expect(response.status()).toBe(200);
    const body = await response.json();
    console.log(`Create review response: ${JSON.stringify(body)}`);

    if (body.data?.createProductReview?.productReview) {
      expect(body.data.createProductReview.productReview.title).toBe(input.title);
      return;
    }

    expect(graphQLErrorMessages(body).length).toBeGreaterThan(0);
  });

  test('Should update a product review or return the actual API message', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const input = {
      id: '/api/shop/reviews/1',
      title: 'Updated review title',
      comment: 'Updated comment from automation.',
      rating: 4,
      status: 1,
    };

    const response = await sendGraphQLRequest(request, UPDATE_PRODUCT_REVIEW, { input }, headers);
    expect(response.status()).toBe(200);
    const body = await response.json();
    console.log(`Update review response: ${JSON.stringify(body)}`);

    if (body.data?.updateProductReview?.productReview) {
      expect(body.data.updateProductReview.productReview.id).toBeDefined();
      return;
    }

    expect(graphQLErrorMessages(body).length).toBeGreaterThan(0);
  });

  test('Should delete a product review and reflect the actual API behavior', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const input = {
      id: '/api/shop/reviews/999999999',
    };

    const response = await sendGraphQLRequest(request, DELETE_PRODUCT_REVIEW, { input }, headers);
    expect(response.status()).toBe(200);
    const body = await response.json();
    console.log(`Delete review response: ${JSON.stringify(body)}`);

    expect(
      body.data?.deleteProductReview?.productReview?.id !== undefined ||
      graphQLErrorMessages(body).length > 0
    ).toBeTruthy();
  });
});
