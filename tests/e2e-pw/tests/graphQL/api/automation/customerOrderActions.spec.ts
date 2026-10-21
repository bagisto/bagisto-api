import { test, expect, APIRequestContext } from '@playwright/test';
import { getCustomerAuthHeaders } from '../../config/auth';
import {
  CREATE_CANCEL_ORDER,
  CREATE_REORDER_ORDER,
} from '../../graphql/Queries/customer.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../graphql/helpers/testSupport';
import { e2eFixtures, missingFixtureReason } from '../../../fixtures/e2eFixtures';

const UNAUTHENTICATED = 'Unauthenticated. Please login to perform this action';
const CANCEL_ORDER_ID_REQUIRED = 'Order ID is required to cancel an order';
const REORDER_ORDER_ID_REQUIRED = 'Order ID is required to reorder';
const NOT_OWNED = (id: number) => `Order with ID "${id}" not found or does not belong to this customer`;
const MISSING_ORDER_ID = 999999999;

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

test.describe('customer order actions graphql api tests', () => {
  test('should reject cancel order without authentication', async ({ request }) => {
    const body = await gql(request, CREATE_CANCEL_ORDER, { input: { orderId: MISSING_ORDER_ID } });

    expect(body.data?.createCancelOrder ?? null).toBeNull();
    expect(graphQLErrorMessages(body)).toEqual([UNAUTHENTICATED]);
  });

  test('should reject reorder without authentication', async ({ request }) => {
    const body = await gql(request, CREATE_REORDER_ORDER, { input: { orderId: MISSING_ORDER_ID } });

    expect(body.data?.createReorderOrder ?? null).toBeNull();
    expect(graphQLErrorMessages(body)).toEqual([UNAUTHENTICATED]);
  });

  test('should report a non existent order when cancelling it', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);
    const body = await gql(request, CREATE_CANCEL_ORDER, { input: { orderId: MISSING_ORDER_ID } }, headers);

    expect(body.data?.createCancelOrder ?? null).toBeNull();
    expect(graphQLErrorMessages(body)).toEqual([NOT_OWNED(MISSING_ORDER_ID)]);
  });

  test('should report a non existent order when reordering it', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);
    const body = await gql(request, CREATE_REORDER_ORDER, { input: { orderId: MISSING_ORDER_ID } }, headers);

    expect(body.data?.createReorderOrder ?? null).toBeNull();
    expect(graphQLErrorMessages(body)).toEqual([NOT_OWNED(MISSING_ORDER_ID)]);
  });

  test('should require an order id to cancel an order', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);
    const body = await gql(request, CREATE_CANCEL_ORDER, { input: {} }, headers);

    expect(body.data?.createCancelOrder ?? null).toBeNull();
    expect(graphQLErrorMessages(body)).toEqual([CANCEL_ORDER_ID_REQUIRED]);
  });

  test('should require an order id to reorder', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);
    const body = await gql(request, CREATE_REORDER_ORDER, { input: {} }, headers);

    expect(body.data?.createReorderOrder ?? null).toBeNull();
    expect(graphQLErrorMessages(body)).toEqual([REORDER_ORDER_ID_REQUIRED]);
  });

  test('should refuse to cancel an order another customer placed', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));

    const headers = await getCustomerAuthHeaders(request);
    const body = await gql(request, CREATE_CANCEL_ORDER, { input: { orderId: fx.order!.id } }, headers);

    expect(body.data?.createCancelOrder ?? null).toBeNull();
    expect(graphQLErrorMessages(body)).toEqual([NOT_OWNED(fx.order!.id)]);
  });

  test('should refuse to reorder an order another customer placed', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));

    const headers = await getCustomerAuthHeaders(request);
    const body = await gql(request, CREATE_REORDER_ORDER, { input: { orderId: fx.order!.id } }, headers);

    expect(body.data?.createReorderOrder ?? null).toBeNull();
    expect(graphQLErrorMessages(body)).toEqual([NOT_OWNED(fx.order!.id)]);
  });
});
