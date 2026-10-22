import { APIRequestContext, APIResponse, expect, test } from '@playwright/test';
import { randomUUID } from 'crypto';
import { sendRestRequest } from '../restClient';

export const SHOP_CART_ENDPOINTS = {
  CUSTOMERS: '/api/shop/customers',
  CUSTOMER_LOGIN: '/api/shop/customer/login',
  PRODUCTS: '/api/shop/products',
  PRODUCT: (id: number | string) => `/api/shop/products/${id}`,
  CART_TOKENS: '/api/shop/cart-tokens',
  READ_CART: '/api/shop/cart',
  ADD_PRODUCT_IN_CART: '/api/shop/add-product-in-cart',
  CART_CROSS_SELLS: '/api/shop/cart/cross-sells',
  REMOVE_CART_ITEMS: '/api/shop/remove-cart-items',
  MOVE_TO_WISHLISTS: '/api/shop/move-to-wishlists',
  MERGE_CARTS: '/api/shop/merge-carts',
  VERIFY_TOKENS: '/api/shop/verify-tokens',
  CHECKOUT_ADDRESSES: '/api/shop/checkout-addresses',
  CHECKOUT_SHIPPING_METHODS: '/api/shop/checkout-shipping-methods',
  PAYMENT_METHODS: '/api/shop/payment-methods',
  CHECKOUT_PAYMENT_METHODS: '/api/shop/checkout-payment-methods',
  CHECKOUT_ORDERS: '/api/shop/checkout-orders',
  CHECKOUT_MESSAGE: (orderId: number | string) => `/api/shop/checkout-messages/${orderId}`,
  CHECKOUT_PAYMENTS_INITIATE: '/api/shop/checkout-payments/initiate',
  CHECKOUT_PAYMENTS_CONFIRM: '/api/shop/checkout-payments/confirm',
  COMPARE_ITEMS: '/api/shop/compare_items',
  COMPARE_ITEM: (id: number | string) => `/api/shop/compare_items/${id}`,
  DELETE_ALL_COMPARE_ITEMS: '/api/shop/delete-all-compare-items',
  WISHLISTS: '/api/shop/wishlists',
  WISHLIST: (id: number | string) => `/api/shop/wishlists/${id}`,
  DELETE_ALL_WISHLISTS: '/api/shop/delete-all-wishlists',
  MOVE_WISHLIST_TO_CARTS: '/api/shop/move-wishlist-to-carts',
};

export const SHOP_CART_MESSAGES = {
  AUTHENTICATION_REQUIRED: 'Authentication token is required',
  INVALID_CART_TOKEN: 'Invalid or expired authentication token',
  CART_NOT_FOUND: 'Cart not found',
  GUEST_CART_NOT_FOUND: 'Guest cart not found',
  MERGE_REQUIRES_AUTH: 'Guest merge requires authentication',
  GUEST_CART_MERGED: 'Guest cart merged successfully',
  NEW_GUEST_CART: 'New guest cart created with unique session token',
  AUTHENTICATED_CART: 'Using authenticated customer cart',
  ITEM_IDS_REQUIRED: 'Item IDs array is required',
  CART_ITEM_NOT_FOUND: 'Cart item not found',
  ADDRESS_DATA_REQUIRED: 'Country, state, and postcode are required',
  TOKEN_IS_VALID: 'Token is valid',
  UNAUTHENTICATED: 'Unauthenticated. Please login to perform this action',
  INVALID_PAYMENT_METHOD: 'Invalid or unavailable payment method',
  ORDER_REDIRECT_REQUIRED:
    'This payment method requires the shopper to complete payment on the gateway. Send them to redirectUrl; the order is created once the gateway confirms the payment.',
  STRIPE_INVALID_SESSION: 'Payment session is invalid.',
  STRIPE_SESSION_INVALID: 'Payment session has expired or is invalid.',
  ORDER_NOT_FOUND: (orderId: number | string) => `Customer order with ID "${orderId}" not found`,
  COMPARE_ITEM_NOT_FOUND: 'Compare item not found',
  COMPARE_PRODUCT_ID_REQUIRED: 'Product ID is required',
  COMPARE_PRODUCT_NOT_FOUND: 'Product not found',
  COMPARE_ALREADY_EXISTS: 'This product is already in your comparison list',
  COMPARE_DELETE_ALL_SUCCESS: 'All compare items have been removed successfully',
  CANNOT_UPDATE_OTHER_PROFILE: 'Unauthorized: Cannot update another customer profile',
  WISHLIST_NOT_FOUND: 'Wishlist item not found',
  WISHLIST_INVALID_QUANTITY: 'Quantity must be greater than 0',
  WISHLIST_MOVED_TO_CART: 'Item moved to cart successfully',
  WISHLIST_DELETE_ALL_SUCCESS: 'All wishlist items have been removed successfully',
  ADDRESS_NOT_FOUND: 'Address not found or does not belong to this customer',
  ADDRESS_INVALID_TOKEN: 'Invalid or expired token',
  ADDRESS_FIELD_REQUIRED: (field: string) => `The ${field} field is required`,
  CHECKOUT_FIELD_REQUIRED: (field: string) => `The ${field} field is required.`,
};

