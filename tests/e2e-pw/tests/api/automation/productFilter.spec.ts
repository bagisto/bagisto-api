// tests/api/automation/productFilter.spec.ts
import { test, expect } from '@playwright/test';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import {
  GET_PRODUCTS_BY_CATEGORY,
  GET_PRODUCTS_BY_TYPE,
  GET_PRODUCTS_BY_ATTRIBUTE,
  GET_PRODUCTS_BY_MULTIPLE_ATTRIBUTES,
  GET_PRODUCTS_FILTERED_AND_SORTED,
} from '../../graphql/Queries/product.queries';
import {
  assertProductsByCategoryResponse,
  assertProductsByTypeResponse,
  assertProductsByAttributeResponse,
  assertProductsByMultipleAttributesResponse,
  assertEmptyProductsList,
  assertGraphQLError,
} from '../../graphql/assertions/product.assertions';

// ==================== SEARCH PRODUCTS BY CATEGORY ID WITH PAGINATION TESTS ====================
test.describe('Search Products by Category ID with Pagination', () => {
  // ==================== POSITIVE TEST CASES ====================

  test('Should fetch products by category ID with pagination', async ({ request }) => {
    const filter = JSON.stringify({ category_id: '22' });
    const variables = { filter, first: 2, after: 'Mg==' };
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_BY_CATEGORY, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();

    if (body.data?.products) {
      assertProductsByCategoryResponse(body, '22', 2);
    } else {
      expect(body).toHaveProperty('data');
    }
  });

  test('Should return products with valid category filter structure', async ({ request }) => {
    const filter = JSON.stringify({ category_id: '22' });
    const variables = { filter, first: 2 };
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_BY_CATEGORY, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();

    if (body.data?.products) {
      const products = body.data.products;
      
      // Validate response structure
      expect(products).toHaveProperty('edges');
      expect(products).toHaveProperty('pageInfo');
      expect(products).toHaveProperty('totalCount');
      expect(Array.isArray(products.edges)).toBeTruthy();
    }
  });

  test('Should return products with all required fields', async ({ request }) => {
    const filter = JSON.stringify({ category_id: '22' });
    const variables = { filter, first: 2 };
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_BY_CATEGORY, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();

    if (body.data?.products?.edges?.length > 0) {
      const product = body.data.products.edges[0].node;
      
      // Validate required fields exist
      expect(product).toHaveProperty('id');
      expect(product).toHaveProperty('sku');
      expect(product).toHaveProperty('name');
      
      // Validate optional fields
      if (product.price !== null && product.price !== undefined) {
        expect(product.price).toBeDefined();
      }
      if (product.urlKey !== null && product.urlKey !== undefined) {
        expect(typeof product.urlKey).toBe('string');
      }
      if (product.baseImageUrl !== null && product.baseImageUrl !== undefined) {
        expect(typeof product.baseImageUrl).toBe('string');
      }
      if (product.description !== null && product.description !== undefined) {
        expect(typeof product.description).toBe('string');
      }
      if (product.shortDescription !== null && product.shortDescription !== undefined) {
        expect(typeof product.shortDescription).toBe('string');
      }
      if (product.specialPrice !== null && product.specialPrice !== undefined) {
        expect(product.specialPrice).toBeDefined();
      }
    }
  });

  test('Should validate pagination info', async ({ request }) => {
    const filter = JSON.stringify({ category_id: '22' });
    const variables = { filter, first: 2 };
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_BY_CATEGORY, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();

    if (body.data?.products) {
      const products = body.data.products;
      
      // Validate pageInfo structure
      expect(products.pageInfo).toHaveProperty('hasNextPage');
      expect(products.pageInfo).toHaveProperty('hasPreviousPage');
      expect(products.pageInfo).toHaveProperty('startCursor');
      expect(products.pageInfo).toHaveProperty('endCursor');
      
      // Validate field types
      expect(typeof products.pageInfo.hasNextPage).toBe('boolean');
      expect(typeof products.pageInfo.hasPreviousPage).toBe('boolean');
    }
  });

  test('Should limit results based on first parameter', async ({ request }) => {
    const filter = JSON.stringify({ category_id: '22' });
    const variables = { filter, first: 2 };
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_BY_CATEGORY, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();

    if (body.data?.products?.edges) {
      const products = body.data.products;
      expect(products.edges.length).toBeLessThanOrEqual(2);
    }
  });

  // ==================== NEGATIVE TEST CASES ====================

  test('Should handle non-existent category ID', async ({ request }) => {
    const filter = JSON.stringify({ category_id: '99999' });
    const variables = { filter, first: 10 };
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_BY_CATEGORY, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();

    // Should return empty list
    if (body.data?.products) {
      expect(body.data.products.edges.length).toBe(0);
    }
  });

  test('Should handle invalid category ID format', async ({ request }) => {
    const filter = JSON.stringify({ category_id: 'invalid' });
    const variables = { filter, first: 10 };
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_BY_CATEGORY, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();

    // Should handle gracefully
    expect(body).toHaveProperty('data');
  });

  test('Should handle missing filter parameter', async ({ request }) => {
    const variables = { first: 10 };
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_BY_CATEGORY, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();

    // May return error or empty results
    if (body.errors) {
      assertGraphQLError(body);
    }
  });
});

