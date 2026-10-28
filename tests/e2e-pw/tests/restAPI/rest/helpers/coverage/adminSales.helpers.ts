import { APIRequestContext, APIResponse, expect, test } from '@playwright/test';
import { sendAdminRequest } from '../adminClient';
import { sendRestRequest } from '../restClient';
import { e2eFixtures } from '../../../../fixtures/e2eFixtures';

export const ADMIN_SALES_COVERAGE = {
  CUSTOMERS: '/api/admin/customers',
  CUSTOMER: (id: number | string) => `/api/admin/customers/${id}`,
  CUSTOMER_ADDRESSES: (customerId: number | string) => `/api/admin/customers/${customerId}/addresses`,
  CUSTOMER_NOTES: (customerId: number | string) => `/api/admin/customers/${customerId}/notes`,
  DRAFT_CARTS: (customerId: number | string) => `/api/admin/customers/${customerId}/draft-carts`,
  CART: (id: number | string) => `/api/admin/carts/${id}`,
  CART_ITEMS: (id: number | string) => `/api/admin/carts/${id}/items`,
  CART_ADDRESSES: (id: number | string) => `/api/admin/carts/${id}/addresses`,
  CART_COUPON: (id: number | string) => `/api/admin/carts/${id}/coupon`,
  CART_SHIPPING_METHODS: (id: number | string) => `/api/admin/carts/${id}/shipping-methods`,
  CART_PAYMENT_METHODS: (id: number | string) => `/api/admin/carts/${id}/payment-methods`,
  PLACE_ORDER: (cartId: number | string) => `/api/admin/orders/place/${cartId}`,
  ORDER: (id: number | string) => `/api/admin/orders/${id}`,
  ORDER_INVOICES: (id: number | string) => `/api/admin/orders/${id}/invoices`,
  INVOICE: (id: number | string) => `/api/admin/invoices/${id}`,
  INVOICES_MASS_UPDATE_STATUS: '/api/admin/invoices/mass-update-status',
  TRANSACTIONS: '/api/admin/transactions',
  ORDERS_EXPORT: '/api/admin/orders/export',
  INVOICES_EXPORT: '/api/admin/invoices/export',
  SHIPMENTS_EXPORT: '/api/admin/shipments/export',
  REFUNDS_EXPORT: '/api/admin/refunds/export',
  TRANSACTIONS_EXPORT: '/api/admin/transactions/export',
  BOOKINGS_EXPORT: '/api/admin/bookings/export',
  REPORTING_CUSTOMERS_VIEW: '/api/admin/reporting/customers/view',
  REPORTING_CUSTOMERS_EXPORT: '/api/admin/reporting/customers/export',
  EU_WITHDRAWALS: '/api/admin/eu-withdrawals',
  EU_WITHDRAWAL: (id: number | string) => `/api/admin/eu-withdrawals/${id}`,
  EU_WITHDRAWAL_DECLINE: (id: number | string) => `/api/admin/eu-withdrawals/${id}/decline`,
  EU_WITHDRAWAL_MARK_REFUNDED: (id: number | string) => `/api/admin/eu-withdrawals/${id}/mark-refunded`,
  CATALOG_PRODUCTS: '/api/admin/catalog/products',
  CART_RULES: '/api/admin/marketing/cart-rules',
  CUSTOMER_GROUPS: '/api/admin/customers/groups',
  CART_RULE: (id: number | string) => `/api/admin/marketing/cart-rules/${id}`,
  CONFIGURATION: '/api/admin/configuration',
  SHOP_CUSTOMER_LOGIN: '/api/shop/customer/login',
  SHOP_ADD_PRODUCT_IN_CART: '/api/shop/add-product-in-cart',
  SHOP_WISHLISTS: '/api/shop/wishlists',
  SHOP_COMPARE_ITEMS: '/api/shop/compare_items',
  SHOP_EU_WITHDRAWALS: '/api/shop/eu-withdrawals',
};

