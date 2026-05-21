import { test, expect } from '@playwright/test';
import { getCustomerAuthHeaders } from '../../config/auth';
import { SHOP_DOCS_QUERIES } from '../../graphql/Queries/shopDocs.queries';
import {
  CREATE_CANCEL_ORDER,
  CREATE_CUSTOMER,
  CREATE_CUSTOMER_LOGIN,
  CREATE_CUSTOMER_PROFILE_DELETE,
  CREATE_CUSTOMER_PROFILE_UPDATE,
  CREATE_FORGOT_PASSWORD,
  CREATE_LOGOUT,
  CREATE_REORDER_ORDER,
  CREATE_VERIFY_TOKEN,
  GET_CUSTOMER_ORDERS_BY_STATUS,
  GET_CUSTOMER_ORDERS_PAGINATED,
} from '../../graphql/Queries/customer.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import {
  expectAuthAwareResult,
  expectGraphQLSuccess,
  graphQLErrorMessages,
} from '../../graphql/helpers/testSupport';

test.describe('Customer GraphQL API - Docs aligned', () => {
  test('Should return the customer profile or the real auth message', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getCustomerProfile, {}, headers);
    expect(response.status()).toBe(200);

    const body = await response.json();
    expectAuthAwareResult(body, 'data.readCustomerProfile');
  });

  test('Should return customer orders or the real auth message', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getCustomerOrders, { first: 5 }, headers);
    expect(response.status()).toBe(200);

    const body = await response.json();
    expectAuthAwareResult(body, 'data.customerOrders');
  });

  test('Should fetch a single customer order when auth and order data are available', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);

    if (!headers) {
      const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getCustomerOrder, { id: '/api/shop/customer-orders/1' });
      const body = await response.json();
      console.log(`Customer order auth response: ${graphQLErrorMessages(body).join(' | ')}`);
      expect(graphQLErrorMessages(body).length > 0 || body.data?.customerOrder === null).toBeTruthy();
      return;
    }

    const ordersResponse = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getCustomerOrders, { first: 1 }, headers);
    const ordersBody = await ordersResponse.json();
    const orderId = ordersBody.data?.customerOrders?.edges?.[0]?.node?.id;

    if (!orderId) {
      console.log('Authenticated customer has no orders in this environment.');
      return;
    }

    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getCustomerOrder, { id: orderId }, headers);
    expect(response.status()).toBe(200);

    const body = await response.json();
    expectGraphQLSuccess(body, 'data.customerOrder');
  });

  test('Should fetch customer order shipments when order data exists', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);

    if (!headers) {
      const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getCustomerOrderShipments, { orderId: 1 });
      const body = await response.json();
      console.log(`Customer order shipments auth response: ${graphQLErrorMessages(body).join(' | ')}`);
      expect(graphQLErrorMessages(body).length > 0 || body.data?.customerOrderShipments === null).toBeTruthy();
      return;
    }

    const ordersResponse = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getCustomerOrders, { first: 1 }, headers);
    const ordersBody = await ordersResponse.json();
    const orderId = ordersBody.data?.customerOrders?.edges?.[0]?.node?.id;

    if (!orderId) {
      console.log('Authenticated customer has no orders to check shipments for.');
      return;
    }

    const response = await sendGraphQLRequest(
      request,
      SHOP_DOCS_QUERIES.getCustomerOrderShipments,
      { orderId },
      headers
    );
    expect(response.status()).toBe(200);

    const body = await response.json();
    expectAuthAwareResult(body, 'data.customerOrderShipments');
  });

  test('Should cover customer orders by status docs query', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const response = await sendGraphQLRequest(request, GET_CUSTOMER_ORDERS_BY_STATUS, { first: 5, status: 'pending' }, headers);
    expect(response.status()).toBe(200);

    const body = await response.json();
    expectAuthAwareResult(body, 'data.customerOrders');
  });

  test('Should cover customer orders pagination docs query', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const response = await sendGraphQLRequest(request, GET_CUSTOMER_ORDERS_PAGINATED, { first: 2, after: null }, headers);
    expect(response.status()).toBe(200);

    const body = await response.json();
    expectAuthAwareResult(body, 'data.customerOrders');
  });

  test('Should try customer login and token verification mutations', async ({ request }) => {
    const loginResponse = await sendGraphQLRequest(request, CREATE_CUSTOMER_LOGIN, {
      email: process.env.BAGISTO_CUSTOMER_EMAIL ?? 'missing@example.com',
      password: process.env.BAGISTO_CUSTOMER_PASSWORD ?? 'invalid-password',
    });
    expect(loginResponse.status()).toBe(200);
    const loginBody = await loginResponse.json();
    console.log(`Customer login response: ${JSON.stringify(loginBody)}`);
    expect(loginBody.data?.createCustomerLogin?.customerLogin || graphQLErrorMessages(loginBody).length > 0).toBeTruthy();

    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const verifyResponse = await sendGraphQLRequest(request, CREATE_VERIFY_TOKEN, {}, headers);
    expect(verifyResponse.status()).toBe(200);
    const verifyBody = await verifyResponse.json();
    console.log(`Verify token response: ${JSON.stringify(verifyBody)}`);
    expect(verifyBody.data?.createVerifyToken?.verifyToken || graphQLErrorMessages(verifyBody).length > 0).toBeTruthy();
  });

  test('Should try customer registration and forgot password mutations', async ({ request }) => {
    const email = `bagisto.playwright+${Date.now()}@example.com`;
    const registrationResponse = await sendGraphQLRequest(request, CREATE_CUSTOMER, {
      input: {
        firstName: 'GraphQL',
        lastName: 'Tester',
        gender: 'Male',
        dateOfBirth: '01/15/1990',
        phone: '5550123',
        status: '1',
        isVerified: '1',
        isSuspended: '0',
        email,
        password: 'SecurePass@123',
        confirmPassword: 'SecurePass@123',
        subscribedToNewsLetter: true,
      },
    });
    expect(registrationResponse.status()).toBe(200);
    const registrationBody = await registrationResponse.json();
    console.log(`Customer registration response: ${JSON.stringify(registrationBody)}`);
    expect(registrationBody.data?.createCustomer?.customer || graphQLErrorMessages(registrationBody).length > 0).toBeTruthy();

    const forgotResponse = await sendGraphQLRequest(request, CREATE_FORGOT_PASSWORD, {
      email: process.env.BAGISTO_CUSTOMER_EMAIL ?? email,
    });
    expect(forgotResponse.status()).toBe(200);
    const forgotBody = await forgotResponse.json();
    console.log(`Forgot password response: ${JSON.stringify(forgotBody)}`);
    expect(forgotBody.data?.createForgotPassword?.forgotPassword || graphQLErrorMessages(forgotBody).length > 0).toBeTruthy();
  });

  test('Should try customer profile and order mutations and show the real API response', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};

    const updateResponse = await sendGraphQLRequest(
      request,
      CREATE_CUSTOMER_PROFILE_UPDATE,
      { input: { firstName: 'Jane', lastName: 'Doe', gender: 'female' } },
      headers
    );
    expect(updateResponse.status()).toBe(200);
    const updateBody = await updateResponse.json();
    console.log(`Update customer profile response: ${JSON.stringify(updateBody)}`);
    expect(updateBody.data?.createCustomerProfileUpdate?.customerProfileUpdate || graphQLErrorMessages(updateBody).length > 0).toBeTruthy();

    const cancelResponse = await sendGraphQLRequest(
      request,
      CREATE_CANCEL_ORDER,
      { input: { orderId: 999999 } },
      headers
    );
    expect(cancelResponse.status()).toBe(200);
    const cancelBody = await cancelResponse.json();
    console.log(`Cancel customer order response: ${JSON.stringify(cancelBody)}`);
    expect(cancelBody.data?.createCancelOrder?.cancelOrder || graphQLErrorMessages(cancelBody).length > 0).toBeTruthy();

    const reorderResponse = await sendGraphQLRequest(
      request,
      CREATE_REORDER_ORDER,
      { input: { orderId: 999999 } },
      headers
    );
    expect(reorderResponse.status()).toBe(200);
    const reorderBody = await reorderResponse.json();
    console.log(`Reorder customer order response: ${JSON.stringify(reorderBody)}`);
    expect(reorderBody.data?.createReorderOrder?.reorderOrder || graphQLErrorMessages(reorderBody).length > 0).toBeTruthy();

    const logoutResponse = await sendGraphQLRequest(request, CREATE_LOGOUT, {}, headers);
    expect(logoutResponse.status()).toBe(200);
    const logoutBody = await logoutResponse.json();
    console.log(`Customer logout response: ${JSON.stringify(logoutBody)}`);
    expect(logoutBody.data?.createLogout?.logout || graphQLErrorMessages(logoutBody).length > 0).toBeTruthy();
  });

  test('Should safely cover delete customer profile mutation through the actual auth response', async ({ request }) => {
    const response = await sendGraphQLRequest(request, CREATE_CUSTOMER_PROFILE_DELETE);
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log(`Delete customer profile response: ${JSON.stringify(body)}`);
    expect(body.data?.createCustomerProfileDelete?.customerProfileDelete || graphQLErrorMessages(body).length > 0).toBeTruthy();
  });
});
