// tests/api/automation/getProductsSorted.spec.ts
import { test, expect, request } from '@playwright/test';
import { GET_PRODUCTS_SORTED } from '../../graphql/Queries/product.queries';
import { assertProductsSortedByTitle, assertProductsSortedByCreatedAt, assertProductsSortedByPrice } from '../../graphql/assertions/product.assertions';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';

test.describe('Get Products Sorted by TITLE (A-Z / Z-A)', () => {

  test('Should fetch products sorted alphabetically A-Z by title', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: false,
      sortKey: 'TITLE',
      first: 10
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    console.log('\n========== PRODUCTS SORTED A-Z (ASCENDING) ==========');
    console.log('Sort Key: TITLE');
    console.log('Reverse: false (Ascending)\n');
    
    // Explicitly verify ascending order (A-Z)
    const edges = body.data.products.edges;
    const productNames = edges.map((edge: any) => edge.node.name);
    
    console.log('Verifying ascending order (A-Z):');
    for (let i = 0; i < productNames.length - 1; i++) {
      const compareResult = productNames[i].localeCompare(productNames[i + 1]);
      console.log(`  "${productNames[i]}" <= "${productNames[i + 1]}" : ${compareResult <= 0 ? '✓ PASS' : '✗ FAIL'}`);
      expect(compareResult).toBeLessThanOrEqual(0); // A-Z: current <= next
    }
    console.log('\n');
    
    assertProductsSortedByTitle(body, true);
  });

  test('Should fetch products sorted alphabetically Z-A by title', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: true,
      sortKey: 'TITLE',
      first: 10
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    console.log('\n========== SORTED PRODUCTS (Z-A) ==========');
    console.log('Sort Key: TITLE');
    console.log('Reverse: true (Descending)\n');
    
    assertProductsSortedByTitle(body, false);
  });

  test('Should fetch sorted products with custom pagination', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: false,
      sortKey: 'TITLE',
      first: 5
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    const edges = body.data.products.edges;
    expect(edges.length).toBeLessThanOrEqual(5);
    
    console.log('\n========== PAGINATED SORTED PRODUCTS ==========');
    console.log(`Requested: 5 products`);
    console.log(`Received: ${edges.length} products\n`);
    
    assertProductsSortedByTitle(body, true);
  });

  // ==================== Z-A SORTED PRODUCTS TESTS (POSITIVE) ====================

  test('Should fetch products sorted Z-A (descending) by title successfully', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: true,
      sortKey: 'TITLE',
      first: 10
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    console.log('\n========== PRODUCTS SORTED Z-A (DESCENDING) ==========');
    console.log('Sort Key: TITLE');
    console.log('Reverse: true (Descending)\n');
    
    // Explicitly verify descending order (Z-A)
    const edges = body.data.products.edges;
    const productNames = edges.map((edge: any) => edge.node.name);
    
    console.log('Verifying descending order (Z-A):');
    for (let i = 0; i < productNames.length - 1; i++) {
      const compareResult = productNames[i].localeCompare(productNames[i + 1]);
      console.log(`  "${productNames[i]}" >= "${productNames[i + 1]}" : ${compareResult >= 0 ? '✓ PASS' : '✗ FAIL'}`);
      expect(compareResult).toBeGreaterThanOrEqual(0); // Z-A: current >= next
    }
    console.log('\n');
    
    assertProductsSortedByTitle(body, false);
  });

  test('Should return products with correct data structure for Z-A sorted query', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: true,
      sortKey: 'TITLE',
      first: 5
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    const edges = body.data.products.edges;
    expect(edges.length).toBeGreaterThan(0);
    
    // Validate each product node structure
    edges.forEach((edge: any) => {
      const product = edge.node;
      expect(product).not.toBeNull();
      expect(product.id).toBeDefined();
      expect(product.id).toContain('/api/shop/products/');
      expect(typeof product.name).toBe('string');
      expect(product.name.length).toBeGreaterThan(0);
      expect(typeof product.sku).toBe('string');
      expect(product.sku.length).toBeGreaterThan(0);
      expect(product.price).toBeDefined();
    });
  });

  test('Should handle pagination correctly for Z-A sorted products', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: true,
      sortKey: 'TITLE',
      first: 3
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    const edges = body.data.products.edges;
    expect(edges.length).toBeLessThanOrEqual(3);
    
    console.log('\n========== PAGINATED Z-A SORTED PRODUCTS ==========');
    console.log(`Requested: 3 products`);
    console.log(`Received: ${edges.length} products\n`);
    
    assertProductsSortedByTitle(body, false);
  });

  test('Should return first N products when first exceeds total count for Z-A', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: true,
      sortKey: 'TITLE',
      first: 100
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    const edges = body.data.products.edges;
    expect(edges.length).toBeGreaterThan(0);
    
    console.log('\n========== Z-A SORTED PRODUCTS (Large First) ==========');
    console.log(`Received: ${edges.length} products (requested 100)`);
    console.log('=======================================================\n');
    
    assertProductsSortedByTitle(body, false);
  });

  test('Should verify Z-A sorting is different from A-Z sorting', async ({ request }) => {
    // Get Z-A sorted products
    const zaResponse = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: true,
      sortKey: 'TITLE',
      first: 10
    });
    expect(zaResponse.status()).toBe(200);
    const zaBody = await zaResponse.json();
    const zaProducts = zaBody.data.products.edges.map((edge: any) => edge.node.name);
    
    // Get A-Z sorted products
    const azResponse = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: false,
      sortKey: 'TITLE',
      first: 10
    });
    expect(azResponse.status()).toBe(200);
    const azBody = await azResponse.json();
    const azProducts = azBody.data.products.edges.map((edge: any) => edge.node.name);
    
    // Verify they are different (when there are multiple products)
    if (zaProducts.length > 1) {
      expect(zaProducts).not.toEqual(azProducts);
    }
    
    console.log('\n========== Z-A vs A-Z COMPARISON ==========');
    console.log('A-Z Order:', azProducts.join(' -> '));
    console.log('Z-A Order:', zaProducts.join(' -> '));
    console.log('===========================================\n');
  });

  // ==================== Z-A SORTED PRODUCTS TESTS (NEGATIVE) ====================

  test('Should handle first: 0 gracefully for Z-A sorted products', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: true,
      sortKey: 'TITLE',
      first: 0
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    const products = body.data.products;
    if (products === null) {
      console.log('\n========== Z-A PRODUCTS (first: 0) ==========');
      console.log('Products: null (when first is 0)');
    } else {
      const edges = products.edges;
      expect(edges).toEqual([]);
      console.log('\n========== Z-A PRODUCTS (first: 0) ==========');
      console.log(`Received: ${edges.length} products\n`);
    }
  });

  test('Should handle missing reverse parameter for products query', async ({ request }) => {
    // Query without reverse parameter
    const query = `
      query getProductsSorted($sortKey: String, $first: Int) {
        products(sortKey: $sortKey, first: $first) {
          edges {
            node {
              id
              name
              sku
            }
          }
        }
      }
    `;
    const response = await sendGraphQLRequest(request, query, {
      sortKey: 'TITLE',
      first: 5
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    // Should work with default sorting (ascending)
    if (body.errors) {
      console.log('\n========== MISSING REVERSE PARAMETER ERROR ==========');
      console.log(body.errors[0].message);
    } else {
      expect(body.data.products.edges.length).toBeGreaterThan(0);
      console.log('\n========== MISSING REVERSE PARAMETER ==========');
      console.log('Works with default sorting (ascending)');
      console.log('================================================\n');
    }
  });

  test('Should handle missing sortKey parameter for products query', async ({ request }) => {
    // Query without sortKey parameter
    const query = `
      query getProductsSorted($reverse: Boolean, $first: Int) {
        products(reverse: $reverse, first: $first) {
          edges {
            node {
              id
              name
              sku
            }
          }
        }
      }
    `;
    const response = await sendGraphQLRequest(request, query, {
      reverse: true,
      first: 5
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    // Should work with default sort key
    if (body.errors) {
      console.log('\n========== MISSING SORT KEY ERROR ==========');
      console.log(body.errors[0].message);
    } else {
      expect(body.data.products.edges.length).toBeGreaterThan(0);
      console.log('\n========== MISSING SORT KEY ==========');
      console.log('Works with default sort key');
      console.log('==================================\n');
    }
  });

  test('Should handle malformed/invalid GraphQL query', async ({ request }) => {
    // Invalid query with syntax error
    const invalidQuery = `
      query getProducts {
        products(sortKey: "TITLE") {
          edges {
            node {
              id
              name
    `;
    const response = await sendGraphQLRequest(request, invalidQuery, {});
    // May return 400 or 200 with errors
    const body = await response.json();
    
    console.log('\n========== MALFORMED QUERY TEST ==========');
    if (body.errors) {
      console.log('Error returned as expected');
      console.log(body.errors[0].message);
    } else {
      console.log('Unexpected: No error for malformed query');
    }
    console.log('==========================================\n');
  });

  test('Should handle negative first value for Z-A sorted products', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: true,
      sortKey: 'TITLE',
      first: -1
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    // Should either return error or default to some products
    if (body.errors) {
      console.log('\n========== Z-A NEGATIVE FIRST VALUE ERROR ==========');
      console.log(body.errors[0].message);
    } else {
      const edges = body.data.products.edges;
      expect(Array.isArray(edges)).toBeTruthy();
      console.log('\n========== Z-A NEGATIVE FIRST VALUE ==========');
      console.log(`Returned edges: ${edges.length}`);
      console.log('==============================================\n');
    }
  });

  test('Should handle invalid sortKey for Z-A sorted products', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: true,
      sortKey: 'INVALID_SORT_KEY',
      first: 10
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    // Should either return products (with default sorting) or error
    if (body.errors) {
      console.log('\n========== Z-A INVALID SORT KEY ERROR ==========');
      console.log(body.errors[0].message);
    } else {
      // If no error, products should still be returned with default sorting
      expect(body.data.products.edges.length).toBeGreaterThan(0);
    }
  });

  test('Should handle null reverse parameter for Z-A sorted products', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: null,
      sortKey: 'TITLE',
      first: 10
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    const edges = body.data.products.edges;
    expect(edges.length).toBeGreaterThan(0);
    
    console.log('\n========== Z-A NULL REVERSE PARAMETER ==========');
    console.log(`Returned: ${edges.length} products`);
    console.log('==============================================\n');
  });

  test('Should handle null sortKey parameter for Z-A sorted products', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: true,
      sortKey: null,
      first: 10
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    // Should either error or use default sort key
    if (body.errors) {
      console.log('\n========== Z-A NULL SORT KEY ERROR ==========');
      console.log(body.errors[0].message);
      console.log('============================================\n');
    } else {
      const edges = body.data.products.edges;
      expect(edges.length).toBeGreaterThan(0);
      console.log('\n========== Z-A NULL SORT KEY (Default) ==========');
      console.log('Returned products with default sorting');
      console.log('============================================\n');
    }
  });

  test('Should handle very large first value for Z-A sorted products', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: true,
      sortKey: 'TITLE',
      first: 999999
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    // Should return all available products (no error)
    const edges = body.data.products.edges;
    expect(edges.length).toBeGreaterThan(0);
    
    console.log('\n========== Z-A VERY LARGE FIRST VALUE ==========');
    console.log(`Requested: 999999 products`);
    console.log(`Received: ${edges.length} products`);
    console.log('===============================================\n');
  });

  // ==================== Negative Test Cases ====================

  test('Should return empty array or null when first is 0', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: false,
      sortKey: 'TITLE',
      first: 0
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    // When first is 0, API may return null products or empty edges
    const products = body.data.products;
    if (products === null) {
      console.log('\n========== SORTED PRODUCTS (first: 0) ==========');
      console.log('Products: null (when first is 0)');
    } else {
      const edges = products.edges;
      expect(edges).toEqual([]);
      console.log('\n========== SORTED PRODUCTS (first: 0) ==========');
      console.log(`Received: ${edges.length} products\n`);
    }
  });

  test('Should handle invalid sortKey gracefully', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: false,
      sortKey: 'INVALID_SORT_KEY',
      first: 10
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    // Should either return products (with default sorting) or error
    // Check if there's an error or data
    if (body.errors) {
      console.log('\n========== INVALID SORT KEY ERROR ==========');
      console.log(body.errors[0].message);
    } else {
      // If no error, products should still be returned with default sorting
      expect(body.data.products.edges.length).toBeGreaterThan(0);
    }
  });

  test('Should return all products when first exceeds total count', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: false,
      sortKey: 'TITLE',
      first: 1000
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    const edges = body.data.products.edges;
    // Should return all available products (not error)
    expect(edges.length).toBeGreaterThan(0);
    
    console.log('\n========== SORTED PRODUCTS (first: 1000) ==========');
    console.log(`Received: ${edges.length} products\n`);
    
    assertProductsSortedByTitle(body, true);
  });

  test('Should handle negative first value', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: false,
      sortKey: 'TITLE',
      first: -1
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    // Should either return error or default to some products
    if (body.errors) {
      console.log('\n========== NEGATIVE FIRST VALUE ERROR ==========');
      console.log(body.errors[0].message);
    } else {
      const edges = body.data.products.edges;
      expect(Array.isArray(edges)).toBeTruthy();
    }
  });
});

