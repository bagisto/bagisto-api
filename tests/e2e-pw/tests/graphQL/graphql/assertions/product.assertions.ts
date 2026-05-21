// utils/assertions/product.assertions.ts

import { expect } from '@playwright/test';

/**
 * Reusable product node validator
 * (can validate parent OR variant)
 */
export const assertProductNode = (product: any) => {
  expect(product).not.toBeNull();

  expect(product.id).toBeDefined();
  expect(product.id).toContain('/api/shop/products/');

  expect(typeof product.name).toBe('string');
  expect(typeof product.sku).toBe('string');

  if (product.urlKey !== undefined) {
    expect(typeof product.urlKey).toBe('string');
  }

  if (product.price !== undefined) {
    expect(product.price).toBeTruthy();
  }
};


/**
 * Basic product response assertion (used earlier)
 */
export const assertProductResponse = (body: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('product');

  assertProductNode(body.data.product);
};

/**
 * Product with variants assertion
 * Reuses assertProductNode
 */
export const assertProductWithVariantsResponse = (body: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data.product).not.toBeNull();

  const product = body.data.product;

  // Reuse parent validation
  assertProductNode(product);

  // Variants validation
  expect(product.variants).toBeDefined();
  expect(Array.isArray(product.variants.edges)).toBeTruthy();
  expect(product.variants.edges.length).toBeGreaterThan(0);

  product.variants.edges.forEach((edge: any) => {
    const variant = edge.node;

    // Reuse same validator for variant
    assertProductNode(variant);

    // Attribute values validation
    expect(Array.isArray(variant.attributeValues.edges)).toBeTruthy();

    variant.attributeValues.edges.forEach((attrEdge: any) => {
      expect(attrEdge.node.attribute.code).toBeTruthy();
      expect(attrEdge.node.attribute.adminName).toBeTruthy();
    });
  });
};

/**
 * Full Product Details Assertion
 * Works for simple and configurable products
 */
export const assertFullProductDetailsResponse = (body: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data.product).not.toBeNull();

  const product = body.data.product;

  // Basic reusable validation
  assertProductNode(product);

  // Descriptions
  expect(typeof product.description).toBe('string');
  expect(typeof product.shortDescription).toBe('string');

  // Special Price (can be "0" or a price string)
  if (product.specialPrice !== null) {
    expect(typeof product.specialPrice).toBe('string');
  }

  // Images validation
  expect(product.images).toBeDefined();
  expect(Array.isArray(product.images.edges)).toBeTruthy();

  product.images.edges.forEach((edge: any) => {
    const image = edge.node;

    expect(image.id).toBeDefined();
    expect(image.publicPath).toContain('http');
    expect(image.position).toBeDefined();
  });

  // Attribute Values validation
  expect(product.attributeValues).toBeDefined();
  expect(Array.isArray(product.attributeValues.edges)).toBeTruthy();

  product.attributeValues.edges.forEach((edge: any) => {
    const attr = edge.node;

    expect(attr.attribute.code).toBeTruthy();
    expect(attr.attribute.adminName).toBeTruthy();
  });

  // Variants validation (simple OR configurable safe)
  expect(product.variants).toBeDefined();
  expect(Array.isArray(product.variants.edges)).toBeTruthy();

  product.variants.edges.forEach((edge: any) => {
    const variant = edge.node;

    assertProductNode(variant);

    if (variant.attributeValues) {
      expect(Array.isArray(variant.attributeValues.edges)).toBeTruthy();

      variant.attributeValues.edges.forEach((attrEdge: any) => {
        expect(attrEdge.node.attribute.code).toBeTruthy();
        expect(attrEdge.node.attribute.adminName).toBeTruthy();
      });
    }
  });

  // Categories validation
  expect(product.categories).toBeDefined();
  expect(Array.isArray(product.categories.edges)).toBeTruthy();

  product.categories.edges.forEach((edge: any) => {
    const category = edge.node;

    expect(category.id).toContain('/api/shop/categories/');
    expect(category.translation.name).toBeTruthy();
  });
};

