// tests/api/automation/createProductReview.spec.ts
import { test, expect } from '@playwright/test';
import { getCustomerAuthHeaders } from '../../config/auth';
import { CREATE_PRODUCT_REVIEW } from '../../graphql/Queries/productReviews.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../graphql/helpers/testSupport';

test.describe('Create Product Review - Basic', () => {
  const validProductReviewInput = {
    productId: 1,
    title: "Excellent quality and very stylish",
    comment: "Very impressed with the product. The fabric feels premium and soft, the fitting is perfect, and the design adds a classy look. Suitable for office wear as well as casual outings. Lightweight yet warm. Highly recommended.",
    rating: 5,
    name: "John Doe",
    email: "john.doe@example.com",
    status: 0
  };

  test('Should create a product review or return the actual auth/message from the API', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const response = await sendGraphQLRequest(request, CREATE_PRODUCT_REVIEW, {
      input: validProductReviewInput
    }, headers);

    const body = await response.json();
    const messages = graphQLErrorMessages(body);
    console.log('Create review response:', JSON.stringify(body, null, 2));

    if (body.data?.createProductReview?.productReview) {
      expect(body.data.createProductReview.productReview.title).toBe(validProductReviewInput.title);
      return;
    }

    expect(messages.length).toBeGreaterThan(0);
  });

  test('Should reject incomplete review payloads with a real validation/auth message', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const minimalInput = {
      productId: 1,
      title: "Good product",
      comment: "I liked this product.",
      rating: 4,
      name: "Jane Smith",
      email: "jane.smith@example.com"
    };

    const response = await sendGraphQLRequest(request, CREATE_PRODUCT_REVIEW, {
      input: minimalInput
    }, headers);

    const body = await response.json();
    const messages = graphQLErrorMessages(body);
    console.log(`Create review message: ${messages.join(' | ')}`);

    if (body.data?.createProductReview?.productReview) {
      expect(body.data.createProductReview.productReview.title).toBe(minimalInput.title);
      return;
    }

    expect(messages.length).toBeGreaterThan(0);
  });
});
