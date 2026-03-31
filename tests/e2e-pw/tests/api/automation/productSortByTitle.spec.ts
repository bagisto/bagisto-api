// tests/api/automation/productSortByTitle.spec.ts
import { test, expect } from '@playwright/test';
import { GET_PRODUCTS_SORTED_BY_TITLE_AZ, GET_PRODUCTS_SORTED_BY_TITLE_ZA } from '../../graphql/Queries/product.queries';
import { assertProductsSortedByTitle } from '../../graphql/assertions/product.assertions';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';

test.describe('Product Sorting by Title', () => {

  /**
   * Test: Sort A-Z by Title
   * Query: products(sortKey: "TITLE", reverse: false, first: 5)
   * Expected: Products should be sorted in ascending alphabetical order (A-Z)
   */
  test('Should sort products A-Z by Title (ascending order)', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED_BY_TITLE_AZ, {});
    expect(response.status()).toBe(200);

    const body = await response.json();
    
    // Check for GraphQL errors
    if (body.errors) {
      console.log('GraphQL Errors:', body.errors);
    }
    
    expect(body).toHaveProperty('data');
    expect(body.data).toHaveProperty('products');
    expect(body.data.products).not.toBeNull();

    const edges = body.data.products.edges;
    const totalCount = body.data.products.totalCount;

    console.log('\n========== SORT A-Z BY TITLE ==========');
    console.log('Query: products(sortKey: "TITLE", reverse: false, first: 5)');
    console.log('Total Products: ' + totalCount);
    console.log('Products Returned: ' + edges.length + '\n');

    // Verify we got exactly 5 products (or less if total count < 5)
    expect(edges.length).toBeLessThanOrEqual(5);

    // Print product names for verification
    const productNames = edges.map((edge: any) => edge.node.name);
    console.log('Products (A-Z Order):');
    productNames.forEach((name: string, index: number) => {
      console.log('  ' + (index + 1) + '. ' + name);
    });
    console.log('\n');

    // Verify ascending order (A-Z)
    for (let i = 0; i < productNames.length - 1; i++) {
      const compareResult = productNames[i].localeCompare(productNames[i + 1]);
      const status = compareResult <= 0 ? 'PASS' : 'FAIL';
      console.log('  "' + productNames[i] + '" <= "' + productNames[i + 1] + '" : ' + status);
      expect(compareResult).toBeLessThanOrEqual(0);
    }

    // Validate each product node structure
    edges.forEach((edge: any) => {
      const product = edge.node;
      expect(product).not.toBeNull();
      expect(product.id).toBeDefined();
      expect(product.id).toContain('/api/shop/products/');
      expect(product.name).toBeDefined();
      expect(typeof product.name).toBe('string');
      expect(product.sku).toBeDefined();
      expect(typeof product.sku).toBe('string');
    });

    // Use the assertion helper for additional validation
    assertProductsSortedByTitle(body, true);
  });

  /**
   * Test: Sort Z-A by Title
   * Query: products(sortKey: "TITLE", reverse: true, first: 5)
   * Expected: Products should be sorted in descending alphabetical order (Z-A)
   */
  test('Should sort products Z-A by Title (descending order)', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED_BY_TITLE_ZA, {});
    expect(response.status()).toBe(200);

    const body = await response.json();
    
    // Check for GraphQL errors
    if (body.errors) {
      console.log('GraphQL Errors:', body.errors);
    }
    
    expect(body).toHaveProperty('data');
    expect(body.data).toHaveProperty('products');
    expect(body.data.products).not.toBeNull();

    const edges = body.data.products.edges;
    const totalCount = body.data.products.totalCount;

    console.log('\n========== SORT Z-A BY TITLE ==========');
    console.log('Query: products(sortKey: "TITLE", reverse: true, first: 5)');
    console.log('Total Products: ' + totalCount);
    console.log('Products Returned: ' + edges.length + '\n');

    // Verify we got exactly 5 products (or less if total count < 5)
    expect(edges.length).toBeLessThanOrEqual(5);

    // Print product names for verification
    const productNames = edges.map((edge: any) => edge.node.name);
    console.log('Products (Z-A Order):');
    productNames.forEach((name: string, index: number) => {
      console.log('  ' + (index + 1) + '. ' + name);
    });
    console.log('\n');

    // Verify descending order (Z-A)
    for (let i = 0; i < productNames.length - 1; i++) {
      const compareResult = productNames[i].localeCompare(productNames[i + 1]);
      const status = compareResult >= 0 ? 'PASS' : 'FAIL';
      console.log('  "' + productNames[i] + '" >= "' + productNames[i + 1] + '" : ' + status);
      expect(compareResult).toBeGreaterThanOrEqual(0);
    }

    // Validate each product node structure
    edges.forEach((edge: any) => {
      const product = edge.node;
      expect(product).not.toBeNull();
      expect(product.id).toBeDefined();
      expect(product.id).toContain('/api/shop/products/');
      expect(product.name).toBeDefined();
      expect(typeof product.name).toBe('string');
      expect(product.sku).toBeDefined();
      expect(typeof product.sku).toBe('string');
    });

    // Use the assertion helper for additional validation
    assertProductsSortedByTitle(body, false);
  });

  /**
   * Test: Verify totalCount is returned correctly for sorted products
   */
  test('Should return correct totalCount for sorted products', async ({ request }) => {
    // Test A-Z
    const responseAZ = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED_BY_TITLE_AZ, {});
    expect(responseAZ.status()).toBe(200);
    const bodyAZ = await responseAZ.json();
    
    expect(bodyAZ.data.products.totalCount).toBeDefined();
    expect(typeof bodyAZ.data.products.totalCount).toBe('number');
    expect(bodyAZ.data.products.totalCount).toBeGreaterThanOrEqual(0);

    // Test Z-A
    const responseZA = await sendGraphQLRequest(request, GET_PRODUCTS_SORTED_BY_TITLE_ZA, {});
    expect(responseZA.status()).toBe(200);
    const bodyZA = await responseZA.json();
    
    expect(bodyZA.data.products.totalCount).toBeDefined();
    expect(typeof bodyZA.data.products.totalCount).toBe('number');
    expect(bodyZA.data.products.totalCount).toBeGreaterThanOrEqual(0);

    // Both queries should return the same totalCount
    expect(bodyAZ.data.products.totalCount).toBe(bodyZA.data.products.totalCount);

    console.log('\n========== TOTAL COUNT VERIFICATION ==========');
    console.log('A-Z Total Count: ' + bodyAZ.data.products.totalCount);
    console.log('Z-A Total Count: ' + bodyZA.data.products.totalCount);
    console.log('===============================================\n');
  });
});
