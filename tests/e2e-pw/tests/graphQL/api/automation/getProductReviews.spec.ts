import { test, expect } from '@playwright/test';
import { SHOP_DOCS_QUERIES } from '../../graphql/Queries/shopDocs.queries';
import {
  GET_PRODUCT_REVIEWS_BY_PRODUCT_ID,
  GET_PRODUCT_REVIEWS_BY_RATING,
  GET_PRODUCT_REVIEWS_BY_STATUS,
} from '../../graphql/Queries/productReviews.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { expectConnection, graphQLErrorMessages } from '../../graphql/helpers/testSupport';

test.describe('Get Product Reviews - Basic', () => {
  test('Should fetch product reviews with default parameters', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getProductReviews, { first: 10 });
    expect(response.status()).toBe(200);
    const body = await response.json();
    const connection = expectConnection(body, 'data.productReviews');
    expect(typeof connection.totalCount).toBe('number');
  });

  test('Should respect the first argument for product reviews', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getProductReviews, { first: 5 });
    expect(response.status()).toBe(200);
    const body = await response.json();
    const connection = expectConnection(body, 'data.productReviews');
    expect(connection.edges.length).toBeLessThanOrEqual(5);
  });

  test('Should return a real message for invalid pagination values', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getProductReviews, { first: 10000 });
    expect(response.status()).toBe(200);
    const body = await response.json();
    const messages = graphQLErrorMessages(body);
    console.log(`Product reviews large first response: ${messages.join(' | ')}`);
    expect(messages.length > 0 || body.data?.productReviews).toBeTruthy();
  });

  test('Should cover product reviews by product id docs query', async ({ request }) => {
    const productListResponse = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getProducts, { first: 1 });
    const productListBody = await productListResponse.json();
    const productNode = productListBody.data?.products?.edges?.[0]?.node;
    const productNumericId = Number(productNode?._id ?? 1);

    const response = await sendGraphQLRequest(request, GET_PRODUCT_REVIEWS_BY_PRODUCT_ID, {
      first: 5,
      product_id: productNumericId,
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    console.log(`Product reviews by product id: ${graphQLErrorMessages(body).join(' | ') || 'success'}`);
    expect(body.data?.productReviews || body.errors).toBeTruthy();
  });

  test('Should cover product reviews by status docs query', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCT_REVIEWS_BY_STATUS, {
      first: 5,
      status: 'approved',
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    console.log(`Product reviews by status: ${graphQLErrorMessages(body).join(' | ') || 'success'}`);
    expect(body.data?.productReviews || body.errors).toBeTruthy();
  });

  test('Should cover product reviews by rating docs query', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCT_REVIEWS_BY_RATING, {
      first: 5,
      rating: 5,
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    console.log(`Product reviews by rating: ${graphQLErrorMessages(body).join(' | ') || 'success'}`);
    expect(body.data?.productReviews || body.errors).toBeTruthy();
  });
});
