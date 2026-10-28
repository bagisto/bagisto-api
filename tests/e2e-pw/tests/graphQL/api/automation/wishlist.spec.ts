import { test, expect, APIRequestContext } from '@playwright/test';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { getCustomerAuthHeaders } from '../../config/auth';
import {
  CREATE_WISHLIST,
  DELETE_ALL_WISHLISTS,
  DELETE_WISHLIST,
  GET_ALL_WISHLISTS,
  GET_WISHLIST,
  GET_WISHLIST_ITEM_IDS,
  TOGGLE_WISHLIST,
} from '../../graphql/Queries/wishlist.queries';
import { graphQLErrorMessages } from '../../graphql/helpers/testSupport';
import { findSaleableSimpleProducts } from '../../../restAPI/rest/helpers/coverage/shopCart.helpers';

const MESSAGES = {
  UNAUTHENTICATED: 'Unauthenticated. Please login to perform this action',
  NOT_FOUND: 'Wishlist item not found',
  ALREADY_EXISTS: 'This product is already in your wishlist',
  ADDED: 'Item Successfully Added To Wishlist',
  REMOVED: 'Item Successfully Removed From Wishlist',
  MOVED_TO_CART: 'Item moved to cart successfully',
  DELETE_ALL_SUCCESS: 'All wishlist items have been removed successfully',
  PRODUCT_NOT_FOUND: 'Product not found',
  ID_ARGUMENT_REQUIRED: 'Field "wishlist" argument "id" of type "ID!" is required but not provided.',
};

const TOGGLE_WITH_MESSAGE = `
  mutation ToggleWishlist($input: toggleWishlistInput!) {
    toggleWishlist(input: $input) {
      wishlist {
        _id
        message
        product {
          _id
        }
      }
    }
  }
`;

const MOVE_TO_CART = `
  mutation MoveWishlistToCart($input: moveWishlistToCartInput!) {
    moveWishlistToCart(input: $input) {
      wishlistToCart {
        success
        message
        itemsQty
      }
    }
  }
`;

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

function wishlistIri(id: number): string {
  return `/api/shop/wishlists/${id}`;
}

async function addToWishlist(request: APIRequestContext, productId: number, headers: Record<string, string>): Promise<number> {
  const body = await runGraphQL(request, CREATE_WISHLIST, { input: { productId } }, headers);

  expectNoErrors(body);

  const id = Number(body.data.createWishlist.wishlist._id);

  expect(id).toBeGreaterThan(0);

  return id;
}

async function listWishlistIds(request: APIRequestContext, headers: Record<string, string>): Promise<number[]> {
  const body = await runGraphQL(request, GET_WISHLIST_ITEM_IDS, { first: 50 }, headers);

  expectNoErrors(body);

  return body.data.wishlists.edges.map((edge: any) => Number(edge.node._id));
}

