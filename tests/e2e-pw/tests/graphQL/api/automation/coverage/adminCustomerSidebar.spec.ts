import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../graphql/helpers/adminGraphqlClient';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../../graphql/helpers/testSupport';
import { env } from '../../../config/env';
import {
  ADMIN_COVERAGE_CUSTOMER_CART_ITEMS,
  ADMIN_COVERAGE_CUSTOMER_COMPARE_ITEMS,
  ADMIN_COVERAGE_CUSTOMER_NOTES,
  ADMIN_COVERAGE_CUSTOMER_RECENT_ORDER_ITEMS,
  ADMIN_COVERAGE_CUSTOMER_WISHLIST_ITEMS,
  ADMIN_COVERAGE_SET_DEFAULT_CUSTOMER_ADDRESS,
} from '../../../graphql/Queries/coverage/adminCustomer.queries';
import { sendAdminRequest } from '../../../../restAPI/rest/helpers/adminClient';
import { sendRestRequest } from '../../../../restAPI/rest/helpers/restClient';
import {
  ADMIN_SALES_COVERAGE as API,
  ADMIN_SALES_MESSAGES as MSG,
  UNKNOWN_ID,
  CoverageCustomer,
  bearer,
  createCustomer,
  customerToken,
  deleteCustomer,
  placeOrder,
  safeJson,
  saleableProductIds,
  uniqueStamp,
} from '../../../../restAPI/rest/helpers/coverage/adminSales.helpers';

test.describe.configure({ timeout: 180_000 });

interface SidebarQuery {
  label: string;
  query: string;
  field: string;
}

const SIDEBAR_QUERIES: SidebarQuery[] = [
  { label: 'notes', query: ADMIN_COVERAGE_CUSTOMER_NOTES, field: 'adminCustomerNotes' },
  { label: 'cart items', query: ADMIN_COVERAGE_CUSTOMER_CART_ITEMS, field: 'adminCustomerCartItems' },
  { label: 'compare items', query: ADMIN_COVERAGE_CUSTOMER_COMPARE_ITEMS, field: 'adminCustomerCompareItems' },
  { label: 'wishlist items', query: ADMIN_COVERAGE_CUSTOMER_WISHLIST_ITEMS, field: 'adminCustomerWishlistItems' },
  { label: 'recent order items', query: ADMIN_COVERAGE_CUSTOMER_RECENT_ORDER_ITEMS, field: 'adminCustomerRecentOrderItems' },
];

async function gql(request: APIRequestContext, query: string, variables: Record<string, any>): Promise<any> {
  const response = await sendAdminGraphQLRequest(request, query, variables);

  expect(response.status()).toBe(200);

  return response.json();
}

async function connectionFor(request: APIRequestContext, sidebar: SidebarQuery, customerId: number): Promise<any> {
  const body = await gql(request, sidebar.query, { customerId });

  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
  expect(Array.isArray(body.data?.[sidebar.field]?.edges)).toBe(true);

  return body.data[sidebar.field];
}

function nodesOf(connection: any): any[] {
  return connection.edges.map((edge: any) => edge.node);
}

async function firstSaleableProductId(request: APIRequestContext): Promise<number> {
  const [productId] = await saleableProductIds(request);
  test.skip(!productId, 'no saleable simple product with stock exists for the storefront');

  return productId;
}

async function shopPost(request: APIRequestContext, customer: CoverageCustomer, path: string, data: Record<string, any>): Promise<void> {
  const token = await customerToken(request, customer);
  const response = await sendRestRequest(request, path, { method: 'POST', data, headers: bearer(token) });

  expect([200, 201], await response.text()).toContain(response.status());
}

async function createAddress(request: APIRequestContext, customerId: number, city: string, defaultAddress: boolean): Promise<number> {
  const response = await sendAdminRequest(request, API.CUSTOMER_ADDRESSES(customerId), {
    method: 'POST',
    data: {
      first_name: 'Default',
      last_name: 'Check',
      address: '1 Coverage Lane',
      city,
      state: 'NY',
      country: 'US',
      postcode: '10001',
      phone: '5551234567',
      default_address: defaultAddress,
    },
  });
  const body = await safeJson(response);

  expect([200, 201], JSON.stringify(body)).toContain(response.status());
  expect(typeof body?.id).toBe('number');

  return body.id;
}

