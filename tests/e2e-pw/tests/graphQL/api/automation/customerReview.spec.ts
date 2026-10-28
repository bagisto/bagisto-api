import { test, expect, APIRequestContext } from '@playwright/test';
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
import { CREATE_PRODUCT_REVIEW } from '../../graphql/Queries/productReviews.queries';
import { SHOP_DOCS_QUERIES } from '../../graphql/Queries/shopDocs.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { expectConnection, graphQLErrorMessages } from '../../graphql/helpers/testSupport';
import { e2eFixtures } from '../../../fixtures/e2eFixtures';

const UNAUTHENTICATED = 'Unauthenticated. Please login to perform this action';
const PENDING = 'pending';

async function gql(
  request: APIRequestContext,
  query: string,
  variables: Record<string, any>,
  headers: Record<string, string> = {}
): Promise<any> {
  const response = await sendGraphQLRequest(request, query, variables, headers);
  expect(response.status()).toBe(200);

  return response.json();
}

async function reviewableProductId(request: APIRequestContext): Promise<number> {
  const fixtureProductId = e2eFixtures().order?.productId;

  if (fixtureProductId) {
    return fixtureProductId;
  }

  const body = await gql(request, SHOP_DOCS_QUERIES.getProducts, { first: 1 });
  const numericId = Number(String(body.data?.products?.edges?.[0]?.node?.id ?? '').split('/').pop());
  expect(numericId, 'the store must list at least one product').toBeGreaterThan(0);

  return numericId;
}

async function writeReview(
  request: APIRequestContext,
  headers: Record<string, string>,
  productId: number,
  rating: number
): Promise<{ id: number; title: string }> {
  const title = `Playwright review ${rating} ${Date.now()}-${Math.floor(Math.random() * 100000)}`;
  const body = await gql(
    request,
    CREATE_PRODUCT_REVIEW,
    {
      input: {
        productId,
        title,
        comment: 'Written by the customer review graphql spec.',
        rating,
        name: 'Playwright Reviewer',
      },
    },
    headers
  );
  expect(body.errors, graphQLErrorMessages(body).join(' | ')).toBeUndefined();

  const review = body.data.createProductReview.productReview;
  expect(review.title).toBe(title);

  return { id: review._id, title };
}

function nodeIds(connection: any): number[] {
  return connection.edges.map((edge: any) => edge.node._id);
}