test.describe('wishlist graphql api tests', () => {
  test.describe.configure({ timeout: 120_000 });

  test('should refuse to list wishlists without a bearer token', async ({ request }) => {
    const body = await runGraphQL(request, GET_ALL_WISHLISTS, { first: 10 });

    expect(graphQLErrorMessages(body)).toEqual([MESSAGES.UNAUTHENTICATED]);
    expect(body.data?.wishlists ?? null).toBeNull();
  });

  test('should page the customer wishlist with first and after', async ({ request }) => {
    const [firstProductId, secondProductId] = await findSaleableSimpleProducts(request, 2);
    const headers = await getCustomerAuthHeaders(request);
    const firstItemId = await addToWishlist(request, firstProductId, headers);
    const secondItemId = await addToWishlist(request, secondProductId, headers);

    const firstPage = await runGraphQL(request, GET_ALL_WISHLISTS, { first: 1 }, headers);
    expectNoErrors(firstPage);

    const page = firstPage.data.wishlists;

    expect(page.totalCount).toBe(2);
    expect(page.edges.map((edge: any) => Number(edge.node._id))).toEqual([firstItemId]);
    expect(Number(page.edges[0].node.product.id.split('/').pop())).toBe(firstProductId);
    expect(page.pageInfo.hasNextPage).toBe(true);
    expect(page.pageInfo.hasPreviousPage).toBe(false);

    const secondPage = await runGraphQL(request, GET_ALL_WISHLISTS, { first: 1, after: page.pageInfo.endCursor }, headers);
    expectNoErrors(secondPage);

    expect(secondPage.data.wishlists.edges.map((edge: any) => Number(edge.node._id))).toEqual([secondItemId]);
    expect(secondPage.data.wishlists.pageInfo.hasNextPage).toBe(false);
  });

  test('should get a wishlist item of the customer by id', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const headers = await getCustomerAuthHeaders(request);
    const itemId = await addToWishlist(request, productId, headers);

    const body = await runGraphQL(request, GET_WISHLIST, { id: wishlistIri(itemId) }, headers);
    expectNoErrors(body);

    expect(Number(body.data.wishlist._id)).toBe(itemId);
    expect(body.data.wishlist.id).toBe(wishlistIri(itemId));
    expect(Number(body.data.wishlist.product.id.split('/').pop())).toBe(productId);
  });

  test('should report a wishlist item that does not exist', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);

    const body = await runGraphQL(request, GET_WISHLIST, { id: wishlistIri(999999999) }, headers);

    expect(graphQLErrorMessages(body)).toEqual([MESSAGES.NOT_FOUND]);
    expect(body.data?.wishlist ?? null).toBeNull();
  });

  test('should not reveal a wishlist item of another customer', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const owner = await getCustomerAuthHeaders(request);
    const itemId = await addToWishlist(request, productId, owner);
    const other = await getCustomerAuthHeaders(request);

    const body = await runGraphQL(request, GET_WISHLIST, { id: wishlistIri(itemId) }, other);

    expect(graphQLErrorMessages(body)).toEqual([MESSAGES.NOT_FOUND]);
    expect(body.data?.wishlist ?? null).toBeNull();
  });

  test('should reject a wishlist query without the id argument', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);
    const body = await runGraphQL(request, `query GetWishlist { wishlist { id } }`, {}, headers);

    expect(graphQLErrorMessages(body)).toEqual([MESSAGES.ID_ARGUMENT_REQUIRED]);
    expect(body.data).toBeUndefined();
  });

  test('should fall back to the first page for a cursor that does not encode an offset', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const headers = await getCustomerAuthHeaders(request);
    const itemId = await addToWishlist(request, productId, headers);
    const after = Buffer.from('not-an-offset').toString('base64');

    const body = await runGraphQL(request, GET_ALL_WISHLISTS, { first: 5, after }, headers);
    expectNoErrors(body);

    expect(body.data.wishlists.totalCount).toBe(1);
    expect(body.data.wishlists.edges.map((edge: any) => Number(edge.node._id))).toEqual([itemId]);
  });

  test('should refuse to add a product already in the wishlist', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const headers = await getCustomerAuthHeaders(request);
    const itemId = await addToWishlist(request, productId, headers);

    const body = await runGraphQL(request, CREATE_WISHLIST, { input: { productId } }, headers);

    expect(graphQLErrorMessages(body)).toEqual([MESSAGES.ALREADY_EXISTS]);
    expect(await listWishlistIds(request, headers)).toEqual([itemId]);
  });

  test('should toggle a product out of and back into the wishlist', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const headers = await getCustomerAuthHeaders(request);
    await addToWishlist(request, productId, headers);

    const removed = await runGraphQL(request, TOGGLE_WITH_MESSAGE, { input: { productId } }, headers);

    expect(graphQLErrorMessages(removed)).toEqual([MESSAGES.REMOVED]);
    expect(await listWishlistIds(request, headers)).toEqual([]);

    const added = await runGraphQL(request, TOGGLE_WITH_MESSAGE, { input: { productId } }, headers);
    expectNoErrors(added);

    const item = added.data.toggleWishlist.wishlist;

    expect(item.message).toBe(MESSAGES.ADDED);
    expect(Number(item.product._id)).toBe(productId);
    expect(await listWishlistIds(request, headers)).toEqual([Number(item._id)]);
  });

  test('should move a wishlist item into the customer cart', async ({ request }) => {
    const [productId] = await findSaleableSimpleProducts(request, 1);
    const headers = await getCustomerAuthHeaders(request);
    const itemId = await addToWishlist(request, productId, headers);

    const body = await runGraphQL(request, MOVE_TO_CART, { input: { wishlistItemId: itemId, quantity: 1 } }, headers);
    expectNoErrors(body);

    expect(body.data.moveWishlistToCart.wishlistToCart).toEqual({
      success: true,
      message: MESSAGES.MOVED_TO_CART,
      itemsQty: 1,
    });
    expect(await listWishlistIds(request, headers)).toEqual([]);
  });

  test('should report a missing wishlist item on move to cart', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);

    const body = await runGraphQL(request, MOVE_TO_CART, { input: { wishlistItemId: 999999999, quantity: 1 } }, headers);

    expect(graphQLErrorMessages(body)).toEqual([MESSAGES.NOT_FOUND]);
    expect(body.data?.moveWishlistToCart ?? null).toBeNull();
  });

  test('should delete one wishlist item of the customer', async ({ request }) => {
    const [firstProductId, secondProductId] = await findSaleableSimpleProducts(request, 2);
    const headers = await getCustomerAuthHeaders(request);
    const firstItemId = await addToWishlist(request, firstProductId, headers);
    const secondItemId = await addToWishlist(request, secondProductId, headers);

    const body = await runGraphQL(request, DELETE_WISHLIST, { input: { id: wishlistIri(firstItemId) } }, headers);
    expectNoErrors(body);

    expect(body.data.deleteWishlist.wishlist.id).toBe(wishlistIri(firstItemId));
    expect(await listWishlistIds(request, headers)).toEqual([secondItemId]);
  });

  test('should delete every wishlist item of the customer', async ({ request }) => {
    const [firstProductId, secondProductId] = await findSaleableSimpleProducts(request, 2);
    const headers = await getCustomerAuthHeaders(request);
    await addToWishlist(request, firstProductId, headers);
    await addToWishlist(request, secondProductId, headers);

    const body = await runGraphQL(request, DELETE_ALL_WISHLISTS, {}, headers);
    expectNoErrors(body);

    expect(body.data.createDeleteAllWishlists.deleteAllWishlists).toEqual({
      message: MESSAGES.DELETE_ALL_SUCCESS,
      deletedCount: 2,
    });
    expect(await listWishlistIds(request, headers)).toEqual([]);
  });
});