export function assertGraphQLError( body: any, expectedMessage?: string) {
  expect(body.errors, 'Expected GraphQL errors but none were returned')
    .toBeDefined();
  const messages = body.errors.map((e: any) => e.message);
  // Print clean error output to terminal
  console.log('\n===== GRAPHQL ERROR =====');
  body.errors.forEach((error: any, index: number) => {
    console.log(`Error ${index + 1}:`);
    console.log('Message:', error.message);
    console.log('Path:', error.path);
    console.log('-------------------------');
  });
  console.log('=========================\n');
  if (expectedMessage) {
    expect(messages.join(' '))
      .toContain(expectedMessage);
  }
}

/**
 * Asserts that products search results are valid
 */
export const assertProductsSearchFilterResponse = (body: any, searchQuery: string, expectedCount?: number) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('products');
  expect(body.data.products).not.toBeNull();

  const edges = body.data.products.edges;
  expect(Array.isArray(edges)).toBeTruthy();
  
  if (expectedCount !== undefined) {
    expect(edges.length).toBe(expectedCount);
  }

  // Print search results for debugging
  console.log(`\n========== PRODUCTS SEARCH RESULTS (${searchQuery}) ==========`);
  edges.forEach((edge: any, index: number) => {
    const product = edge.node;
    console.log(`${index + 1}. ${product.name} (${product.sku}) - ${product.price}`);
  });
  console.log('=============================================================\n');

  // Validate each product node structure
  edges.forEach((edge: any) => {
    const product = edge.node;
    assertProductNode(product);
  });
};

/**
 * Asserts that no products are found for a search query
 */
export const assertProductsSearchNoResults = (body: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('products');
  expect(body.data.products).not.toBeNull();

  const edges = body.data.products.edges;
  expect(Array.isArray(edges)).toBeTruthy();
  expect(edges.length).toBe(0);

  console.log('\n===== NO PRODUCTS FOUND FOR SEARCH QUERY =====\n');
};

/**
 * Asserts that products are sorted correctly (alphabetically by name)
 */
export const assertProductsSortedByTitle = (body: any, ascending: boolean = true) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('products');
  expect(body.data.products).not.toBeNull();

  const edges = body.data.products.edges;
  expect(Array.isArray(edges)).toBeTruthy();
  expect(edges.length).toBeGreaterThan(0);

  // Extract product names to verify sorting
  const productNames = edges.map((edge: any) => edge.node.name);

  // Print product names for debugging
  console.log('\n========== PRODUCTS SORTED BY TITLE ==========');
  productNames.forEach((name: string, index: number) => {
    console.log(`${index + 1}. ${name}`);
  });
  console.log('===============================================\n');

  // Verify ascending (A-Z) or descending (Z-A) order
  if (ascending) {
    for (let i = 0; i < productNames.length - 1; i++) {
      expect(productNames[i].localeCompare(productNames[i + 1])).toBeLessThanOrEqual(0);
    }
  } else {
    for (let i = 0; i < productNames.length - 1; i++) {
      expect(productNames[i].localeCompare(productNames[i + 1])).toBeGreaterThanOrEqual(0);
    }
  }

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
};

/**
 * Asserts that products are sorted correctly by createdAt date
 */
export const assertProductsSortedByCreatedAt = (body: any, ascending: boolean = true) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('products');
  expect(body.data.products).not.toBeNull();

  const edges = body.data.products.edges;
  expect(Array.isArray(edges)).toBeTruthy();
  expect(edges.length).toBeGreaterThan(0);

  // Extract product createdAt dates to verify sorting
  const productDates = edges.map((edge: any) => new Date(edge.node.createdAt));
  const productNames = edges.map((edge: any) => edge.node.name);

  // Print product names and dates for debugging
  console.log('\n========== PRODUCTS SORTED BY CREATED_AT ==========');
  edges.forEach((edge: any, index: number) => {
    console.log(`${index + 1}. ${edge.node.name} - ${edge.node.createdAt}`);
  });
  console.log('==================================================\n');

  // Verify ascending (oldest first) or descending (newest first) order
  if (ascending) {
    for (let i = 0; i < productDates.length - 1; i++) {
      expect(productDates[i].getTime()).toBeLessThanOrEqual(productDates[i + 1].getTime());
    }
  } else {
    for (let i = 0; i < productDates.length - 1; i++) {
      expect(productDates[i].getTime()).toBeGreaterThanOrEqual(productDates[i + 1].getTime());
    }
  }

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
    expect(product.createdAt).toMatch(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/);
  });
};

