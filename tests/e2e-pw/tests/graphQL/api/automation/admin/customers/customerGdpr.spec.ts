import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import { sendGraphQLRequest } from '../../../../graphql/helpers/graphqlClient';
import { ADMIN_CUSTOMER_CREATE } from '../../../../graphql/Queries/admin/customers/customers.queries';
import {
  ADMIN_CUSTOMER_GDPR_LIST,
  ADMIN_CUSTOMER_GDPR_DETAIL,
  ADMIN_CUSTOMER_GDPR_UPDATE,
  ADMIN_CUSTOMER_GDPR_PROCESS,
  ADMIN_CUSTOMER_GDPR_DOWNLOAD_DATA,
} from '../../../../graphql/Queries/admin/customers/customerGdpr.queries';
import { SHOP_ACCOUNT_QUERIES as SHOP } from '../../../../graphql/Queries/coverage/shopAccount.queries';

test.describe.configure({ timeout: 60_000 });

const GDPR_NOT_FOUND = 'GDPR request not found.';

const CUSTOMER_NOT_FOUND = 'Customer not found.';

const DELETE_CUSTOMER = `
  mutation deleteAdminCustomer($id: ID!) {
    deleteAdminCustomer(input: { id: $id }) {
      adminCustomer {
        _id
      }
    }
  }
`;

interface ShopCustomer {
  id: number;
  email: string;
  token: string;
}

interface RaisedRequest {
  id: number;
  message: string;
  customer: ShopCustomer;
}

function uniqueSuffix(): string {
  return `${Date.now()}-${Math.floor(Math.random() * 1_000_000)}`;
}

function gdprIri(id: number): string {
  return `/api/admin/customers/gdpr-requests/${id}`;
}

async function gql(request: APIRequestContext, query: string, variables: Record<string, any> = {}): Promise<any> {
  const response = await sendAdminGraphQLRequest(request, query, variables);
  expect(response.status()).toBe(200);

  return response.json();
}

async function shopGql(request: APIRequestContext, query: string, variables: Record<string, any> = {}, token?: string): Promise<any> {
  const response = await sendGraphQLRequest(request, query, variables, token ? { Authorization: `Bearer ${token}` } : {});
  expect(response.status()).toBe(200);

  return response.json();
}

function expectGraphQLError(body: any, field: string, message: string): void {
  expect(body.data?.[field] ?? null).toBeNull();
  expect(body.errors?.[0]?.message).toBe(message);
}

async function gdprEnabled(request: APIRequestContext): Promise<boolean> {
  const body = await shopGql(request, SHOP.gdprStatus);
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

  return body.data.gdprStatus.enabled === true;
}

async function registerShopCustomer(request: APIRequestContext): Promise<ShopCustomer> {
  const email = `e2e-gql-gdpr-${uniqueSuffix()}@example.com`;
  const password = 'Passw0rd!123';

  const body = await shopGql(request, SHOP.registerCustomer, {
    input: {
      firstName: 'Gdpr',
      lastName: 'Subject',
      email,
      password,
      confirmPassword: password,
      status: '1',
      isVerified: '1',
      isSuspended: '0',
      subscribedToNewsLetter: false,
    },
  });
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

  const customer = body.data.createCustomer.customer;
  let token = customer.token;

  if (!token) {
    const login = await shopGql(request, SHOP.loginCustomer, { email, password });
    token = login.data.createCustomerLogin.customerLogin.token;
  }

  expect(typeof token).toBe('string');

  return { id: Number(customer._id), email, token };
}

async function raiseRequest(request: APIRequestContext): Promise<RaisedRequest> {
  const customer = await registerShopCustomer(request);
  const message = `e2e admin gdpr ${uniqueSuffix()}`;

  const body = await shopGql(request, SHOP.createGdprRequest, { type: 'update', message }, customer.token);
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

  const raised = body.data.createGdprRequest.gdprRequest;
  expect(raised.status).toBe('pending');

  return { id: raised._id, message, customer };
}

async function dropCustomer(request: APIRequestContext, id: number): Promise<void> {
  await sendAdminGraphQLRequest(request, DELETE_CUSTOMER, { id: `/api/admin/customers/${id}` });
}

async function adminDetail(request: APIRequestContext, id: number): Promise<any> {
  const body = await gql(request, ADMIN_CUSTOMER_GDPR_DETAIL, { id: gdprIri(id) });
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

  return body.data.adminCustomerGdprRequest;
}