test.describe('wishlist add and toggle authorization graphql', () => {
  const MISSING_PRODUCT_ID = 999999999;

  test('should refuse a wishlist add from an anonymous caller before looking up the product', async ({ request }) => {
    const response = await sendGraphQLRequest(request, CREATE_WISHLIST, { input: { productId: MISSING_PRODUCT_ID } });
    expect(response.status()).toBe(200);

    const body = await response.json();
    const messages = graphQLErrorMessages(body);
    expect(messages).toContain(MESSAGES.UNAUTHENTICATED);
    expect(messages).not.toContain(MESSAGES.PRODUCT_NOT_FOUND);
    expect(body.data?.createWishlist ?? null).toBeNull();
  });

  test('should refuse a wishlist toggle from an anonymous caller before looking up the product', async ({ request }) => {
    const response = await sendGraphQLRequest(request, TOGGLE_WISHLIST, { input: { productId: MISSING_PRODUCT_ID } });
    expect(response.status()).toBe(200);

    const body = await response.json();
    const messages = graphQLErrorMessages(body);
    expect(messages).toContain(MESSAGES.UNAUTHENTICATED);
    expect(messages).not.toContain(MESSAGES.PRODUCT_NOT_FOUND);
    expect(body.data?.toggleWishlist ?? null).toBeNull();
  });

  test('should report a missing product to an authenticated customer toggling the wishlist', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);

    const response = await sendGraphQLRequest(
      request,
      TOGGLE_WISHLIST,
      { input: { productId: MISSING_PRODUCT_ID } },
      headers
    );
    expect(response.status()).toBe(200);

    const body = await response.json();
    expect(graphQLErrorMessages(body)).toEqual([MESSAGES.PRODUCT_NOT_FOUND]);
    expect(body.data?.toggleWishlist ?? null).toBeNull();
  });

  test('should list a product an authenticated customer adds to the wishlist', async ({ request }) => {
    const headers = await getCustomerAuthHeaders(request);
    const [productId] = await findSaleableSimpleProducts(request, 1);

    const createResponse = await sendGraphQLRequest(request, CREATE_WISHLIST, { input: { productId } }, headers);
    expect(createResponse.status()).toBe(200);

    const createBody = await createResponse.json();
    expect(createBody.errors, `create wishlist errored: ${graphQLErrorMessages(createBody).join(' | ')}`).toBeUndefined();

    const wishlistItemId = Number(createBody.data?.createWishlist?.wishlist?._id);
    expect(wishlistItemId).toBeGreaterThan(0);

    const listResponse = await sendGraphQLRequest(request, GET_WISHLIST_ITEM_IDS, { first: 10 }, headers);
    expect(listResponse.status()).toBe(200);

    const listBody = await listResponse.json();
    expect(listBody.errors, `list wishlists errored: ${graphQLErrorMessages(listBody).join(' | ')}`).toBeUndefined();

    const itemIds = (listBody.data?.wishlists?.edges ?? []).map((edge: any) => Number(edge.node._id));
    expect(itemIds).toEqual([wishlistItemId]);
  });
});