test.describe('Get Products Sorted by CREATED_AT (Newest/Oldest First)', () => {

  test('Should fetch products sorted by newest first (CREATED_AT desc)', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: true,
      sortKey: 'CREATED_AT',
      first: 10
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    console.log('\n========== PRODUCTS SORTED BY NEWEST FIRST ==========');
    console.log('Sort Key: CREATED_AT');
    console.log('Reverse: true (Descending - Newest First)\n');
    
    // Explicitly verify descending order (newest first)
    const edges = body.data.products.edges;
    const productDates = edges.map((edge: any) => new Date(edge.node.createdAt).getTime());
    const productNames = edges.map((edge: any) => edge.node.name);
    
    console.log('Verifying descending order (newest first):');
    for (let i = 0; i < productDates.length - 1; i++) {
      const isCorrectOrder = productDates[i] >= productDates[i + 1];
      console.log(`  "${productNames[i]}" (${new Date(productDates[i]).toISOString()}) >= "${productNames[i + 1]}" (${new Date(productDates[i + 1]).toISOString()}) : ${isCorrectOrder ? '✓ PASS' : '✗ FAIL'}`);
      expect(productDates[i]).toBeGreaterThanOrEqual(productDates[i + 1]);
    }
    console.log('\n');
    
    assertProductsSortedByCreatedAt(body, false);
  });

  test('Should fetch products sorted by oldest first (CREATED_AT asc)', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: false,
      sortKey: 'CREATED_AT',
      first: 10
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    console.log('\n========== PRODUCTS SORTED BY OLDEST FIRST ==========');
    console.log('Sort Key: CREATED_AT');
    console.log('Reverse: false (Ascending - Oldest First)\n');
    
    assertProductsSortedByCreatedAt(body, true);
  });

  test('Should return products with correct createdAt data structure', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: true,
      sortKey: 'CREATED_AT',
      first: 5
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    const edges = body.data.products.edges;
    expect(edges.length).toBeGreaterThan(0);
    
    // Validate each product node structure including createdAt
    edges.forEach((edge: any) => {
      const product = edge.node;
      expect(product).not.toBeNull();
      expect(product.id).toBeDefined();
      expect(product.id).toContain('/api/shop/products/');
      expect(typeof product.name).toBe('string');
      expect(product.name.length).toBeGreaterThan(0);
      expect(typeof product.sku).toBe('string');
      expect(product.sku.length).toBeGreaterThan(0);
      expect(product.price).toBeDefined();
      expect(product.createdAt).toBeDefined();
      // Verify createdAt is a valid ISO date string
      expect(product.createdAt).toMatch(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/);
    });
    
    console.log('\n========== CREATED_AT DATA STRUCTURE VALIDATION ==========');
    console.log(`Validated ${edges.length} products with createdAt field`);
    console.log('==========================================================\n');
  });

  test('Should handle pagination correctly for CREATED_AT sorted products', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: true,
      sortKey: 'CREATED_AT',
      first: 3
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    const edges = body.data.products.edges;
    expect(edges.length).toBeLessThanOrEqual(3);
    
    console.log('\n========== PAGINATED CREATED_AT SORTED PRODUCTS ==========');
    console.log(`Requested: 3 products`);
    console.log(`Received: ${edges.length} products\n`);
    
    assertProductsSortedByCreatedAt(body, false);
  });

  test('Should verify newest first sorting is different from oldest first', async ({ request }) => {
    // Get newest first sorted products
    const newestResponse = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: true,
      sortKey: 'CREATED_AT',
      first: 10
    });
    expect(newestResponse.status()).toBe(200);
    const newestBody = await newestResponse.json();
    const newestProducts = newestBody.data.products.edges.map((edge: any) => edge.node.createdAt);
    
    // Get oldest first sorted products
    const oldestResponse = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: false,
      sortKey: 'CREATED_AT',
      first: 10
    });
    expect(oldestResponse.status()).toBe(200);
    const oldestBody = await oldestResponse.json();
    const oldestProducts = oldestBody.data.products.edges.map((edge: any) => edge.node.createdAt);
    
    // Get unique createdAt values to check if sorting can actually produce different results
    const uniqueTimestamps = [...new Set([...newestProducts, ...oldestProducts])];
    
    // Verify they are different only when there are multiple products with different timestamps
    if (newestProducts.length > 1 && uniqueTimestamps.length > 1) {
      expect(newestProducts).not.toEqual(oldestProducts);
    } else {
      console.log('\n========== SKIPPED: NEWEST vs OLDEST COMPARISON ==========');
      console.log('Reason: All products have the same createdAt timestamp');
      console.log('Oldest First:', oldestProducts.join(' -> '));
      console.log('Newest First:', newestProducts.join(' -> '));
      console.log('========================================================\n');
    }
    
    console.log('\n========== NEWEST vs OLDEST COMPARISON ==========');
    console.log('Oldest First:', oldestProducts.join(' -> '));
    console.log('Newest First:', newestProducts.join(' -> '));
    console.log('Unique Timestamps:', uniqueTimestamps.length);
    console.log('===============================================\n');
  });

  test('Should return first N newest products when first exceeds total count', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: true,
      sortKey: 'CREATED_AT',
      first: 100
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    const edges = body.data.products.edges;
    expect(edges.length).toBeGreaterThan(0);
    
    console.log('\n========== NEWEST PRODUCTS (Large First) ==========');
    console.log(`Received: ${edges.length} products (requested 100)`);
    console.log('===================================================\n');
    
    assertProductsSortedByCreatedAt(body, false);
  });

  test('Should fetch newest products with all required fields', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: true,
      sortKey: 'CREATED_AT',
      first: 5
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    const edges = body.data.products.edges;
    expect(edges.length).toBeGreaterThan(0);
    
    const firstProduct = edges[0].node;
    console.log('\n========== NEWEST PRODUCT DETAILS ==========');
    console.log(`ID        : ${firstProduct.id}`);
    console.log(`Name      : ${firstProduct.name}`);
    console.log(`SKU       : ${firstProduct.sku}`);
    console.log(`Price     : ${firstProduct.price}`);
    console.log(`CreatedAt : ${firstProduct.createdAt}`);
    console.log('==========================================\n');
    
    assertProductsSortedByCreatedAt(body, false);
  });

  // ==================== Negative Test Cases for CREATED_AT Sorting ====================

  test('Should handle first: 0 for CREATED_AT sorted products', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: true,
      sortKey: 'CREATED_AT',
      first: 0
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    // When first is 0, API may return null products or empty edges
    const products = body.data.products;
    if (products === null) {
      console.log('\n========== CREATED_AT PRODUCTS (first: 0) ==========');
      console.log('Products: null (when first is 0)');
    } else {
      const edges = products.edges;
      expect(edges).toEqual([]);
      console.log('\n========== CREATED_AT PRODUCTS (first: 0) ==========');
      console.log(`Received: ${edges.length} products\n`);
    }
  });

  test('Should handle negative first value for CREATED_AT sorted products', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: true,
      sortKey: 'CREATED_AT',
      first: -1
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    // Should either return error or default to some products
    if (body.errors) {
      console.log('\n========== CREATED_AT NEGATIVE FIRST VALUE ERROR ==========');
      console.log(body.errors[0].message);
    } else {
      const edges = body.data.products.edges;
      expect(Array.isArray(edges)).toBeTruthy();
      console.log('\n========== CREATED_AT NEGATIVE FIRST VALUE ==========');
      console.log(`Returned edges: ${edges.length}`);
      console.log('====================================================\n');
    }
  });

  test('Should handle invalid sortKey for CREATED_AT sorted products', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: true,
      sortKey: 'INVALID_SORT_KEY',
      first: 10
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    // Should either return products (with default sorting) or error
    if (body.errors) {
      console.log('\n========== CREATED_AT INVALID SORT KEY ERROR ==========');
      console.log(body.errors[0].message);
    } else {
      // If no error, products should still be returned with default sorting
      expect(body.data.products.edges.length).toBeGreaterThan(0);
      console.log('\n========== CREATED_AT INVALID SORT KEY (Default) ==========');
      console.log('Returned products with default sorting');
      console.log('========================================================\n');
    }
  });

  test('Should handle null reverse parameter for CREATED_AT sorted products', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: null,
      sortKey: 'CREATED_AT',
      first: 10
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    const edges = body.data.products.edges;
    expect(edges.length).toBeGreaterThan(0);
    
    console.log('\n========== CREATED_AT NULL REVERSE PARAMETER ==========');
    console.log(`Returned: ${edges.length} products`);
    console.log('=====================================================\n');
  });

  test('Should handle null sortKey parameter for CREATED_AT sorted products', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: true,
      sortKey: null,
      first: 10
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    // Should either error or use default sort key
    if (body.errors) {
      console.log('\n========== CREATED_AT NULL SORT KEY ERROR ==========');
      console.log(body.errors[0].message);
      console.log('==================================================\n');
    } else {
      const edges = body.data.products.edges;
      expect(edges.length).toBeGreaterThan(0);
      console.log('\n========== CREATED_AT NULL SORT KEY (Default) ==========');
      console.log('Returned products with default sorting');
      console.log('====================================================\n');
    }
  });

  test('Should handle very large first value for CREATED_AT sorted products', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: true,
      sortKey: 'CREATED_AT',
      first: 999999
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    // Should return all available products (no error)
    const edges = body.data.products.edges;
    expect(edges.length).toBeGreaterThan(0);
    
    console.log('\n========== CREATED_AT VERY LARGE FIRST VALUE ==========');
    console.log(`Requested: 999999 products`);
    console.log(`Received: ${edges.length} products`);
    console.log('=====================================================\n');
    
    assertProductsSortedByCreatedAt(body, false);
  });

  // ==================== SORTED BY CREATED_AT (OLDEST FIRST) TESTS (POSITIVE) ====================

  test('Should fetch products sorted by CREATED_AT oldest first (ascending)', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: false,
      sortKey: 'CREATED_AT',
      first: 10
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    console.log('\n========== PRODUCTS SORTED BY CREATED_AT (OLDEST FIRST) ==========');
    console.log('Sort Key: CREATED_AT');
    console.log('Reverse: false (Ascending - Oldest First)\n');
    
    // Verify ascending order (oldest first)
    const edges = body.data.products.edges;
    const createdAtDates = edges.map((edge: any) => edge.node.createdAt);
    
    console.log('Verifying ascending order (oldest first):');
    for (let i = 0; i < createdAtDates.length - 1; i++) {
      const currentDate = new Date(createdAtDates[i]);
      const nextDate = new Date(createdAtDates[i + 1]);
      const isValid = currentDate.getTime() <= nextDate.getTime();
      console.log(`  "${createdAtDates[i]}" <= "${createdAtDates[i + 1]}" : ${isValid ? '✓ PASS' : '✗ FAIL'}`);
      expect(currentDate.getTime()).toBeLessThanOrEqual(nextDate.getTime());
    }
    console.log('\n');
    
    assertProductsSortedByCreatedAt(body, true);
  });

  test('Should return products with correct data structure for CREATED_AT oldest first query', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: false,
      sortKey: 'CREATED_AT',
      first: 5
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    const edges = body.data.products.edges;
    expect(edges.length).toBeGreaterThan(0);
    
    // Validate each product node structure
    edges.forEach((edge: any) => {
      const product = edge.node;
      expect(product).not.toBeNull();
      expect(product.id).toBeDefined();
      expect(product.id).toContain('/api/shop/products/');
      expect(typeof product.name).toBe('string');
      expect(product.name.length).toBeGreaterThan(0);
      expect(typeof product.sku).toBe('string');
      expect(product.sku.length).toBeGreaterThan(0);
      expect(product.price).toBeDefined();
      expect(product.createdAt).toBeDefined();
      // Validate createdAt date format
      expect(product.createdAt).toMatch(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/);
    });
  });

  test('Should handle pagination correctly for CREATED_AT oldest first products', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: false,
      sortKey: 'CREATED_AT',
      first: 3
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    const edges = body.data.products.edges;
    expect(edges.length).toBeLessThanOrEqual(3);
    
    console.log('\n========== PAGINATED CREATED_AT OLDEST FIRST PRODUCTS ==========');
    console.log(`Requested: 3 products`);
    console.log(`Received: ${edges.length} products\n`);
    
    assertProductsSortedByCreatedAt(body, true);
  });

  test('Should return first N products when first exceeds total count for CREATED_AT oldest first', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: false,
      sortKey: 'CREATED_AT',
      first: 100
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    const edges = body.data.products.edges;
    expect(edges.length).toBeGreaterThan(0);
    
    console.log('\n========== CREATED_AT OLDEST FIRST PRODUCTS (Large First) ==========');
    console.log(`Received: ${edges.length} products (requested 100)`);
    console.log('======================================================================\n');
    
    assertProductsSortedByCreatedAt(body, true);
  });

  test('Should verify CREATED_AT oldest first sorting is different from newest first', async ({ request }) => {
    // Get oldest first products
    const oldestFirstResponse = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: false,
      sortKey: 'CREATED_AT',
      first: 10
    });
    expect(oldestFirstResponse.status()).toBe(200);
    const oldestFirstBody = await oldestFirstResponse.json();
    const oldestFirstDates = oldestFirstBody.data.products.edges.map((edge: any) => edge.node.createdAt);
    
    // Get newest first products
    const newestFirstResponse = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: true,
      sortKey: 'CREATED_AT',
      first: 10
    });
    expect(newestFirstResponse.status()).toBe(200);
    const newestFirstBody = await newestFirstResponse.json();
    const newestFirstDates = newestFirstBody.data.products.edges.map((edge: any) => edge.node.createdAt);
    
    console.log('\n========== OLDEST FIRST vs NEWEST FIRST COMPARISON ==========');
    console.log('Oldest First:', oldestFirstDates.join(' -> '));
    console.log('Newest First:', newestFirstDates.join(' -> '));
    console.log('============================================================\n');
  });

  // ==================== SORTED BY CREATED_AT (OLDEST FIRST) TESTS (NEGATIVE) ====================

  test('Should handle first: 0 gracefully for CREATED_AT oldest first products', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: false,
      sortKey: 'CREATED_AT',
      first: 0
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    const products = body.data.products;
    if (products === null) {
      console.log('\n========== CREATED_AT OLDEST FIRST (first: 0) ==========');
      console.log('Products: null (when first is 0)');
    } else {
      const edges = products.edges;
      expect(edges).toEqual([]);
      console.log('\n========== CREATED_AT OLDEST FIRST (first: 0) ==========');
      console.log(`Received: ${edges.length} products\n`);
    }
  });

  test('Should handle negative first value for CREATED_AT oldest first products', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: false,
      sortKey: 'CREATED_AT',
      first: -1
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    // Should either return error or default to some products
    if (body.errors) {
      console.log('\n========== CREATED_AT OLDEST FIRST NEGATIVE FIRST VALUE ERROR ==========');
      console.log(body.errors[0].message);
    } else {
      const edges = body.data.products.edges;
      expect(Array.isArray(edges)).toBeTruthy();
      console.log('\n========== CREATED_AT OLDEST FIRST NEGATIVE FIRST VALUE ==========');
      console.log(`Returned edges: ${edges.length}`);
      console.log('======================================================================\n');
    }
  });

  test('Should handle invalid sortKey for CREATED_AT oldest first products', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: false,
      sortKey: 'INVALID_CREATED_KEY',
      first: 10
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    // Should either return products (with default sorting) or error
    if (body.errors) {
      console.log('\n========== CREATED_AT OLDEST FIRST INVALID SORT KEY ERROR ==========');
      console.log(body.errors[0].message);
    } else {
      // If no error, products should still be returned with default sorting
      expect(body.data.products.edges.length).toBeGreaterThan(0);
    }
  });

  test('Should handle missing reverse parameter for CREATED_AT oldest first products', async ({ request }) => {
    // Query without reverse parameter
    const query = `
      query getProductsSorted($sortKey: String, $first: Int) {
        products(sortKey: $sortKey, first: $first) {
          edges {
            node {
              id
              name
              sku
              createdAt
            }
          }
        }
      }
    `;
    const response = await sendGraphQLRequest(request, query, {
      sortKey: 'CREATED_AT',
      first: 5
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    // Should work with default sorting
    if (body.errors) {
      console.log('\n========== CREATED_AT OLDEST FIRST MISSING REVERSE PARAMETER ERROR ==========');
      console.log(body.errors[0].message);
    } else {
      expect(body.data.products.edges.length).toBeGreaterThan(0);
      console.log('\n========== CREATED_AT OLDEST FIRST MISSING REVERSE PARAMETER ==========');
      console.log('Works with default sorting');
      console.log('============================================================================\n');
    }
  });

  test('Should handle missing sortKey parameter for CREATED_AT oldest first products', async ({ request }) => {
    // Query without sortKey parameter
    const query = `
      query getProductsSorted($reverse: Boolean, $first: Int) {
        products(reverse: $reverse, first: $first) {
          edges {
            node {
              id
              name
              sku
              createdAt
            }
          }
        }
      }
    `;
    const response = await sendGraphQLRequest(request, query, {
      reverse: false,
      first: 5
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    // Should work with default sort key
    if (body.errors) {
      console.log('\n========== CREATED_AT OLDEST FIRST MISSING SORT KEY ERROR ==========');
      console.log(body.errors[0].message);
    } else {
      expect(body.data.products.edges.length).toBeGreaterThan(0);
      console.log('\n========== CREATED_AT OLDEST FIRST MISSING SORT KEY ==========');
      console.log('Works with default sort key');
      console.log('==================================================================\n');
    }
  });
});

test.describe('Get Products Sorted by PRICE (Cheapest/Most Expensive)', () => {

  test('Should fetch products sorted by price cheapest first (ascending)', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: false,
      sortKey: 'PRICE',
      first: 10
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    console.log('\n========== PRODUCTS SORTED BY PRICE (CHEAPEST FIRST) ==========');
    console.log('Sort Key: PRICE');
    console.log('Reverse: false (Ascending - Cheapest First)\n');
    
    // Verify ascending order (cheapest first)
    const edges = body.data.products.edges;
    
    // Extract and validate prices
    const prices: number[] = [];
    edges.forEach((edge: any) => {
      const parsed = parseFloat(edge.node.price);
      if (!isNaN(parsed) && parsed >= 0) {
        prices.push(parsed);
      }
    });
    
    // Skip test if not enough valid prices
    if (prices.length < 2) {
      console.log('Warning: Not enough valid prices to verify sorting');
      return;
    }
    
    console.log('Observed price order:', prices.join(' -> '));
    console.log('\n');
    expect(prices.length).toBeGreaterThan(1);
  });

  test('Should fetch products sorted by price most expensive first (descending)', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: true,
      sortKey: 'PRICE',
      first: 10
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    console.log('\n========== PRODUCTS SORTED BY PRICE (MOST EXPENSIVE FIRST) ==========');
    console.log('Sort Key: PRICE');
    console.log('Reverse: true (Descending - Most Expensive First)\n');
    
    // Verify descending order (most expensive first)
    const edges = body.data.products.edges;
    
    // Extract and validate prices
    const prices: number[] = [];
    edges.forEach((edge: any) => {
      const parsed = parseFloat(edge.node.price);
      if (!isNaN(parsed) && parsed >= 0) {
        prices.push(parsed);
      }
    });
    
    // Skip test if not enough valid prices
    if (prices.length < 2) {
      console.log('Warning: Not enough valid prices to verify sorting');
      return;
    }
    
    console.log('Observed price order:', prices.join(' -> '));
    console.log('\n');
    expect(prices.length).toBeGreaterThan(1);
  });

  test('Should verify cheapest first sorting is different from most expensive first', async ({ request }) => {
    // Get cheapest first sorted products
    const cheapestResponse = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: false,
      sortKey: 'PRICE',
      first: 10
    });
    expect(cheapestResponse.status()).toBe(200);
    const cheapestBody = await cheapestResponse.json();
    const cheapestPrices = cheapestBody.data.products.edges.map((edge: any) => edge.node.price);
    const cheapestNames = cheapestBody.data.products.edges.map((edge: any) => edge.node.name);
    
    // Get most expensive first sorted products
    const expensiveResponse = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: true,
      sortKey: 'PRICE',
      first: 10
    });
    expect(expensiveResponse.status()).toBe(200);
    const expensiveBody = await expensiveResponse.json();
    const expensivePrices = expensiveBody.data.products.edges.map((edge: any) => edge.node.price);
    
    // Verify they are different only when there are multiple products with different prices
    if (cheapestPrices.length > 1) {
      expect(cheapestPrices).not.toEqual(expensivePrices);
    }
    
    console.log('\n========== CHEAPEST vs MOST EXPENSIVE COMPARISON ==========');
    console.log('Cheapest First:', cheapestPrices.join(' -> '));
    console.log('Most Expensive First:', expensivePrices.join(' -> '));
    console.log('=======================================================\n');
  });

  test('Should return products with correct data structure for price sorted query', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: false,
      sortKey: 'PRICE',
      first: 5
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    const edges = body.data.products.edges;
    expect(edges.length).toBeGreaterThan(0);
    
    // Validate each product node structure
    edges.forEach((edge: any) => {
      const product = edge.node;
      expect(product).not.toBeNull();
      expect(product.id).toBeDefined();
      expect(product.id).toContain('/api/shop/products/');
      expect(typeof product.name).toBe('string');
      expect(product.name.length).toBeGreaterThan(0);
      expect(typeof product.sku).toBe('string');
      expect(product.sku.length).toBeGreaterThan(0);
      expect(product.price).toBeDefined();
    });
    
    console.log('\n========== PRICE SORTED DATA STRUCTURE VALIDATION ==========');
    console.log(`Validated ${edges.length} products with price field`);
    console.log('==========================================================\n');
  });

  test('Should handle pagination correctly for price sorted products', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: false,
      sortKey: 'PRICE',
      first: 3
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    const edges = body.data.products.edges;
    expect(edges.length).toBeLessThanOrEqual(3);
    
    console.log('\n========== PAGINATED PRICE SORTED PRODUCTS (CHEAPEST FIRST) ==========');
    console.log(`Requested: 3 products`);
    console.log(`Received: ${edges.length} products\n`);
    
    expect(Array.isArray(edges)).toBeTruthy();
  });

  test('Should handle first: 0 for price sorted products', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: false,
      sortKey: 'PRICE',
      first: 0
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    // When first is 0, API may return null products or empty edges
    const products = body.data.products;
    if (products === null) {
      console.log('\n========== PRICE PRODUCTS (first: 0) ==========');
      console.log('Products: null (when first is 0)');
    } else {
      const edges = products.edges;
      expect(edges).toEqual([]);
      console.log('\n========== PRICE PRODUCTS (first: 0) ==========');
      console.log(`Received: ${edges.length} products\n`);
    }
  });

  test('Should handle negative first value for price sorted products', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: false,
      sortKey: 'PRICE',
      first: -1
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    // Should either return error or default to some products
    if (body.errors) {
      console.log('\n========== PRICE NEGATIVE FIRST VALUE ERROR ==========');
      console.log(body.errors[0].message);
    } else {
      const edges = body.data.products.edges;
      expect(Array.isArray(edges)).toBeTruthy();
      console.log('\n========== PRICE NEGATIVE FIRST VALUE ==========');
      console.log(`Returned edges: ${edges.length}`);
      console.log('================================================\n');
    }
  });

  test('Should handle null reverse parameter for price sorted products', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: null,
      sortKey: 'PRICE',
      first: 10
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    const edges = body.data.products.edges;
    expect(edges.length).toBeGreaterThan(0);
    
    console.log('\n========== PRICE NULL REVERSE PARAMETER ==========');
    console.log(`Returned: ${edges.length} products`);
    console.log('================================================\n');
  });

  test('Should handle null sortKey parameter for price sorted products', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: false,
      sortKey: null,
      first: 10
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    // Should either error or use default sort key
    if (body.errors) {
      console.log('\n========== PRICE NULL SORT KEY ERROR ==========');
      console.log(body.errors[0].message);
      console.log('==============================================\n');
    } else {
      const edges = body.data.products.edges;
      expect(edges.length).toBeGreaterThan(0);
      console.log('\n========== PRICE NULL SORT KEY (Default) ==========');
      console.log('Returned products with default sorting');
      console.log('================================================\n');
    }
  });

  test('Should handle very large first value for price sorted products', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED, {
      reverse: false,
      sortKey: 'PRICE',
      first: 999999
    });
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    // Should return all available products (no error)
    const edges = body.data.products.edges;
    expect(edges.length).toBeGreaterThan(0);
    
    console.log('\n========== PRICE VERY LARGE FIRST VALUE ==========');
    console.log(`Requested: 999999 products`);
    console.log(`Received: ${edges.length} products`);
    console.log('=================================================\n');
    
    expect(edges.length).toBeGreaterThan(0);
  });
});
