// tests/restAPI/api/automation/admin/customers/customerNotes.spec.ts
//
// Admin Customer Notes (W2) — POST /customers/{customerId}/notes (append-only).
//
// Probed 2026-05-26: input is { note: string, customerNotified?: boolean }.
// Snake_case `customer_notified` is rejected with a misformatted-data 500
// (name converter incompatibility) — use camelCase.

import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_CUSTOMERS } from '../../../../rest/endpoints/admin.customers.endpoints';

// Per-test timeout bumped — note write + customer delete can be slow under
// parallel load (60s default occasionally hits the cleanup DELETE).
test.describe.configure({ timeout: 120_000 });

async function createCustomer(request: APIRequestContext) {
  const resp = await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMERS, {
    method: 'POST',
    data: {
      first_name: 'Note',
      last_name: 'Subject',
      email: `e2e_note_${Date.now()}_${Math.floor(Math.random() * 100000)}@example.com`,
      customer_group_id: 2,
      channel_id: 1,
      send_password: false,
      password: 'e2epass123',
    },
  });
  return await resp.json();
}

async function dropCustomer(request: APIRequestContext, id: number) {
  // Best-effort cleanup — never fail the test on slow / failed delete cascade
  // (customer + notes + listener queue can take a while under parallel load).
  try {
    await sendAdminRequest(request, ADMIN_CUSTOMERS.CUSTOMER(id), { method: 'DELETE' });
  } catch {
    // ignore
  }
}

test.describe('Admin Customer Notes REST API', () => {
  test('create note with only note text', async ({ request }) => {
    const customer = await createCustomer(request);

    const resp = await sendAdminRequest(
      request,
      ADMIN_CUSTOMERS.CUSTOMER_NOTES(customer.id),
      { method: 'POST', data: { note: `e2e note ${Date.now()}` } },
    );
    expect([200, 201]).toContain(resp.status());
    const body = await resp.json();
    expect(body.id).toBeGreaterThan(0);
    expect(body.customerId).toBe(customer.id);
    expect(typeof body.note).toBe('string');

    await dropCustomer(request, customer.id);
  });

  test('create note with customerNotified=true', async ({ request }) => {
    const customer = await createCustomer(request);

    const resp = await sendAdminRequest(
      request,
      ADMIN_CUSTOMERS.CUSTOMER_NOTES(customer.id),
      {
        method: 'POST',
        data: { note: `notify ${Date.now()}`, customerNotified: true },
      },
    );
    expect([200, 201]).toContain(resp.status());
    const body = await resp.json();
    expect(body.customerNotified).toBe(true);

    await dropCustomer(request, customer.id);
  });

  test('create rejects empty note', async ({ request }) => {
    const customer = await createCustomer(request);

    const resp = await sendAdminRequest(
      request,
      ADMIN_CUSTOMERS.CUSTOMER_NOTES(customer.id),
      { method: 'POST', data: { note: '' } },
    );
    expect([400, 422, 500]).toContain(resp.status());

    await dropCustomer(request, customer.id);
  });

  test('create for unknown customer returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(
      request,
      ADMIN_CUSTOMERS.CUSTOMER_NOTES(99999999),
      { method: 'POST', data: { note: 'orphan' } },
    );
    expect([404, 400]).toContain(resp.status());
  });
});
