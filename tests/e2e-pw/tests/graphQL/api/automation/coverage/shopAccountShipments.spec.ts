import { test, expect, APIRequestContext } from '@playwright/test';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../../graphql/helpers/testSupport';
import { SHOP_ACCOUNT_QUERIES as Q } from '../../../graphql/Queries/coverage/shopAccount.queries';
import { SHOP_ACCOUNT_MESSAGES as MSG } from '../../../../restAPI/rest/endpoints/coverage/shopAccount.endpoints';
import { e2eFixtures, missingFixtureReason, E2eCustomerFixture } from '../../../../fixtures/e2eFixtures';

function bearer(token: string): Record<string, string> {
  return { Authorization: `Bearer ${token}` };
}

async function gql(request: APIRequestContext, query: string, variables: Record<string, any> = {}, token?: string): Promise<any> {
  const response = await sendGraphQLRequest(request, query, variables, token ? bearer(token) : {});
  expect(response.status()).toBe(200);

  return response.json();
}

async function loginToken(request: APIRequestContext, customer: E2eCustomerFixture): Promise<string> {
  const body = await gql(request, Q.loginCustomer, { email: customer.email, password: customer.password });
  expect(body.errors).toBeUndefined();

  const login = body.data.createCustomerLogin.customerLogin;
  expect(login.success).toBe(true);

  return login.token;
}

function nodeIds(connection: any): number[] {
  return connection.edges.map((edge: any) => Number(edge.node._id));
}

test.describe('shop account order shipment items graphql api', () => {
  test('should refuse the shipment item list without a customer token', async ({ request }) => {
    const body = await gql(request, Q.shipmentItems, { first: 10 });

    expect(body.data?.customerOrderShipmentItems ?? null).toBeNull();
    expect(graphQLErrorMessages(body)).toContain(MSG.UNAUTHENTICATED);
  });

  test('should list the shipped items of the customer', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const token = await loginToken(request, fx.customer!);
    const body = await gql(request, Q.shipmentItems, { first: 50 }, token);

    expect(body.errors).toBeUndefined();
    const connection = body.data.customerOrderShipmentItems;
    expect(Array.isArray(connection.edges)).toBe(true);

    const ids = nodeIds(connection);
    for (const itemId of fx.order!.shipmentItemIds) {
      expect(ids).toContain(itemId);
    }

    for (const edge of connection.edges) {
      expect(typeof edge.node.sku).toBe('string');
      expect(edge.node.qty).toBeGreaterThan(0);
    }
  });

  test('should hide the shipped items from another customer', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.otherCustomer, missingFixtureReason('otherCustomer'));

    const token = await loginToken(request, fx.otherCustomer!);
    const body = await gql(request, Q.shipmentItems, { first: 50 }, token);

    expect(body.errors).toBeUndefined();
    const ids = nodeIds(body.data.customerOrderShipmentItems);
    for (const itemId of fx.order!.shipmentItemIds) {
      expect(ids).not.toContain(itemId);
    }
  });

  test('should return a shipped item by id with its shipment', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const token = await loginToken(request, fx.customer!);
    const itemId = fx.order!.shipmentItemIds[0];
    const body = await gql(request, Q.shipmentItem, { id: `/api/shop/customer-order-shipment-items/${itemId}` }, token);

    expect(body.errors).toBeUndefined();
    const item = body.data.customerOrderShipmentItem;
    expect(item._id).toBe(itemId);
    expect(typeof item.sku).toBe('string');
    expect(item.qty).toBeGreaterThan(0);
    expect(item.shipment._id).toBe(fx.order!.shipmentId);
  });

  test('should refuse a shipped item of another customer', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.otherCustomer, missingFixtureReason('otherCustomer'));

    const token = await loginToken(request, fx.otherCustomer!);
    const body = await gql(request, Q.shipmentItem, { id: String(fx.order!.shipmentItemIds[0]) }, token);

    expect(body.data?.customerOrderShipmentItem ?? null).toBeNull();
    expect(graphQLErrorMessages(body).length).toBeGreaterThan(0);
  });

  test('should refuse a shipped item without a customer token', async ({ request }) => {
    const body = await gql(request, Q.shipmentItem, { id: '1' });

    expect(body.data?.customerOrderShipmentItem ?? null).toBeNull();
    expect(graphQLErrorMessages(body)).toContain(MSG.UNAUTHENTICATED);
  });
});