export const ADMIN_SALES_MESSAGES = {
  UNAUTHENTICATED: 'Unauthenticated.',
  CART_NOT_FOUND: 'Cart not found.',
  CUSTOMER_NOT_FOUND: 'Customer not found.',
  PRODUCT_REQUIRED: 'productId is required.',
  PRODUCT_NOT_FOUND: 'Product not found.',
  QTY_REQUIRED: 'qty is required and must be a non-empty object of itemId => quantity.',
  CART_ITEM_REQUIRED: 'cartItemId is required.',
  ITEM_ADDED: 'Item added to cart.',
  ITEM_UPDATED: 'Cart items updated.',
  ITEM_REMOVED: 'Item removed from cart.',
  ADDRESS_SAVED: 'Address saved.',
  ADDRESS_REQUIRED: 'A billing address is required.',
  BILLING_PHONE_REQUIRED: 'The billing phone is required to save the address.',
  COUPON_CODE_REQUIRED: 'A coupon code is required.',
  COUPON_APPLIED: 'Coupon applied.',
  COUPON_ALREADY_APPLIED: 'Coupon is already applied to this cart.',
  COUPON_NOT_FOUND: 'Coupon not found or inactive.',
  COUPON_REMOVED: 'Coupon removed.',
  DRAFT_CREATED: 'Draft cart created.',
  ADDRESSES_REQUIRED_FOR_SHIPPING: 'Addresses must be saved before selecting a shipping method.',
  SHIPPING_REQUIRED_FOR_PAYMENT: 'Shipping method must be selected before payment method.',
  SHIPPING_METHOD_REQUIRED: 'Shipping method is required.',
  SHIPPING_METHOD_SAVED: 'Shipping method saved.',
  PAYMENT_METHOD_REQUIRED: 'Payment method is required.',
  PAYMENT_METHOD_SAVED: 'Payment method saved.',
  ORDER_PLACED: 'Order placed successfully.',
  PLACE_ORDER_EMPTY_CART: 'Cart is empty — add items before placing an order.',
  PLACE_ORDER_PAYMENT_REQUIRED: 'A payment method must be selected before placing the order.',
  INVOICE_NOT_FOUND: 'Invoice not found.',
  INVOICE_EMAIL_INVALID: 'A valid recipient email is required.',
  INVOICE_MASS_STATUS_INDICES_REQUIRED: 'At least one invoice id is required.',
  INVOICE_MASS_STATUS_VALUE_INVALID: 'Status must be one of: pending, paid, overdue.',
  INVOICE_MASS_STATUS_SUCCESS: 'Invoice status updated successfully.',
  TRANSACTION_INVOICE_REQUIRED: 'The invoiceId field is required.',
  TRANSACTION_INVOICE_MISSING: 'The specified invoice could not be found.',
  TRANSACTION_ALREADY_PAID: 'This invoice is already fully paid.',
  TRANSACTION_AMOUNT_EXCEEDS: 'The payment amount exceeds the invoice grand total.',
  EXPORT_FORMAT_UNSUPPORTED: 'Only csv, xls and xlsx exports are supported.',
  REPORTING_EXPORT_FORMAT_UNSUPPORTED: 'Only the csv, xls and xlsx export formats are supported.',
  REPORTING_INVALID_TYPE: 'Unknown reporting stat type for this entity.',
  WITHDRAWAL_NOT_FOUND: 'Withdrawal declaration not found.',
  WITHDRAWAL_DECLINED: 'Withdrawal marked as declined.',
  WITHDRAWAL_REFUNDED: 'Withdrawal marked as refunded.',
  WITHDRAWAL_DECLINE_REASON_REQUIRED: 'The declined reason field is required.',
  WITHDRAWAL_REFUND_NOTE_TOO_LONG: 'The refund note field must not be greater than 500 characters.',
  ADDRESS_NOT_FOUND: 'Address not found.',
  ADDRESS_NOT_OWNED: 'This address does not belong to the specified customer.',
  CART_RULE_NOT_FOUND: 'Cart rule not found.',
};

