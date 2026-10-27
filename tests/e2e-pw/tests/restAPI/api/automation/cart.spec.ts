import { test, expect, APIRequestContext, APIResponse } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { sendAdminRequest } from '../../rest/helpers/adminClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import { ADMIN_MARKETING } from '../../rest/endpoints/admin.marketing.endpoints';
import {
  ShopGuestCart,
  addCartLine,
  bearer,
  createGuestCart,
  expectProblem,
  findSaleableSimpleProducts,
  readCart,
  readJson,
  uniqueSuffix,
} from '../../rest/helpers/coverage/shopCart.helpers';

test.describe.configure({ timeout: 120_000 });

const MSG = {
  AUTHENTICATION_REQUIRED: 'Authentication token is required',
  PRODUCT_ADDED: 'Product added to cart successfully',
  ITEM_UPDATED: 'Cart item updated successfully',
  CART_ITEM_NOT_FOUND: 'Cart item not found',
  COUPON_APPLIED: 'Coupon applied successfully',
  COUPON_REMOVED: 'Coupon removed successfully',
  COUPON_INVALID: 'Coupon code is invalid.',
};

const ALL_CUSTOMER_GROUP_IDS = [1, 2, 3];
const DEFAULT_CHANNEL_ID = 1;

interface CouponRule {
  id: number;
  code: string;
}

async function postCart(
  request: APIRequestContext,
  endpoint: string,
  token: string | null,
  data: Record<string, any> = {}
): Promise<APIResponse> {
  return sendRestRequest(request, endpoint, {
    method: 'POST',
    data,
    headers: token ? bearer(token) : {},
  });
}

async function expectCartPayload(response: APIResponse, status: number): Promise<any> {
  const body = await readJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(status);

  return body;
}

async function guestCartWithLine(request: APIRequestContext): Promise<{ cart: ShopGuestCart; itemId: number; productId: number }> {
  const [productId] = await findSaleableSimpleProducts(request, 1);
  const cart = await createGuestCart(request);
  const line = await addCartLine(request, cart.token, productId, 1);

  return { cart, itemId: line.itemId, productId };
}

async function createCouponRule(request: APIRequestContext): Promise<CouponRule> {
  const code = `E2ECART${uniqueSuffix().replace(/[^0-9]/g, '')}`;
  const response = await sendAdminRequest(request, ADMIN_MARKETING.CART_RULES, {
    method: 'POST',
    data: {
      name: `E2E shop cart coupon ${code}`,
      description: 'e2e shop cart coupon rule',
      channels: [DEFAULT_CHANNEL_ID],
      customer_groups: ALL_CUSTOMER_GROUP_IDS,
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
  const body = await readJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(201);
  expect(typeof body.id).toBe('number');

  return { id: body.id, code };
}

async function deleteCouponRule(request: APIRequestContext, rule: CouponRule | null): Promise<void> {
  if (!rule) {
    return;
  }

  const response = await sendAdminRequest(request, ADMIN_MARKETING.CART_RULE(rule.id), { method: 'DELETE' });

  expect(response.status(), await response.text()).toBe(200);
}

test.describe('cart rest api', () => {
  test('should get the current cart', async ({ request }) => {
    const cart = await createGuestCart(request);

    const body = await expectCartPayload(await readCart(request, cart.token), 201);

    expect(body.id).toBe(cart.cartId);
    expect(body.itemsCount).toBe(0);
    expect(body.items).toEqual([]);
  });

  test('should refuse to read the cart without a bearer token', async ({ request }) => {
    await expectProblem(await postCart(request, ENDPOINTS.CREATE_CART, null), 401, MSG.AUTHENTICATION_REQUIRED);
  });

  test('should not route a get on the cart path', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CREATE_CART);

    expect(response.status()).toBe(404);
  });
});

test.describe('cart item operations', () => {
  test('should add a product to the cart', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const cart = await createGuestCart(request);

    const body = await expectCartPayload(
      await postCart(request, ENDPOINTS.ADD_TO_CART, cart.token, { productId, quantity: 1 }),
      201
    );

    expect(body.success).toBe(true);
    expect(body.message).toBe(MSG.PRODUCT_ADDED);
    expect(body.id).toBe(cart.cartId);
    expect(body.itemsCount).toBe(1);
    expect(body.items).toHaveLength(1);
    expect(Number(body.items[0].productId)).toBe(productId);
    expect(Number(body.items[0].quantity)).toBe(1);
  });

  test('should refuse add-to-cart without a bearer token', async ({ request }) => {
    const response = await postCart(request, ENDPOINTS.ADD_TO_CART, null, { productId: 1, quantity: 2, options: {} });

    await expectProblem(response, 401, MSG.AUTHENTICATION_REQUIRED);
  });

  test('should reject add-to-cart without a product id', async ({ request }) => {
    const cart = await createGuestCart(request);

    const response = await postCart(request, ENDPOINTS.ADD_TO_CART, cart.token, { quantity: 1 });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(400);
    expect(body.title).toBe('Bad Request');

    const after = await expectCartPayload(await readCart(request, cart.token), 201);
    expect(after.itemsCount).toBe(0);
  });

  test('should update a cart item quantity', async ({ request }) => {
    const { cart, itemId } = await guestCartWithLine(request);

    const body = await expectCartPayload(
      await postCart(request, ENDPOINTS.UPDATE_CART_ITEM, cart.token, { cartItemId: itemId, quantity: 2 }),
      201
    );

    expect(body.success).toBe(true);
    expect(body.message).toBe(MSG.ITEM_UPDATED);

    const item = body.items.find((line: any) => Number(line.id) === itemId);
    expect(Number(item?.quantity)).toBe(2);

    const after = await expectCartPayload(await readCart(request, cart.token), 201);
    expect(Number(after.items.find((line: any) => Number(line.id) === itemId)?.quantity)).toBe(2);
  });

  test('should remove a cart item', async ({ request }) => {
    const { cart, itemId } = await guestCartWithLine(request);

    const body = await expectCartPayload(
      await postCart(request, ENDPOINTS.REMOVE_CART_ITEM, cart.token, { cartItemId: itemId }),
      201
    );

    expect(body.itemsCount).toBe(0);
    expect(body.items).toEqual([]);
  });

  test('should refuse removal of a non-existent cart item', async ({ request }) => {
    const { cart, itemId } = await guestCartWithLine(request);

    const response = await postCart(request, ENDPOINTS.REMOVE_CART_ITEM, cart.token, { cartItemId: 999999999 });

    await expectProblem(response, 400, MSG.CART_ITEM_NOT_FOUND);

    const after = await expectCartPayload(await readCart(request, cart.token), 201);
    expect(after.items.map((line: any) => Number(line.id))).toEqual([itemId]);
  });
});