test.describe('customer review graphql api', () => {
  let headers: Record<string, string>;
  let productId: number;

  test.beforeEach(async ({ request }) => {
    headers = await getCustomerAuthHeaders(request);
    productId = await reviewableProductId(request);
  });

  test('should list the reviews the customer wrote', async ({ request }) => {
    const review = await writeReview(request, headers, productId, 5);

    const connection = expectConnection(
      await gql(request, GET_CUSTOMER_REVIEWS, { first: 5 }, headers),
      'data.customerReviews'
    );

    expect(connection.totalCount).toBe(1);
    const node = connection.edges[0].node;
    expect(node._id).toBe(review.id);
    expect(node.title).toBe(review.title);
    expect(node.rating).toBe(5);
    expect(node.status).toBe(PENDING);
    expect(node.product._id).toBe(productId);
  });

  test('should filter the customer reviews by status', async ({ request }) => {
    const review = await writeReview(request, headers, productId, 4);

    const pending = expectConnection(
      await gql(request, GET_CUSTOMER_REVIEWS_BY_STATUS, { first: 5, status: PENDING }, headers),
      'data.customerReviews'
    );
    expect(nodeIds(pending)).toEqual([review.id]);

    const approved = expectConnection(
      await gql(request, GET_CUSTOMER_REVIEWS_BY_STATUS, { first: 5, status: 'approved' }, headers),
      'data.customerReviews'
    );
    expect(approved.totalCount).toBe(0);
  });

  test('should filter the customer reviews by rating', async ({ request }) => {
    const five = await writeReview(request, headers, productId, 5);
    await writeReview(request, headers, productId, 3);

    const connection = expectConnection(
      await gql(request, GET_CUSTOMER_REVIEWS_BY_RATING, { first: 5, rating: 5 }, headers),
      'data.customerReviews'
    );

    expect(nodeIds(connection)).toEqual([five.id]);
    expect(connection.edges[0].node.rating).toBe(5);
  });

  test('should combine the status and rating filters', async ({ request }) => {
    await writeReview(request, headers, productId, 5);
    const three = await writeReview(request, headers, productId, 3);

    const pendingThree = expectConnection(
      await gql(request, GET_CUSTOMER_REVIEWS_COMBINED_FILTERS, { first: 5, status: PENDING, rating: 3 }, headers),
      'data.customerReviews'
    );
    expect(nodeIds(pendingThree)).toEqual([three.id]);

    const approvedFive = expectConnection(
      await gql(request, GET_CUSTOMER_REVIEWS_COMBINED_FILTERS, { first: 5, status: 'approved', rating: 5 }, headers),
      'data.customerReviews'
    );
    expect(approvedFive.totalCount).toBe(0);
  });

  test('should page through the customer reviews', async ({ request }) => {
    const first = await writeReview(request, headers, productId, 5);
    const second = await writeReview(request, headers, productId, 4);

    const firstPage = expectConnection(
      await gql(request, GET_CUSTOMER_REVIEWS_PAGINATED, { first: 1, after: null }, headers),
      'data.customerReviews'
    );
    expect(firstPage.totalCount).toBe(2);
    expect(firstPage.edges).toHaveLength(1);
    expect(firstPage.pageInfo.hasNextPage).toBe(true);

    const secondPage = expectConnection(
      await gql(request, GET_CUSTOMER_REVIEWS_PAGINATED, { first: 1, after: firstPage.pageInfo.endCursor }, headers),
      'data.customerReviews'
    );
    expect(secondPage.edges).toHaveLength(1);
    expect(secondPage.pageInfo.hasNextPage).toBe(false);

    expect([firstPage.edges[0].node._id, secondPage.edges[0].node._id]).toEqual([second.id, first.id]);
  });

  test('should return a review the customer wrote by id', async ({ request }) => {
    const review = await writeReview(request, headers, productId, 4);

    const body = await gql(request, GET_CUSTOMER_REVIEW, { id: String(review.id) }, headers);

    expect(body.errors, graphQLErrorMessages(body).join(' | ')).toBeUndefined();
    expect(body.data.customerReview._id).toBe(review.id);
    expect(body.data.customerReview.title).toBe(review.title);
    expect(body.data.customerReview.product._id).toBe(productId);
  });

  test('should refuse a review another customer wrote', async ({ request }) => {
    const review = await writeReview(request, headers, productId, 4);
    const otherHeaders = await getCustomerAuthHeaders(request);
    const id = String(review.id);

    const body = await gql(request, GET_CUSTOMER_REVIEW, { id }, otherHeaders);

    expect(body.data?.customerReview ?? null).toBeNull();
    expect(graphQLErrorMessages(body)).toEqual([`Customer review with ID "${id}" not found`]);
  });

  test('should refuse the review list without a customer token', async ({ request }) => {
    const body = await gql(request, GET_CUSTOMER_REVIEWS, { first: 5 });

    expect(body.data?.customerReviews ?? null).toBeNull();
    expect(graphQLErrorMessages(body)).toContain(UNAUTHENTICATED);
  });

  test('should cover customer review introspection query', async ({ request }) => {
    const body = await gql(request, GET_CUSTOMER_REVIEW_INTROSPECTION, {});

    expect(body.errors).toBeUndefined();
    const fieldNames = body.data.__type.fields.map((field: any) => field.name);
    expect(fieldNames).toEqual(expect.arrayContaining(['_id', 'title', 'comment', 'rating', 'status', 'product']));
  });
});
