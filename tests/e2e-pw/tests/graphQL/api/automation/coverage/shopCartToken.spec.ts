import { test, expect, APIRequestContext } from '@playwright/test';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../../graphql/helpers/testSupport';
import { SHOP_CART_QUERIES } from '../../../graphql/Queries/coverage/shopCart.queries';
import {
  SHOP_CART_MESSAGES,
  addCartLine,
  bearer,
  createGuestCart,
  findSaleableSimpleProducts,
  randomUuid,
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

function cartProducts(cart: any): Array<{ productId: number; quantity: number }> {
  return (cart?.items?.edges ?? []).map((edge: any) => ({
    productId: Number(edge.node.productId),
    quantity: Number(edge.node.quantity),
  }));
}

test.describe('shop read get cart token graphql api', () => {
  test('should return the guest cart of the bearer token', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const guest = await createGuestCart(request);
    const line = await addCartLine(request, guest.token, productId, 2);

    const body = await runGraphQL(request, SHOP_CART_QUERIES.readGetCartToken, {}, bearer(guest.token));

    expectNoErrors(body);

    const cart = body.data.readGetCartToken.getCartToken;

    expect(Number(cart._id)).toBe(line.cartId);
    expect(cart.isGuest).toBe(true);
    expect(cart.customerId).toBeNull();
    expect(cart.itemsCount).toBe(1);
    expect(cart.itemsQty).toBe(2);
    expect(cartProducts(cart)).toEqual([{ productId, quantity: 2 }]);
  });

  test('should return the active cart of the bearer customer', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const customer = await registerShopCustomer(request);
    const line = await addCartLine(request, customer.token, productId, 1);

    const body = await runGraphQL(request, SHOP_CART_QUERIES.readGetCartToken, {}, bearer(customer.token));

    expectNoErrors(body);

    const cart = body.data.readGetCartToken.getCartToken;

    expect(Number(cart._id)).toBe(line.cartId);
    expect(cart.isGuest).toBe(false);
    expect(cart.customerId).toBe(customer.id);
    expect(cartProducts(cart).map((item) => item.productId)).toEqual([productId]);
  });

  test('should refuse to read a cart without a bearer token', async ({ request }) => {
    const body = await runGraphQL(request, SHOP_CART_QUERIES.readGetCartToken);

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.AUTHENTICATION_REQUIRED);
    expect(body.data?.readGetCartToken ?? null).toBeNull();
  });

  test('should report an unknown cart token as a missing cart', async ({ request }) => {
    const body = await runGraphQL(request, SHOP_CART_QUERIES.readGetCartToken, {}, bearer(randomUuid()));

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.CART_NOT_FOUND);
    expect(body.data?.readGetCartToken ?? null).toBeNull();
  });
});

test.describe('shop create merge cart graphql api', () => {
  test('should merge a guest cart into the customer cart', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const customer = await registerShopCustomer(request);
    await addCartLine(request, customer.token, productId, 1);
    const guest = await createGuestCart(request);
    const guestLine = await addCartLine(request, guest.token, productId, 2);

    const body = await runGraphQL(
      request,
      SHOP_CART_QUERIES.createMergeCart,
      { guestCartToken: guest.token, cartId: guestLine.cartId },
      bearer(customer.token)
    );

    expectNoErrors(body);

    const cart = body.data.createMergeCart.mergeCart;

    expect(cart.success).toBe(true);
    expect(cart.message).toBe(SHOP_CART_MESSAGES.GUEST_CART_MERGED);
    expect(cart.isGuest).toBe(false);
    expect(cart.customerId).toBe(customer.id);
    expect(cartProducts(cart)).toEqual([{ productId, quantity: 3 }]);

    const stale = await runGraphQL(request, SHOP_CART_QUERIES.readGetCartToken, {}, bearer(guest.token));

    expect(graphQLErrorMessages(stale)).toContain(SHOP_CART_MESSAGES.CART_NOT_FOUND);
  });

  test('should refuse a merge authorised only by a guest cart token', async ({ request }) => {
    const guest = await createGuestCart(request);

    const body = await runGraphQL(
      request,
      SHOP_CART_QUERIES.createMergeCart,
      { guestCartToken: guest.token },
      bearer(guest.token)
    );

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.MERGE_REQUIRES_AUTH);
    expect(body.data?.createMergeCart ?? null).toBeNull();
  });

  test('should refuse a merge without a bearer token', async ({ request }) => {
    const guest = await createGuestCart(request);

    const body = await runGraphQL(request, SHOP_CART_QUERIES.createMergeCart, { guestCartToken: guest.token });

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.AUTHENTICATION_REQUIRED);
    expect(body.data?.createMergeCart ?? null).toBeNull();
  });

  test('should report an unknown guest cart token as not found', async ({ request }) => {
    const customer = await registerShopCustomer(request);

    const body = await runGraphQL(
      request,
      SHOP_CART_QUERIES.createMergeCart,
      { guestCartToken: randomUuid() },
      bearer(customer.token)
    );

    expect(graphQLErrorMessages(body)).toContain(SHOP_CART_MESSAGES.GUEST_CART_NOT_FOUND);
    expect(body.data?.createMergeCart ?? null).toBeNull();
  });
});
