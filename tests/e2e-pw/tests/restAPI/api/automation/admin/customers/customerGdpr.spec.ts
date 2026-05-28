// tests/restAPI/api/automation/admin/customers/customerGdpr.spec.ts
//
// Admin Customer GDPR (W2) — read-only/edit moderation surface on the GDPR
// queue, plus the download-data dump.
//
// GDPR rows are storefront-originated; in dev DB the table may be empty.
// All read tests skip cleanly when there are no rows. The destructive
// `process` for type=delete is deliberately NOT exercised — it would cascade
// a customer delete.

import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_CUSTOMERS } from '../../../../rest/endpoints/admin.customers.endpoints';

test.describe.configure({ timeout: 60_000 });

async function pickRequestId(request: APIRequestContext): Promise<number | null> {
  const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.GDPR_REQUESTS, {
    params: { per_page: '1' },
  });
  const body = await resp.json();
  if (!body.data || body.data.length === 0) return null;
  return body.data[0].id;
}

async function pickCustomerId(request: APIRequestContext): Promise<number | null> {
  const resp = await sendAdminRequest(request, '/api/admin/customers', {
    params: { per_page: '1' },
  });
  const body = await resp.json();
  if (!body.data || body.data.length === 0) return null;
  return body.data[0].id;
}

test.describe('Admin Customer GDPR REST API', () => {
  test('list returns envelope (possibly empty)', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.GDPR_REQUESTS);
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.data).toBeDefined();
    expect(Array.isArray(body.data)).toBe(true);
    expect(body.meta).toBeDefined();
  });

  test('list filter by status=pending is accepted', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.GDPR_REQUESTS, {
      params: { status: 'pending' },
    });
    expect(resp.status()).toBe(200);
  });

  test('list filter by type=update is accepted', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.GDPR_REQUESTS, {
      params: { type: 'update' },
    });
    expect(resp.status()).toBe(200);
  });

  test('detail returns 200 for an existing GDPR request', async ({ request }) => {
    const id = await pickRequestId(request);
    if (id === null) test.skip(true, 'no GDPR requests in dev DB');
    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.GDPR_REQUEST(id!));
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.id).toBe(id);
  });

  test('detail returns 404 for unknown id', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.GDPR_REQUEST(99999999));
    expect(resp.status()).toBe(404);
  });

  test('update rejects invalid status', async ({ request }) => {
    const id = await pickRequestId(request);
    if (id === null) test.skip(true, 'no GDPR requests in dev DB');

    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.GDPR_REQUEST(id!), {
      method: 'PUT' as any,
      data: { status: 'definitely-not-a-status' },
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('process unknown id returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.GDPR_PROCESS(99999999), {
      method: 'POST',
      data: {},
    });
    expect([400, 404]).toContain(resp.status());
  });

  // The destructive cascade is documented but never exercised here.
  test.fixme('process(type=delete) cascades customer delete — out of scope', async () => {});

  test('download-data returns JSON dump for an existing customer', async ({ request }) => {
    const id = await pickCustomerId(request);
    if (id === null) test.skip(true, 'no customers in dev DB');

    const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.GDPR_DOWNLOAD_DATA(id!), {
      method: 'POST',
      data: {},
    });
    expect([200, 201]).toContain(resp.status());
    const body = await resp.json();
    expect(body.customerId).toBe(id);
    expect(typeof body.customerEmail).toBe('string');
    expect(body.data).toBeDefined();
    // password / remember_token must be stripped
    if (body.data && body.data.customer) {
      expect(body.data.customer.password).toBeUndefined();
      expect(body.data.customer.remember_token).toBeUndefined();
    }
  });

  test('download-data for unknown customer returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(
      request,
      ADMIN_CUSTOMERS.GDPR_DOWNLOAD_DATA(99999999),
      { method: 'POST', data: {} },
    );
    expect([400, 404]).toContain(resp.status());
  });
});
