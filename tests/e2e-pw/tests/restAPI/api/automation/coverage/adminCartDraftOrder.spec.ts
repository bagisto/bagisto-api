import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../rest/helpers/adminClient';
import { sendRestRequest } from '../../../rest/helpers/restClient';
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
  createCustomerWithDraftCart,
  deleteCartRule,
  expectProblem,
  expectUnauthenticated,
  safeJson,
  saleableProductIds,
  selectShipping,
  supportedPaymentMethod,
} from '../../../rest/helpers/coverage/adminSales.helpers';

test.describe.configure({ timeout: 180_000 });

type Method = 'GET' | 'POST' | 'PUT' | 'DELETE';

interface CartCall {
  label: string;
  method: Method;
  path: (id: number) => string;
  data?: Record<string, any>;
}

const CART_CALLS: CartCall[] = [
  { label: 'cart detail', method: 'GET', path: API.CART },
  { label: 'add item', method: 'POST', path: API.CART_ITEMS, data: { productId: 1, quantity: 1 } },
  { label: 'update items', method: 'PUT', path: API.CART_ITEMS, data: { qty: { 1: 1 } } },
  { label: 'remove item', method: 'DELETE', path: API.CART_ITEMS, data: { cartItemId: 1 } },
  { label: 'save addresses', method: 'POST', path: API.CART_ADDRESSES, data: { billing: { firstName: 'A' } } },
  { label: 'apply coupon', method: 'POST', path: API.CART_COUPON, data: { code: 'NO-SUCH-CODE' } },
  { label: 'remove coupon', method: 'DELETE', path: API.CART_COUPON },
  { label: 'shipping rates', method: 'GET', path: API.CART_SHIPPING_METHODS },
  { label: 'select shipping', method: 'POST', path: API.CART_SHIPPING_METHODS, data: { shippingMethod: 'flatrate_flatrate' } },
  { label: 'payment methods', method: 'GET', path: API.CART_PAYMENT_METHODS },
  { label: 'select payment', method: 'POST', path: API.CART_PAYMENT_METHODS, data: { method: 'cashondelivery' } },
  { label: 'place order', method: 'POST', path: API.PLACE_ORDER },
];