export const SHOP_CART_OFFLINE_PAYMENT_METHODS = ['moneytransfer', 'cashondelivery'];

export interface ShopCustomer {
  id: number;
  email: string;
  firstName: string;
  lastName: string;
  token: string;
}

export interface ShopGuestCart {
  token: string;
  cartId: number;
}

export interface ShopCartLine {
  cartId: number;
  itemId: number;
  productId: number;
  quantity: number;
}

export interface ShopCrossSellSource {
  productId: number;
  crossSellIds: number[];
}

export interface ShopPlacedOrder {
  orderId: number;
  cartId: number;
}

export interface ShopCheckoutBilling {
  billingFirstName: string;
  billingLastName: string;
  billingEmail: string;
  billingAddress: string;
  billingCity: string;
  billingCountry: string;
  billingState: string;
  billingPostcode: string;
  billingPhoneNumber: string;
  useForShipping: boolean;
}

let saleableSimpleProductIds: number[] | undefined;

let crossSellSource: ShopCrossSellSource | null | undefined;

export function uniqueSuffix(): string {
  return `${Date.now()}${Math.floor(Math.random() * 100000)}`;
}

export function lettersFrom(value: string): string {
  return value.replace(/\d/g, (digit) => 'abcdefghij'[Number(digit)]);
}

export function bearer(token: string): Record<string, string> {
  return { Authorization: `Bearer ${token}` };
}

export function randomUuid(): string {
  return randomUUID();
}

export function referenceId(value: unknown): number {
  if (value && typeof value === 'object') {
    const record = value as Record<string, unknown>;

    return Number(String(record._id ?? record.id ?? '').split('/').pop());
  }

  return Number(String(value ?? '').split('/').pop());
}

export function buildBilling(suffix: string): ShopCheckoutBilling {
  return {
    billingFirstName: `Checkout${lettersFrom(suffix)}`,
    billingLastName: 'Tester',
    billingEmail: `shop.checkout.${suffix}@playwrighttest.local`,
    billingAddress: `${suffix} Checkout Street`,
    billingCity: 'Los Angeles',
    billingCountry: 'US',
    billingState: 'CA',
    billingPostcode: '90001',
    billingPhoneNumber: '2125551234',
    useForShipping: true,
  };
}

export async function readJson(response: APIResponse): Promise<any> {
  const text = await response.text();

  try {
    return text === '' ? null : JSON.parse(text);
  } catch {
    return { raw: text };
  }
}

export async function expectProblem(response: APIResponse, status: number, detail: string): Promise<void> {
  const body = await readJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(status);
  expect(body?.detail, JSON.stringify(body)).toBe(detail);
}

export async function registerShopCustomer(request: APIRequestContext): Promise<ShopCustomer> {
  const suffix = uniqueSuffix();
  const email = `shop.cart.${suffix}@playwrighttest.local`;
  const password = `Cart${suffix}!Aa`;
  const firstName = 'Cart';
  const lastName = `Tester${lettersFrom(suffix)}`;

  const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.CUSTOMERS, {
    method: 'POST',
    data: { firstName, lastName, email, password, confirmPassword: password },
  });
  const body = await readJson(response);

  expect(response.status(), `customer registration failed: ${JSON.stringify(body)}`).toBe(201);

  let token = typeof body?.token === 'string' ? body.token : '';

  if (!token) {
    const login = await sendRestRequest(request, SHOP_CART_ENDPOINTS.CUSTOMER_LOGIN, {
      method: 'POST',
      data: { email, password },
    });
    const loginBody = await readJson(login);

    token = typeof loginBody?.token === 'string' ? loginBody.token : '';
  }

  expect(token, `no customer token after registering ${email}: ${JSON.stringify(body)}`).toContain('|');

  return { id: Number(body.id), email, firstName, lastName, token };
}

export async function createGuestCart(request: APIRequestContext): Promise<ShopGuestCart> {
  const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.CART_TOKENS, {
    method: 'POST',
    data: { createNew: true },
  });
  const body = await readJson(response);

  expect(response.status(), `guest cart creation failed: ${JSON.stringify(body)}`).toBe(201);
  expect(typeof body?.sessionToken, JSON.stringify(body)).toBe('string');

  return { token: body.sessionToken, cartId: Number(body.id) };
}