export const EXPORT_CONTENT_TYPES = {
  csv: 'text/csv',
  xls: 'application/vnd.ms-excel',
  xlsx: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
};

export const UNKNOWN_ID = 999999999;

const DEFAULT_CUSTOMER_GROUP_ID = 2;

const DEFAULT_CHANNEL_ID = 1;

const DEFAULT_CHANNEL_CODE = 'default';

const EU_WITHDRAWAL_SLUG = 'sales.eu_withdrawal.general';

const EU_WITHDRAWAL_ENABLED_KEY = 'sales.eu_withdrawal.general.enabled';

const SUPPORTED_PAYMENT_METHODS = ['cashondelivery', 'moneytransfer'];

export interface CoverageCustomer {
  id: number;
  email: string;
  password: string;
  firstName: string;
  lastName: string;
}

export interface CoverageCart {
  customer: CoverageCustomer;
  cartId: number;
}

export interface CoverageCartWithItem extends CoverageCart {
  productId: number;
  cartItemId: number;
}

export interface CoverageOrder extends CoverageCartWithItem {
  orderId: number;
  incrementId: string;
}

export interface CoverageInvoice {
  id: number;
  baseGrandTotal: number;
  state: string;
}

export interface CoverageCouponRule {
  id: number;
  code: string;
}

export function uniqueStamp(): string {
  return `${Date.now()}${Math.floor(Math.random() * 1_000_000)}`;
}

export async function safeJson(response: APIResponse): Promise<any> {
  try {
    return await response.json();
  } catch {
    return null;
  }
}

export function problemDetail(body: any): string {
  return String(body?.detail ?? body?.message ?? '');
}

export async function expectProblem(response: APIResponse, status: number, detail: string): Promise<void> {
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(status);
  expect(problemDetail(body)).toBe(detail);
}

export async function expectUnauthenticated(response: APIResponse): Promise<void> {
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(401);
  expect(body?.message).toBe(ADMIN_SALES_MESSAGES.UNAUTHENTICATED);
}

export function bearer(token: string): Record<string, string> {
  return { Authorization: `Bearer ${token}` };
}

export function parseCsvRow(line: string): string[] {
  const cells: string[] = [];
  let current = '';
  let quoted = false;

  for (let index = 0; index < line.length; index++) {
    const char = line[index];

    if (quoted) {
      if (
        char === '"'
        && line[index + 1] === '"'
      ) {
        current += '"';
        index++;
      } else if (char === '"') {
        quoted = false;
      } else {
        current += char;
      }

      continue;
    }

    if (char === '"') {
      quoted = true;
    } else if (char === ',') {
      cells.push(current);
      current = '';
    } else {
      current += char;
    }
  }

  cells.push(current);

  return cells;
}

export function csvHeaderRow(body: string): string[] {
  return parseCsvRow(body.replace(/^﻿/, '').split(/\r?\n/)[0]);
}

export async function createCustomer(request: APIRequestContext): Promise<CoverageCustomer> {
  const stamp = uniqueStamp();
  const customer = {
    firstName: 'Coverage',
    lastName: `Buyer${stamp}`,
    email: `e2e_sales_cov_${stamp}@example.com`,
    password: `Cov${stamp}!`,
  };

  const response = await sendAdminRequest(request, ADMIN_SALES_COVERAGE.CUSTOMERS, {
    method: 'POST',
    data: {
      first_name: customer.firstName,
      last_name: customer.lastName,
      email: customer.email,
      customer_group_id: DEFAULT_CUSTOMER_GROUP_ID,
      channel_id: DEFAULT_CHANNEL_ID,
      send_password: false,
      password: customer.password,
    },
  });
  const body = await safeJson(response);

  expect([200, 201], JSON.stringify(body)).toContain(response.status());
  expect(typeof body?.id).toBe('number');

  return { id: body.id, ...customer };
}

export async function deleteCustomer(request: APIRequestContext, id: number): Promise<void> {
  try {
    await sendAdminRequest(request, ADMIN_SALES_COVERAGE.CUSTOMER(id), { method: 'DELETE' });
  } catch {
    return;
  }
}

