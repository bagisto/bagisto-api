import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import {
  SHOP_CART_ENDPOINTS,
  SHOP_CART_MESSAGES,
  addWishlistItem,
  bearer,
  expectProblem,
  findSaleableSimpleProducts,
  listWishlistProductIds,
  readJson,
  registerShopCustomer,
} from '../../../rest/helpers/coverage/shopCart.helpers';

test.describe.configure({ timeout: 120_000 });

test.describe('shop delete all wishlists rest api', () => {
  test('should clear the wishlist of the customer only', async ({ request }) => {
    const [firstProductId, secondProductId] = await findSaleableSimpleProducts(request, 2);
    const customer = await registerShopCustomer(request);
    await addWishlistItem(request, customer, firstProductId);
    await addWishlistItem(request, customer, secondProductId);
    const other = await registerShopCustomer(request);
    await addWishlistItem(request, other, firstProductId);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.DELETE_ALL_WISHLISTS, {
      method: 'POST',
      data: {},
      headers: bearer(customer.token),
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.deletedCount).toBe(2);
    expect(body.message).toBe(SHOP_CART_MESSAGES.WISHLIST_DELETE_ALL_SUCCESS);
    expect(await listWishlistProductIds(request, customer)).toEqual([]);
    expect(await listWishlistProductIds(request, other)).toEqual([firstProductId]);
  });

  test('should report zero deleted items for an empty wishlist', async ({ request }) => {
    const customer = await registerShopCustomer(request);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.DELETE_ALL_WISHLISTS, {
      method: 'POST',
      data: {},
      headers: bearer(customer.token),
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.deletedCount).toBe(0);
    expect(body.message).toBe(SHOP_CART_MESSAGES.WISHLIST_DELETE_ALL_SUCCESS);
  });

  test('should refuse to clear a wishlist without a customer token', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.DELETE_ALL_WISHLISTS, {
      method: 'POST',
      data: {},
    });

    await expectProblem(response, 403, SHOP_CART_MESSAGES.UNAUTHENTICATED);
  });
});

test.describe('shop move wishlist to cart rest api', () => {
  test('should move a wishlist item into the customer cart', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const customer = await registerShopCustomer(request);
    const wishlistItemId = await addWishlistItem(request, customer, productId);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.MOVE_WISHLIST_TO_CARTS, {
      method: 'POST',
      data: { wishlistItemId, quantity: 2 },
      headers: bearer(customer.token),
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.success).toBe(true);
    expect(body.message).toBe(SHOP_CART_MESSAGES.WISHLIST_MOVED_TO_CART);
    expect(body.customerId).toBe(customer.id);

    const line = (body.items ?? []).find((item: any) => Number(item.productId) === productId);

    expect(line, JSON.stringify(body)).toBeTruthy();
    expect(Number(line.quantity)).toBe(2);
    expect(await listWishlistProductIds(request, customer)).toEqual([]);
  });

  test('should reject a quantity below one', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const customer = await registerShopCustomer(request);
    const wishlistItemId = await addWishlistItem(request, customer, productId);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.MOVE_WISHLIST_TO_CARTS, {
      method: 'POST',
      data: { wishlistItemId, quantity: 0 },
      headers: bearer(customer.token),
    });

    await expectProblem(response, 400, SHOP_CART_MESSAGES.WISHLIST_INVALID_QUANTITY);
    expect(await listWishlistProductIds(request, customer)).toEqual([productId]);
  });

  test('should not move a wishlist item of another customer', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const owner = await registerShopCustomer(request);
    const wishlistItemId = await addWishlistItem(request, owner, productId);
    const intruder = await registerShopCustomer(request);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.MOVE_WISHLIST_TO_CARTS, {
      method: 'POST',
      data: { wishlistItemId, quantity: 1 },
      headers: bearer(intruder.token),
    });

    await expectProblem(response, 404, SHOP_CART_MESSAGES.WISHLIST_NOT_FOUND);
    expect(await listWishlistProductIds(request, owner)).toEqual([productId]);
  });

  test('should refuse to move a wishlist item without a customer token', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const owner = await registerShopCustomer(request);
    const wishlistItemId = await addWishlistItem(request, owner, productId);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.MOVE_WISHLIST_TO_CARTS, {
      method: 'POST',
      data: { wishlistItemId, quantity: 1 },
    });

    await expectProblem(response, 403, SHOP_CART_MESSAGES.UNAUTHENTICATED);
    expect(await listWishlistProductIds(request, owner)).toEqual([productId]);
  });
});