async function addressIsDefault(request: APIRequestContext, customerId: number, addressId: number): Promise<boolean> {
  const response = await sendAdminRequest(request, `${API.CUSTOMER_ADDRESSES(customerId)}/${addressId}`);
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);

  return Boolean(body.defaultAddress);
}

function addressIri(customerId: number, addressId: number): string {
  return `/api/admin/customers/${customerId}/addresses/${addressId}`;
}

test.describe('admin customer sidebar graphql', () => {
  for (const sidebar of SIDEBAR_QUERIES) {
    test(`should refuse the customer ${sidebar.label} without an admin token`, async ({ request }) => {
      const response = await sendGraphQLRequest(request, sidebar.query, { customerId: UNKNOWN_ID }, {}, env.adminGraphqlEndpoint);

      expect(response.status()).toBe(401);
      expect((await response.json())?.message).toBe(MSG.UNAUTHENTICATED);
    });

    test(`should report an unknown customer for ${sidebar.label}`, async ({ request }) => {
      const body = await gql(request, sidebar.query, { customerId: UNKNOWN_ID });

      expect(graphQLErrorMessages(body)).toContain(MSG.CUSTOMER_NOT_FOUND);
    });

    test(`should return no ${sidebar.label} for a new customer`, async ({ request }) => {
      const customer = await createCustomer(request);

      try {
        const connection = await connectionFor(request, sidebar, customer.id);

        expect(connection.edges).toEqual([]);
        expect(connection.totalCount).toBe(0);
      } finally {
        await deleteCustomer(request, customer.id);
      }
    });
  }

  test('should list the customer notes newest first', async ({ request }) => {
    const customer = await createCustomer(request);
    const firstNote = `first note ${uniqueStamp()}`;
    const secondNote = `second note ${uniqueStamp()}`;

    try {
      for (const [note, customerNotified] of [[firstNote, false], [secondNote, true]] as const) {
        const response = await sendAdminRequest(request, API.CUSTOMER_NOTES(customer.id), {
          method: 'POST',
          data: { note, customerNotified },
        });
        expect(response.status(), await response.text()).toBe(201);
      }

      const connection = await connectionFor(request, SIDEBAR_QUERIES[0], customer.id);
      const nodes = nodesOf(connection);

      expect(connection.totalCount).toBe(2);
      expect(nodes.map(node => node.note)).toEqual([secondNote, firstNote]);
      expect(nodes.map(node => node.customerNotified)).toEqual([true, false]);
      expect(nodes.every(node => node.customerId === customer.id)).toBe(true);
      expect(nodes.every(node => typeof node.createdAt === 'string')).toBe(true);
    } finally {
      await deleteCustomer(request, customer.id);
    }
  });

  test('should list the products in the customer storefront cart', async ({ request }) => {
    const productId = await firstSaleableProductId(request);
    const customer = await createCustomer(request);

    try {
      await shopPost(request, customer, API.SHOP_ADD_PRODUCT_IN_CART, { productId, quantity: 2 });

      const connection = await connectionFor(request, SIDEBAR_QUERIES[1], customer.id);
      const [node] = nodesOf(connection);

      expect(connection.totalCount).toBe(1);
      expect(node.productId).toBe(productId);
      expect(node.quantity).toBe(2);
      expect(node.total).toBeCloseTo(node.price * 2, 2);
      expect(typeof node.sku).toBe('string');
      expect(typeof node.formattedTotal).toBe('string');
    } finally {
      await deleteCustomer(request, customer.id);
    }
  });

  test('should list the products in the customer compare list', async ({ request }) => {
    const productId = await firstSaleableProductId(request);
    const customer = await createCustomer(request);

    try {
      await shopPost(request, customer, API.SHOP_COMPARE_ITEMS, { productId });

      const connection = await connectionFor(request, SIDEBAR_QUERIES[2], customer.id);
      const [node] = nodesOf(connection);

      expect(connection.totalCount).toBe(1);
      expect(node.productId).toBe(productId);
      expect(typeof node.sku).toBe('string');
      expect(typeof node.name).toBe('string');
      expect(typeof node.formattedPrice).toBe('string');
    } finally {
      await deleteCustomer(request, customer.id);
    }
  });

  test('should list the products in the customer wishlist', async ({ request }) => {
    const productId = await firstSaleableProductId(request);
    const customer = await createCustomer(request);

    try {
      await shopPost(request, customer, API.SHOP_WISHLISTS, { productId });

      const connection = await connectionFor(request, SIDEBAR_QUERIES[3], customer.id);
      const [node] = nodesOf(connection);

      expect(connection.totalCount).toBe(1);
      expect(node.productId).toBe(productId);
      expect(typeof node.sku).toBe('string');
      expect(typeof node.formattedPrice).toBe('string');
    } finally {
      await deleteCustomer(request, customer.id);
    }
  });

  test('should list the products the customer recently ordered', async ({ request }) => {
    const order = await placeOrder(request);

    const connection = await connectionFor(request, SIDEBAR_QUERIES[4], order.customer.id);
    const nodes = nodesOf(connection);

    expect(connection.totalCount).toBe(1);
    expect(nodes.map(node => node.productId)).toEqual([order.productId]);
    expect(nodes[0].type).toBe('simple');
    expect(nodes[0].price).toBeGreaterThan(0);
  });
});

