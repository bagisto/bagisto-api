import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import { sendGraphQLRequest } from '../../../../graphql/helpers/graphqlClient';
import {
  ADMIN_CUSTOMER_REVIEWS_LIST,
  ADMIN_CUSTOMER_REVIEW_DETAIL,
  ADMIN_CUSTOMER_REVIEW_UPDATE,
} from '../../../../graphql/Queries/admin/customers/customerReviews.queries';
import { CREATE_PRODUCT_REVIEW } from '../../../../graphql/Queries/productReviews.queries';
import { SHOP_ACCOUNT_QUERIES as SHOP } from '../../../../graphql/Queries/coverage/shopAccount.queries';
import { e2eFixtures, missingFixtureReason } from '../../../../../fixtures/e2eFixtures';

test.describe.configure({ timeout: 60_000 });

const REVIEW_NOT_FOUND = 'Review not found.';

const REVIEWS_DISABLED = 'Product reviews are currently disabled';

const MASS_DELETE_REVIEWS = `
  mutation createAdminCustomerReviewMassDelete($indices: Iterable!) {
    createAdminCustomerReviewMassDelete(input: { indices: $indices }) {
      adminCustomerReviewMassDelete {
        deleted
        skipped
        message
      }
    }
  }
`;

const MASS_UPDATE_REVIEW_STATUS = `
  mutation createAdminCustomerReviewMassUpdateStatus($indices: Iterable!, $value: String!) {
    createAdminCustomerReviewMassUpdateStatus(input: { indices: $indices, value: $value }) {
      adminCustomerReviewMassUpdateStatus {
        updated
        value
        message
      }
    }
  }
`;

const DELETE_CUSTOMER = `
  mutation deleteAdminCustomer($id: ID!) {
    deleteAdminCustomer(input: { id: $id }) {
      adminCustomer {
        _id
      }
    }
  }
`;

interface OwnedReview {
  id: number;
  customerId: number;
  productId: number;
  title: string;
}

let review: OwnedReview | undefined;

function uniqueSuffix(): string {
  return `${Date.now()}-${Math.floor(Math.random() * 1_000_000)}`;
}

function reviewIri(id: number): string {
  return `/api/admin/customers/reviews/${id}`;
}

async function gql(request: APIRequestContext, query: string, variables: Record<string, any> = {}): Promise<any> {
  const response = await sendAdminGraphQLRequest(request, query, variables);
  expect(response.status()).toBe(200);

  return response.json();
}

async function shopGql(request: APIRequestContext, query: string, variables: Record<string, any> = {}, token?: string): Promise<any> {
  const response = await sendGraphQLRequest(request, query, variables, token ? { Authorization: `Bearer ${token}` } : {});
  expect(response.status()).toBe(200);

  return response.json();
}

function expectGraphQLError(body: any, field: string, message: string): void {
  expect(body.data?.[field] ?? null).toBeNull();
  expect(body.errors?.[0]?.message).toBe(message);
}

async function registerShopCustomer(request: APIRequestContext): Promise<{ id: number; token: string }> {
  const email = `e2e-gql-review-${uniqueSuffix()}@example.com`;
  const password = 'Passw0rd!123';

  const body = await shopGql(request, SHOP.registerCustomer, {
    input: {
      firstName: 'Review',
      lastName: 'Author',
      email,
      password,
      confirmPassword: password,
      status: '1',
      isVerified: '1',
      isSuspended: '0',
      subscribedToNewsLetter: false,
    },
  });
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

  const customer = body.data.createCustomer.customer;
  let token = customer.token;

  if (!token) {
    const login = await shopGql(request, SHOP.loginCustomer, { email, password });
    token = login.data.createCustomerLogin.customerLogin.token;
  }

  expect(typeof token).toBe('string');

  return { id: Number(customer._id), token };
}

async function writeReview(request: APIRequestContext, productId: number): Promise<OwnedReview | null> {
  const customer = await registerShopCustomer(request);
  const title = `e2e admin review ${uniqueSuffix()}`;

  const body = await shopGql(request, CREATE_PRODUCT_REVIEW, {
    input: {
      productId,
      title,
      comment: 'Written by the admin customer reviews spec.',
      rating: 4,
      name: 'Review Author',
    },
  }, customer.token);

  if (body.errors?.[0]?.message === REVIEWS_DISABLED) {
    await sendAdminGraphQLRequest(request, DELETE_CUSTOMER, { id: `/api/admin/customers/${customer.id}` });

    return null;
  }

  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
  const created = body.data.createProductReview.productReview;
  expect(created.title).toBe(title);

  return { id: Number(created._id), customerId: customer.id, productId, title };
}

async function reviewDetail(request: APIRequestContext, id: number): Promise<any> {
  const body = await gql(request, ADMIN_CUSTOMER_REVIEW_DETAIL, { id: reviewIri(id) });
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

  return body.data.adminCustomerReview;
}

async function customerReviewIds(request: APIRequestContext, filters: Record<string, any>): Promise<number[]> {
  const body = await gql(request, ADMIN_CUSTOMER_REVIEWS_LIST, { first: 10, customer_id: review.customerId, ...filters });
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

  return body.data.adminCustomerReviews.edges.map((edge: any) => edge.node._id);
}