export async function createDraftCart(request: APIRequestContext, customerId: number): Promise<number> {
  const response = await sendAdminRequest(request, ADMIN_SALES_COVERAGE.DRAFT_CARTS(customerId), { method: 'POST' });
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(201);
  expect(typeof body?.cartId).toBe('number');

  return body.cartId;
}

export async function createCustomerWithDraftCart(request: APIRequestContext): Promise<CoverageCart> {
  const customer = await createCustomer(request);
  const cartId = await createDraftCart(request, customer.id);

  return { customer, cartId };
}

export async function saleableProductIds(request: APIRequestContext): Promise<number[]> {
  const ids: number[] = [];
  const fixtureProductId = e2eFixtures().order?.productId;

  if (fixtureProductId) {
    ids.push(fixtureProductId);
  }

  const response = await sendAdminRequest(request, ADMIN_SALES_COVERAGE.CATALOG_PRODUCTS, {
    params: { type: 'simple', status: '1', per_page: '25', sort: 'id', order: 'desc' },
  });

  if (response.status() === 200) {
    for (const row of (await safeJson(response))?.data ?? []) {
      if (
        Number(row.quantity) >= 10
        && Number(row.price) > 0
      ) {
        ids.push(Number(row.id));
      }
    }
  }

  return [...new Set(ids)];
}

export async function addSaleableItem(request: APIRequestContext, cartId: number): Promise<{ productId: number; cartItemId: number; cart: any }> {
  for (const productId of await saleableProductIds(request)) {
    const response = await sendAdminRequest(request, ADMIN_SALES_COVERAGE.CART_ITEMS(cartId), {
      method: 'POST',
      data: { productId, quantity: 1 },
    });
    const body = await safeJson(response);

    if (
      response.status() === 201
      && body?.success === true
      && Array.isArray(body.items)
      && body.items.length > 0
    ) {
      return { productId, cartItemId: body.items[0].id, cart: body };
    }
  }

  test.skip(true, 'no saleable simple product with stock exists to add to a draft cart');

  throw new Error('unreachable');
}

export async function createCartWithItem(request: APIRequestContext): Promise<CoverageCartWithItem> {
  const draft = await createCustomerWithDraftCart(request);
  const { productId, cartItemId } = await addSaleableItem(request, draft.cartId);

  return { ...draft, productId, cartItemId };
}

export function billingAddress(customer: CoverageCustomer): Record<string, any> {
  return {
    firstName: customer.firstName,
    lastName: customer.lastName,
    email: customer.email,
    address: ['12 Coverage Street'],
    city: 'Berlin',
    country: 'DE',
    state: 'BE',
    postcode: '10115',
    phone: '+4930123456',
    useForShipping: true,
  };
}

export async function saveAddresses(request: APIRequestContext, cart: CoverageCart): Promise<void> {
  const response = await sendAdminRequest(request, ADMIN_SALES_COVERAGE.CART_ADDRESSES(cart.cartId), {
    method: 'POST',
    data: { billing: billingAddress(cart.customer) },
  });
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(201);
  expect(body?.success).toBe(true);
}

export async function availableShippingMethods(request: APIRequestContext, cartId: number): Promise<any[]> {
  const response = await sendAdminRequest(request, ADMIN_SALES_COVERAGE.CART_SHIPPING_METHODS(cartId));
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);

  const rates = Array.isArray(body?.data) ? body.data : [];

  test.skip(rates.length === 0, 'no shipping carrier offers a rate for the draft cart');

  return rates;
}

export async function selectShipping(request: APIRequestContext, cartId: number): Promise<string> {
  const [rate] = await availableShippingMethods(request, cartId);

  const response = await sendAdminRequest(request, ADMIN_SALES_COVERAGE.CART_SHIPPING_METHODS(cartId), {
    method: 'POST',
    data: { shippingMethod: rate.method },
  });
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(201);
  expect(body?.shippingMethod).toBe(rate.method);

  return rate.method;
}

