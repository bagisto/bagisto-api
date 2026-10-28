import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_CUSTOMERS_LIST,
  ADMIN_CUSTOMER_DETAIL,
  ADMIN_CUSTOMER_CREATE,
  ADMIN_CUSTOMER_UPDATE,
} from '../../../../graphql/Queries/admin/customers/customers.queries';

test.describe.configure({ timeout: 120_000 });

const CUSTOMER_NOT_FOUND = 'Customer not found.';

const DELETE_CUSTOMER = `
  mutation deleteAdminCustomer($id: ID!) {
    deleteAdminCustomer(input: { id: $id }) {
      adminCustomer {
        _id
        email
        message
      }
    }
  }
`;

const MASS_DELETE_CUSTOMERS = `
  mutation createAdminCustomerMassDelete($indices: Iterable!) {
    createAdminCustomerMassDelete(input: { indices: $indices }) {
      adminCustomerMassDelete {
        deleted
        skipped
        message
      }
    }
  }
`;

const MASS_UPDATE_CUSTOMER_STATUS = `
  mutation createAdminCustomerMassUpdateStatus($indices: Iterable!, $value: Int!) {
    createAdminCustomerMassUpdateStatus(input: { indices: $indices, value: $value }) {
      adminCustomerMassUpdateStatus {
        updated
        value
        message
      }
    }
  }
`;

interface CreatedCustomer {
  id: string;
  _id: number;
  email: string;
  firstName: string;
  lastName: string;
}

function uniqueEmail(prefix = 'cust'): string {
  return `e2e_gql_${prefix}_${Date.now()}_${Math.floor(Math.random() * 100000)}@example.com`;
}

function customerIri(id: number): string {
  return `/api/admin/customers/${id}`;
}

function customerVariables(overrides: Record<string, any> = {}): Record<string, any> {
  return {
    firstName: 'GQL',
    lastName: 'Tester',
    email: uniqueEmail(),
    customerGroupId: 2,
    channelId: 1,
    sendPassword: false,
    password: 'e2epass123',
    ...overrides,
  };
}

async function gql(request: APIRequestContext, query: string, variables: Record<string, any> = {}): Promise<any> {
  const response = await sendAdminGraphQLRequest(request, query, variables);
  expect(response.status()).toBe(200);

  return response.json();
}

function expectGraphQLError(body: any, field: string, message: string): void {
  expect(body.data?.[field] ?? null).toBeNull();
  expect(body.errors?.[0]?.message).toBe(message);
}

async function createCustomer(request: APIRequestContext, overrides: Record<string, any> = {}): Promise<CreatedCustomer> {
  const variables = customerVariables(overrides);
  const body = await gql(request, ADMIN_CUSTOMER_CREATE, variables);
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

  const created = body.data.createAdminCustomer.adminCustomer;
  expect(created._id).toBeGreaterThan(0);
  expect(created.email).toBe(variables.email);
  expect(created.firstName).toBe(variables.firstName);
  expect(created.lastName).toBe(variables.lastName);
  expect(created.group._id).toBe(2);

  return created;
}

async function dropCustomer(request: APIRequestContext, id: number): Promise<void> {
  await sendAdminGraphQLRequest(request, DELETE_CUSTOMER, { id: customerIri(id) });
}

async function expectCustomerGone(request: APIRequestContext, id: number): Promise<void> {
  expectGraphQLError(await gql(request, ADMIN_CUSTOMER_DETAIL, { id: customerIri(id) }), 'adminCustomer', CUSTOMER_NOT_FOUND);
}

async function customerDetail(request: APIRequestContext, id: number): Promise<any> {
  const body = await gql(request, ADMIN_CUSTOMER_DETAIL, { id: customerIri(id) });
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

  return body.data.adminCustomer;
}