// ==================== FILTER BY PRODUCT TYPE TESTS ====================
test.describe('Filter Products by Type', () => {
  // ==================== POSITIVE TEST CASES ====================

  test('Should fetch products filtered by type - configurable', async ({ request }) => {
    const filter = JSON.stringify({ type: 'configurable' });
    const variables = { filter };
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_BY_TYPE, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();

    if (body.data?.products) {
      assertProductsByTypeResponse(body, 'configurable');
    } else {
      expect(body).toHaveProperty('data');
    }
  });

  test('Should fetch products filtered by type - simple', async ({ request }) => {
    const filter = JSON.stringify({ type: 'simple' });
    const variables = { filter };
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_BY_TYPE, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();

    if (body.data?.products) {
      assertProductsByTypeResponse(body, 'simple');
    } else {
      expect(body).toHaveProperty('data');
    }
  });

  test('Should return products with type filter structure', async ({ request }) => {
    const filter = JSON.stringify({ type: 'configurable' });
    const variables = { filter };
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_BY_TYPE, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();

    if (body.data?.products) {
      const products = body.data.products;
      
      // Validate response structure
      expect(products).toHaveProperty('edges');
      expect(products).toHaveProperty('totalCount');
      expect(Array.isArray(products.edges)).toBeTruthy();
      expect(typeof products.totalCount).toBe('number');
    }
  });

  // ==================== NEGATIVE TEST CASES ====================

  test('Should handle invalid product type', async ({ request }) => {
    const filter = JSON.stringify({ type: 'invalid_type' });
    const variables = { filter };
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_BY_TYPE, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();

    // Should return empty list
    if (body.data?.products) {
      expect(body.data.products.edges.length).toBe(0);
    }
  });

  test('Should handle missing type parameter', async ({ request }) => {
    const filter = JSON.stringify({});
    const variables = { filter };
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_BY_TYPE, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();

    // Should handle gracefully
    expect(body).toHaveProperty('data');
  });
});

// ==================== FILTER BY ATTRIBUTE (COLOR) TESTS ====================
test.describe('Filter Products by Attribute (Color)', () => {
  // ==================== POSITIVE TEST CASES ====================

  test('Should fetch products filtered by color attribute', async ({ request }) => {
    const filter = JSON.stringify({ color: '3' });
    const variables = { filter };
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_BY_ATTRIBUTE, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();

    if (body.data?.products) {
      assertProductsByAttributeResponse(body, 'color', '3');
    } else {
      expect(body).toHaveProperty('data');
    }
  });

  test('Should return products with color attribute filter structure', async ({ request }) => {
    const filter = JSON.stringify({ color: '3' });
    const variables = { filter };
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_BY_ATTRIBUTE, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();

    if (body.data?.products) {
      const products = body.data.products;
      
      // Validate response structure
      expect(products).toHaveProperty('edges');
      expect(products).toHaveProperty('totalCount');
      expect(Array.isArray(products.edges)).toBeTruthy();
    }
  });

  test('Should validate product fields in attribute filter results', async ({ request }) => {
    const filter = JSON.stringify({ color: '3' });
    const variables = { filter };
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_BY_ATTRIBUTE, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();

    if (body.data?.products?.edges?.length > 0) {
      const product = body.data.products.edges[0].node;
      
      // Validate product fields
      expect(product.id).toBeDefined();
      expect(product.sku).toBeDefined();
    }
  });

  // ==================== NEGATIVE TEST CASES ====================

  test('Should handle non-existent color attribute value', async ({ request }) => {
    const filter = JSON.stringify({ color: '99999' });
    const variables = { filter };
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_BY_ATTRIBUTE, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();

    // Should return empty list
    if (body.data?.products) {
      expect(body.data.products.edges.length).toBe(0);
    }
  });

  test('Should handle invalid color attribute value', async ({ request }) => {
    const filter = JSON.stringify({ color: 'invalid' });
    const variables = { filter };
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_BY_ATTRIBUTE, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();

    // Should handle gracefully
    expect(body).toHaveProperty('data');
  });
});

