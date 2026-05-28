// tests/graphQL/api/automation/admin/customers/customerImpersonate.spec.ts
//
// Admin Customer Impersonate (W2 GraphQL) — issues a customer Sanctum token.
// The token is asserted for shape only; we do NOT exercise it against any
// storefront endpoint (out of scope here).

import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_CUSTOMER_CREATE,
  ADMIN_CUSTOMER_DELETE,
} from '../../../../graphql/Queries/admin/customers/customers.queries';
import { ADMIN_CUSTOMER_IMPERSONATE } from '../../../../graphql/Queries/admin/customers/customerImpersonate.queries';

test.describe.configure({ timeout: 120_000 });

async function createCustomer(request: APIRequestContext) {
  const resp = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_CREATE, {
    firstName: 'Imp',
    lastName: 'ersonate',
    email: `e2e_gql_imp_${Date.now()}_${Math.floor(Math.random() * 100000)}@example.com`,
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

test.describe('Admin Customer Impersonate GraphQL API', () => {
  test('impersonate returns a token bound to the customer', async ({ request }) => {
    const customer = await createCustomer(request);
    expect(customer).toBeTruthy();

    const resp = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_IMPERSONATE, {
      customerId: customer._id,
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.errors, `unexpected errors: ${JSON.stringify(body.errors)}`).toBeUndefined();

    const imp = body?.data?.createAdminCustomerImpersonate?.adminCustomerImpersonate;
    expect(imp).toBeTruthy();
    expect(typeof imp.token).toBe('string');
    expect(imp.token.length).toBeGreaterThan(20);
    expect(imp.customerId).toBe(customer._id);
    expect(imp.customerEmail).toBe(customer.email);
    expect(typeof imp.expiresAt).toBe('string');
    expect(typeof imp.impersonatedByAdminId).toBe('number');

    await dropCustomer(request, customer.id);
  });

  test('impersonate of unknown customer surfaces error', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_CUSTOMER_IMPERSONATE, {
      customerId: 99999999,
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    const hasErrors = Array.isArray(body?.errors) && body.errors.length > 0;
    const isNull = body?.data?.createAdminCustomerImpersonate?.adminCustomerImpersonate === null;
    expect(hasErrors || isNull).toBe(true);
  });
});
