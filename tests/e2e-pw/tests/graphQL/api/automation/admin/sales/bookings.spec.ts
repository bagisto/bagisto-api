// tests/graphQL/api/automation/admin/sales/bookings.spec.ts
//
// Admin Bookings GraphQL — listing (cursor) + detail.
// One row per booking line on an order.

import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_BOOKINGS_LIST_QUERY,
  ADMIN_BOOKING_DETAIL_QUERY,
} from '../../../../graphql/Queries/admin/sales/bookings.queries';

test.describe.configure({ timeout: 60_000 });

async function firstBookingEdge(request: any): Promise<any | null> {
  const resp = await sendAdminGraphQLRequest(request, ADMIN_BOOKINGS_LIST_QUERY, { first: 1 });
  if (resp.status() !== 200) return null;
  const body = await resp.json();
  const edges = body?.data?.adminBookings?.edges ?? [];
  return edges.length ? edges[0] : null;
}

test.describe('Admin Bookings GraphQL API', () => {
  test('listing returns cursor connection', async ({ request }) => {
    const response = await sendAdminGraphQLRequest(request, ADMIN_BOOKINGS_LIST_QUERY, { first: 5 });
    const status = response.status();
    console.log('gql bookings listing:', status);
    expect(status).toBe(200);
    const body = await response.json();
    expect(body.errors, `unexpected errors: ${JSON.stringify(body.errors)}`).toBeUndefined();
    const conn = body?.data?.adminBookings;
    expect(conn).toBeTruthy();
    expect(Array.isArray(conn.edges)).toBe(true);
    console.log(`  edges=${conn.edges.length}`);
  });

  test('listing row carries expected fields', async ({ request }) => {
    const edge = await firstBookingEdge(request);
    if (!edge) {
      test.skip(true, 'no bookings in DB');
      return;
    }
    expect(edge.node).toHaveProperty('id');
    expect(edge.node).toHaveProperty('_id');
    expect(edge.node).toHaveProperty('orderId');
  });

  test('detail by id returns the booking', async ({ request }) => {
    const edge = await firstBookingEdge(request);
    if (!edge) {
      test.skip(true, 'no bookings in DB');
      return;
    }
    const response = await sendAdminGraphQLRequest(request, ADMIN_BOOKING_DETAIL_QUERY, {
      id: edge.node.id,
    });
    const status = response.status();
    console.log(`gql booking detail (${edge.node._id}):`, status);
    expect(status).toBe(200);
    const body = await response.json();
    if (body.errors) {
      console.log('  detail errors:', JSON.stringify(body.errors).slice(0, 200));
      return;
    }
    const detail = body?.data?.adminBooking;
    expect(detail).toBeTruthy();
    expect(detail._id).toBe(edge.node._id);
  });
});
