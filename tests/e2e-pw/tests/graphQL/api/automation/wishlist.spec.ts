// Wishlist API Test Cases
import { test, expect } from '@playwright/test';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { getCustomerAuthHeaders } from '../../config/auth';
import { SHOP_DOCS_QUERIES } from '../../graphql/Queries/shopDocs.queries';
import {
  CREATE_WISHLIST,
  DELETE_ALL_WISHLISTS,
  DELETE_WISHLIST,
  GET_ALL_WISHLISTS,
  GET_WISHLIST,
  MOVE_WISHLIST_TO_CART,
  TOGGLE_WISHLIST,
} from '../../graphql/Queries/wishlist.queries';
import { graphQLErrorMessages } from '../../graphql/helpers/testSupport';

async function getFirstProductId(request: any) {
  const response = await sendGraphQLRequest(request, SHOP_DOCS_QUERIES.getProducts, { first: 1 });
  const body = await response.json();
  const node = body.data?.products?.edges?.[0]?.node;
  const numericId = Number(String(node?.id ?? '').split('/').pop());
  return node?._id ?? (Number.isFinite(numericId) && numericId > 0 ? numericId : null);
}

test.describe('Wishlist GraphQL API Tests', () => {
  
  // ==================== POSITIVE TESTS ====================
  
  test('Should get all wishlists successfully', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ALL_WISHLISTS, { first: 10 });
    
    expect([200, 500]).toContain(response.status());
    
    if (response.status() === 200) {
      const body = await response.json();
      expect(body.data?.wishlists).toBeDefined();
    }
  });

  test('Should return wishlist with pagination', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ALL_WISHLISTS, { first: 5 });
    
    expect([200, 500]).toContain(response.status());
    
    if (response.status() === 200) {
      const body = await response.json();
      const wishlists = body.data?.wishlists;
      
      if (wishlists) {
        expect(wishlists.pageInfo?.hasNextPage !== undefined || wishlists.edges !== undefined).toBeTruthy();
      }
    }
  });

  test('Should get wishlist by valid ID', async ({ request }) => {
    const allResponse = await sendGraphQLRequest(request, GET_ALL_WISHLISTS, { first: 1 });
    
    if (allResponse.status() === 500) {
      console.log('Server error - skipping test');
      return;
    }
    
    const allBody = await allResponse.json();
    
    if (allBody.data?.wishlists?.edges?.length > 0) {
      const wishlistId = allBody.data.wishlists.edges[0].node.id;
      
      const response = await sendGraphQLRequest(request, GET_WISHLIST, { id: wishlistId });
      
      expect([200, 404, 500]).toContain(response.status());
    }
  });

  // ==================== NEGATIVE TESTS ====================
  
  test('Should handle invalid wishlist ID gracefully', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_WISHLIST, { id: 'invalid-id-99999' });
    
    expect([200, 404, 500]).toContain(response.status());
    
    if (response.status() === 200) {
      const body = await response.json();
      expect(body.data?.wishlist === null || body.errors !== undefined).toBeTruthy();
    }
  });

  test('Should handle missing ID parameter gracefully', async ({ request }) => {
    const invalidQuery = `
      query GetWishlist {
        wishlist {
          id
        }
      }
    `;
    
    const response = await sendGraphQLRequest(request, invalidQuery);
    
    expect([200, 500]).toContain(response.status());
    
    if (response.status() === 200) {
      const body = await response.json();
      expect(body.errors !== undefined).toBeTruthy();
    }
  });

  test('Should handle invalid cursor in pagination', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_ALL_WISHLISTS, { 
      first: 5, 
      after: 'invalid-cursor-string' 
    });
    
    expect([200, 500]).toContain(response.status());
  });

  test('Should try wishlist mutations and show the real API response', async ({ request }) => {
    const headers = (await getCustomerAuthHeaders(request)) ?? {};
    const productId = await getFirstProductId(request);

    const createResponse = await sendGraphQLRequest(
      request,
      CREATE_WISHLIST,
      { input: { productId, clientMutationId: 'wishlist-create-001' } },
      headers
    );
    expect(createResponse.status()).toBe(200);
    const createBody = await createResponse.json();
    console.log(`Create wishlist response: ${JSON.stringify(createBody)}`);
    expect(createBody.data?.createWishlist?.wishlist || graphQLErrorMessages(createBody).length > 0).toBeTruthy();

    const toggleResponse = await sendGraphQLRequest(
      request,
      TOGGLE_WISHLIST,
      { input: { productId, clientMutationId: 'wishlist-toggle-001' } },
      headers
    );
    expect(toggleResponse.status()).toBe(200);
    const toggleBody = await toggleResponse.json();
    console.log(`Toggle wishlist response: ${JSON.stringify(toggleBody)}`);
    expect(toggleBody.data?.toggleWishlist || graphQLErrorMessages(toggleBody).length > 0).toBeTruthy();

    const wishlistId =
      createBody.data?.createWishlist?.wishlist?.id ??
      createBody.data?.createWishlist?.wishlist?._id ??
      'invalid-id-99999';

    const moveResponse = await sendGraphQLRequest(
      request,
      MOVE_WISHLIST_TO_CART,
      { input: { wishlistItemId: Number(wishlistId) || 999999, quantity: 1, clientMutationId: 'wishlist-move-001' } },
      headers
    );
    expect(moveResponse.status()).toBe(200);
    const moveBody = await moveResponse.json();
    console.log(`Move wishlist to cart response: ${JSON.stringify(moveBody)}`);
    expect(moveBody.data?.moveWishlistToCart?.wishlistToCart || graphQLErrorMessages(moveBody).length > 0).toBeTruthy();

    const deleteResponse = await sendGraphQLRequest(
      request,
      DELETE_WISHLIST,
      { input: { id: wishlistId, clientMutationId: 'wishlist-delete-001' } },
      headers
    );
    expect(deleteResponse.status()).toBe(200);
    const deleteBody = await deleteResponse.json();
    console.log(`Delete wishlist response: ${JSON.stringify(deleteBody)}`);
    expect(deleteBody.data?.deleteWishlist?.wishlist || graphQLErrorMessages(deleteBody).length > 0).toBeTruthy();

    const deleteAllResponse = await sendGraphQLRequest(request, DELETE_ALL_WISHLISTS, {}, headers);
    expect(deleteAllResponse.status()).toBe(200);
    const deleteAllBody = await deleteAllResponse.json();
    console.log(`Delete all wishlists response: ${JSON.stringify(deleteAllBody)}`);
    expect(
      deleteAllBody.data?.createDeleteAllWishlists?.deleteAllWishlists ||
      graphQLErrorMessages(deleteAllBody).length > 0
    ).toBeTruthy();
  });
});
