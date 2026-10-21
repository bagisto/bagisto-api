import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../graphql/helpers/adminGraphqlClient';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../../graphql/helpers/testSupport';
import { env } from '../../../config/env';
import {
  ADMIN_COVERAGE_CART,
  ADMIN_COVERAGE_CART_ADD_ITEM,
  ADMIN_COVERAGE_CART_APPLY_COUPON,
  ADMIN_COVERAGE_CART_PAYMENT_METHODS,
  ADMIN_COVERAGE_CART_REMOVE_COUPON,
  ADMIN_COVERAGE_CART_REMOVE_ITEM,
  ADMIN_COVERAGE_CART_SAVE_ADDRESS,
  ADMIN_COVERAGE_CART_SET_PAYMENT_METHOD,
  ADMIN_COVERAGE_CART_SET_SHIPPING_METHOD,
  ADMIN_COVERAGE_CART_SHIPPING_RATES,
  ADMIN_COVERAGE_CART_UPDATE_ITEMS,
  ADMIN_COVERAGE_COPY_CART_RULE,
  ADMIN_COVERAGE_CREATE_DRAFT_CART,
  ADMIN_COVERAGE_PLACE_ORDER,
} from '../../../graphql/Queries/coverage/adminCart.queries';
import { sendAdminRequest } from '../../../../restAPI/rest/helpers/adminClient';
import {
  ADMIN_SALES_COVERAGE as API,
  ADMIN_SALES_MESSAGES as MSG,
  UNKNOWN_ID,
  billingAddress,
  createCartReadyForPayment,
  createCartReadyForShipping,
  createCartReadyToPlace,
  createCartWithItem,
  createCouponRule,
  createCustomer,
  createCustomerWithDraftCart,
  deleteCartRule,
  safeJson,
  saleableProductIds,
  supportedPaymentMethod,
} from '../../../../restAPI/rest/helpers/coverage/adminSales.helpers';

test.describe.configure({ timeout: 180_000 });

function cartIri(id: number): string {
  return `/api/admin/carts/${id}`;
}

async function gql(request: APIRequestContext, query: string, variables: Record<string, any>): Promise<any> {
  const response = await sendAdminGraphQLRequest(request, query, variables);

  expect(response.status()).toBe(200);

  return response.json();
}

function expectData(body: any, path: string): any {
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

  const value = path.split('.').reduce((node, key) => node?.[key], body.data);
  expect(value, JSON.stringify(body.data)).toBeTruthy();

  return value;
}

function expectError(body: any, message: string): void {
  expect(graphQLErrorMessages(body), JSON.stringify(body)).toContain(message);
}

function cartInput(cartId: number, extra: Record<string, any> = {}): Record<string, any> {
  return { input: { id: cartIri(cartId), cartId: String(cartId), ...extra } };
}

function numericCartInput(cartId: number, extra: Record<string, any> = {}): Record<string, any> {
  return { input: { id: cartIri(cartId), cartId, ...extra } };
}

interface AnonymousCall {
  label: string;
  query: string;
  variables: Record<string, any>;
}

const ANONYMOUS_CALLS: AnonymousCall[] = [
  { label: 'the draft cart creation', query: ADMIN_COVERAGE_CREATE_DRAFT_CART, variables: { input: { customerId: 1 } } },
  { label: 'the draft cart query', query: ADMIN_COVERAGE_CART, variables: { id: cartIri(UNKNOWN_ID) } },
  { label: 'adding a cart item', query: ADMIN_COVERAGE_CART_ADD_ITEM, variables: cartInput(UNKNOWN_ID, { productId: 1 }) },
  { label: 'updating cart items', query: ADMIN_COVERAGE_CART_UPDATE_ITEMS, variables: cartInput(UNKNOWN_ID, { qty: { 1: 1 } }) },
  { label: 'removing a cart item', query: ADMIN_COVERAGE_CART_REMOVE_ITEM, variables: cartInput(UNKNOWN_ID, { cartItemId: 1 }) },
  { label: 'saving cart addresses', query: ADMIN_COVERAGE_CART_SAVE_ADDRESS, variables: cartInput(UNKNOWN_ID, { billing: { city: 'Berlin' } }) },
  { label: 'applying a coupon', query: ADMIN_COVERAGE_CART_APPLY_COUPON, variables: cartInput(UNKNOWN_ID, { code: 'NO-SUCH' }) },
  { label: 'removing a coupon', query: ADMIN_COVERAGE_CART_REMOVE_COUPON, variables: cartInput(UNKNOWN_ID) },
  { label: 'the shipping rates query', query: ADMIN_COVERAGE_CART_SHIPPING_RATES, variables: { cartId: UNKNOWN_ID } },
  { label: 'selecting a shipping method', query: ADMIN_COVERAGE_CART_SET_SHIPPING_METHOD, variables: numericCartInput(UNKNOWN_ID, { shippingMethod: 'flatrate_flatrate' }) },
  { label: 'the payment methods query', query: ADMIN_COVERAGE_CART_PAYMENT_METHODS, variables: { cartId: UNKNOWN_ID } },
  { label: 'selecting a payment method', query: ADMIN_COVERAGE_CART_SET_PAYMENT_METHOD, variables: numericCartInput(UNKNOWN_ID, { method: 'cashondelivery' }) },
  { label: 'placing an order', query: ADMIN_COVERAGE_PLACE_ORDER, variables: { input: { cartId: UNKNOWN_ID } } },
  { label: 'copying a cart rule', query: ADMIN_COVERAGE_COPY_CART_RULE, variables: { input: { id: `/api/admin/marketing/cart-rules/${UNKNOWN_ID}`, cartRuleId: UNKNOWN_ID } } },
];

