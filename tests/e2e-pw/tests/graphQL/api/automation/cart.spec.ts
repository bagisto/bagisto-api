import { test, expect, APIRequestContext } from '@playwright/test';
import { getGuestCartHeaders } from '../../config/auth';
import {
  ADD_PRODUCT_TO_CART,
  APPLY_COUPON,
  REMOVE_CART_ITEM,
  REMOVE_COUPON,
  UPDATE_CART_ITEM,
} from '../../graphql/Queries/cart.queries';
import { SHOP_DOCS_QUERIES } from '../../graphql/Queries/shopDocs.queries';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../graphql/helpers/testSupport';

async function getFirstProductId(request: APIRequestContext): Promise<number> {
  const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getProducts, { first: 1 });
  const body = await response.json();
  const node = body.data?.products?.edges?.[0]?.node;
  const numericId = Number(String(node?.id ?? '').split('/').pop());

  expect(numericId > 0, 'test store must have at least one product available').toBeTruthy();
  return numericId;
}

async function addProductAndGetItemId(
  request: APIRequestContext,
  guestHeaders: Record<string, string>
): Promise<number> {
  const productId = await getFirstProductId(request);
  const response = await sendGraphQLRequest(
    request,
    ADD_PRODUCT_TO_CART,
    { input: { productId, quantity: 1 } },
    guestHeaders
  );
  expect(response.status()).toBe(200);

  const body = await response.json();
  expect(body.errors, `add-to-cart errored: ${graphQLErrorMessages(body).join(' | ')}`).toBeUndefined();

  const payload = body.data?.createAddProductInCart?.addProductInCart;
  expect(payload?.success).toBe(true);

  const rawItemId = payload?.items?.edges?.[0]?.node?.id;
  const itemId = Number(rawItemId);
  expect(itemId, `unable to read numeric cart item id from: ${rawItemId}`).toBeGreaterThan(0);
  return itemId;
}

test.describe('Cart GraphQL API Tests', () => {
  test.slow();

  test('Should create a guest cart token successfully', async ({ request }) => {
    const guestHeaders = await getGuestCartHeaders(request);
    expect(guestHeaders.Authorization).toMatch(/^Bearer .+/);
  });

  test('Should return a GraphQL validation error for an invalid cart query', async ({ request }) => {
    const invalidQuery = `
      mutation invalidReadCart {
        createReadCart(input: { invalid: "value" }) {
          id
        }
      }
    `;

    const response = await sendGraphQLRequest(request, invalidQuery);
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(graphQLErrorMessages(body).length).toBeGreaterThan(0);
  });

  test('Should add a product to the cart', async ({ request }) => {
    const guestHeaders = await getGuestCartHeaders(request);
    const productId = await getFirstProductId(request);

    const response = await sendGraphQLRequest(
      request,
      ADD_PRODUCT_TO_CART,
      { input: { productId, quantity: 1 } },
      guestHeaders
    );
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(body.errors, `add-to-cart errored: ${graphQLErrorMessages(body).join(' | ')}`).toBeUndefined();

    const payload = body.data?.createAddProductInCart?.addProductInCart;
    expect(payload?.success).toBe(true);
    expect(payload?.message).toMatch(/added/i);
    expect(payload?.items?.edges?.[0]?.node?.productId).toBe(productId);
  });

  test('Should fetch the current cart using the docs-aligned read cart mutation', async ({ request }) => {
    const guestHeaders = await getGuestCartHeaders(request);
    await addProductAndGetItemId(request, guestHeaders);

    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.createReadCart, {}, guestHeaders);
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(body.errors, `read-cart errored: ${graphQLErrorMessages(body).join(' | ')}`).toBeUndefined();

    const readCart = body.data?.createReadCart?.readCart;
    expect(readCart).toBeTruthy();
    expect(readCart.itemsCount).toBeGreaterThan(0);
    expect(readCart.itemsQty).toBeGreaterThan(0);
    expect(readCart.isGuest).toBe(true);
  });

  test('Should update the quantity of a cart item', async ({ request }) => {
    const guestHeaders = await getGuestCartHeaders(request);
    const cartItemId = await addProductAndGetItemId(request, guestHeaders);

    const response = await sendGraphQLRequest(
      request,
      UPDATE_CART_ITEM,
      { input: { cartItemId, quantity: 3 } },
      guestHeaders
    );
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(body.errors, `update errored: ${graphQLErrorMessages(body).join(' | ')}`).toBeUndefined();

    const payload = body.data?.createUpdateCartItem?.updateCartItem;
    expect(payload?.success).toBe(true);
    const updatedItem = payload?.items?.edges?.find((edge: any) => Number(edge?.node?.id) === cartItemId);
    expect(updatedItem?.node?.quantity).toBe(3);
  });

  test('Should remove an item from the cart', async ({ request }) => {
    const guestHeaders = await getGuestCartHeaders(request);
    const cartItemId = await addProductAndGetItemId(request, guestHeaders);

    const response = await sendGraphQLRequest(
      request,
      REMOVE_CART_ITEM,
      { input: { cartItemId } },
      guestHeaders
    );
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(body.errors, `remove errored: ${graphQLErrorMessages(body).join(' | ')}`).toBeUndefined();

    const payload = body.data?.createRemoveCartItem?.removeCartItem;
    expect(payload).toBeTruthy();
    const stillPresent = payload?.items?.edges?.some((edge: any) => Number(edge?.node?.id) === cartItemId);
    expect(stillPresent).toBeFalsy();
  });

  test('Should apply and remove a coupon on the cart', async ({ request }) => {
    const guestHeaders = await getGuestCartHeaders(request);
    await addProductAndGetItemId(request, guestHeaders);

    const applyResponse = await sendGraphQLRequest(
      request,
      APPLY_COUPON,
      { couponCode: 'SAVE10' },
      guestHeaders
    );
    expect(applyResponse.status()).toBe(200);

    const applyBody = await applyResponse.json();
    expect(applyBody.errors, `apply coupon errored: ${graphQLErrorMessages(applyBody).join(' | ')}`).toBeUndefined();
    expect(applyBody.data?.createApplyCoupon?.applyCoupon).toBeTruthy();

    const removeResponse = await sendGraphQLRequest(request, REMOVE_COUPON, {}, guestHeaders);
    expect(removeResponse.status()).toBe(200);

    const removeBody = await removeResponse.json();
    expect(removeBody.errors, `remove coupon errored: ${graphQLErrorMessages(removeBody).join(' | ')}`).toBeUndefined();

    const removePayload = removeBody.data?.createRemoveCoupon?.removeCoupon;
    expect(removePayload).toBeTruthy();
    expect(removePayload.couponCode).toBeNull();
  });
});