/**
 * Asserts that products are sorted correctly by price
 * @param body - GraphQL response body
 * @param ascending - true for cheapest first (ascending), false for most expensive first (descending)
 */
export const assertProductsSortedByPrice = (body: any, ascending: boolean = true) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('products');
  expect(body.data.products).not.toBeNull();

  const edges = body.data.products.edges;
  expect(Array.isArray(edges)).toBeTruthy();
  expect(edges.length).toBeGreaterThan(0);

  // Extract product prices to verify sorting
  const productPrices: number[] = [];
  const productNames: string[] = [];
  
  // Validate and extract prices, handling different price formats
  edges.forEach((edge: any) => {
    const price = edge.node.price;
    let parsedPrice: number;
    
    // Handle different price formats
    if (price === null || price === undefined) {
      parsedPrice = NaN;
    } else if (typeof price === 'number') {
      parsedPrice = price;
    } else if (typeof price === 'string') {
      parsedPrice = parseFloat(price);
    } else if (typeof price === 'object') {
      // Handle object format like {minPrice: X, maxPrice: Y} or {price: X}
      if (price.minPrice !== undefined) {
        parsedPrice = parseFloat(price.minPrice);
      } else if (price.price !== undefined) {
        parsedPrice = parseFloat(price.price);
      } else if (price.maxPrice !== undefined) {
        parsedPrice = parseFloat(price.maxPrice);
      } else {
        parsedPrice = NaN;
      }
    } else {
      parsedPrice = NaN;
    }
    
    // Only include products with valid numeric prices
    if (!isNaN(parsedPrice) && parsedPrice >= 0) {
      productPrices.push(parsedPrice);
      productNames.push(edge.node.name);
    }
  });

  // If we don't have enough products with valid prices, skip the sorting assertion
  if (productPrices.length < 2) {
    console.log('\n========== PRODUCTS SORTED BY PRICE ==========');
    console.log('Warning: Not enough products with valid prices to verify sorting');
    console.log(`Valid prices found: ${productPrices.length}`);
    console.log('==============================================\n');
    return; // Skip assertion if not enough data
  }
  
  // Print product names and prices for debugging
  console.log('\n========== PRODUCTS SORTED BY PRICE ==========');
  productNames.forEach((name, index) => {
    console.log(`${index + 1}. ${name} - ${productPrices[index]}`);
  });
  console.log('==============================================\n');

  // Verify ascending (cheapest first) or descending (most expensive first) order
  if (ascending) {
    for (let i = 0; i < productPrices.length - 1; i++) {
      expect(productPrices[i]).toBeLessThanOrEqual(productPrices[i + 1]);
    }
  } else {
    for (let i = 0; i < productPrices.length - 1; i++) {
      expect(productPrices[i]).toBeGreaterThanOrEqual(productPrices[i + 1]);
    }
  }

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
};

/**
 * Asserts products filtered by category with pagination
 */
export const assertProductsByCategoryResponse = (body: any, categoryId: string, expectedFirst?: number) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('products');
  expect(body.data.products).not.toBeNull();

  const products = body.data.products;

  // Validate edges array
  expect(Array.isArray(products.edges)).toBeTruthy();

  if (expectedFirst !== undefined) {
    expect(products.edges.length).toBeLessThanOrEqual(expectedFirst);
  }

  // Validate totalCount
  expect(typeof products.totalCount).toBe('number');
  expect(products.totalCount).toBeGreaterThanOrEqual(0);

  // Validate pageInfo
  expect(products.pageInfo).toBeDefined();
  expect(typeof products.pageInfo.hasNextPage).toBe('boolean');
  expect(typeof products.pageInfo.hasPreviousPage).toBe('boolean');
  if (products.pageInfo.startCursor !== null && products.pageInfo.startCursor !== undefined) {
    expect(typeof products.pageInfo.startCursor).toBe('string');
  }
  if (products.pageInfo.endCursor !== null && products.pageInfo.endCursor !== undefined) {
    expect(typeof products.pageInfo.endCursor).toBe('string');
  }

  // Validate each product node
  products.edges.forEach((edge: any) => {
    const product = edge.node;
    expect(product).not.toBeNull();
    expect(product.id).toBeDefined();
    expect(product.sku).toBeDefined();
    expect(product.name).toBeDefined();
  });

  console.log('\n========== PRODUCTS BY CATEGORY ==========');
  console.log(`Category ID: ${categoryId}`);
  console.log(`Total Count: ${products.totalCount}`);
  console.log(`Has Next Page: ${products.pageInfo.hasNextPage}`);
  console.log(`Has Previous Page: ${products.pageInfo.hasPreviousPage}`);
  console.log('========================================\n');
};

