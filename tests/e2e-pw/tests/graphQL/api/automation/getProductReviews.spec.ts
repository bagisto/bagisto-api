import { test, expect, APIRequestContext } from '@playwright/test';
import { SHOP_DOCS_QUERIES } from '../../graphql/Queries/shopDocs.queries';
import {
  CREATE_PRODUCT_REVIEW,
  GET_PRODUCT_REVIEWS_BY_PRODUCT_ID,
  GET_PRODUCT_REVIEWS_BY_RATING,
  GET_PRODUCT_REVIEWS_BY_STATUS,
} from '../../graphql/Queries/productReviews.queries';
import { ADMIN_CUSTOMER_REVIEW_UPDATE } from '../../graphql/Queries/admin/customers/customerReviews.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { sendAdminGraphQLRequest } from '../../graphql/helpers/adminGraphqlClient';
import { expectConnection } from '../../graphql/helpers/testSupport';
import { getCustomerAuthHeaders } from '../../config/auth';
import { sendAdminRequest } from '../../../restAPI/rest/helpers/adminClient';
import { SHOP_CATALOG } from '../../../restAPI/rest/endpoints/coverage/shopCatalog.endpoints';

test.describe.configure({ timeout: 120_000 });

const PAGE_SIZE_MAXIMUM = 50;

const PRODUCT_REVIEWS_BY_PRODUCT_AND_RATING = `
  query productReviewsByProductAndRating($first: Int, $product_id: Int, $rating: Int) {
    productReviews(first: $first, product_id: $product_id, rating: $rating) {
      edges {
        node {
          _id
          rating
          status
        }
      }
      totalCount
    }
  }
`;

const PRODUCT_REVIEWS_BY_PRODUCT_AND_STATUS = `
  query productReviewsByProductAndStatus($first: Int, $product_id: Int, $status: String) {
    productReviews(first: $first, product_id: $product_id, status: $status) {
      edges {
        node {
          _id
          rating
          status
        }
      }
      totalCount
    }
  }
`;

interface SeededReviews {
  approvedFive: number;
  approvedFour: number;
  pendingFive: number;
}

async function createReview(
  request: APIRequestContext,
  headers: Record<string, string>,
  productId: number,
  rating: number
): Promise<number> {
  const response = await sendGraphQLRequest(
    request,
    CREATE_PRODUCT_REVIEW,
    {
      input: {
        productId,
        title: `e2e review rated ${rating}`,
        comment: `an e2e review rated ${rating} for the filter checks`,
        rating,
        name: 'E2E Reviewer',
      },
    },
    headers
  );
  expect(response.status()).toBe(200);

  const body = await response.json();
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

  return body.data.createProductReview.productReview._id;
}

async function approveReview(request: APIRequestContext, reviewId: number): Promise<void> {
  const response = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_REVIEW_UPDATE, {
    id: `/api/admin/customers/reviews/${reviewId}`,
    status: 'approved',
  });
  expect(response.status()).toBe(200);

  const body = await response.json();
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
  expect(body.data.updateAdminCustomerReview.adminCustomerReview.status).toBe('approved');
}

function reviewIds(connection: any): number[] {
  return connection.edges.map((edge: any) => edge.node._id);
}

test.describe('get product reviews - basic', () => {
  test('should fetch product reviews with default parameters', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getProductReviews, { first: 10 });
    expect(response.status()).toBe(200);
    const body = await response.json();
    const connection = expectConnection(body, 'data.productReviews');
    expect(typeof connection.totalCount).toBe('number');
  });

  test('should respect the first argument for product reviews', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getProductReviews, { first: 5 });
    expect(response.status()).toBe(200);
    const body = await response.json();
    const connection = expectConnection(body, 'data.productReviews');
    expect(connection.edges.length).toBeLessThanOrEqual(5);
  });

  test('should clamp an oversized first argument to the maximum page size', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getProductReviews, { first: 10000 });
    expect(response.status()).toBe(200);
    const body = await response.json();
    const connection = expectConnection(body, 'data.productReviews');
    expect(connection.edges.length).toBeLessThanOrEqual(PAGE_SIZE_MAXIMUM);
    expect(connection.edges.length).toBe(Math.min(connection.totalCount, PAGE_SIZE_MAXIMUM));
  });
});

test.describe('get product reviews - filters', () => {
  let productId = 0;
  let reviews: SeededReviews;

  test.beforeEach(async ({ request }) => {
    productId = 0;

    const created = await sendAdminRequest(request, SHOP_CATALOG.ADMIN_PRODUCTS, {
      method: 'POST',
      data: {
        sku: `e2e-gql-reviews-${Date.now()}-${Math.floor(Math.random() * 1e6)}`,
        attribute_family_id: 1,
        type: 'simple',
      },
    });
    expect(created.status()).toBe(201);
    productId = (await created.json()).id;

    const headers = await getCustomerAuthHeaders(request);

    reviews = {
      approvedFive: await createReview(request, headers, productId, 5),
      approvedFour: await createReview(request, headers, productId, 4),
      pendingFive: await createReview(request, headers, productId, 5),
    };

    await approveReview(request, reviews.approvedFive);
    await approveReview(request, reviews.approvedFour);
  });

  test.afterEach(async ({ request }) => {
    if (productId) {
      await sendAdminRequest(request, SHOP_CATALOG.ADMIN_PRODUCT(productId), { method: 'DELETE' });
    }
  });

  test('should cover product reviews by product id docs query', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCT_REVIEWS_BY_PRODUCT_ID, {
      first: 50,
      product_id: productId,
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    const connection = expectConnection(body, 'data.productReviews');

    expect(connection.totalCount).toBe(2);
    expect(reviewIds(connection)).toEqual([reviews.approvedFive, reviews.approvedFour]);
    expect(connection.edges.every((edge: any) => edge.node.status === 'approved')).toBe(true);
  });

  test('should cover product reviews by status docs query', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCT_REVIEWS_BY_STATUS, {
      first: 50,
      status: 'approved',
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    const connection = expectConnection(body, 'data.productReviews');
    expect(connection.edges.every((edge: any) => edge.node.status === 'approved')).toBe(true);

    const pendingResponse = await sendGraphQLRequest(request, PRODUCT_REVIEWS_BY_PRODUCT_AND_STATUS, {
      first: 50,
      product_id: productId,
      status: 'pending',
    });
    expect(pendingResponse.status()).toBe(200);
    const pendingBody = await pendingResponse.json();
    const pendingConnection = expectConnection(pendingBody, 'data.productReviews');

    expect(reviewIds(pendingConnection)).toEqual([reviews.approvedFive, reviews.approvedFour]);
    expect(reviewIds(pendingConnection)).not.toContain(reviews.pendingFive);
  });

  test('should cover product reviews by rating docs query', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCT_REVIEWS_BY_RATING, {
      first: 50,
      rating: 5,
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    const connection = expectConnection(body, 'data.productReviews');
    expect(
      connection.edges.every((edge: any) => edge.node.rating === 5 && edge.node.status === 'approved')
    ).toBe(true);

    const scopedResponse = await sendGraphQLRequest(request, PRODUCT_REVIEWS_BY_PRODUCT_AND_RATING, {
      first: 50,
      product_id: productId,
      rating: 5,
    });
    expect(scopedResponse.status()).toBe(200);
    const scopedBody = await scopedResponse.json();
    const scoped = expectConnection(scopedBody, 'data.productReviews');

    expect(scoped.totalCount).toBe(1);
    expect(reviewIds(scoped)).toEqual([reviews.approvedFive]);
  });
});
