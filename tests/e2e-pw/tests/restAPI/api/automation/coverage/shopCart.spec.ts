import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import {
  SHOP_CART_ENDPOINTS,
  SHOP_CART_MESSAGES,
  addCartLine,
  addProductToCart,
  bearer,
  createGuestCart,
  expectProblem,
  findCrossSellSource,
  findSaleableSimpleProducts,
  listWishlistProductIds,
  randomUuid,
  readCart,
  readJson,
  registerShopCustomer,
} from '../../../rest/helpers/coverage/shopCart.helpers';

test.describe.configure({ timeout: 120_000 });

test.describe('shop cart cross-sells rest api', () => {
  test('should list the cross-sell products of the cart items', async ({ request }) => {
    const source = await findCrossSellSource(request);

    test.skip(!source, 'no saleable simple product in the catalog has cross-sell products configured');

    const guest = await createGuestCart(request);
    await addCartLine(request, guest.token, source!.productId, 1);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.CART_CROSS_SELLS, {
      headers: bearer(guest.token),
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(Array.isArray(body)).toBe(true);
    expect(body.length).toBeGreaterThan(0);

    for (const product of body) {
      expect(source!.crossSellIds).toContain(Number(product.id));
      expect(Number(product.id)).not.toBe(source!.productId);
      expect(typeof product.sku).toBe('string');
      expect(typeof product.name).toBe('string');
    }
  });

  test('should leave out a cross-sell product that is already in the cart', async ({ request }) => {
    const source = await findCrossSellSource(request);

    test.skip(!source, 'no saleable simple product in the catalog has cross-sell products configured');

    const guest = await createGuestCart(request);
    await addCartLine(request, guest.token, source!.productId, 1);

    const crossSellId = source!.crossSellIds[0];
    const added = await addProductToCart(request, guest.token, crossSellId, 1);

    test.skip(added.status() !== 201, `cross-sell product ${crossSellId} cannot be added to a cart`);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.CART_CROSS_SELLS, {
      headers: bearer(guest.token),
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body.map((product: any) => Number(product.id))).not.toContain(crossSellId);
  });

  test('should return no cross-sells for an empty cart', async ({ request }) => {
    const guest = await createGuestCart(request);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.CART_CROSS_SELLS, {
      headers: bearer(guest.token),
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body).toEqual([]);
  });

  test('should refuse cross-sells without a bearer token', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.CART_CROSS_SELLS);

    await expectProblem(response, 401, SHOP_CART_MESSAGES.AUTHENTICATION_REQUIRED);
  });

  test('should refuse cross-sells for an unknown cart token', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.CART_CROSS_SELLS, {
      headers: bearer(randomUuid()),
    });

    await expectProblem(response, 404, SHOP_CART_MESSAGES.INVALID_CART_TOKEN);
  });
});

