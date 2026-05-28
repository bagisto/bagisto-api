// tests/graphQL/api/automation/admin/customers/customerGdpr.spec.ts
//
// Admin Customer GDPR (W2 GraphQL) — moderation surface on the GDPR queue
// plus the download-data dump. GDPR rows are storefront-originated; in dev
// DB the table may be empty. The destructive process(type=delete) is
// deliberately NOT exercised (would cascade a customer delete).

import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_CUSTOMERS_LIST,
} from '../../../../graphql/Queries/admin/customers/customers.queries';
import {
  ADMIN_CUSTOMER_GDPR_LIST,
  ADMIN_CUSTOMER_GDPR_DETAIL,
  ADMIN_CUSTOMER_GDPR_UPDATE,
  ADMIN_CUSTOMER_GDPR_PROCESS,
  ADMIN_CUSTOMER_GDPR_DOWNLOAD_DATA,
} from '../../../../graphql/Queries/admin/customers/customerGdpr.queries';

test.describe.configure({ timeout: 60_000 });

async function pickRequest(request: APIRequestContext): Promise<{ id: string; _id: number } | null> {
  const resp = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_GDPR_LIST, { first: 1 });
  const body = await resp.json();
  const edges = body?.data?.adminCustomerGdprRequests?.edges ?? [];
  if (edges.length === 0) return null;
  return edges[0].node;
}

async function pickCustomerId(request: APIRequestContext): Promise<number | null> {
  const resp = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMERS_LIST, { first: 1 });
  const body = await resp.json();
  const edges = body?.data?.adminCustomers?.edges ?? [];
  if (edges.length === 0) return null;
  return edges[0].node._id;
}

test.describe('Admin Customer GDPR GraphQL API', () => {
  test('list returns edges (possibly empty)', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_GDPR_LIST, {
      first: 5,
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.errors).toBeUndefined();
    expect(Array.isArray(body?.data?.adminCustomerGdprRequests?.edges)).toBe(true);
  });

  test('list filter by status=pending is accepted', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_GDPR_LIST, {
      status: 'pending',
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.errors).toBeUndefined();
  });

  test('list filter by type=update is accepted', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_GDPR_LIST, {
      type: 'update',
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.errors).toBeUndefined();
  });

  test('detail returns the row', async ({ request }) => {
    const row = await pickRequest(request);
    if (!row) test.skip(true, 'no GDPR requests in dev DB');

    const resp = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_GDPR_DETAIL, {
      id: row!.id,
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.errors).toBeUndefined();
    const detail = body?.data?.adminCustomerGdprRequest;
    expect(detail).toBeTruthy();
    expect(detail._id).toBe(row!._id);
  });

  test('detail returns null/errors for unknown id', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_GDPR_DETAIL, {
      id: '/api/admin/customers/gdpr-requests/99999999',
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    const isNull = body?.data?.adminCustomerGdprRequest === null;
    expect(hasErrors || isNull).toBe(true);
  });

  test('update rejects invalid status', async ({ request }) => {
    const row = await pickRequest(request);
    if (!row) test.skip(true, 'no GDPR requests in dev DB');

    const resp = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_GDPR_UPDATE, {
      id: row!.id,
      status: 'definitely-not-a-status',
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    const isNull = body?.data?.updateAdminCustomerGdprRequest?.adminCustomerGdprRequest === null;
    expect(hasErrors || isNull).toBe(true);
  });

  test('process unknown id surfaces error', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_GDPR_PROCESS, {
      requestId: '99999999',
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    const isNull = body?.data?.createAdminCustomerGdprProcess?.adminCustomerGdprProcess === null;
    expect(hasErrors || isNull).toBe(true);
  });

  // The destructive cascade is documented but never exercised here.
  test.fixme('process(type=delete) cascades customer delete — out of scope', async () => {});

  test('download-data returns JSON dump for an existing customer', async ({ request }) => {
    const customerId = await pickCustomerId(request);
    if (customerId === null) test.skip(true, 'no customers in dev DB');

    const resp = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_GDPR_DOWNLOAD_DATA, {
      customerId: customerId!,
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.errors, `download-data errors: ${JSON.stringify(body.errors)}`).toBeUndefined();
    const dump = body?.data?.createAdminCustomerGdprDownloadData?.adminCustomerGdprDownloadData;
    expect(dump).toBeTruthy();
    expect(dump.customerId).toBe(customerId);
    expect(typeof dump.customerEmail).toBe('string');
    expect(dump.data).toBeTruthy();
    // password / remember_token must be stripped from the inner customer block
    if (dump.data && dump.data.customer) {
      expect(dump.data.customer.password).toBeUndefined();
      expect(dump.data.customer.remember_token).toBeUndefined();
    }
  });

  test('download-data for unknown customer surfaces error', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_GDPR_DOWNLOAD_DATA, {
      customerId: 99999999,
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    const isNull = body?.data?.createAdminCustomerGdprDownloadData?.adminCustomerGdprDownloadData === null;
    expect(hasErrors || isNull).toBe(true);
  });
});
