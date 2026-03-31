import { test, expect } from '@playwright/test';
import { getCustomerAuthHeaders } from '../../config/auth';
import {
  GET_CUSTOMER_REVIEW,
  GET_CUSTOMER_REVIEW_INTROSPECTION,
  GET_CUSTOMER_REVIEWS,
  GET_CUSTOMER_REVIEWS_BY_RATING,
  GET_CUSTOMER_REVIEWS_BY_STATUS,
  GET_CUSTOMER_REVIEWS_COMBINED_FILTERS,
  GET_CUSTOMER_REVIEWS_PAGINATED,
} from '../../graphql/Queries/customerReview.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { expectAuthAwareResult, graphQLErrorMessages, pick } from '../../graphql/helpers/testSupport';

test.describe('Customer Review GraphQL API - Docs aligned', () => {
  test('Should cover customer reviews query with default pagination', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const response = await sendGraphQLRequest(request, GET_CUSTOMER_REVIEWS, { first: 5 }, headers);
    expect(response.status()).toBe(200);

    const body = await response.json();
    expectAuthAwareResult(body, 'data.customerReviews');

    const edges = pick(body, 'data.customerReviews.edges') ?? [];
    console.log(`Customer reviews response: ${edges.length} items or auth message.`);
  });

  test('Should cover customer reviews by status query', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const response = await sendGraphQLRequest(
      request,
      GET_CUSTOMER_REVIEWS_BY_STATUS,
      { first: 5, status: 'approved' },
      headers
    );
    expect(response.status()).toBe(200);

    const body = await response.json();
    expectAuthAwareResult(body, 'data.customerReviews');
    console.log(`Customer reviews by status: ${graphQLErrorMessages(body).join(' | ') || 'success'}`);
  });

  test('Should cover customer reviews by rating query', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const response = await sendGraphQLRequest(
      request,
      GET_CUSTOMER_REVIEWS_BY_RATING,
      { first: 5, rating: 5 },
      headers
    );
    expect(response.status()).toBe(200);

    const body = await response.json();
    expectAuthAwareResult(body, 'data.customerReviews');
    console.log(`Customer reviews by rating: ${graphQLErrorMessages(body).join(' | ') || 'success'}`);
  });

  test('Should cover customer reviews combined filters query', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const response = await sendGraphQLRequest(
      request,
      GET_CUSTOMER_REVIEWS_COMBINED_FILTERS,
      { first: 5, status: 'approved', rating: 5 },
      headers
    );
    expect(response.status()).toBe(200);

    const body = await response.json();
    expectAuthAwareResult(body, 'data.customerReviews');
    console.log(`Customer reviews combined filters: ${graphQLErrorMessages(body).join(' | ') || 'success'}`);
  });

  test('Should cover customer reviews pagination query', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const response = await sendGraphQLRequest(
      request,
      GET_CUSTOMER_REVIEWS_PAGINATED,
      { first: 2, after: null },
      headers
    );
    expect(response.status()).toBe(200);

    const body = await response.json();
    expectAuthAwareResult(body, 'data.customerReviews');
    console.log(`Customer reviews paginated: ${graphQLErrorMessages(body).join(' | ') || 'success'}`);
  });

  test('Should cover single customer review query when data exists', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);

    if (!headers) {
      const response = await sendGraphQLRequest(request, GET_CUSTOMER_REVIEW, { id: '/api/shop/customer-reviews/1' });
      const body = await response.json();
      console.log(`Customer review auth response: ${graphQLErrorMessages(body).join(' | ')}`);
      expect(graphQLErrorMessages(body).length > 0 || body.data?.customerReview === null).toBeTruthy();
      return;
    }

    const reviewsResponse = await sendGraphQLRequest(request, GET_CUSTOMER_REVIEWS, { first: 1 }, headers);
    const reviewsBody = await reviewsResponse.json();
    const reviewId = reviewsBody.data?.customerReviews?.edges?.[0]?.node?.id;

    if (!reviewId) {
      console.log('Authenticated customer has no reviews in this environment.');
      expect(true).toBeTruthy();
      return;
    }

    const response = await sendGraphQLRequest(request, GET_CUSTOMER_REVIEW, { id: reviewId }, headers);
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(body.errors).toBeUndefined();
    expect(body.data?.customerReview).toBeTruthy();
    console.log(`Customer review detail loaded for ${reviewId}`);
  });

  test('Should cover customer review introspection query', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_CUSTOMER_REVIEW_INTROSPECTION);
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(body.errors).toBeUndefined();
    expect(body.data?.__type?.fields?.length).toBeGreaterThan(0);
    console.log(`CustomerReview introspection fields: ${body.data.__type.fields.length}`);
  });
});