test.describe('admin customer default address graphql', () => {
  test('should make the address the only default of its customer', async ({ request }) => {
    const customer = await createCustomer(request);

    try {
      const first = await createAddress(request, customer.id, 'Albany', true);
      const second = await createAddress(request, customer.id, 'Buffalo', false);

      const body = await gql(request, ADMIN_COVERAGE_SET_DEFAULT_CUSTOMER_ADDRESS, {
        input: { id: addressIri(customer.id, second), customerId: customer.id },
      });

      expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

      const address = body.data.setDefaultAdminCustomerAddress.adminCustomerAddress;
      expect(address._id).toBe(second);
      expect(address.customerId).toBe(customer.id);
      expect(address.defaultAddress).toBe(true);
      expect(address.city).toBe('Buffalo');

      expect(await addressIsDefault(request, customer.id, second)).toBe(true);
      expect(await addressIsDefault(request, customer.id, first)).toBe(false);
    } finally {
      await deleteCustomer(request, customer.id);
    }
  });

  test('should refuse an address claimed for a customer that does not own it', async ({ request }) => {
    const owner = await createCustomer(request);
    const other = await createCustomer(request);

    try {
      const address = await createAddress(request, owner.id, 'Albany', false);

      const body = await gql(request, ADMIN_COVERAGE_SET_DEFAULT_CUSTOMER_ADDRESS, {
        input: { id: addressIri(owner.id, address), customerId: other.id },
      });

      expect(graphQLErrorMessages(body)).toContain(MSG.ADDRESS_NOT_OWNED);
      expect(await addressIsDefault(request, owner.id, address)).toBe(false);
    } finally {
      await deleteCustomer(request, owner.id);
      await deleteCustomer(request, other.id);
    }
  });

  test('should report an unknown address', async ({ request }) => {
    const customer = await createCustomer(request);

    try {
      const body = await gql(request, ADMIN_COVERAGE_SET_DEFAULT_CUSTOMER_ADDRESS, {
        input: { id: addressIri(customer.id, UNKNOWN_ID), customerId: customer.id },
      });

      expect(graphQLErrorMessages(body)).toContain(MSG.ADDRESS_NOT_FOUND);
    } finally {
      await deleteCustomer(request, customer.id);
    }
  });

  test('should refuse to set a default address without an admin token', async ({ request }) => {
    const response = await sendGraphQLRequest(
      request,
      ADMIN_COVERAGE_SET_DEFAULT_CUSTOMER_ADDRESS,
      { input: { id: addressIri(UNKNOWN_ID, UNKNOWN_ID), customerId: UNKNOWN_ID } },
      {},
      env.adminGraphqlEndpoint,
    );

    expect(response.status()).toBe(401);
    expect((await response.json())?.message).toBe(MSG.UNAUTHENTICATED);
  });
});