/**
 * Asserts products filtered by type
 */
export const assertProductsByTypeResponse = (body: any, productType: string) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('products');
  expect(body.data.products).not.toBeNull();

  const products = body.data.products;

  // Validate edges array
  expect(Array.isArray(products.edges)).toBeTruthy();

  // Validate totalCount
  expect(typeof products.totalCount).toBe('number');
  expect(products.totalCount).toBeGreaterThanOrEqual(0);

  // Validate each product node
  products.edges.forEach((edge: any) => {
    const product = edge.node;
    expect(product).not.toBeNull();
    expect(product.id).toBeDefined();
    expect(product.sku).toBeDefined();
  });

  console.log('\n========== PRODUCTS BY TYPE ==========');
  console.log(`Product Type: ${productType}`);
  console.log(`Total Count: ${products.totalCount}`);
  console.log('====================================\n');
};

/**
 * Asserts products filtered by attribute
 */
export const assertProductsByAttributeResponse = (body: any, attributeCode: string, attributeValue: string) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('products');
  expect(body.data.products).not.toBeNull();

  const products = body.data.products;

  // Validate edges array
  expect(Array.isArray(products.edges)).toBeTruthy();

  // Validate totalCount
  expect(typeof products.totalCount).toBe('number');
  expect(products.totalCount).toBeGreaterThanOrEqual(0);

  // Validate each product node
  products.edges.forEach((edge: any) => {
    const product = edge.node;
    expect(product).not.toBeNull();
    expect(product.id).toBeDefined();
    expect(product.sku).toBeDefined();
  });

  console.log('\n========== PRODUCTS BY ATTRIBUTE ==========');
  console.log(`Attribute: ${attributeCode} = ${attributeValue}`);
  console.log(`Total Count: ${products.totalCount}`);
  console.log('=========================================\n');
};

/**
 * Asserts products filtered by multiple attributes
 */
export const assertProductsByMultipleAttributesResponse = (body: any, attributes: Record<string, string>, expectedFirst?: number) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('products');
  expect(body.data.products).not.toBeNull();

  const products = body.data.products;

  // Validate edges array
  expect(Array.isArray(products.edges)).toBeTruthy();

  if (expectedFirst !== undefined) {
    expect(products.edges.length).toBeLessThanOrEqual(expectedFirst);
  }

  // Validate totalCount
  expect(typeof products.totalCount).toBe('number');
  expect(products.totalCount).toBeGreaterThanOrEqual(0);

  // Validate each product node
  products.edges.forEach((edge: any) => {
    const product = edge.node;
    expect(product).not.toBeNull();
    expect(product.id).toBeDefined();
    expect(product.sku).toBeDefined();
    expect(product.name).toBeDefined();
    expect(product.price).toBeDefined();
  });

  console.log('\n========== PRODUCTS BY MULTIPLE ATTRIBUTES ==========');
  Object.entries(attributes).forEach(([key, value]) => {
    console.log(`${key}: ${value}`);
  });
  console.log(`Total Count: ${products.totalCount}`);
  console.log('==================================================\n');
};

/**
 * Asserts empty products list
 */
export const assertEmptyProductsList = (body: any) => {
  expect(body).toHaveProperty('data');
  expect(body.data).toHaveProperty('products');
  expect(body.data.products).not.toBeNull();

  const products = body.data.products;
  expect(products.edges.length).toBe(0);
  expect(products.totalCount).toBe(0);

  console.log('\n===== NO PRODUCTS FOUND =====\n');
};