test.describe('shop remove cart items rest api', () => {
  test('should remove the listed items and keep the others', async ({ request }) => {
    const [firstProductId, secondProductId] = await findSaleableSimpleProducts(request, 2);
    const guest = await createGuestCart(request);
    const first = await addCartLine(request, guest.token, firstProductId, 1);
    await addCartLine(request, guest.token, secondProductId, 1);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.REMOVE_CART_ITEMS, {
      method: 'POST',
      data: { itemIds: [first.itemId] },
      headers: bearer(guest.token),
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.id).toBe(first.cartId);
    expect(body.itemsCount).toBe(1);
    expect(body.items.map((item: any) => Number(item.productId))).toEqual([secondProductId]);
  });

  test('should empty the cart when every item is removed', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const guest = await createGuestCart(request);
    const line = await addCartLine(request, guest.token, productId, 2);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.REMOVE_CART_ITEMS, {
      method: 'POST',
      data: { itemIds: [line.itemId] },
      headers: bearer(guest.token),
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.itemsCount).toBe(0);
    expect(body.items).toEqual([]);
  });

  test('should require the item ids to remove', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const guest = await createGuestCart(request);
    await addCartLine(request, guest.token, productId, 1);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.REMOVE_CART_ITEMS, {
      method: 'POST',
      data: { itemIds: [] },
      headers: bearer(guest.token),
    });

    await expectProblem(response, 400, SHOP_CART_MESSAGES.ITEM_IDS_REQUIRED);

    const cart = await readJson(await readCart(request, guest.token));

    expect(cart.itemsCount).toBe(1);
  });

  test('should refuse to remove items without a bearer token', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.REMOVE_CART_ITEMS, {
      method: 'POST',
      data: { itemIds: [1] },
    });

    await expectProblem(response, 401, SHOP_CART_MESSAGES.AUTHENTICATION_REQUIRED);
  });

  test('should report an unknown cart token as a missing cart', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.REMOVE_CART_ITEMS, {
      method: 'POST',
      data: { itemIds: [1] },
      headers: bearer(randomUuid()),
    });

    await expectProblem(response, 404, SHOP_CART_MESSAGES.CART_NOT_FOUND);
  });

  test('should not remove an item that belongs to another cart', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const owner = await createGuestCart(request);
    const ownerLine = await addCartLine(request, owner.token, productId, 1);
    const intruder = await createGuestCart(request);
    await addCartLine(request, intruder.token, productId, 1);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.REMOVE_CART_ITEMS, {
      method: 'POST',
      data: { itemIds: [ownerLine.itemId] },
      headers: bearer(intruder.token),
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.itemsCount).toBe(1);

    const ownerCart = await readJson(await readCart(request, owner.token));

    expect(ownerCart.itemsCount).toBe(1);
    expect(ownerCart.items.map((item: any) => Number(item.id))).toContain(ownerLine.itemId);
  });
});

test.describe('shop move cart items to wishlist rest api', () => {
  test('should move a cart item to the customer wishlist', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const customer = await registerShopCustomer(request);
    const line = await addCartLine(request, customer.token, productId, 1);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.MOVE_TO_WISHLISTS, {
      method: 'POST',
      data: { itemIds: [line.itemId], quantities: [1] },
      headers: bearer(customer.token),
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.id).toBe(line.cartId);
    expect(body.itemsCount).toBe(0);
    expect(await listWishlistProductIds(request, customer)).toContain(productId);
  });

  test('should require the item ids to move', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const customer = await registerShopCustomer(request);
    await addCartLine(request, customer.token, productId, 1);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.MOVE_TO_WISHLISTS, {
      method: 'POST',
      data: { itemIds: [], quantities: [] },
      headers: bearer(customer.token),
    });

    await expectProblem(response, 400, SHOP_CART_MESSAGES.ITEM_IDS_REQUIRED);

    const cart = await readJson(await readCart(request, customer.token));

    expect(cart.itemsCount).toBe(1);
    expect(await listWishlistProductIds(request, customer)).toEqual([]);
  });

  test('should refuse to move items without a bearer token', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.MOVE_TO_WISHLISTS, {
      method: 'POST',
      data: { itemIds: [1], quantities: [1] },
    });

    await expectProblem(response, 401, SHOP_CART_MESSAGES.AUTHENTICATION_REQUIRED);
  });

  test('should not move another customer cart item into the wishlist', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const owner = await registerShopCustomer(request);
    const ownerLine = await addCartLine(request, owner.token, productId, 1);
    const intruder = await registerShopCustomer(request);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.MOVE_TO_WISHLISTS, {
      method: 'POST',
      data: { itemIds: [ownerLine.itemId], quantities: [1] },
      headers: bearer(intruder.token),
    });

    await expectProblem(response, 404, SHOP_CART_MESSAGES.CART_NOT_FOUND);

    const ownerCart = await readJson(await readCart(request, owner.token));

    expect(ownerCart.itemsCount).toBe(1);
    expect(await listWishlistProductIds(request, intruder)).toEqual([]);
    expect(await listWishlistProductIds(request, owner)).toEqual([]);
  });
});