export async function supportedPaymentMethod(request: APIRequestContext, cartId: number): Promise<string> {
  const response = await sendAdminRequest(request, ADMIN_SALES_COVERAGE.CART_PAYMENT_METHODS(cartId));
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);

  const methods: string[] = (body?.data ?? []).map((row: any) => row.method);
  const method = SUPPORTED_PAYMENT_METHODS.find(code => methods.includes(code));

  test.skip(!method, 'neither cashondelivery nor moneytransfer is enabled for admin-placed orders');

  return method as string;
}

export async function selectPayment(request: APIRequestContext, cartId: number): Promise<string> {
  const method = await supportedPaymentMethod(request, cartId);

  const response = await sendAdminRequest(request, ADMIN_SALES_COVERAGE.CART_PAYMENT_METHODS(cartId), {
    method: 'POST',
    data: { method },
  });
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(201);
  expect(body?.paymentMethod).toBe(method);

  return method;
}

export async function createCartReadyForShipping(request: APIRequestContext): Promise<CoverageCartWithItem> {
  const cart = await createCartWithItem(request);

  await saveAddresses(request, cart);

  return cart;
}

export async function createCartReadyForPayment(request: APIRequestContext): Promise<CoverageCartWithItem> {
  const cart = await createCartReadyForShipping(request);

  await selectShipping(request, cart.cartId);

  return cart;
}

export async function createCartReadyToPlace(request: APIRequestContext): Promise<CoverageCartWithItem> {
  const cart = await createCartReadyForPayment(request);

  await selectPayment(request, cart.cartId);

  return cart;
}

export async function placeOrder(request: APIRequestContext): Promise<CoverageOrder> {
  const cart = await createCartReadyToPlace(request);

  const response = await sendAdminRequest(request, ADMIN_SALES_COVERAGE.PLACE_ORDER(cart.cartId), { method: 'POST' });
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(201);
  expect(body?.success).toBe(true);
  expect(typeof body?.orderId).toBe('number');

  return { ...cart, orderId: body.orderId, incrementId: String(body.incrementId) };
}

export async function invoiceOrder(request: APIRequestContext, orderId: number): Promise<CoverageInvoice> {
  const orderResponse = await sendAdminRequest(request, ADMIN_SALES_COVERAGE.ORDER(orderId));
  const order = await safeJson(orderResponse);

  expect(orderResponse.status(), JSON.stringify(order)).toBe(200);

  const items = (order?.items ?? []).map((item: any) => ({ orderItemId: item.id, quantity: item.qtyOrdered }));

  expect(items.length).toBeGreaterThan(0);

  const response = await sendAdminRequest(request, ADMIN_SALES_COVERAGE.ORDER_INVOICES(orderId), {
    method: 'POST',
    data: { items },
  });
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(201);
  expect(typeof body?.id).toBe('number');

  return { id: body.id, baseGrandTotal: Number(body.baseGrandTotal), state: body.state };
}

export async function setInvoiceState(request: APIRequestContext, invoiceIds: number[], value: string): Promise<void> {
  const response = await sendAdminRequest(request, ADMIN_SALES_COVERAGE.INVOICES_MASS_UPDATE_STATUS, {
    method: 'POST',
    data: { indices: invoiceIds, value },
  });
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);
  expect(body?.updated).toEqual(invoiceIds);
}

export async function invoiceState(request: APIRequestContext, invoiceId: number): Promise<string> {
  const response = await sendAdminRequest(request, ADMIN_SALES_COVERAGE.INVOICE(invoiceId));
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);

  return body?.state;
}

export async function customerToken(request: APIRequestContext, customer: CoverageCustomer): Promise<string> {
  const response = await sendRestRequest(request, ADMIN_SALES_COVERAGE.SHOP_CUSTOMER_LOGIN, {
    method: 'POST',
    data: { email: customer.email, password: customer.password },
  });
  const body = await safeJson(response);

  expect([200, 201], JSON.stringify(body)).toContain(response.status());
  expect(typeof body?.token).toBe('string');

  return body.token;
}