test.describe('admin customer reviews graphql api', () => {
  test.beforeEach(async ({ request }) => {
    review = undefined;

    const order = e2eFixtures().order;
    test.skip(!order, missingFixtureReason('order'));

    const written = await writeReview(request, order!.productId);
    test.skip(!written, 'Customer product reviews are disabled in the admin configuration.');

    review = written;
  });

  test.afterEach(async ({ request }) => {
    if (!review) {
      return;
    }

    await sendAdminGraphQLRequest(request, MASS_DELETE_REVIEWS, { indices: [review.id] });
    await sendAdminGraphQLRequest(request, DELETE_CUSTOMER, { id: `/api/admin/customers/${review.customerId}` });
  });

  test('list filtered by customer returns only the review the test wrote', async ({ request }) => {
    const body = await gql(request, ADMIN_CUSTOMER_REVIEWS_LIST, { first: 10, customer_id: review.customerId });

    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
    const nodes = body.data.adminCustomerReviews.edges.map((edge: any) => edge.node);
    expect(nodes.map((node: any) => node._id)).toEqual([review.id]);
    expect(nodes[0].title).toBe(review.title);
    expect(nodes[0].rating).toBe(4);
    expect(nodes[0].status).toBe('pending');
    expect(nodes[0].product._id).toBe(review.productId);
    expect(nodes[0].customer._id).toBe(review.customerId);
  });

  test('list status and rating filters match the pending four star review', async ({ request }) => {
    expect(await customerReviewIds(request, { status: 'pending' })).toEqual([review.id]);
    expect(await customerReviewIds(request, { status: 'approved' })).toEqual([]);
    expect(await customerReviewIds(request, { rating: 4 })).toEqual([review.id]);
    expect(await customerReviewIds(request, { rating: 5 })).toEqual([]);
  });

  test('detail returns the review', async ({ request }) => {
    const detail = await reviewDetail(request, review.id);

    expect(detail._id).toBe(review.id);
    expect(detail.title).toBe(review.title);
    expect(detail.comment).toBe('Written by the admin customer reviews spec.');
    expect(detail.rating).toBe(4);
    expect(detail.status).toBe('pending');
    expect(detail.product._id).toBe(review.productId);
    expect(detail.customer._id).toBe(review.customerId);
  });

  test('detail of an unknown id reports review not found', async ({ request }) => {
    expectGraphQLError(await gql(request, ADMIN_CUSTOMER_REVIEW_DETAIL, { id: reviewIri(99999999) }), 'adminCustomerReview', REVIEW_NOT_FOUND);
  });

  test('update approves the review', async ({ request }) => {
    const body = await gql(request, ADMIN_CUSTOMER_REVIEW_UPDATE, { id: reviewIri(review.id), status: 'approved' });

    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
    expect(body.data.updateAdminCustomerReview.adminCustomerReview._id).toBe(review.id);
    expect(body.data.updateAdminCustomerReview.adminCustomerReview.status).toBe('approved');
    expect((await reviewDetail(request, review.id)).status).toBe('approved');
  });

  test('update rejects an invalid status and keeps the review pending', async ({ request }) => {
    const body = await gql(request, ADMIN_CUSTOMER_REVIEW_UPDATE, { id: reviewIri(review.id), status: 'completely-bogus' });

    expectGraphQLError(body, 'updateAdminCustomerReview', 'Status must be one of: pending, approved, disapproved.');
    expect((await reviewDetail(request, review.id)).status).toBe('pending');
  });

  test('mass update status disapproves the review', async ({ request }) => {
    const body = await gql(request, MASS_UPDATE_REVIEW_STATUS, { indices: [review.id], value: 'disapproved' });

    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
    const result = body.data.createAdminCustomerReviewMassUpdateStatus.adminCustomerReviewMassUpdateStatus;
    expect(result.updated).toEqual([review.id]);
    expect(result.value).toBe('disapproved');
    expect(result.message).toBe('Review status updated.');
    expect((await reviewDetail(request, review.id)).status).toBe('disapproved');
  });

  test('mass update status rejects empty indices', async ({ request }) => {
    const body = await gql(request, MASS_UPDATE_REVIEW_STATUS, { indices: [], value: 'pending' });

    expectGraphQLError(body, 'createAdminCustomerReviewMassUpdateStatus', 'At least one review id is required.');
  });

  test('mass update status rejects an invalid value and keeps the review pending', async ({ request }) => {
    const body = await gql(request, MASS_UPDATE_REVIEW_STATUS, { indices: [review.id], value: 'bogus' });

    expectGraphQLError(body, 'createAdminCustomerReviewMassUpdateStatus', 'Status value must be one of: pending, approved, disapproved.');
    expect((await reviewDetail(request, review.id)).status).toBe('pending');
  });

  test('mass delete removes the review', async ({ request }) => {
    const body = await gql(request, MASS_DELETE_REVIEWS, { indices: [review.id] });

    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
    const result = body.data.createAdminCustomerReviewMassDelete.adminCustomerReviewMassDelete;
    expect(result.deleted).toEqual([review.id]);
    expect(result.skipped).toEqual([]);
    expect(result.message).toBe('Reviews deleted.');
    expectGraphQLError(await gql(request, ADMIN_CUSTOMER_REVIEW_DETAIL, { id: reviewIri(review.id) }), 'adminCustomerReview', REVIEW_NOT_FOUND);
  });

  test('mass delete rejects empty indices', async ({ request }) => {
    const body = await gql(request, MASS_DELETE_REVIEWS, { indices: [] });

    expectGraphQLError(body, 'createAdminCustomerReviewMassDelete', 'At least one review id is required.');
  });
});
