import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import {
  SHOP_CART_MESSAGES,
  ShopCustomer,
  addWishlistItem,
  bearer,
  expectProblem,
  findSaleableSimpleProducts,
  readJson,
  referenceId,
  registerShopCustomer,
} from '../../rest/helpers/coverage/shopCart.helpers';

test.describe.configure({ timeout: 120_000 });

const WISHLISTS = '/api/shop/wishlists';
const WISHLIST = (id: number | string) => `/api/shop/wishlists/${id}`;
const WISHLIST_TOGGLE = '/api/shop/wishlists/toggle';
const MISSING_PRODUCT_ID = 999999999;

test.describe('wishlist rest api without a customer token', () => {
  test('should refuse the wishlist listing', async ({ request }) => {
    await expectProblem(await sendRestRequest(request, WISHLISTS), 403, SHOP_CART_MESSAGES.UNAUTHENTICATED);
  });

  test('should refuse a single wishlist item', async ({ request }) => {
    await expectProblem(await sendRestRequest(request, WISHLIST(1)), 403, SHOP_CART_MESSAGES.UNAUTHENTICATED);
  });

  test('should refuse a wishlist add before looking up the product', async ({ request }) => {
    const response = await sendRestRequest(request, WISHLISTS, {
      method: 'POST',
      data: { productId: MISSING_PRODUCT_ID },
    });

    await expectProblem(response, 403, SHOP_CART_MESSAGES.UNAUTHENTICATED);
  });

  test('should refuse a wishlist toggle before looking up the product', async ({ request }) => {
    const response = await sendRestRequest(request, WISHLIST_TOGGLE, {
      method: 'POST',
      data: { productId: MISSING_PRODUCT_ID },
    });

    await expectProblem(response, 403, SHOP_CART_MESSAGES.UNAUTHENTICATED);
  });
});

test.describe('wishlist rest api for a customer', () => {
  let customer: ShopCustomer;
  let productId: number;
  let wishlistId: number;

  test.beforeEach(async ({ request }) => {
    [productId] = await findSaleableSimpleProducts(request, 1);
    customer = await registerShopCustomer(request);
    wishlistId = await addWishlistItem(request, customer, productId);
  });

  test('should return own wishlist when authenticated', async ({ request }) => {
    const response = await sendRestRequest(request, WISHLISTS, { headers: bearer(customer.token) });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(Array.isArray(body), JSON.stringify(body)).toBe(true);
    expect(body).toHaveLength(1);
    expect(body[0].id).toBe(wishlistId);
    expect(referenceId(body[0].product)).toBe(productId);
  });

  test('should return single wishlist by id when authenticated', async ({ request }) => {
    const response = await sendRestRequest(request, WISHLIST(wishlistId), { headers: bearer(customer.token) });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body.id).toBe(wishlistId);
    expect(referenceId(body.product)).toBe(productId);
  });

  test('should not return a wishlist item of another customer', async ({ request }) => {
    const other = await registerShopCustomer(request);

    await expectProblem(
      await sendRestRequest(request, WISHLIST(wishlistId), { headers: bearer(other.token) }),
      404,
      SHOP_CART_MESSAGES.WISHLIST_NOT_FOUND
    );
  });

  test('should report a missing product to an authenticated customer toggling the wishlist', async ({ request }) => {
    const response = await sendRestRequest(request, WISHLIST_TOGGLE, {
      method: 'POST',
      data: { productId: MISSING_PRODUCT_ID },
      headers: bearer(customer.token),
    });

    await expectProblem(response, 404, 'Product not found');
  });
});
