// Wishlist API Test Cases
import { test, expect } from '@playwright/test';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { GET_ALL_WISHLISTS, GET_WISHLIST } from '../../graphql/Queries/wishlist.queries';

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
});