test.describe('admin customer gdpr graphql api', () => {
  test('list filtered by customer returns only the request that customer raised', async ({ request }) => {
    test.skip(!(await gdprEnabled(request)), 'GDPR is disabled in the admin configuration.');

    const raised = await raiseRequest(request);

    try {
      const body = await gql(request, ADMIN_CUSTOMER_GDPR_LIST, { first: 5, customer_id: raised.customer.id });

      expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
      const nodes = body.data.adminCustomerGdprRequests.edges.map((edge: any) => edge.node);
      expect(nodes.map((node: any) => node._id)).toEqual([raised.id]);
      expect(nodes[0].customerId).toBe(raised.customer.id);
      expect(nodes[0].email).toBe(raised.customer.email);
      expect(nodes[0].type).toBe('update');
      expect(nodes[0].status).toBe('pending');
      expect(nodes[0].message).toBe(raised.message);
    } finally {
      await dropCustomer(request, raised.customer.id);
    }
  });

  test('list status and type filters include the pending update request', async ({ request }) => {
    test.skip(!(await gdprEnabled(request)), 'GDPR is disabled in the admin configuration.');

    const raised = await raiseRequest(request);

    try {
      const matching = await gql(request, ADMIN_CUSTOMER_GDPR_LIST, {
        first: 5,
        customer_id: raised.customer.id,
        status: 'pending',
        type: 'update',
      });
      expect(matching.errors, JSON.stringify(matching.errors)).toBeUndefined();
      expect(matching.data.adminCustomerGdprRequests.edges.map((edge: any) => edge.node._id)).toEqual([raised.id]);

      const otherType = await gql(request, ADMIN_CUSTOMER_GDPR_LIST, {
        first: 5,
        customer_id: raised.customer.id,
        type: 'delete',
      });
      expect(otherType.errors, JSON.stringify(otherType.errors)).toBeUndefined();
      expect(otherType.data.adminCustomerGdprRequests.edges).toEqual([]);
    } finally {
      await dropCustomer(request, raised.customer.id);
    }
  });

  test('detail returns the request the test raised', async ({ request }) => {
    test.skip(!(await gdprEnabled(request)), 'GDPR is disabled in the admin configuration.');

    const raised = await raiseRequest(request);

    try {
      const detail = await adminDetail(request, raised.id);
      expect(detail._id).toBe(raised.id);
      expect(detail.customerId).toBe(raised.customer.id);
      expect(detail.customerName).toBe('Gdpr Subject');
      expect(detail.email).toBe(raised.customer.email);
      expect(detail.type).toBe('update');
      expect(detail.status).toBe('pending');
      expect(detail.message).toBe(raised.message);
      expect(detail.revokedAt).toBeNull();
    } finally {
      await dropCustomer(request, raised.customer.id);
    }
  });

  test('detail of an unknown id reports gdpr request not found', async ({ request }) => {
    expectGraphQLError(await gql(request, ADMIN_CUSTOMER_GDPR_DETAIL, { id: gdprIri(99999999) }), 'adminCustomerGdprRequest', GDPR_NOT_FOUND);
  });

  test('update rejects an invalid status and leaves the request pending', async ({ request }) => {
    test.skip(!(await gdprEnabled(request)), 'GDPR is disabled in the admin configuration.');

    const raised = await raiseRequest(request);

    try {
      const body = await gql(request, ADMIN_CUSTOMER_GDPR_UPDATE, {
        id: gdprIri(raised.id),
        status: 'definitely-not-a-status',
      });

      expectGraphQLError(body, 'updateAdminCustomerGdprRequest', 'Status must be one of: pending, processing, declined, approved, revoked.');
      expect((await adminDetail(request, raised.id)).status).toBe('pending');
    } finally {
      await dropCustomer(request, raised.customer.id);
    }
  });

  test('process of an unknown id reports gdpr request not found', async ({ request }) => {
    const body = await gql(request, ADMIN_CUSTOMER_GDPR_PROCESS, { requestId: '99999999' });

    expectGraphQLError(body, 'createAdminCustomerGdprProcess', GDPR_NOT_FOUND);
  });

  test('download data returns the dump of a customer the test created', async ({ request }) => {
    const created = await gql(request, ADMIN_CUSTOMER_CREATE, {
      firstName: 'Gdpr',
      lastName: 'Dump',
      email: `e2e_gql_gdpr_${Date.now()}_${Math.floor(Math.random() * 100000)}@example.com`,
      customerGroupId: 2,
      channelId: 1,
      sendPassword: false,
      password: 'e2epass123',
    });
    expect(created.errors, JSON.stringify(created.errors)).toBeUndefined();
    const customer = created.data.createAdminCustomer.adminCustomer;

    try {
      const body = await gql(request, ADMIN_CUSTOMER_GDPR_DOWNLOAD_DATA, { customerId: customer._id });

      expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
      const dump = body.data.createAdminCustomerGdprDownloadData.adminCustomerGdprDownloadData;
      expect(dump.customerId).toBe(customer._id);
      expect(dump.customerEmail).toBe(customer.email);
      expect(Number.isNaN(Date.parse(dump.generatedAt))).toBe(false);
      expect(dump.data.customer.id).toBe(customer._id);
      expect(dump.data.customer.email).toBe(customer.email);
      expect(dump.data.customer).not.toHaveProperty('password');
      expect(dump.data.customer).not.toHaveProperty('remember_token');
      expect(dump.data.addresses).toEqual([]);
      expect(dump.data.orders).toEqual([]);
      expect(dump.data.reviews).toEqual([]);
      expect(dump.data.notes).toEqual([]);
    } finally {
      await dropCustomer(request, customer._id);
    }
  });

  test('download data for an unknown customer reports customer not found', async ({ request }) => {
    const body = await gql(request, ADMIN_CUSTOMER_GDPR_DOWNLOAD_DATA, { customerId: 99999999 });

    expectGraphQLError(body, 'createAdminCustomerGdprDownloadData', CUSTOMER_NOT_FOUND);
  });
});