test.describe('admin create order draft cart rest api', () => {
  for (const call of CART_CALLS) {
    test(`should refuse ${call.label} without an admin token`, async ({ request }) => {
      const response = await sendRestRequest(request, call.path(UNKNOWN_ID), {
        method: call.method,
        data: call.method === 'GET' ? undefined : call.data ?? {},
      });

      await expectUnauthenticated(response);
    });

    test(`should return 404 for ${call.label} on an unknown cart`, async ({ request }) => {
      const response = await sendAdminRequest(request, call.path(UNKNOWN_ID), {
        method: call.method,
        data: call.method === 'GET' ? undefined : call.data ?? {},
      });

      await expectProblem(response, 404, MSG.CART_NOT_FOUND);
    });
  }

  test('should return the draft cart with its item and totals', async ({ request }) => {
    const cart = await createCartWithItem(request);

    const response = await sendAdminRequest(request, API.CART(cart.cartId));
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body.id).toBe(cart.cartId);
    expect(body.customerId).toBe(cart.customer.id);
    expect(body.isActive).toBe(false);
    expect(body.itemsCount).toBe(1);
    expect(body.itemsQty).toBe(1);
    expect(body.items.map((item: any) => item.productId)).toEqual([cart.productId]);
    expect(body.grandTotal).toBeGreaterThan(0);
    expect(typeof body.formattedGrandTotal).toBe('string');
    expect(body.billingAddress).toBeNull();
    expect(body.shippingMethod).toBeNull();
  });

  test('should add a product to the draft cart', async ({ request }) => {
    const draft = await createCustomerWithDraftCart(request);
    const [productId] = await saleableProductIds(request);
    test.skip(!productId, 'no saleable simple product with stock exists to add to a draft cart');

    const response = await sendAdminRequest(request, API.CART_ITEMS(draft.cartId), {
      method: 'POST',
      data: { productId, quantity: 1 },
    });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    test.skip(body.success !== true, `product ${productId} could not be added: ${body.message}`);
    expect(body.message).toBe(MSG.ITEM_ADDED);
    expect(body.id).toBe(draft.cartId);
    expect(body.items).toHaveLength(1);
    expect(body.items[0].productId).toBe(productId);
    expect(body.items[0].quantity).toBe(1);
  });

  test('should reject adding an item without a product id', async ({ request }) => {
    const draft = await createCustomerWithDraftCart(request);

    const response = await sendAdminRequest(request, API.CART_ITEMS(draft.cartId), {
      method: 'POST',
      data: { quantity: 1 },
    });

    await expectProblem(response, 400, MSG.PRODUCT_REQUIRED);
  });

  test('should return 404 when adding an unknown product', async ({ request }) => {
    const draft = await createCustomerWithDraftCart(request);

    const response = await sendAdminRequest(request, API.CART_ITEMS(draft.cartId), {
      method: 'POST',
      data: { productId: UNKNOWN_ID, quantity: 1 },
    });

    await expectProblem(response, 404, MSG.PRODUCT_NOT_FOUND);
  });

  test('should update the quantity of a cart item', async ({ request }) => {
    const cart = await createCartWithItem(request);

    const response = await sendAdminRequest(request, API.CART_ITEMS(cart.cartId), {
      method: 'PUT',
      data: { qty: { [cart.cartItemId]: 2 } },
    });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body.success).toBe(true);
    expect(body.message).toBe(MSG.ITEM_UPDATED);
    expect(body.items.find((item: any) => item.id === cart.cartItemId)?.quantity).toBe(2);

    const reread = await safeJson(await sendAdminRequest(request, API.CART(cart.cartId)));
    expect(reread.itemsQty).toBe(2);
  });

  test('should reject an item update without quantities', async ({ request }) => {
    const cart = await createCartWithItem(request);

    const response = await sendAdminRequest(request, API.CART_ITEMS(cart.cartId), {
      method: 'PUT',
      data: { qty: {} },
    });

    await expectProblem(response, 400, MSG.QTY_REQUIRED);
  });

  test('should remove a cart item', async ({ request }) => {
    const cart = await createCartWithItem(request);

    const response = await sendAdminRequest(request, API.CART_ITEMS(cart.cartId), {
      method: 'DELETE',
      data: { cartItemId: cart.cartItemId },
    });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body.success).toBe(true);
    expect(body.message).toBe(MSG.ITEM_REMOVED);
    expect(body.items).toEqual([]);

    const reread = await safeJson(await sendAdminRequest(request, API.CART(cart.cartId)));
    expect(reread.items).toEqual([]);
  });

  test('should reject an item removal without a cart item id', async ({ request }) => {
    const cart = await createCartWithItem(request);

    const response = await sendAdminRequest(request, API.CART_ITEMS(cart.cartId), {
      method: 'DELETE',
      data: {},
    });

    await expectProblem(response, 400, MSG.CART_ITEM_REQUIRED);
  });

  test('should save the billing address and reuse it for shipping', async ({ request }) => {
    const cart = await createCartWithItem(request);

    const response = await sendAdminRequest(request, API.CART_ADDRESSES(cart.cartId), {
      method: 'POST',
      data: { billing: billingAddress(cart.customer) },
    });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.success).toBe(true);
    expect(body.message).toBe(MSG.ADDRESS_SAVED);
    expect(body.billingAddress.email).toBe(cart.customer.email);
    expect(body.billingAddress.city).toBe('Berlin');
    expect(body.billingAddress.country).toBe('DE');
    expect(body.shippingAddress.postcode).toBe('10115');
    expect(body.shippingAddress.lastName).toBe(cart.customer.lastName);
  });

  test('should reject saving addresses without a billing address', async ({ request }) => {
    const cart = await createCartWithItem(request);

    const response = await sendAdminRequest(request, API.CART_ADDRESSES(cart.cartId), {
      method: 'POST',
      data: {},
    });

    await expectProblem(response, 400, MSG.ADDRESS_REQUIRED);
  });

  test('should reject an incomplete billing address', async ({ request }) => {
    const cart = await createCartWithItem(request);
    const { phone, ...billing } = billingAddress(cart.customer);

    const response = await sendAdminRequest(request, API.CART_ADDRESSES(cart.cartId), {
      method: 'POST',
      data: { billing },
    });

    expect(phone).toBeTruthy();
    await expectProblem(response, 422, MSG.BILLING_PHONE_REQUIRED);
  });

  test('should list shipping rates once the addresses are saved', async ({ request }) => {
    const cart = await createCartReadyForShipping(request);

    const response = await sendAdminRequest(request, API.CART_SHIPPING_METHODS(cart.cartId));
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(Array.isArray(body.data)).toBe(true);
    test.skip(body.data.length === 0, 'no shipping carrier offers a rate for the draft cart');

    for (const rate of body.data) {
      expect(typeof rate.method).toBe('string');
      expect(typeof rate.carrierCode).toBe('string');
      expect(rate.method.startsWith(`${rate.carrierCode}_`)).toBe(true);
      expect(typeof rate.formattedPrice).toBe('string');
    }
  });

  test('should refuse shipping rates before the addresses are saved', async ({ request }) => {
    const cart = await createCartWithItem(request);

    const response = await sendAdminRequest(request, API.CART_SHIPPING_METHODS(cart.cartId));

    await expectProblem(response, 409, MSG.ADDRESSES_REQUIRED_FOR_SHIPPING);
  });

  test('should select a shipping method for the draft cart', async ({ request }) => {
    const cart = await createCartReadyForShipping(request);
    const rates = await safeJson(await sendAdminRequest(request, API.CART_SHIPPING_METHODS(cart.cartId)));
    const method = rates?.data?.[0]?.method;
    test.skip(!method, 'no shipping carrier offers a rate for the draft cart');

    const response = await sendAdminRequest(request, API.CART_SHIPPING_METHODS(cart.cartId), {
      method: 'POST',
      data: { shippingMethod: method },
    });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.success).toBe(true);
    expect(body.message).toBe(MSG.SHIPPING_METHOD_SAVED);
    expect(body.shippingMethod).toBe(method);

    const reread = await safeJson(await sendAdminRequest(request, API.CART(cart.cartId)));
    expect(reread.shippingMethod).toBe(method);
  });

  test('should reject a shipping selection without a method', async ({ request }) => {
    const cart = await createCartReadyForShipping(request);

    const response = await sendAdminRequest(request, API.CART_SHIPPING_METHODS(cart.cartId), {
      method: 'POST',
      data: {},
    });

    await expectProblem(response, 400, MSG.SHIPPING_METHOD_REQUIRED);
  });

  test('should refuse a shipping selection before the addresses are saved', async ({ request }) => {
    const cart = await createCartWithItem(request);

    const response = await sendAdminRequest(request, API.CART_SHIPPING_METHODS(cart.cartId), {
      method: 'POST',
      data: { shippingMethod: 'flatrate_flatrate' },
    });

    await expectProblem(response, 409, MSG.ADDRESSES_REQUIRED_FOR_SHIPPING);
  });

  test('should list payment methods once a shipping method is selected', async ({ request }) => {
    const cart = await createCartReadyForPayment(request);

    const response = await sendAdminRequest(request, API.CART_PAYMENT_METHODS(cart.cartId));
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(Array.isArray(body.data)).toBe(true);
    expect(body.data.length).toBeGreaterThan(0);

    for (const method of body.data) {
      expect(typeof method.method).toBe('string');
      expect(typeof method.methodTitle).toBe('string');
    }
  });

  test('should refuse payment methods before a shipping method is selected', async ({ request }) => {
    const cart = await createCartReadyForShipping(request);

    const response = await sendAdminRequest(request, API.CART_PAYMENT_METHODS(cart.cartId));

    await expectProblem(response, 409, MSG.SHIPPING_REQUIRED_FOR_PAYMENT);
  });

  test('should select a payment method for the draft cart', async ({ request }) => {
    const cart = await createCartReadyForPayment(request);
    const method = await supportedPaymentMethod(request, cart.cartId);

    const response = await sendAdminRequest(request, API.CART_PAYMENT_METHODS(cart.cartId), {
      method: 'POST',
      data: { method },
    });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.success).toBe(true);
    expect(body.message).toBe(MSG.PAYMENT_METHOD_SAVED);
    expect(body.paymentMethod).toBe(method);
    expect(typeof body.paymentMethodTitle).toBe('string');
  });

  test('should reject a payment selection without a method', async ({ request }) => {
    const cart = await createCartReadyForPayment(request);

    const response = await sendAdminRequest(request, API.CART_PAYMENT_METHODS(cart.cartId), {
      method: 'POST',
      data: {},
    });

    await expectProblem(response, 400, MSG.PAYMENT_METHOD_REQUIRED);
  });

  test('should refuse a payment selection before a shipping method is selected', async ({ request }) => {
    const cart = await createCartReadyForShipping(request);

    const response = await sendAdminRequest(request, API.CART_PAYMENT_METHODS(cart.cartId), {
      method: 'POST',
      data: { method: 'cashondelivery' },
    });

    await expectProblem(response, 409, MSG.SHIPPING_REQUIRED_FOR_PAYMENT);
  });

  test('should apply a coupon code and discount the draft cart', async ({ request }) => {
    const rule = await createCouponRule(request);

    try {
      const cart = await createCartWithItem(request);
      const before = await safeJson(await sendAdminRequest(request, API.CART(cart.cartId)));

      const response = await sendAdminRequest(request, API.CART_COUPON(cart.cartId), {
        method: 'POST',
        data: { code: rule.code },
      });
      const body = await safeJson(response);

      expect(response.status(), JSON.stringify(body)).toBe(201);
      expect(body.success).toBe(true);
      expect(body.message).toBe(MSG.COUPON_APPLIED);
      expect(body.couponCode).toBe(rule.code);
      expect(body.discountAmount).toBeGreaterThan(0);
      expect(body.grandTotal).toBeLessThan(before.grandTotal);
    } finally {
      await deleteCartRule(request, rule.id);
    }
  });

  test('should refuse a coupon code that is already applied', async ({ request }) => {
    const rule = await createCouponRule(request);

    try {
      const cart = await createCartWithItem(request);
      const first = await sendAdminRequest(request, API.CART_COUPON(cart.cartId), {
        method: 'POST',
        data: { code: rule.code },
      });
      expect(first.status(), await first.text()).toBe(201);

      const response = await sendAdminRequest(request, API.CART_COUPON(cart.cartId), {
        method: 'POST',
        data: { code: rule.code },
      });

      await expectProblem(response, 400, MSG.COUPON_ALREADY_APPLIED);
    } finally {
      await deleteCartRule(request, rule.id);
    }
  });

  test('should return 404 for an unknown coupon code', async ({ request }) => {
    const cart = await createCartWithItem(request);

    const response = await sendAdminRequest(request, API.CART_COUPON(cart.cartId), {
      method: 'POST',
      data: { code: `NO-SUCH-${Date.now()}` },
    });

    await expectProblem(response, 404, MSG.COUPON_NOT_FOUND);
  });

  test('should reject applying a coupon without a code', async ({ request }) => {
    const cart = await createCartWithItem(request);

    const response = await sendAdminRequest(request, API.CART_COUPON(cart.cartId), {
      method: 'POST',
      data: { code: '' },
    });

    await expectProblem(response, 400, MSG.COUPON_CODE_REQUIRED);
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

      const response = await sendAdminRequest(request, API.CART_COUPON(cart.cartId), { method: 'DELETE' });
      const body = await safeJson(response);

      expect(response.status(), JSON.stringify(body)).toBe(200);
      expect(body.success).toBe(true);
      expect(body.message).toBe(MSG.COUPON_REMOVED);
      expect(body.couponCode).toBeNull();
      expect(body.discountAmount).toBe(0);
    } finally {
      await deleteCartRule(request, rule.id);
    }
  });

  test('should place the order and consume the draft cart', async ({ request }) => {
    const cart = await createCartReadyToPlace(request);

    const response = await sendAdminRequest(request, API.PLACE_ORDER(cart.cartId), { method: 'POST' });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.success).toBe(true);
    expect(body.message).toBe(MSG.ORDER_PLACED);
    expect(body.customerId).toBe(cart.customer.id);
    expect(body.orderId).toBeGreaterThan(0);
    expect(typeof body.incrementId).toBe('string');
    expect(body.grandTotal).toBeGreaterThan(0);

    const order = await safeJson(await sendAdminRequest(request, API.ORDER(body.orderId)));
    expect(order.incrementId).toBe(body.incrementId);
    expect(order.customerEmail).toBe(cart.customer.email);
    expect(order.items.map((item: any) => item.productId)).toEqual([cart.productId]);

    await expectProblem(await sendAdminRequest(request, API.CART(cart.cartId)), 404, MSG.CART_NOT_FOUND);
  });

  test('should refuse to place an order for an empty draft cart', async ({ request }) => {
    const draft = await createCustomerWithDraftCart(request);

    const response = await sendAdminRequest(request, API.PLACE_ORDER(draft.cartId), { method: 'POST' });

    await expectProblem(response, 409, MSG.PLACE_ORDER_EMPTY_CART);
  });

  test('should refuse to place an order before a payment method is selected', async ({ request }) => {
    const cart = await createCartReadyForShipping(request);
    await selectShipping(request, cart.cartId);

    const response = await sendAdminRequest(request, API.PLACE_ORDER(cart.cartId), { method: 'POST' });

    await expectProblem(response, 409, MSG.PLACE_ORDER_PAYMENT_REQUIRED);
  });
});
