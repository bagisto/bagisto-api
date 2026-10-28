import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import { sendGraphQLRequest } from '../../../../graphql/helpers/graphqlClient';
import { ADMIN_RETURNABLE_ORDERS_QUERY } from '../../../../graphql/Queries/admin/sales/returnableOrders.queries';
import { env } from '../../../../config/env';

test.describe.configure({ timeout: 60_000 });

async function queryReturnableOrders(request: APIRequestContext, variables: Record<string, any>) {
  const resp = await sendAdminGraphQLRequest(request, ADMIN_RETURNABLE_ORDERS_QUERY, variables);
  expect(resp.status()).toBe(200);
  const body = await resp.json();
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
  const connection = body.data?.adminReturnableOrders;
  expect(Array.isArray(connection?.edges)).toBe(true);
  return connection;
}

test.describe('admin rma returnable orders graphql', () => {
  test('should refuse the order picker without an admin token', async ({ request }) => {
    const resp = await sendGraphQLRequest(request, ADMIN_RETURNABLE_ORDERS_QUERY, { first: 1 }, {}, env.adminGraphqlEndpoint);
    expect(resp.status()).toBe(401);
    expect((await resp.json())?.message).toBe('Unauthenticated.');
  });

  test('should return a cursor connection of orders that still have a quantity to return', async ({ request }) => {
    const connection = await queryReturnableOrders(request, { first: 5 });

    expect(typeof connection.totalCount).toBe('number');
    expect(typeof connection.pageInfo.hasNextPage).toBe('boolean');
    expect(connection.edges.length).toBeLessThanOrEqual(5);

    for (const { cursor, node } of connection.edges) {
      expect(typeof cursor).toBe('string');
      expect(typeof node._id).toBe('number');
      expect(typeof node.incrementId).toBe('string');
      expect(typeof node.isGuest).toBe('boolean');
      expect(node.returnableQty).toBe(node.totalQtyOrdered - node.totalRmaQty);
      expect(node.returnableQty).toBeGreaterThan(0);
    }
  });

  test('should return no orders for an increment id no order has', async ({ request }) => {
    const connection = await queryReturnableOrders(request, { first: 5, increment_id: `no-such-order-${Date.now()}` });
    expect(connection.edges).toEqual([]);
    expect(connection.totalCount).toBe(0);
  });

  test('should narrow the connection to the requested order id', async ({ request }) => {
    const first = (await queryReturnableOrders(request, { first: 1 })).edges[0]?.node;
    test.skip(!first, 'no order a return can be opened for exists to read');

    const connection = await queryReturnableOrders(request, { first: 5, id: first._id });
    expect(connection.edges.map((edge: any) => edge.node._id)).toEqual([first._id]);
    expect(connection.edges[0].node.incrementId).toBe(first.incrementId);
  });
});
