// tests/graphQL/api/automation/admin/sales/shipments.spec.ts
//
// Admin Shipments GraphQL — listing (cursor) + detail.

import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_SHIPMENTS_LIST_QUERY,
  ADMIN_SHIPMENT_DETAIL_QUERY,
} from '../../../../graphql/Queries/admin/sales/shipments.queries';

test.describe.configure({ timeout: 60_000 });

async function firstShipmentEdge(request: any): Promise<any | null> {
  const resp = await sendAdminGraphQLRequest(request, ADMIN_SHIPMENTS_LIST_QUERY, { first: 1 });
  if (resp.status() !== 200) return null;
  const body = await resp.json();
  const edges = body?.data?.adminShipments?.edges ?? [];
  return edges.length ? edges[0] : null;
}

test.describe('Admin Shipments GraphQL API', () => {
  test('listing returns cursor connection', async ({ request }) => {
    const response = await sendAdminGraphQLRequest(request, ADMIN_SHIPMENTS_LIST_QUERY, { first: 5 });
    const status = response.status();
    console.log('gql shipments listing:', status);
    expect(status).toBe(200);
    const body = await response.json();
    expect(body.errors, `unexpected errors: ${JSON.stringify(body.errors)}`).toBeUndefined();
    const conn = body?.data?.adminShipments;
    expect(conn).toBeTruthy();
    expect(Array.isArray(conn.edges)).toBe(true);
    console.log(`  edges=${conn.edges.length}`);
  });

  test('listing row carries expected fields', async ({ request }) => {
    const edge = await firstShipmentEdge(request);
    if (!edge) {
      test.skip(true, 'no shipments in DB');
      return;
    }
    expect(edge.node).toHaveProperty('id');
    expect(edge.node).toHaveProperty('_id');
    expect(edge.node).toHaveProperty('orderId');
    expect(edge.node).toHaveProperty('totalQty');
  });

  test('detail by id returns the shipment', async ({ request }) => {
    const edge = await firstShipmentEdge(request);
    if (!edge) {
      test.skip(true, 'no shipments in DB');
      return;
    }
    const response = await sendAdminGraphQLRequest(request, ADMIN_SHIPMENT_DETAIL_QUERY, {
      id: edge.node.id,
    });
    const status = response.status();
    console.log(`gql shipment detail (${edge.node._id}):`, status);
    expect(status).toBe(200);
    const body = await response.json();
    if (body.errors) {
      console.log('  detail errors:', JSON.stringify(body.errors).slice(0, 200));
      return;
    }
    const detail = body?.data?.adminShipment;
    expect(detail).toBeTruthy();
    expect(detail._id).toBe(edge.node._id);
  });
});
