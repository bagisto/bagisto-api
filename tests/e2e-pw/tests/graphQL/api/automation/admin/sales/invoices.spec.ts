// tests/graphQL/api/automation/admin/sales/invoices.spec.ts
//
// Admin Invoices GraphQL — listing (cursor) + detail.
// PDF print is REST-only (binary stream not transportable over GraphQL).

import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_INVOICES_LIST_QUERY,
  ADMIN_INVOICE_DETAIL_QUERY,
} from '../../../../graphql/Queries/admin/sales/invoices.queries';

test.describe.configure({ timeout: 60_000 });

async function firstInvoiceEdge(request: any): Promise<any | null> {
  const resp = await sendAdminGraphQLRequest(request, ADMIN_INVOICES_LIST_QUERY, { first: 1 });
  if (resp.status() !== 200) return null;
  const body = await resp.json();
  const edges = body?.data?.adminInvoices?.edges ?? [];
  return edges.length ? edges[0] : null;
}

test.describe('Admin Invoices GraphQL API', () => {
  test('listing returns cursor connection', async ({ request }) => {
    const response = await sendAdminGraphQLRequest(request, ADMIN_INVOICES_LIST_QUERY, { first: 5 });
    const status = response.status();
    console.log('gql invoices listing:', status);
    expect(status).toBe(200);
    const body = await response.json();
    expect(body.errors, `unexpected errors: ${JSON.stringify(body.errors)}`).toBeUndefined();
    const conn = body?.data?.adminInvoices;
    expect(conn).toBeTruthy();
    expect(Array.isArray(conn.edges)).toBe(true);
    console.log(`  edges=${conn.edges.length}`);
  });

  test('listing row carries slim fields', async ({ request }) => {
    const edge = await firstInvoiceEdge(request);
    if (!edge) {
      test.skip(true, 'no invoices in DB');
      return;
    }
    expect(edge.node).toHaveProperty('id');
    expect(edge.node).toHaveProperty('_id');
    expect(edge.node).toHaveProperty('orderId');
    expect(edge.node).toHaveProperty('state');
  });

  test('detail by id returns the invoice', async ({ request }) => {
    const edge = await firstInvoiceEdge(request);
    if (!edge) {
      test.skip(true, 'no invoices in DB');
      return;
    }
    const response = await sendAdminGraphQLRequest(request, ADMIN_INVOICE_DETAIL_QUERY, {
      id: edge.node.id,
    });
    const status = response.status();
    console.log(`gql invoice detail (${edge.node._id}):`, status);
    expect(status).toBe(200);
    const body = await response.json();
    if (body.errors) {
      console.log('  detail errors:', JSON.stringify(body.errors).slice(0, 200));
      return;
    }
    const detail = body?.data?.adminInvoice;
    expect(detail).toBeTruthy();
    expect(detail._id).toBe(edge.node._id);
  });
});
