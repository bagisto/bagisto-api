import { test, expect, APIRequestContext } from '@playwright/test';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../../graphql/helpers/testSupport';
import { SHOP_CART_QUERIES } from '../../../graphql/Queries/coverage/shopCart.queries';
import {
  SHOP_CART_MESSAGES,
  addCartLine,
  bearer,
  createGuestCart,
  findCrossSellSource,
  findSaleableSimpleProducts,
  listWishlistProductIds,
  randomUuid,
  readCart,
  readJson,
  referenceId,
  registerShopCustomer,
} from '../../../../restAPI/rest/helpers/coverage/shopCart.helpers';

test.describe.configure({ timeout: 120_000 });

async function runGraphQL(
  request: APIRequestContext,
  query: string,
  variables: Record<string, any> = {},
  headers: Record<string, string> = {}
): Promise<any> {
  const response = await sendGraphQLRequest(request, query, variables, headers);

  expect(response.status()).toBe(200);

  return response.json();
}

function expectNoErrors(body: any): void {
  expect(body.errors, graphQLErrorMessages(body).join(' | ')).toBeUndefined();
}

function cartProductIds(cart: any): number[] {
  return (cart?.items?.edges ?? []).map((edge: any) => Number(edge.node.productId));
}

test.describe('shop cart cross-sells graphql api', () => {
  test('should list the cross-sell products of the cart items', async ({ request }) => {
    const source = await findCrossSellSource(request);

    test.skip(!source, 'no saleable simple product in the catalog has cross-sell products configured');

    const guest = await createGuestCart(request);
    await addCartLine(request, guest.token, source!.productId, 1);

    const body = await runGraphQL(request, SHOP_CART_QUERIES.cartCrossSells, { first: 20 }, bearer(guest.token));

    expectNoErrors(body);

    const connection = body.data.cartCrossSells;
    const ids = connection.edges.map((edge: any) => referenceId(edge.node._id ?? edge.node.id));

    expect(ids.length).toBeGreaterThan(0);
    expect(connection.totalCount).toBeGreaterThanOrEqual(ids.length);

    for (const id of ids) {
      expect(source!.crossSellIds).toContain(id);
      expect(id).not.toBe(source!.productId);
    }

    for (const edge of connection.edges) {
      expect(typeof edge.node.sku).toBe('string');
      expect(typeof edge.node.name).toBe('string');
    }
  });

  test('should return no cross-sells for an empty cart', async ({ request }) => {
    const guest = await createGuestCart(request);

    const body = await runGraphQL(request, SHOP_CART_QUERIES.cartCrossSells, { first: 20 }, bearer(guest.token));

    expectNoErrors(body);
    expect(body.data.cartCrossSells.edges).toEqual([]);
    expect(body.data.cartCrossSells.totalCount).toBe(0);
  });

  test('should refuse cross-sells without a bearer token', async ({ request }) => {
    const body = await runGraphQL(request, SHOP_CART_QUERIES.cartCrossSells, { first: 20 });

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.AUTHENTICATION_REQUIRED);
    expect(body.data?.cartCrossSells ?? null).toBeNull();
  });

  test('should refuse cross-sells for an unknown cart token', async ({ request }) => {
    const body = await runGraphQL(request, SHOP_CART_QUERIES.cartCrossSells, { first: 20 }, bearer(randomUuid()));

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.INVALID_CART_TOKEN);
    expect(body.data?.cartCrossSells ?? null).toBeNull();
  });
});

test.describe('shop create remove cart items graphql api', () => {
  test('should remove the listed items and keep the others', async ({ request }) => {
    const [firstProductId, secondProductId] = await findSaleableSimpleProducts(request, 2);
    const guest = await createGuestCart(request);
    const first = await addCartLine(request, guest.token, firstProductId, 1);
    await addCartLine(request, guest.token, secondProductId, 1);

    const body = await runGraphQL(
      request,
      SHOP_CART_QUERIES.createRemoveCartItems,
      { itemIds: [first.itemId] },
      bearer(guest.token)
    );

    expectNoErrors(body);

    const cart = body.data.createRemoveCartItems.removeCartItems;

    expect(Number(cart._id)).toBe(first.cartId);
    expect(cart.itemsCount).toBe(1);
    expect(cartProductIds(cart)).toEqual([secondProductId]);
  });

  test('should require the item ids to remove', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const guest = await createGuestCart(request);
    await addCartLine(request, guest.token, productId, 1);

    const body = await runGraphQL(request, SHOP_CART_QUERIES.createRemoveCartItems, { itemIds: [] }, bearer(guest.token));

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.ITEM_IDS_REQUIRED);

    const cart = await readJson(await readCart(request, guest.token));

    expect(cart.itemsCount).toBe(1);
  });

  test('should refuse to remove items without a bearer token', async ({ request }) => {
    const body = await runGraphQL(request, SHOP_CART_QUERIES.createRemoveCartItems, { itemIds: [1] });

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.AUTHENTICATION_REQUIRED);
    expect(body.data?.createRemoveCartItems ?? null).toBeNull();
  });

  test('should not remove an item that belongs to another cart', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const owner = await createGuestCart(request);
    const ownerLine = await addCartLine(request, owner.token, productId, 1);
    const intruder = await createGuestCart(request);
    await addCartLine(request, intruder.token, productId, 1);

    const body = await runGraphQL(
      request,
      SHOP_CART_QUERIES.createRemoveCartItems,
      { itemIds: [ownerLine.itemId] },
      bearer(intruder.token)
    );

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.CART_ITEM_NOT_FOUND);
    expect(body.data?.createRemoveCartItems ?? null).toBeNull();

    const intruderCart = await readJson(await readCart(request, intruder.token));
    const ownerCart = await readJson(await readCart(request, owner.token));

    expect(intruderCart.itemsCount).toBe(1);
    expect(ownerCart.itemsCount).toBe(1);
    expect(ownerCart.items.map((item: any) => Number(item.id))).toContain(ownerLine.itemId);
  });
});