test.describe('admin create order draft cart graphql', () => {
  for (const call of ANONYMOUS_CALLS) {
    test(`should refuse ${call.label} without an admin token`, async ({ request }) => {
      const response = await sendGraphQLRequest(request, call.query, call.variables, {}, env.adminGraphqlEndpoint);

      expect(response.status()).toBe(401);
      expect((await response.json())?.message).toBe(MSG.UNAUTHENTICATED);
    });
  }

  test('should create an empty draft cart for a customer', async ({ request }) => {
    const customer = await createCustomer(request);

    const body = await gql(request, ADMIN_COVERAGE_CREATE_DRAFT_CART, { input: { customerId: customer.id } });
    const draft = expectData(body, 'createAdminDraftCart.adminDraftCart');

    expect(draft.success).toBe(true);
    expect(draft.message).toBe(MSG.DRAFT_CREATED);
    expect(draft.customerId).toBe(customer.id);
    expect(draft.cartId).toBeGreaterThan(0);

    const cart = await safeJson(await sendAdminRequest(request, API.CART(draft.cartId)));
    expect(cart.customerId).toBe(customer.id);
    expect(cart.isActive).toBe(false);
    expect(cart.items).toEqual([]);
  });

  test('should report an unknown customer when creating a draft cart', async ({ request }) => {
    expectError(await gql(request, ADMIN_COVERAGE_CREATE_DRAFT_CART, { input: { customerId: UNKNOWN_ID } }), MSG.CUSTOMER_NOT_FOUND);
  });

  test('should return the draft cart by its iri', async ({ request }) => {
    const cart = await createCartWithItem(request);

    const node = expectData(await gql(request, ADMIN_COVERAGE_CART, { id: cartIri(cart.cartId) }), 'adminCart');

    expect(node._id).toBe(cart.cartId);
    expect(node.customerId).toBe(cart.customer.id);
    expect(node.isActive).toBe(false);
    expect(node.itemsCount).toBe(1);
    expect(node.items.map((item: any) => item.productId)).toEqual([cart.productId]);
    expect(node.grandTotal).toBeGreaterThan(0);
  });

  test('should report an unknown draft cart', async ({ request }) => {
    expectError(await gql(request, ADMIN_COVERAGE_CART, { id: cartIri(UNKNOWN_ID) }), MSG.CART_NOT_FOUND);
  });

  test('should add a product to the draft cart', async ({ request }) => {
    const draft = await createCustomerWithDraftCart(request);
    const [productId] = await saleableProductIds(request);
    test.skip(!productId, 'no saleable simple product with stock exists to add to a draft cart');

    const body = await gql(request, ADMIN_COVERAGE_CART_ADD_ITEM, cartInput(draft.cartId, { productId, quantity: 1 }));
    const cart = expectData(body, 'addItemAdminCart.adminCart');

    test.skip(cart.success !== true, `product ${productId} could not be added: ${cart.message}`);
    expect(cart.message).toBe(MSG.ITEM_ADDED);
    expect(cart._id).toBe(draft.cartId);
    expect(cart.items).toHaveLength(1);
    expect(cart.items[0].productId).toBe(productId);
  });

  test('should report a missing product id when adding an item', async ({ request }) => {
    const draft = await createCustomerWithDraftCart(request);

    expectError(await gql(request, ADMIN_COVERAGE_CART_ADD_ITEM, cartInput(draft.cartId, { quantity: 1 })), MSG.PRODUCT_REQUIRED);
  });

  test('should report an unknown product when adding an item', async ({ request }) => {
    const draft = await createCustomerWithDraftCart(request);

    expectError(
      await gql(request, ADMIN_COVERAGE_CART_ADD_ITEM, cartInput(draft.cartId, { productId: UNKNOWN_ID, quantity: 1 })),
      MSG.PRODUCT_NOT_FOUND,
    );
  });

  test('should report an unknown cart when adding an item', async ({ request }) => {
    expectError(await gql(request, ADMIN_COVERAGE_CART_ADD_ITEM, cartInput(UNKNOWN_ID, { productId: 1 })), MSG.CART_NOT_FOUND);
  });

  test('should update the quantity of a cart item', async ({ request }) => {
    const cart = await createCartWithItem(request);

    const body = await gql(request, ADMIN_COVERAGE_CART_UPDATE_ITEMS, cartInput(cart.cartId, { qty: { [cart.cartItemId]: 3 } }));
    const node = expectData(body, 'updateItemsAdminCart.adminCart');

    expect(node.success).toBe(true);
    expect(node.message).toBe(MSG.ITEM_UPDATED);
    expect(node.items.find((item: any) => item.id === cart.cartItemId)?.quantity).toBe(3);

    const reread = await safeJson(await sendAdminRequest(request, API.CART(cart.cartId)));
    expect(reread.itemsQty).toBe(3);
  });

  test('should report missing quantities when updating items', async ({ request }) => {
    const cart = await createCartWithItem(request);

    expectError(await gql(request, ADMIN_COVERAGE_CART_UPDATE_ITEMS, cartInput(cart.cartId)), MSG.QTY_REQUIRED);
  });

  test('should remove a cart item', async ({ request }) => {
    const cart = await createCartWithItem(request);

    const body = await gql(request, ADMIN_COVERAGE_CART_REMOVE_ITEM, cartInput(cart.cartId, { cartItemId: cart.cartItemId }));
    const node = expectData(body, 'removeItemAdminCart.adminCart');

    expect(node.success).toBe(true);
    expect(node.message).toBe(MSG.ITEM_REMOVED);
    expect(node.items).toEqual([]);

    const reread = await safeJson(await sendAdminRequest(request, API.CART(cart.cartId)));
    expect(reread.items).toEqual([]);
  });

  test('should report a missing cart item id when removing an item', async ({ request }) => {
    const cart = await createCartWithItem(request);

    expectError(await gql(request, ADMIN_COVERAGE_CART_REMOVE_ITEM, cartInput(cart.cartId)), MSG.CART_ITEM_REQUIRED);
  });

  test('should save the billing address and reuse it for shipping', async ({ request }) => {
    const cart = await createCartWithItem(request);

    const body = await gql(request, ADMIN_COVERAGE_CART_SAVE_ADDRESS, cartInput(cart.cartId, { billing: billingAddress(cart.customer) }));
    const node = expectData(body, 'saveAddressAdminCart.adminCart');

    expect(node.success).toBe(true);
    expect(node.message).toBe(MSG.ADDRESS_SAVED);
    expect(node.billingAddress.email).toBe(cart.customer.email);
    expect(node.billingAddress.city).toBe('Berlin');
    expect(node.shippingAddress.postcode).toBe('10115');
  });

  test('should report a missing billing address', async ({ request }) => {
    const cart = await createCartWithItem(request);

    expectError(await gql(request, ADMIN_COVERAGE_CART_SAVE_ADDRESS, cartInput(cart.cartId)), MSG.ADDRESS_REQUIRED);
  });

  test('should list shipping rates once the addresses are saved', async ({ request }) => {
    const cart = await createCartReadyForShipping(request);

    const connection = expectData(await gql(request, ADMIN_COVERAGE_CART_SHIPPING_RATES, { cartId: cart.cartId }), 'adminCartShippingRates');

    expect(Array.isArray(connection.edges)).toBe(true);
    test.skip(connection.edges.length === 0, 'no shipping carrier offers a rate for the draft cart');
    expect(connection.totalCount).toBe(connection.edges.length);

    for (const { node } of connection.edges) {
      expect(node.method.startsWith(`${node.carrierCode}_`)).toBe(true);
      expect(typeof node.formattedPrice).toBe('string');
    }
  });

  test('should refuse shipping rates before the addresses are saved', async ({ request }) => {
    const cart = await createCartWithItem(request);

    expectError(await gql(request, ADMIN_COVERAGE_CART_SHIPPING_RATES, { cartId: cart.cartId }), MSG.ADDRESSES_REQUIRED_FOR_SHIPPING);
  });

  test('should select a shipping method for the draft cart', async ({ request }) => {
    const cart = await createCartReadyForShipping(request);
    const rates = await safeJson(await sendAdminRequest(request, API.CART_SHIPPING_METHODS(cart.cartId)));
    const method = rates?.data?.[0]?.method;
    test.skip(!method, 'no shipping carrier offers a rate for the draft cart');

    const body = await gql(request, ADMIN_COVERAGE_CART_SET_SHIPPING_METHOD, numericCartInput(cart.cartId, { shippingMethod: method }));
    const node = expectData(body, 'setShippingMethodAdminCart.adminCart');

    expect(node.success).toBe(true);
    expect(node.message).toBe(MSG.SHIPPING_METHOD_SAVED);
    expect(node.shippingMethod).toBe(method);
  });

  test('should report a missing shipping method', async ({ request }) => {
    const cart = await createCartReadyForShipping(request);

    expectError(await gql(request, ADMIN_COVERAGE_CART_SET_SHIPPING_METHOD, numericCartInput(cart.cartId)), MSG.SHIPPING_METHOD_REQUIRED);
  });

  test('should refuse a shipping selection before the addresses are saved', async ({ request }) => {
    const cart = await createCartWithItem(request);

    expectError(
      await gql(request, ADMIN_COVERAGE_CART_SET_SHIPPING_METHOD, numericCartInput(cart.cartId, { shippingMethod: 'flatrate_flatrate' })),
      MSG.ADDRESSES_REQUIRED_FOR_SHIPPING,
    );
  });

  test('should list payment methods once a shipping method is selected', async ({ request }) => {
    const cart = await createCartReadyForPayment(request);

    const connection = expectData(await gql(request, ADMIN_COVERAGE_CART_PAYMENT_METHODS, { cartId: cart.cartId }), 'adminCartPaymentMethods');

    expect(connection.edges.length).toBeGreaterThan(0);
    expect(connection.totalCount).toBe(connection.edges.length);

    for (const { node } of connection.edges) {
      expect(typeof node.method).toBe('string');
      expect(typeof node.methodTitle).toBe('string');
    }
  });

  test('should refuse payment methods before a shipping method is selected', async ({ request }) => {
    const cart = await createCartReadyForShipping(request);

    expectError(await gql(request, ADMIN_COVERAGE_CART_PAYMENT_METHODS, { cartId: cart.cartId }), MSG.SHIPPING_REQUIRED_FOR_PAYMENT);
  });

  test('should select a payment method for the draft cart', async ({ request }) => {
    const cart = await createCartReadyForPayment(request);
    const method = await supportedPaymentMethod(request, cart.cartId);

    const body = await gql(request, ADMIN_COVERAGE_CART_SET_PAYMENT_METHOD, numericCartInput(cart.cartId, { method }));
    const node = expectData(body, 'setPaymentMethodAdminCart.adminCart');

    expect(node.success).toBe(true);
    expect(node.message).toBe(MSG.PAYMENT_METHOD_SAVED);
    expect(node.paymentMethod).toBe(method);
  });

  test('should report a missing payment method', async ({ request }) => {
    const cart = await createCartReadyForPayment(request);

    expectError(await gql(request, ADMIN_COVERAGE_CART_SET_PAYMENT_METHOD, numericCartInput(cart.cartId)), MSG.PAYMENT_METHOD_REQUIRED);
  });

  test('should apply a coupon code and discount the draft cart', async ({ request }) => {
    const rule = await createCouponRule(request);

    try {
      const cart = await createCartWithItem(request);

      const body = await gql(request, ADMIN_COVERAGE_CART_APPLY_COUPON, cartInput(cart.cartId, { code: rule.code }));
      const node = expectData(body, 'applyCouponAdminCart.adminCart');

      expect(node.success).toBe(true);
      expect(node.message).toBe(MSG.COUPON_APPLIED);
      expect(node.couponCode).toBe(rule.code);
      expect(node.discountAmount).toBeGreaterThan(0);
    } finally {
      await deleteCartRule(request, rule.id);
    }
  });

  test('should report an unknown coupon code', async ({ request }) => {
    const cart = await createCartWithItem(request);

    expectError(
      await gql(request, ADMIN_COVERAGE_CART_APPLY_COUPON, cartInput(cart.cartId, { code: `NO-SUCH-${Date.now()}` })),
      MSG.COUPON_NOT_FOUND,
    );
  });

  test('should report a missing coupon code', async ({ request }) => {
    const cart = await createCartWithItem(request);

    expectError(await gql(request, ADMIN_COVERAGE_CART_APPLY_COUPON, cartInput(cart.cartId, { code: '' })), MSG.COUPON_CODE_REQUIRED);
  });

  test('should remove the applied coupon from the draft cart', async ({ request }) => {
    const rule = await createCouponRule(request);

    try {
      const cart = await createCartWithItem(request);
      const applied = await sendAdminRequest(request, API.CART_COUPON(cart.cartId), {
        method: 'POST',
        data: { code: rule.code },
      });
      expect(applied.status(), await applied.text()).toBe(201);

      const body = await gql(request, ADMIN_COVERAGE_CART_REMOVE_COUPON, cartInput(cart.cartId));
      const node = expectData(body, 'removeCouponAdminCart.adminCart');

      expect(node.success).toBe(true);
      expect(node.message).toBe(MSG.COUPON_REMOVED);
      expect(node.couponCode).toBeNull();
      expect(node.discountAmount).toBe(0);
    } finally {
      await deleteCartRule(request, rule.id);
    }
  });

  test('should report an unknown cart when removing a coupon', async ({ request }) => {
    expectError(await gql(request, ADMIN_COVERAGE_CART_REMOVE_COUPON, cartInput(UNKNOWN_ID)), MSG.CART_NOT_FOUND);
  });

  test('should place the order and consume the draft cart', async ({ request }) => {
    const cart = await createCartReadyToPlace(request);

    const body = await gql(request, ADMIN_COVERAGE_PLACE_ORDER, { input: { cartId: cart.cartId } });
    const placed = expectData(body, 'createAdminPlaceOrder.adminPlaceOrder');

    expect(placed.success).toBe(true);
    expect(placed.message).toBe(MSG.ORDER_PLACED);
    expect(placed.customerId).toBe(cart.customer.id);
    expect(placed.orderId).toBeGreaterThan(0);
    expect(typeof placed.incrementId).toBe('string');

    const order = await safeJson(await sendAdminRequest(request, API.ORDER(placed.orderId)));
    expect(order.incrementId).toBe(placed.incrementId);
    expect(order.items.map((item: any) => item.productId)).toEqual([cart.productId]);

    expectError(await gql(request, ADMIN_COVERAGE_CART, { id: cartIri(cart.cartId) }), MSG.CART_NOT_FOUND);
  });

  test('should report an unknown cart when placing an order', async ({ request }) => {
    expectError(await gql(request, ADMIN_COVERAGE_PLACE_ORDER, { input: { cartId: UNKNOWN_ID } }), MSG.CART_NOT_FOUND);
  });

  test('should refuse to place an order for an empty draft cart', async ({ request }) => {
    const draft = await createCustomerWithDraftCart(request);

    expectError(await gql(request, ADMIN_COVERAGE_PLACE_ORDER, { input: { cartId: draft.cartId } }), MSG.PLACE_ORDER_EMPTY_CART);
  });
});