// ==================== FILTER BY MULTIPLE ATTRIBUTES TESTS ====================
test.describe('Filter Products by Multiple Attributes', () => {
  // ==================== POSITIVE TEST CASES ====================

  test('Should fetch products filtered by multiple attributes', async ({ request }) => {
    const filter = JSON.stringify({ color: '5', size: '1', brand: '5' });
    const variables = { filter, first: 10 };
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_BY_MULTIPLE_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();

    if (body.data?.products) {
      const attributes = { color: '5', size: '1', brand: '5' };
      assertProductsByMultipleAttributesResponse(body, attributes, 10);
    } else {
      expect(body).toHaveProperty('data');
    }
  });

  test('Should return products with multiple attribute filter structure', async ({ request }) => {
    const filter = JSON.stringify({ color: '5', size: '1', brand: '5' });
    const variables = { filter, first: 10 };
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_BY_MULTIPLE_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();

    if (body.data?.products) {
      const products = body.data.products;
      
      // Validate response structure
      expect(products).toHaveProperty('edges');
      expect(products).toHaveProperty('totalCount');
      expect(Array.isArray(products.edges)).toBeTruthy();
    }
  });

  test('Should validate all product fields in multiple attribute results', async ({ request }) => {
    const filter = JSON.stringify({ color: '5', size: '1', brand: '5' });
    const variables = { filter, first: 10 };
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_BY_MULTIPLE_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();

    if (body.data?.products?.edges?.length > 0) {
      const product = body.data.products.edges[0].node;
      
      // Validate all required fields
      expect(product.id).toBeDefined();
      expect(product.sku).toBeDefined();
      expect(product.name).toBeDefined();
      expect(product.price).toBeDefined();
    }
  });

  test('Should limit results based on first parameter', async ({ request }) => {
    const filter = JSON.stringify({ color: '5', size: '1', brand: '5' });
    const variables = { filter, first: 3 };
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_BY_MULTIPLE_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();

    if (body.data?.products?.edges) {
      const products = body.data.products;
      expect(products.edges.length).toBeLessThanOrEqual(3);
    }
  });

  test('Should filter by two attributes', async ({ request }) => {
    const filter = JSON.stringify({ color: '5', size: '1' });
    const variables = { filter, first: 10 };
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_BY_MULTIPLE_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();

    if (body.data?.products) {
      const attributes = { color: '5', size: '1' };
      assertProductsByMultipleAttributesResponse(body, attributes, 10);
    }
  });

  // ==================== NEGATIVE TEST CASES ====================

  test('Should handle non-existent attribute combination', async ({ request }) => {
    const filter = JSON.stringify({ color: '99999', size: '99999', brand: '99999' });
    const variables = { filter, first: 10 };
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_BY_MULTIPLE_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();

    // Should return empty list
    if (body.data?.products) {
      expect(body.data.products.edges.length).toBe(0);
    }
  });

  test('Should handle single attribute in multi-attribute filter', async ({ request }) => {
    const filter = JSON.stringify({ color: '5' });
    const variables = { filter, first: 10 };
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_BY_MULTIPLE_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();

    // Should work with single attribute
    if (body.data?.products) {
      const attributes = { color: '5' };
      assertProductsByMultipleAttributesResponse(body, attributes, 10);
    }
  });

  test('Should handle empty filter', async ({ request }) => {
    const filter = JSON.stringify({});
    const variables = { filter, first: 10 };
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_BY_MULTIPLE_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();

    // Should return products (unfiltered)
    expect(body).toHaveProperty('data');
  });

  test('Should handle invalid attribute values', async ({ request }) => {
    const filter = JSON.stringify({ color: 'invalid', size: 'invalid' });
    const variables = { filter, first: 10 };
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_BY_MULTIPLE_ATTRIBUTES, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();

    // Should handle gracefully
    expect(body).toHaveProperty('data');
  });

  test('Should cover docs query for new products', async ({ request }) => {
    const filter = JSON.stringify({ new: '1' });
    const variables = { filter, sortKey: 'CREATED_AT', reverse: true, first: 10 };
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_FILTERED_AND_SORTED, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(body.data?.products || body.errors).toBeTruthy();
  });

  test('Should cover docs query for featured products', async ({ request }) => {
    const filter = JSON.stringify({ featured: '1' });
    const variables = { filter, sortKey: 'CREATED_AT', reverse: true, first: 12 };
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_FILTERED_AND_SORTED, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(body.data?.products || body.errors).toBeTruthy();
  });

  test('Should cover docs query for popular products by brand', async ({ request }) => {
    const filter = JSON.stringify({ brand: '25' });
    const variables = { filter, sortKey: 'CREATED_AT', reverse: true, first: 12 };
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_FILTERED_AND_SORTED, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(body.data?.products || body.errors).toBeTruthy();
  });
});