test.describe('cart coupon operations', () => {
  test('should refuse coupon application without a bearer token', async ({ request }) => {
    const response = await postCart(request, ENDPOINTS.APPLY_COUPON, null, { couponCode: 'SAVE10' });

    await expectProblem(response, 401, MSG.AUTHENTICATION_REQUIRED);
  });

  test('should refuse coupon removal without a bearer token', async ({ request }) => {
    await expectProblem(await postCart(request, ENDPOINTS.REMOVE_COUPON, null), 401, MSG.AUTHENTICATION_REQUIRED);
  });
});

test.describe('regression coupon flow returns coupon code success and message', () => {
  let rule: CouponRule | null;

  test.beforeEach(() => {
    rule = null;
  });

  test.afterEach(async ({ request }) => {
    await deleteCouponRule(request, rule);
  });

  test('apply valid coupon reports success and echoes the coupon code', async ({ request }) => {
    const { cart } = await guestCartWithLine(request);
    rule = await createCouponRule(request);

    const body = await expectCartPayload(
      await postCart(request, ENDPOINTS.APPLY_COUPON, cart.token, { couponCode: rule.code }),
      201
    );

    expect(body.success).toBe(true);
    expect(body.message).toBe(MSG.COUPON_APPLIED);
    expect(body.couponCode).toBe(rule.code);
    expect(body.discountAmount).toBeGreaterThan(0);

    const after = await expectCartPayload(await readCart(request, cart.token), 201);
    expect(after.couponCode).toBe(rule.code);
  });

  test('remove coupon reports success and clears the coupon code', async ({ request }) => {
    const { cart } = await guestCartWithLine(request);
    rule = await createCouponRule(request);

    const applied = await expectCartPayload(
      await postCart(request, ENDPOINTS.APPLY_COUPON, cart.token, { couponCode: rule.code }),
      201
    );
    expect(applied.couponCode).toBe(rule.code);

    const body = await expectCartPayload(await postCart(request, ENDPOINTS.REMOVE_COUPON, cart.token), 201);

    expect(body.success).toBe(true);
    expect(body.message).toBe(MSG.COUPON_REMOVED);
    expect(body.couponCode).toBeNull();
    expect(body.discountAmount).toBe(0);

    const after = await expectCartPayload(await readCart(request, cart.token), 201);
    expect(after.couponCode).toBeNull();
  });

  test('apply unknown coupon is refused with 422 and leaves no coupon code', async ({ request }) => {
    const { cart } = await guestCartWithLine(request);
    const bogus = `INVALIDCODE${uniqueSuffix().replace(/[^0-9]/g, '')}`;

    const body = await expectCartPayload(
      await postCart(request, ENDPOINTS.APPLY_COUPON, cart.token, { couponCode: bogus }),
      422
    );

    expect(body.detail ?? body.message).toBe(MSG.COUPON_INVALID);

    const after = await expectCartPayload(await readCart(request, cart.token), 201);
    expect(after.couponCode).toBeNull();
  });
});
