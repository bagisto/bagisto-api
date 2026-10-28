import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../rest/helpers/adminClient';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import {
  ADMIN_SALES_COVERAGE,
  CoverageCustomer,
  bearer,
  createCustomer,
  customerToken,
  deleteCustomer,
  placeOrder,
  saleableProductIds,
} from '../../../rest/helpers/coverage/adminSales.helpers';
import {
  REST_GAPS,
  REST_GAPS_MESSAGES as MSG,
  UNKNOWN_ID,
  expectAdminUnauthenticated,
  expectDetail,
  rowsOf,
  safeJson,
} from '../../../rest/helpers/coverage/restGaps.helpers';

test.describe.configure({ timeout: 180_000 });

interface SidebarEndpoint {
  label: string;
  path: (customerId: number) => string;
}

const SIDEBARS: SidebarEndpoint[] = [
  { label: 'cart items', path: REST_GAPS.CUSTOMER_CART_ITEMS },
  { label: 'compare items', path: REST_GAPS.CUSTOMER_COMPARE_ITEMS },
  { label: 'wishlist items', path: REST_GAPS.CUSTOMER_WISHLIST_ITEMS },
  { label: 'recent order items', path: REST_GAPS.CUSTOMER_RECENT_ORDER_ITEMS },
];

async function sidebarRows(request: APIRequestContext, path: string): Promise<any[]> {
  const response = await sendAdminRequest(request, path);
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);
  expect(Array.isArray(body) || Array.isArray(body?.data), JSON.stringify(body)).toBe(true);

  return rowsOf(body);
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
  const response = await sendAdminRequest(request, ADMIN_SALES_COVERAGE.CUSTOMER_ADDRESSES(customerId), {
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
  const response = await sendAdminRequest(request, REST_GAPS.CUSTOMER_ADDRESS(customerId, addressId));
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);

  return Boolean(body.defaultAddress);
}

async function setDefault(request: APIRequestContext, customerId: number, addressId: number) {
  return sendAdminRequest(request, REST_GAPS.CUSTOMER_ADDRESS_SET_DEFAULT(customerId, addressId), {
    method: 'POST',
    data: {},
  });
}

test.describe('admin customer sidebar rest api', () => {
  for (const sidebar of SIDEBARS) {
    test(`should refuse the customer ${sidebar.label} without an admin token`, async ({ request }) => {
      await expectAdminUnauthenticated(await sendRestRequest(request, sidebar.path(UNKNOWN_ID)));
    });

    test(`should report an unknown customer for ${sidebar.label}`, async ({ request }) => {
      await expectDetail(await sendAdminRequest(request, sidebar.path(UNKNOWN_ID)), 404, MSG.CUSTOMER_NOT_FOUND);
    });

    test(`should return no ${sidebar.label} for a new customer`, async ({ request }) => {
      const customer = await createCustomer(request);

      try {
        expect(await sidebarRows(request, sidebar.path(customer.id))).toEqual([]);
      } finally {
        await deleteCustomer(request, customer.id);
      }
    });
  }

  test('should list the products in the customer storefront cart', async ({ request }) => {
    const productId = await firstSaleableProductId(request);
    const customer = await createCustomer(request);

    try {
      await shopPost(request, customer, ADMIN_SALES_COVERAGE.SHOP_ADD_PRODUCT_IN_CART, { productId, quantity: 2 });

      const rows = await sidebarRows(request, REST_GAPS.CUSTOMER_CART_ITEMS(customer.id));

      expect(rows).toHaveLength(1);
      expect(rows[0].productId).toBe(productId);
      expect(rows[0].quantity).toBe(2);
      expect(rows[0].total).toBeCloseTo(rows[0].price * 2, 2);
      expect(typeof rows[0].sku).toBe('string');
      expect(typeof rows[0].formattedTotal).toBe('string');
    } finally {
      await deleteCustomer(request, customer.id);
    }
  });

  test('should list the products in the customer compare list', async ({ request }) => {
    const productId = await firstSaleableProductId(request);
    const customer = await createCustomer(request);

    try {
      await shopPost(request, customer, ADMIN_SALES_COVERAGE.SHOP_COMPARE_ITEMS, { productId });

      const rows = await sidebarRows(request, REST_GAPS.CUSTOMER_COMPARE_ITEMS(customer.id));

      expect(rows).toHaveLength(1);
      expect(rows[0].productId).toBe(productId);
      expect(typeof rows[0].sku).toBe('string');
      expect(typeof rows[0].name).toBe('string');
      expect(typeof rows[0].formattedPrice).toBe('string');
    } finally {
      await deleteCustomer(request, customer.id);
    }
  });

  test('should list the products in the customer wishlist', async ({ request }) => {
    const productId = await firstSaleableProductId(request);
    const customer = await createCustomer(request);

    try {
      await shopPost(request, customer, ADMIN_SALES_COVERAGE.SHOP_WISHLISTS, { productId });

      const rows = await sidebarRows(request, REST_GAPS.CUSTOMER_WISHLIST_ITEMS(customer.id));

      expect(rows).toHaveLength(1);
      expect(rows[0].productId).toBe(productId);
      expect(typeof rows[0].sku).toBe('string');
      expect(typeof rows[0].formattedPrice).toBe('string');
    } finally {
      await deleteCustomer(request, customer.id);
    }
  });

  test('should list the products the customer recently ordered', async ({ request }) => {
    const order = await placeOrder(request);

    const rows = await sidebarRows(request, REST_GAPS.CUSTOMER_RECENT_ORDER_ITEMS(order.customer.id));

    expect(rows.map(row => row.productId)).toEqual([order.productId]);
    expect(rows[0].type).toBe('simple');
    expect(rows[0].price).toBeGreaterThan(0);
  });
});

test.describe('admin customer default address rest api', () => {
  test('should make the address the only default of its customer', async ({ request }) => {
    const customer = await createCustomer(request);

    try {
      const first = await createAddress(request, customer.id, 'Albany', true);
      const second = await createAddress(request, customer.id, 'Buffalo', false);

      const response = await setDefault(request, customer.id, second);
      const body = await safeJson(response);

      expect(response.status(), JSON.stringify(body)).toBe(200);
      expect(body.id).toBe(second);
      expect(body.customerId).toBe(customer.id);
      expect(body.defaultAddress).toBe(true);
      expect(body.city).toBe('Buffalo');

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

      await expectDetail(await setDefault(request, other.id, address), 403, MSG.ADDRESS_NOT_OWNED);

      expect(await addressIsDefault(request, owner.id, address)).toBe(false);
    } finally {
      await deleteCustomer(request, owner.id);
      await deleteCustomer(request, other.id);
    }
  });

  test('should report an unknown address', async ({ request }) => {
    const customer = await createCustomer(request);

    try {
      await expectDetail(await setDefault(request, customer.id, UNKNOWN_ID), 404, MSG.ADDRESS_NOT_FOUND);
    } finally {
      await deleteCustomer(request, customer.id);
    }
  });

  test('should refuse to set a default address without an admin token', async ({ request }) => {
    await expectAdminUnauthenticated(
      await sendRestRequest(request, REST_GAPS.CUSTOMER_ADDRESS_SET_DEFAULT(UNKNOWN_ID, UNKNOWN_ID), { method: 'POST', data: {} })
    );
  });
});
