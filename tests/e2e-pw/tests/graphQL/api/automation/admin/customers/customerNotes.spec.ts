// tests/graphQL/api/automation/admin/customers/customerNotes.spec.ts
//
// Admin Customer Notes (W2 GraphQL) — POST only. Append-only on the
// `customer_notes` table; no list/detail endpoint.

import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_CUSTOMER_CREATE,
  ADMIN_CUSTOMER_DELETE,
} from '../../../../graphql/Queries/admin/customers/customers.queries';
import { ADMIN_CUSTOMER_NOTE_CREATE } from '../../../../graphql/Queries/admin/customers/customerNotes.queries';

test.describe.configure({ timeout: 120_000 });

async function createCustomer(request: APIRequestContext) {
  const resp = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_CREATE, {
    firstName: 'Note',
    lastName: 'Subject',
    email: `e2e_gql_note_${Date.now()}_${Math.floor(Math.random() * 100000)}@example.com`,
    customerGroupId: 2,
    channelId: 1,
    sendPassword: false,
    password: 'e2epass123',
  });
  const body = await resp.json();
  return body?.data?.createAdminCustomer?.adminCustomer;
}

async function dropCustomer(request: APIRequestContext, id: string) {
  try {
    await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_DELETE, { id });
  } catch {
    // ignore
  }
}

test.describe('Admin Customer Notes GraphQL API', () => {
  test('create note with only note text', async ({ request }) => {
    const customer = await createCustomer(request);
    expect(customer).toBeTruthy();

    const resp = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_NOTE_CREATE, {
      customerId: customer._id,
      note: `e2e gql note ${Date.now()}`,
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    // No errors[] expected on happy path.
    expect(body.errors, `unexpected errors: ${JSON.stringify(body.errors)}`).toBeUndefined();

    await dropCustomer(request, customer.id);
  });

  test('create note with customerNotified=true', async ({ request }) => {
    const customer = await createCustomer(request);

    const resp = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_NOTE_CREATE, {
      customerId: customer._id,
      note: `notify ${Date.now()}`,
      customerNotified: true,
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.errors).toBeUndefined();

    await dropCustomer(request, customer.id);
  });

  test('create rejects empty note', async ({ request }) => {
    const customer = await createCustomer(request);

    const resp = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_NOTE_CREATE, {
      customerId: customer._id,
      note: '',
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    expect(hasErrors).toBe(true);

    await dropCustomer(request, customer.id);
  });

  test('create for unknown customer surfaces error', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_NOTE_CREATE, {
      customerId: 99999999,
      note: 'orphan',
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    const isNull = body?.data?.createAdminCustomerNote?.adminCustomerNote === null;
    expect(hasErrors || isNull).toBe(true);
  });
});
