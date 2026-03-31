import { test, expect } from '@playwright/test';
import { getCustomerAuthHeaders } from '../../config/auth';
import { SHOP_DOCS_QUERIES } from '../../graphql/Queries/shopDocs.queries';
import { GET_CUSTOMER_ORDERS_BY_STATUS, GET_CUSTOMER_ORDERS_PAGINATED } from '../../graphql/Queries/customer.queries';
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
});