test.describe('admin customers graphql api', () => {
  test('list returns edges and page info', async ({ request }) => {
    const body = await gql(request, ADMIN_CUSTOMERS_LIST, { first: 3 });

    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
    const connection = body.data.adminCustomers;
    expect(Array.isArray(connection.edges)).toBe(true);
    expect(connection.edges.length).toBeLessThanOrEqual(3);
    expect(typeof connection.pageInfo.hasNextPage).toBe('boolean');
  });

  test('list filter by email narrows the result to the created customer', async ({ request }) => {
    const created = await createCustomer(request);

    try {
      const body = await gql(request, ADMIN_CUSTOMERS_LIST, { email: created.email });

      expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
      const nodes = body.data.adminCustomers.edges.map((edge: any) => edge.node);
      expect(nodes.map((node: any) => node._id)).toEqual([created._id]);
      expect(nodes[0].email).toBe(created.email);
      expect(nodes[0].name).toBe('GQL Tester');
    } finally {
      await dropCustomer(request, created._id);
    }
  });

  test('list status filter includes an active customer only under status 1', async ({ request }) => {
    const created = await createCustomer(request);

    try {
      const active = await gql(request, ADMIN_CUSTOMERS_LIST, { email: created.email, status: 1 });
      expect(active.errors, JSON.stringify(active.errors)).toBeUndefined();
      expect(active.data.adminCustomers.edges.map((edge: any) => edge.node._id)).toEqual([created._id]);
      expect(active.data.adminCustomers.edges[0].node.status).toBe(1);

      const inactive = await gql(request, ADMIN_CUSTOMERS_LIST, { email: created.email, status: 0 });
      expect(inactive.errors, JSON.stringify(inactive.errors)).toBeUndefined();
      expect(inactive.data.adminCustomers.edges).toEqual([]);
    } finally {
      await dropCustomer(request, created._id);
    }
  });

  test('create detail and delete round trip', async ({ request }) => {
    const created = await createCustomer(request);

    const detail = await customerDetail(request, created._id);
    expect(detail._id).toBe(created._id);
    expect(detail.email).toBe(created.email);
    expect(detail.firstName).toBe(created.firstName);
    expect(detail.lastName).toBe(created.lastName);
    expect(detail.status).toBe(1);
    expect(detail.group._id).toBe(2);

    const removal = await gql(request, DELETE_CUSTOMER, { id: customerIri(created._id) });
    expect(removal.errors, JSON.stringify(removal.errors)).toBeUndefined();
    expect(removal.data.deleteAdminCustomer.adminCustomer._id).toBe(created._id);
    expect(removal.data.deleteAdminCustomer.adminCustomer.message).toBe('Customer deleted successfully.');

    await expectCustomerGone(request, created._id);
  });

  test('create rejects an empty email', async ({ request }) => {
    const body = await gql(request, ADMIN_CUSTOMER_CREATE, customerVariables({ email: '' }));

    expectGraphQLError(body, 'createAdminCustomer', 'The email field is required.');
  });

  test('create rejects a duplicate email in the same channel', async ({ request }) => {
    const first = await createCustomer(request);

    try {
      const duplicate = await gql(request, ADMIN_CUSTOMER_CREATE, customerVariables({ email: first.email, firstName: 'Dup' }));
      expectGraphQLError(duplicate, 'createAdminCustomer', 'The email has already been taken.');

      const listing = await gql(request, ADMIN_CUSTOMERS_LIST, { email: first.email });
      expect(listing.data.adminCustomers.edges.map((edge: any) => edge.node._id)).toEqual([first._id]);
    } finally {
      await dropCustomer(request, first._id);
    }
  });

  test('update persists the new last name', async ({ request }) => {
    const created = await createCustomer(request);

    try {
      const newLast = `Updated${Date.now()}`;
      const body = await gql(request, ADMIN_CUSTOMER_UPDATE, {
        id: customerIri(created._id),
        firstName: created.firstName,
        lastName: newLast,
        email: created.email,
        customerGroupId: 2,
      });

      expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
      expect(body.data.updateAdminCustomer.adminCustomer._id).toBe(created._id);
      expect(body.data.updateAdminCustomer.adminCustomer.lastName).toBe(newLast);

      const detail = await customerDetail(request, created._id);
      expect(detail.lastName).toBe(newLast);
      expect(detail.firstName).toBe(created.firstName);
    } finally {
      await dropCustomer(request, created._id);
    }
  });

  test('detail of an unknown id reports customer not found', async ({ request }) => {
    expectGraphQLError(await gql(request, ADMIN_CUSTOMER_DETAIL, { id: customerIri(99999999) }), 'adminCustomer', CUSTOMER_NOT_FOUND);
  });

  test('mass delete removes both customers', async ({ request }) => {
    const a = await createCustomer(request);
    const b = await createCustomer(request);

    const body = await gql(request, MASS_DELETE_CUSTOMERS, { indices: [a._id, b._id] });

    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
    const result = body.data.createAdminCustomerMassDelete.adminCustomerMassDelete;
    expect(result.deleted).toEqual([a._id, b._id]);
    expect(result.skipped).toEqual([]);
    expect(result.message).toBe('Customers deleted.');

    await expectCustomerGone(request, a._id);
    await expectCustomerGone(request, b._id);
  });

  test('mass delete rejects empty indices', async ({ request }) => {
    const body = await gql(request, MASS_DELETE_CUSTOMERS, { indices: [] });

    expectGraphQLError(body, 'createAdminCustomerMassDelete', 'At least one customer id is required.');
  });

  test('mass update status deactivates both customers', async ({ request }) => {
    const a = await createCustomer(request);
    const b = await createCustomer(request);

    try {
      const body = await gql(request, MASS_UPDATE_CUSTOMER_STATUS, { indices: [a._id, b._id], value: 0 });

      expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
      const result = body.data.createAdminCustomerMassUpdateStatus.adminCustomerMassUpdateStatus;
      expect(result.updated).toEqual([a._id, b._id]);
      expect(result.value).toBe(0);
      expect(result.message).toBe('Customer status updated.');

      expect((await customerDetail(request, a._id)).status).toBe(0);
      expect((await customerDetail(request, b._id)).status).toBe(0);
    } finally {
      await sendAdminGraphQLRequest(request, MASS_DELETE_CUSTOMERS, { indices: [a._id, b._id] });
    }
  });

  test('mass update status rejects a value other than 0 or 1', async ({ request }) => {
    const created = await createCustomer(request);

    try {
      const body = await gql(request, MASS_UPDATE_CUSTOMER_STATUS, { indices: [created._id], value: 99 });

      expectGraphQLError(body, 'createAdminCustomerMassUpdateStatus', 'Status value must be 0 or 1.');
      expect((await customerDetail(request, created._id)).status).toBe(1);
    } finally {
      await dropCustomer(request, created._id);
    }
  });
});
