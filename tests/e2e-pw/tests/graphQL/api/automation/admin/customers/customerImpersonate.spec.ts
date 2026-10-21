import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import { ADMIN_CUSTOMER_CREATE } from '../../../../graphql/Queries/admin/customers/customers.queries';
import { ADMIN_CUSTOMER_IMPERSONATE } from '../../../../graphql/Queries/admin/customers/customerImpersonate.queries';

test.describe.configure({ timeout: 120_000 });

const DELETE_CUSTOMER = `
  mutation deleteAdminCustomer($id: ID!) {
    deleteAdminCustomer(input: { id: $id }) {
      adminCustomer {
        _id
      }
    }
  }
`;

async function gql(request: APIRequestContext, query: string, variables: Record<string, any> = {}): Promise<any> {
  const response = await sendAdminGraphQLRequest(request, query, variables);
  expect(response.status()).toBe(200);

  return response.json();
}

async function createCustomer(request: APIRequestContext): Promise<any> {
  const body = await gql(request, ADMIN_CUSTOMER_CREATE, {
    firstName: 'Imp',
    lastName: 'ersonate',
    email: `e2e_gql_imp_${Date.now()}_${Math.floor(Math.random() * 100000)}@example.com`,
    customerGroupId: 2,
    channelId: 1,
    sendPassword: false,
    password: 'e2epass123',
  });
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

  return body.data.createAdminCustomer.adminCustomer;
}

test.describe('admin customer impersonate graphql api', () => {
  test('impersonate returns a token bound to the customer', async ({ request }) => {
    const customer = await createCustomer(request);

    try {
      const body = await gql(request, ADMIN_CUSTOMER_IMPERSONATE, { customerId: customer._id });

      expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
      const impersonation = body.data.createAdminCustomerImpersonate.adminCustomerImpersonate;
      expect(typeof impersonation.token).toBe('string');
      expect(impersonation.token).toMatch(/^\d+\|.{20,}$/);
      expect(impersonation.customerId).toBe(customer._id);
      expect(impersonation.customerEmail).toBe(customer.email);
      expect(Number.isNaN(Date.parse(impersonation.expiresAt))).toBe(false);
      expect(Date.parse(impersonation.expiresAt)).toBeGreaterThan(Date.now());
      expect(impersonation.impersonatedByAdminId).toBeGreaterThan(0);
    } finally {
      await sendAdminGraphQLRequest(request, DELETE_CUSTOMER, { id: `/api/admin/customers/${customer._id}` });
    }
  });

  test('impersonate of an unknown customer reports customer not found', async ({ request }) => {
    const body = await gql(request, ADMIN_CUSTOMER_IMPERSONATE, { customerId: 99999999 });

    expect(body.data?.createAdminCustomerImpersonate ?? null).toBeNull();
    expect(body.errors?.[0]?.message).toBe('Customer not found.');
  });
});