test.describe('shop create move to wishlist graphql api', () => {
  test('should move a cart item to the customer wishlist', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const customer = await registerShopCustomer(request);
    const line = await addCartLine(request, customer.token, productId, 1);

    const body = await runGraphQL(
      request,
      SHOP_CART_QUERIES.createMoveToWishlist,
      { itemIds: [line.itemId], quantities: [1] },
      bearer(customer.token)
    );

    expectNoErrors(body);

    const cart = body.data.createMoveToWishlist.moveToWishlist;

    expect(Number(cart._id)).toBe(line.cartId);
    expect(cart.itemsCount).toBe(0);
    expect(await listWishlistProductIds(request, customer)).toContain(productId);
  });

  test('should require the item ids to move', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const customer = await registerShopCustomer(request);
    await addCartLine(request, customer.token, productId, 1);

    const body = await runGraphQL(
      request,
      SHOP_CART_QUERIES.createMoveToWishlist,
      { itemIds: [], quantities: [] },
      bearer(customer.token)
    );

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.ITEM_IDS_REQUIRED);
    expect(await listWishlistProductIds(request, customer)).toEqual([]);
  });

  test('should refuse to move items without a bearer token', async ({ request }) => {
    const body = await runGraphQL(request, SHOP_CART_QUERIES.createMoveToWishlist, { itemIds: [1], quantities: [1] });

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.AUTHENTICATION_REQUIRED);
    expect(body.data?.createMoveToWishlist ?? null).toBeNull();
  });
});

test.describe('shop create estimate shipping graphql api', () => {
  test('should estimate the totals of the bearer cart for an address', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const guest = await createGuestCart(request);
    const line = await addCartLine(request, guest.token, productId, 1);

    const body = await runGraphQL(
      request,
      SHOP_CART_QUERIES.createEstimateShipping,
      { country: 'US', state: 'CA', postcode: '90001' },
      bearer(guest.token)
    );

    expectNoErrors(body);

    const cart = body.data.createEstimateShipping.estimateShipping;

    expect(Number(cart._id)).toBe(line.cartId);
    expect(cart.itemsCount).toBe(1);
    expect(cart.subtotal).toBeGreaterThan(0);
    expect(cart.grandTotal).toBeGreaterThanOrEqual(cart.subtotal);
    expect(cartProductIds(cart)).toEqual([productId]);
  });

  test('should require the country, state and postcode', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const guest = await createGuestCart(request);
    await addCartLine(request, guest.token, productId, 1);

    const body = await runGraphQL(
      request,
      SHOP_CART_QUERIES.createEstimateShipping,
      { country: 'US', state: 'CA', postcode: null },
      bearer(guest.token)
    );

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.ADDRESS_DATA_REQUIRED);
    expect(body.data?.createEstimateShipping ?? null).toBeNull();
  });

  test('should refuse an estimate without a bearer token', async ({ request }) => {
    const body = await runGraphQL(request, SHOP_CART_QUERIES.createEstimateShipping, {
      country: 'US',
      state: 'CA',
      postcode: '90001',
    });

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.AUTHENTICATION_REQUIRED);
    expect(body.data?.createEstimateShipping ?? null).toBeNull();
  });

  test('should report an unknown cart token as a missing cart', async ({ request }) => {
    const body = await runGraphQL(
      request,
      SHOP_CART_QUERIES.createEstimateShipping,
      { country: 'US', state: 'CA', postcode: '90001' },
      bearer(randomUuid())
    );

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.CART_NOT_FOUND);
    expect(body.data?.createEstimateShipping ?? null).toBeNull();
  });
});