async function customerGroupIds(request: APIRequestContext): Promise<number[]> {
  const response = await sendAdminRequest(request, ADMIN_SALES_COVERAGE.CUSTOMER_GROUPS, { params: { per_page: '100' } });
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);

  const ids = (body?.data ?? []).map((group: any) => group.id);
  expect(ids.length, JSON.stringify(body)).toBeGreaterThan(0);

  return ids;
}

export async function createCouponRule(request: APIRequestContext): Promise<CoverageCouponRule> {
  const stamp = uniqueStamp();
  const code = `COV${stamp}`;
  const groups = await customerGroupIds(request);

  const response = await sendAdminRequest(request, ADMIN_SALES_COVERAGE.CART_RULES, {
    method: 'POST',
    data: {
      name: `E2E coverage coupon ${stamp}`,
      description: 'e2e coverage coupon rule',
      channels: [DEFAULT_CHANNEL_ID],
      customer_groups: groups,
      coupon_type: 1,
      use_auto_generation: 0,
      coupon_code: code,
      action_type: 'by_percent',
      discount_amount: 10,
      sort_order: 0,
      status: 1,
      conditions: [],
      uses_per_customer: 0,
      uses_per_coupon: 0,
    },
  });
  const body = await safeJson(response);

  expect([200, 201], JSON.stringify(body)).toContain(response.status());
  expect(typeof body?.id).toBe('number');

  return { id: body.id, code };
}

export async function deleteCartRule(request: APIRequestContext, id: number): Promise<void> {
  try {
    await sendAdminRequest(request, ADMIN_SALES_COVERAGE.CART_RULE(id), { method: 'DELETE' });
  } catch {
    return;
  }
}

export async function euWithdrawalEnabledValue(request: APIRequestContext): Promise<string | null> {
  const response = await sendAdminRequest(request, ADMIN_SALES_COVERAGE.CONFIGURATION, {
    params: { slug: EU_WITHDRAWAL_SLUG, channel: DEFAULT_CHANNEL_CODE },
  });
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);

  const payload = Array.isArray(body) ? body[0] : body;

  return payload?.values?.[EU_WITHDRAWAL_ENABLED_KEY] ?? null;
}

export async function setEuWithdrawalEnabled(request: APIRequestContext, value: string): Promise<void> {
  const response = await sendAdminRequest(request, ADMIN_SALES_COVERAGE.CONFIGURATION, {
    method: 'POST',
    data: {
      slug: EU_WITHDRAWAL_SLUG,
      channel: DEFAULT_CHANNEL_CODE,
      locale: 'en',
      values: { [EU_WITHDRAWAL_ENABLED_KEY]: value },
    },
  });

  expect([200, 201], await response.text()).toContain(response.status());
}

export async function fileCustomerWithdrawal(request: APIRequestContext, order: CoverageOrder, reason: string): Promise<number> {
  const token = await customerToken(request, order.customer);

  const response = await sendRestRequest(request, ADMIN_SALES_COVERAGE.SHOP_EU_WITHDRAWALS, {
    method: 'POST',
    data: { order_id: order.orderId, reason_text: reason },
    headers: bearer(token),
  });
  const body = await safeJson(response);

  expect([200, 201], JSON.stringify(body)).toContain(response.status());
  expect(typeof body?.id).toBe('number');

  return body.id;
}

export async function placeOrderWithWithdrawal(request: APIRequestContext, reason: string): Promise<{ order: CoverageOrder; withdrawalId: number }> {
  const order = await placeOrder(request);
  const original = await euWithdrawalEnabledValue(request);

  if (original === '1') {
    return { order, withdrawalId: await fileCustomerWithdrawal(request, order, reason) };
  }

  await setEuWithdrawalEnabled(request, '1');

  try {
    return { order, withdrawalId: await fileCustomerWithdrawal(request, order, reason) };
  } finally {
    await setEuWithdrawalEnabled(request, original ?? '0');
  }
}