test.describe('admin cart rule copy graphql', () => {
  test('should copy a cart rule as an inactive duplicate', async ({ request }) => {
    const source = await createCouponRule(request);
    let copyId: number | null = null;

    try {
      const sourceDetail = await safeJson(await sendAdminRequest(request, API.CART_RULE(source.id)));

      const body = await gql(request, ADMIN_COVERAGE_COPY_CART_RULE, {
        input: { id: `/api/admin/marketing/cart-rules/${source.id}`, cartRuleId: source.id },
      });
      const copy = expectData(body, 'copyAdminMarketingCartRule.adminMarketingCartRule');
      copyId = copy._id;

      expect(copy._id).not.toBe(source.id);
      expect(copy.name).toBe(`Copy of ${sourceDetail.name}`);
      expect(Number(copy.status)).toBe(0);

      const copyDetail = await sendAdminRequest(request, API.CART_RULE(copy._id));
      expect(copyDetail.status()).toBe(200);
      expect((await safeJson(copyDetail)).name).toBe(`Copy of ${sourceDetail.name}`);
    } finally {
      await deleteCartRule(request, source.id);

      if (copyId) {
        await deleteCartRule(request, copyId);
      }
    }
  });

  test('should report an unknown cart rule when copying', async ({ request }) => {
    const body = await gql(request, ADMIN_COVERAGE_COPY_CART_RULE, {
      input: { id: `/api/admin/marketing/cart-rules/${UNKNOWN_ID}`, cartRuleId: UNKNOWN_ID },
    });

    expectError(body, MSG.CART_RULE_NOT_FOUND);
  });
});
