import { test, expect, APIRequestContext } from '@playwright/test';
import { getCustomerAuthHeaders } from '../../config/auth';
import {
  CREATE_PRODUCT_REVIEW,
  UPDATE_PRODUCT_REVIEW,
} from '../../graphql/Queries/productReviews.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../graphql/helpers/testSupport';
import { findSaleableSimpleProducts } from '../../../restAPI/rest/helpers/coverage/shopCart.helpers';

test.describe.configure({ timeout: 120_000 });

const MESSAGES = {
  REVIEWS_DISABLED: 'Product reviews are currently disabled',
  NOT_OWNED: 'This review was not written by you and cannot be modified or deleted',
  ITEM_NOT_FOUND: (iri: string) => `Item "${iri}" not found.`,
};

const CUSTOMER_NAME = 'Playwright Tester';

const DELETE_REVIEW = `
  mutation deleteProductReview($input: deleteProductReviewInput!) {
    deleteProductReview(input: $input) {
      productReview {
        id
      }
    }
  }
`;

interface CreatedReview {
  id: number;
  iri: string;
  headers: Record<string, string>;
  title: string;
}

async function runGraphQL(
  request: APIRequestContext,
  query: string,
  variables: Record<string, any>,
  headers: Record<string, string>
): Promise<any> {
  const response = await sendGraphQLRequest(request, query, variables, headers);

  expect(response.status()).toBe(200);

  return response.json();
}

function reviewIri(id: number): string {
  return `/api/shop/reviews/${id}`;
}

async function createOwnReview(request: APIRequestContext): Promise<CreatedReview> {
  const [productId] = await findSaleableSimpleProducts(request, 1);
  const headers = await getCustomerAuthHeaders(request);
  const title = `Review lifecycle ${Date.now()}-${Math.floor(Math.random() * 1_000_000)}`;

  const body = await runGraphQL(
    request,
    CREATE_PRODUCT_REVIEW,
    {
      input: {
        productId,
        title,
        comment: 'Checking the review lifecycle through the graphql api.',
        rating: 5,
        name: 'Ignored Client Name',
        email: 'ignored.client@example.com',
        status: 1,
      },
    },
    headers
  );

  test.skip(
    graphQLErrorMessages(body).includes(MESSAGES.REVIEWS_DISABLED),
    'customer product reviews are disabled in catalog.products.review.customer_review'
  );

  expect(body.errors, graphQLErrorMessages(body).join(' | ')).toBeUndefined();

  const review = body.data.createProductReview.productReview;

  expect(review.title).toBe(title);
  expect(review.rating).toBe(5);
  expect(review.name).toBe(CUSTOMER_NAME);
  expect(review.status).toBe('0');

  const id = Number(review._id);

  expect(id).toBeGreaterThan(0);

  return { id, iri: reviewIri(id), headers, title };
}

test.describe('product review crud graphql api', () => {
  test('should create a pending review in the name of the customer', async ({ request }) => {
    const review = await createOwnReview(request);

    expect(review.iri).toBe(reviewIri(review.id));
  });

  test('should update the review written by the customer', async ({ request }) => {
    const review = await createOwnReview(request);
    const input = {
      id: review.iri,
      title: `${review.title} updated`,
      comment: 'Updated comment from automation.',
      rating: 4,
      status: 1,
    };

    const body = await runGraphQL(request, UPDATE_PRODUCT_REVIEW, { input }, review.headers);

    expect(body.errors, graphQLErrorMessages(body).join(' | ')).toBeUndefined();

    const updated = body.data.updateProductReview.productReview;

    expect(Number(updated._id)).toBe(review.id);
    expect(updated.title).toBe(input.title);
    expect(updated.comment).toBe(input.comment);
    expect(updated.rating).toBe(4);
    expect(updated.name).toBe(CUSTOMER_NAME);
    expect(updated.status).toBe('0');
  });

  test('should refuse to update a review written by another customer', async ({ request }) => {
    const review = await createOwnReview(request);
    const otherHeaders = await getCustomerAuthHeaders(request);

    const body = await runGraphQL(
      request,
      UPDATE_PRODUCT_REVIEW,
      { input: { id: review.iri, title: 'Hijacked title' } },
      otherHeaders
    );

    expect(graphQLErrorMessages(body)).toEqual([MESSAGES.NOT_OWNED]);
    expect(body.data?.updateProductReview ?? null).toBeNull();
  });

  test('should delete the review written by the customer', async ({ request }) => {
    const review = await createOwnReview(request);

    const body = await runGraphQL(request, DELETE_REVIEW, { input: { id: review.iri } }, review.headers);

    expect(body.errors, graphQLErrorMessages(body).join(' | ')).toBeUndefined();
    expect(body.data.deleteProductReview.productReview.id).toBe(review.iri);

    const again = await runGraphQL(request, DELETE_REVIEW, { input: { id: review.iri } }, review.headers);

    expect(graphQLErrorMessages(again)).toEqual([MESSAGES.ITEM_NOT_FOUND(review.iri)]);
  });

  test('should refuse to delete a review written by another customer', async ({ request }) => {
    const review = await createOwnReview(request);
    const otherHeaders = await getCustomerAuthHeaders(request);

    const body = await runGraphQL(request, DELETE_REVIEW, { input: { id: review.iri } }, otherHeaders);

    expect(graphQLErrorMessages(body)).toEqual([MESSAGES.NOT_OWNED]);

    const own = await runGraphQL(request, DELETE_REVIEW, { input: { id: review.iri } }, review.headers);

    expect(own.errors, graphQLErrorMessages(own).join(' | ')).toBeUndefined();
  });

  test('should report a review that does not exist on delete', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);
    const iri = reviewIri(999999999);

    const body = await runGraphQL(request, DELETE_REVIEW, { input: { id: iri } }, headers);

    expect(graphQLErrorMessages(body)).toEqual([MESSAGES.ITEM_NOT_FOUND(iri)]);
    expect(body.data?.deleteProductReview ?? null).toBeNull();
  });
});
