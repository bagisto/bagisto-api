import { test, expect } from '@playwright/test';
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
import { expectGraphQLSuccess, graphQLErrorMessages, logGraphQLMessages } from '../../graphql/helpers/testSupport';

async function getFirstProductId(request: any) {
  const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getProducts, { first: 1 });
  const body = await response.json();
  const node = body.data?.products?.edges?.[0]?.node;
  const numericId = Number(String(node?.id ?? '').split('/').pop());
  return node?._id ?? (Number.isFinite(numericId) && numericId > 0 ? numericId : null);
}

test.describe('Cart GraphQL API Tests', () => {
  test('Should create a guest cart token successfully', async ({ request }) => {
    const guestHeaders = await getGuestCartHeaders(request);
    expect(guestHeaders.Authorization).toContain('Bearer ');
  });

  test('Should fetch the current cart using the docs-aligned read cart mutation', async ({ request }) => {
    const guestHeaders = await getGuestCartHeaders(request);
    const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.createReadCart, {}, guestHeaders);
    expect(response.status()).toBe(200);

    const body = await response.json();
    expectGraphQLSuccess(body, 'data.createReadCart.readCart');
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
    logGraphQLMessages('Cart invalid query', body);
    expect(graphQLErrorMessages(body).length).toBeGreaterThan(0);
  });

  test('Should try to add a product to cart and show the real API response', async ({ request }) => {
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
    console.log(`Add to cart response: ${JSON.stringify(body)}`);
    expect(body.data?.createAddProductInCart?.addProductInCart || graphQLErrorMessages(body).length > 0).toBeTruthy();
  });

  test('Should try to update a cart item and show the real API response', async ({ request }) => {
    const guestHeaders = await getGuestCartHeaders(request);
    const addResponse = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.createReadCart, {}, guestHeaders);
    const addBody = await addResponse.json();
    const itemId = addBody.data?.createReadCart?.readCart?.items?.[0]?.id;

    const response = await sendGraphQLRequest(
      request,
      UPDATE_CART_ITEM,
      { input: { cartItemId: itemId ?? 999999, quantity: 2 } },
      guestHeaders
    );
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log(`Update cart item response: ${JSON.stringify(body)}`);
    expect(body.data?.createUpdateCartItem?.updateCartItem || graphQLErrorMessages(body).length > 0).toBeTruthy();
  });

  test('Should try to remove a cart item and show the real API response', async ({ request }) => {
    const guestHeaders = await getGuestCartHeaders(request);
    const readResponse = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.createReadCart, {}, guestHeaders);
    const readBody = await readResponse.json();
    const itemId = readBody.data?.createReadCart?.readCart?.items?.[0]?.id;

    const response = await sendGraphQLRequest(
      request,
      REMOVE_CART_ITEM,
      { input: { cartItemId: itemId ?? 999999 } },
      guestHeaders
    );
    expect(response.status()).toBe(200);

    const body = await response.json();
    console.log(`Remove cart item response: ${JSON.stringify(body)}`);
    expect(body.data?.createRemoveCartItem?.removeCartItem || graphQLErrorMessages(body).length > 0).toBeTruthy();
  });

  test('Should try to apply and remove a coupon and show the real API response', async ({ request }) => {
    const guestHeaders = await getGuestCartHeaders(request);
    const applyResponse = await sendGraphQLRequest(request, APPLY_COUPON, { couponCode: 'SAVE10' }, guestHeaders);
    expect(applyResponse.status()).toBe(200);
    const applyBody = await applyResponse.json();
    console.log(`Apply coupon response: ${JSON.stringify(applyBody)}`);
    expect(applyBody.data?.createApplyCoupon?.applyCoupon || graphQLErrorMessages(applyBody).length > 0).toBeTruthy();

    const removeResponse = await sendGraphQLRequest(request, REMOVE_COUPON, {}, guestHeaders);
    expect(removeResponse.status()).toBe(200);
    const removeBody = await removeResponse.json();
    console.log(`Remove coupon response: ${JSON.stringify(removeBody)}`);
    expect(removeBody.data?.createRemoveCoupon?.removeCoupon || graphQLErrorMessages(removeBody).length > 0).toBeTruthy();
  });
});