export async function addProductToCart(
  request: APIRequestContext,
  token: string,
  productId: number,
  quantity = 1
): Promise<APIResponse> {
  return sendRestRequest(request, SHOP_CART_ENDPOINTS.ADD_PRODUCT_IN_CART, {
    method: 'POST',
    data: { productId, quantity },
    headers: bearer(token),
  });
}

export async function addCartLine(
  request: APIRequestContext,
  token: string,
  productId: number,
  quantity = 1
): Promise<ShopCartLine> {
  const response = await addProductToCart(request, token, productId, quantity);
  const body = await readJson(response);

  expect(response.status(), `adding product ${productId} failed: ${JSON.stringify(body)}`).toBe(201);

  const item = (body.items ?? []).find((line: any) => Number(line.productId) === productId);

  expect(item, `product ${productId} missing from the cart: ${JSON.stringify(body)}`).toBeTruthy();

  return {
    cartId: Number(body.id),
    itemId: Number(item.id),
    productId,
    quantity: Number(item.quantity),
  };
}

export async function readCart(request: APIRequestContext, token: string): Promise<APIResponse> {
  return sendRestRequest(request, SHOP_CART_ENDPOINTS.READ_CART, {
    method: 'POST',
    data: {},
    headers: bearer(token),
  });
}

export async function listProductIds(request: APIRequestContext, params: Record<string, string>): Promise<number[]> {
  const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.PRODUCTS, { params });
  const body = await readJson(response);

  expect(response.status(), `listing products failed: ${JSON.stringify(body)}`).toBe(200);

  const rows = Array.isArray(body) ? body : body?.data ?? [];

  return rows.map((row: any) => Number(row.id)).filter((id: number) => id > 0);
}

export async function findSaleableSimpleProducts(request: APIRequestContext, count: number): Promise<number[]> {
  if (saleableSimpleProductIds && saleableSimpleProductIds.length >= count) {
    return saleableSimpleProductIds.slice(0, count);
  }

  const candidates = await listProductIds(request, { type: 'simple', per_page: '40' });
  const probe = await createGuestCart(request);
  const found: number[] = [];

  for (const productId of candidates) {
    if (found.length >= Math.max(count, 3)) {
      break;
    }

    const response = await addProductToCart(request, probe.token, productId, 1);

    if (response.status() === 201) {
      found.push(productId);
    }
  }

  saleableSimpleProductIds = found;

  test.skip(found.length < count, `the catalog needs ${count} saleable simple products, found ${found.length}`);

  return found.slice(0, count);
}

export async function findCrossSellSource(request: APIRequestContext): Promise<ShopCrossSellSource | null> {
  if (crossSellSource !== undefined) {
    return crossSellSource;
  }

  const candidates = await listProductIds(request, { type: 'simple', per_page: '40' });
  const probe = await createGuestCart(request);

  crossSellSource = null;

  for (const productId of candidates.slice(0, 25)) {
    const detail = await sendRestRequest(request, SHOP_CART_ENDPOINTS.PRODUCT(productId));

    if (detail.status() !== 200) {
      continue;
    }

    const body = await readJson(detail);
    const crossSellIds = (body?.crossSells ?? [])
      .map((product: any) => referenceId(product))
      .filter((id: number) => id > 0 && id !== productId);

    if (crossSellIds.length === 0) {
      continue;
    }

    const added = await addProductToCart(request, probe.token, productId, 1);

    if (added.status() === 201) {
      crossSellSource = { productId, crossSellIds };

      break;
    }
  }

  return crossSellSource;
}

export async function saveCheckoutAddress(
  request: APIRequestContext,
  token: string,
  billing: ShopCheckoutBilling
): Promise<void> {
  const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.CHECKOUT_ADDRESSES, {
    method: 'POST',
    data: { ...billing },
    headers: bearer(token),
  });
  const body = await readJson(response);

  expect(response.status(), `saving the checkout address failed: ${JSON.stringify(body)}`).toBe(201);
  expect(body?.success, JSON.stringify(body)).toBe(true);
}

