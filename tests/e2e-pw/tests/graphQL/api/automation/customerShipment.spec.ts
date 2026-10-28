import { test, expect, APIRequestContext } from '@playwright/test';
import { getCustomerAuthHeaders } from '../../config/auth';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { expectConnection, graphQLErrorMessages } from '../../graphql/helpers/testSupport';
import { e2eFixtures, missingFixtureReason, E2eCustomerFixture } from '../../../fixtures/e2eFixtures';

const UNAUTHENTICATED = 'Unauthenticated. Please login to perform this action';
const SHIPMENT_NOT_FOUND = (id: number | string) => `Shipment with ID "${id}" not found or does not belong to this customer`;

const LOGIN = `
  mutation createCustomerLogin($email: String!, $password: String!) {
    createCustomerLogin(input: { email: $email, password: $password }) {
      customerLogin {
        token
        success
      }
    }
  }
`;

const ORDER_SHIPMENTS = `
  query getOrderShipments($orderId: Int, $first: Int) {
    customerOrderShipments(orderId: $orderId, first: $first) {
      edges {
        node {
          id
          _id
          status
          totalQty
        }
      }
      totalCount
    }
  }
`;

const ORDER_SHIPMENT = `
  query getOrderShipment($id: ID!) {
    customerOrderShipment(id: $id) {
      _id
      totalQty
    }
  }
`;

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

async function customerHeaders(request: APIRequestContext, customer: E2eCustomerFixture): Promise<Record<string, string>> {
  const body = await gql(request, LOGIN, { email: customer.email, password: customer.password });
  expect(body.errors).toBeUndefined();
  expect(body.data.createCustomerLogin.customerLogin.success).toBe(true);

  return { Authorization: `Bearer ${body.data.createCustomerLogin.customerLogin.token}` };
}

test.describe('customer shipment graphql api', () => {
  test('should list the shipment of the customer order', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const headers = await customerHeaders(request, fx.customer!);
    const connection = expectConnection(
      await gql(request, ORDER_SHIPMENTS, { orderId: fx.order!.id, first: 10 }, headers),
      'data.customerOrderShipments'
    );

    expect(connection.totalCount).toBe(1);
    expect(connection.edges[0].node._id).toBe(fx.order!.shipmentId);
    expect(connection.edges[0].node.totalQty).toBeGreaterThan(0);
  });

  test('should list no shipments for an order the customer does not own', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));

    const headers = await getCustomerAuthHeaders(request);
    const connection = expectConnection(
      await gql(request, ORDER_SHIPMENTS, { orderId: fx.order!.id, first: 10 }, headers),
      'data.customerOrderShipments'
    );

    expect(connection.totalCount).toBe(0);
    expect(connection.edges).toEqual([]);
  });

  test('should return a shipment of the customer by id', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));
    test.skip(!fx.customer, missingFixtureReason('customer'));

    const headers = await customerHeaders(request, fx.customer!);
    const body = await gql(request, ORDER_SHIPMENT, { id: String(fx.order!.shipmentId) }, headers);

    expect(body.errors, graphQLErrorMessages(body).join(' | ')).toBeUndefined();
    expect(body.data.customerOrderShipment._id).toBe(fx.order!.shipmentId);
  });

  test('should refuse a shipment of another customer', async ({ request }) => {
    const fx = e2eFixtures();
    test.skip(!fx.order, missingFixtureReason('order'));

    const headers = await getCustomerAuthHeaders(request);
    const id = String(fx.order!.shipmentId);
    const body = await gql(request, ORDER_SHIPMENT, { id }, headers);

    expect(body.data?.customerOrderShipment ?? null).toBeNull();
    expect(graphQLErrorMessages(body)).toEqual([SHIPMENT_NOT_FOUND(id)]);
  });

  test('should refuse the shipment list without a customer token', async ({ request }) => {
    const body = await gql(request, ORDER_SHIPMENTS, { orderId: 1, first: 10 });

    expect(body.data?.customerOrderShipments ?? null).toBeNull();
    expect(graphQLErrorMessages(body)).toContain(UNAUTHENTICATED);
  });
});
