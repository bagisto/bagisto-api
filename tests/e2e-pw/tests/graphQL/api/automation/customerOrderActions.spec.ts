import { test, expect } from '@playwright/test';
import { getCustomerAuthHeaders } from '../../config/auth';
import {
  CREATE_CANCEL_ORDER,
  CREATE_REORDER_ORDER,
} from '../../graphql/Queries/customer.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../graphql/helpers/testSupport';

test.describe('Customer Order Actions GraphQL API Tests', () => {
  test('Should reject cancel-order without authentication', async ({ request }) => {
    const response = await sendGraphQLRequest(request, CREATE_CANCEL_ORDER, {
      input: { orderId: 999999 },
    });
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log(`Unauthenticated cancel response: ${JSON.stringify(body)}`);
    expect(
      graphQLErrorMessages(body).length > 0 ||
      body.data?.createCancelOrder?.cancelOrder?.success === false ||
      body.data?.createCancelOrder === null
    ).toBeTruthy();
  });

  test('Should reject reorder without authentication', async ({ request }) => {
    const response = await sendGraphQLRequest(request, CREATE_REORDER_ORDER, {
      input: { orderId: 999999 },
    });
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log(`Unauthenticated reorder response: ${JSON.stringify(body)}`);
    expect(
      graphQLErrorMessages(body).length > 0 ||
      body.data?.createReorderOrder?.reorderOrder?.success === false ||
      body.data?.createReorderOrder === null
    ).toBeTruthy();
  });

  test('Should return failure or error when cancelling a non-existent order', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};

    const response = await sendGraphQLRequest(
      request,
      CREATE_CANCEL_ORDER,
      { input: { orderId: 999999 } },
      headers
    );
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log(`Cancel non-existent order response: ${JSON.stringify(body)}`);
    const payload = body.data?.createCancelOrder?.cancelOrder;
    expect(
      graphQLErrorMessages(body).length > 0 ||
      payload?.success === false ||
      body.data?.createCancelOrder === null
    ).toBeTruthy();
  });

  test('Should return failure or error when reordering a non-existent order', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};

    const response = await sendGraphQLRequest(
      request,
      CREATE_REORDER_ORDER,
      { input: { orderId: 999999 } },
      headers
    );
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log(`Reorder non-existent order response: ${JSON.stringify(body)}`);
    const payload = body.data?.createReorderOrder?.reorderOrder;
    expect(
      graphQLErrorMessages(body).length > 0 ||
      payload?.success === false ||
      body.data?.createReorderOrder === null
    ).toBeTruthy();
  });

  test('Should reject cancel with missing orderId via GraphQL schema validation', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};

    const response = await sendGraphQLRequest(
      request,
      CREATE_CANCEL_ORDER,
      { input: {} },
      headers
    );
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log(`Cancel missing orderId response: ${JSON.stringify(body)}`);
    // GraphQL may either schema-reject or the processor may reject with success:false
    expect(
      graphQLErrorMessages(body).length > 0 ||
      body.data?.createCancelOrder?.cancelOrder?.success === false
    ).toBeTruthy();
  });

  test('Should reject reorder with missing orderId via GraphQL schema validation', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};

    const response = await sendGraphQLRequest(
      request,
      CREATE_REORDER_ORDER,
      { input: {} },
      headers
    );
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log(`Reorder missing orderId response: ${JSON.stringify(body)}`);
    expect(
      graphQLErrorMessages(body).length > 0 ||
      body.data?.createReorderOrder?.reorderOrder?.success === false
    ).toBeTruthy();
  });

  test('Should keep cancelling another customer\'s order behind an error or failure', async ({ request }) => {
    // Authenticate as a fresh customer who definitely doesn't own order #1
    const headers = (await getCustomerAuthHeaders(request)) ?? {};

    const response = await sendGraphQLRequest(
      request,
      CREATE_CANCEL_ORDER,
      { input: { orderId: 1 } },
      headers
    );
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log(`Cross-customer cancel response: ${JSON.stringify(body)}`);
    const payload = body.data?.createCancelOrder?.cancelOrder;
    // Either error, success:false, or null payload — must NOT silently succeed
    expect(payload?.success === true).toBeFalsy();
  });
});