export async function chooseFirstShippingMethod(request: APIRequestContext, token: string): Promise<string> {
  const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.CHECKOUT_SHIPPING_METHODS, {
    headers: bearer(token),
  });
  const body = await readJson(response);

  expect(response.status(), `listing shipping methods failed: ${JSON.stringify(body)}`).toBe(200);

  const methods: string[] = (Array.isArray(body) ? body : []).map((rate: any) => rate.method).filter(Boolean);

  test.skip(methods.length === 0, 'no shipping carrier is available for a US address');

  const save = await sendRestRequest(request, SHOP_CART_ENDPOINTS.CHECKOUT_SHIPPING_METHODS, {
    method: 'POST',
    data: { shippingMethod: methods[0] },
    headers: bearer(token),
  });
  const saveBody = await readJson(save);

  expect(save.status(), `saving shipping method ${methods[0]} failed: ${JSON.stringify(saveBody)}`).toBe(201);
  expect(saveBody?.success, JSON.stringify(saveBody)).toBe(true);

  return methods[0];
}

export async function listPaymentMethodCodes(request: APIRequestContext, token: string): Promise<string[]> {
  const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.PAYMENT_METHODS, {
    headers: bearer(token),
  });
  const body = await readJson(response);

  expect(response.status(), `listing payment methods failed: ${JSON.stringify(body)}`).toBe(200);

  return (Array.isArray(body) ? body : []).map((method: any) => String(method.method ?? method.id));
}

export async function choosePaymentMethod(request: APIRequestContext, token: string, method: string): Promise<void> {
  const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.CHECKOUT_PAYMENT_METHODS, {
    method: 'POST',
    data: { paymentMethod: method },
    headers: bearer(token),
  });
  const body = await readJson(response);

  expect(response.status(), `saving payment method ${method} failed: ${JSON.stringify(body)}`).toBe(201);
  expect(body?.success, JSON.stringify(body)).toBe(true);
}

export async function placeCustomerOrder(
  request: APIRequestContext,
  customer: ShopCustomer,
  productId: number
): Promise<ShopPlacedOrder> {
  const line = await addCartLine(request, customer.token, productId, 1);

  await saveCheckoutAddress(request, customer.token, buildBilling(uniqueSuffix()));
  await chooseFirstShippingMethod(request, customer.token);

  const available = await listPaymentMethodCodes(request, customer.token);
  const offline = SHOP_CART_OFFLINE_PAYMENT_METHODS.find((code) => available.includes(code));

  test.skip(!offline, `no offline payment method is enabled (available: ${available.join(', ')})`);

  await choosePaymentMethod(request, customer.token, offline!);

  const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.CHECKOUT_ORDERS, {
    method: 'POST',
    data: {},
    headers: bearer(customer.token),
  });
  const body = await readJson(response);

  expect(response.status(), `placing the order failed: ${JSON.stringify(body)}`).toBe(201);
  expect(body?.redirect, JSON.stringify(body)).toBe(false);

  const orderId = Number(body?.orderId);

  expect(orderId, JSON.stringify(body)).toBeGreaterThan(0);

  return { orderId, cartId: line.cartId };
}

export async function addWishlistItem(
  request: APIRequestContext,
  customer: ShopCustomer,
  productId: number
): Promise<number> {
  const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.WISHLISTS, {
    method: 'POST',
    data: { productId },
    headers: bearer(customer.token),
  });
  const body = await readJson(response);

  expect(response.status(), `adding product ${productId} to the wishlist failed: ${JSON.stringify(body)}`).toBe(201);
  expect(typeof body?.id, JSON.stringify(body)).toBe('number');

  return Number(body.id);
}

export async function listWishlistProductIds(request: APIRequestContext, customer: ShopCustomer): Promise<number[]> {
  const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.WISHLISTS, {
    headers: bearer(customer.token),
  });
  const body = await readJson(response);

  expect(response.status(), `listing the wishlist failed: ${JSON.stringify(body)}`).toBe(200);
  expect(Array.isArray(body), JSON.stringify(body)).toBe(true);

  return body.map((item: any) => referenceId(item.product));
}

export async function addCompareItem(
  request: APIRequestContext,
  customer: ShopCustomer,
  productId: number
): Promise<number> {
  const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.COMPARE_ITEMS, {
    method: 'POST',
    data: { productId },
    headers: bearer(customer.token),
  });
  const body = await readJson(response);

  expect(response.status(), `adding product ${productId} to compare failed: ${JSON.stringify(body)}`).toBe(201);
  expect(typeof body?.id, JSON.stringify(body)).toBe('number');

  return Number(body.id);
}

export async function listCompareItemIds(request: APIRequestContext, customer: ShopCustomer): Promise<number[]> {
  const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.COMPARE_ITEMS, {
    headers: bearer(customer.token),
  });
  const body = await readJson(response);

  expect(response.status(), `listing compare items failed: ${JSON.stringify(body)}`).toBe(200);
  expect(Array.isArray(body), JSON.stringify(body)).toBe(true);

  return body.map((item: any) => Number(item.id));
}
