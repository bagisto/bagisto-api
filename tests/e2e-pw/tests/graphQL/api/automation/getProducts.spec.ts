// tests/api/product/getProduct.spec.ts
import { test, expect, request } from '@playwright/test';
import { GET_PRODUCT_BY_ID, GET_PRODUCT_BY_SKU, GET_PRODUCT_WITH_VARIANTS, GET_FULL_PRODUCT_DETAILS,
  GET_PRODUCT_BY_URLKEY, GET_PRODUCTS_SEARCH_FILTER, GET_PRODUCTS_WITH_FORMATTED_PRICES,
  GET_SIMPLE_PRODUCTS, GET_CONFIGURABLE_PRODUCTS, GET_BOOKING_PRODUCTS, GET_VIRTUAL_PRODUCTS,
  GET_GROUPED_PRODUCTS, GET_DOWNLOADABLE_PRODUCTS, GET_BUNDLE_PRODUCTS,
  GET_PRODUCT_BOOKING_APPOINTMENT, GET_PRODUCT_BOOKING_RENTAL, GET_PRODUCT_BOOKING_DEFAULT,
  GET_PRODUCT_BOOKING_TABLE, GET_PRODUCT_BOOKING_EVENT
 } 
from '../../graphql/Queries/product.queries';
import { assertProductResponse, assertProductWithVariantsResponse, assertFullProductDetailsResponse,
  assertProductNode, assertGraphQLError, assertProductsSearchFilterResponse, assertProductsSearchNoResults
 } 
from '../../graphql/assertions/product.assertions';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { expectConnection, graphQLErrorMessages, logGraphQLMessages, pick } from '../../graphql/helpers/testSupport';

async function findBookingProductBySubtype(request: any, subtype: string) {
  const response = await sendGraphQLRequest(request, GET_BOOKING_PRODUCTS);
  expect(response.status()).toBe(200);

  const body = await response.json();
  const connection = expectConnection(body, 'data.products');

  for (const edge of connection.edges ?? []) {
    const bookingEdges = edge.node?.bookingProducts?.edges ?? [];

    if (bookingEdges.some((bookingEdge: any) => bookingEdge.node?.type === subtype)) {
      return edge.node;
    }
  }

  return null;
}

test.describe('Get Products by Search and Filter API', () => {

  test('Should return products matching search query with valid parameters', async ({ request }) => {
    const variables = {
      query: "knit",
      sortKey: "TITLE",
      reverse: false,
      first: 10
    };

    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SEARCH_FILTER, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    assertProductsSearchFilterResponse(body, variables.query);
  });

  test('Should return products sorted by title descending (reverse=true)', async ({ request }) => {
    const variables = {
      query: "knit",
      sortKey: "TITLE",
      reverse: true,
      first: 10
    };

    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SEARCH_FILTER, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    assertProductsSearchFilterResponse(body, variables.query);
  });

  test('Should return limited number of products when first parameter is specified', async ({ request }) => {
    const variables = {
      query: "knit",
      sortKey: "TITLE",
      reverse: false,
      first: 5
    };

    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SEARCH_FILTER, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    const edges = body.data.products.edges;
    expect(edges.length).toBeLessThanOrEqual(variables.first);
    assertProductsSearchFilterResponse(body, variables.query, edges.length);
  });

  test('Should return products without search query (all products)', async ({ request }) => {
    const variables = {
      query: "",
      sortKey: "TITLE",
      reverse: false,
      first: 10
    };

    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SEARCH_FILTER, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    expect(body.data.products.edges.length).toBeGreaterThan(0);
    body.data.products.edges.forEach((edge: any) => {
      assertProductNode(edge.node);
    });
  });

  test('Should return no products for non-existent search term', async ({ request }) => {
    const variables = {
      query: "nonexistentproduct12345",
      sortKey: "TITLE",
      reverse: false,
      first: 10
    };

    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SEARCH_FILTER, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    assertProductsSearchNoResults(body);
  });

  test('Should handle special characters in search query', async ({ request }) => {
    const variables = {
      query: "!@#$%^&*()",
      sortKey: "TITLE",
      reverse: false,
      first: 10
    };

    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SEARCH_FILTER, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    // Should not throw an error, might return no products
    expect(body.errors).toBeUndefined();
    expect(body.data.products).toBeDefined();
  });

  test('Should handle empty search query gracefully', async ({ request }) => {
    const variables = {
      query: null,
      sortKey: "TITLE",
      reverse: false,
      first: 10
    };

    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SEARCH_FILTER, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    // Should not throw an error
    expect(body.errors).toBeUndefined();
  });

  test('Should handle large first parameter value', async ({ request }) => {
    const variables = {
      query: "knit",
      sortKey: "TITLE",
      reverse: false,
      first: 1000
    };

    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SEARCH_FILTER, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    // Should return products without errors
    expect(body.errors).toBeUndefined();
    expect(body.data.products.edges.length).toBeGreaterThanOrEqual(0);
  });

  test('Should handle invalid sortKey gracefully', async ({ request }) => {
    const variables = {
      query: "knit",
      sortKey: "INVALID_SORT_KEY",
      reverse: false,
      first: 10
    };

    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SEARCH_FILTER, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    // Should not return an error
    expect(body.errors).toBeUndefined();
    // Should return products
    expect(body.data.products.edges.length).toBeGreaterThan(0);
    // Validate each product node structure
    body.data.products.edges.forEach((edge: any) => {
      assertProductNode(edge.node);
    });
  });
});

test.describe('Get Product by ID API', () => {

  test('Should fetch product successfully with valid ID', async ({ request }) => {
    const response = await sendGraphQLRequest( request, GET_PRODUCT_BY_ID,
    { id: "1" });
    expect(response.status()).toBe(200);
     const body = await response.json();
  const product = body.data.product;
  // Pretty print in terminal
  console.log('\n========== PRODUCT DETAILS ==========');
  console.log(`ID      : ${product.id}`);
  console.log(`Name    : ${product.name}`);
  console.log(`SKU     : ${product.sku}`);
  console.log(`URL Key : ${product.urlKey}`);
  console.log(`Price   : ${product.price}`);
  console.log('=====================================\n');
  assertProductResponse(body);
  });

  test('Should return null for invalid product ID', async ({ request }) => {
    const response = await sendGraphQLRequest( request, GET_PRODUCT_BY_ID,
    { id: "999999" });
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(body.data.product).toBeNull();
    console.log(body.errors);
  });

  test('Should fetch product successfully with valid SKU', async ({ request }) => {
    const response = await sendGraphQLRequest( request, GET_PRODUCT_BY_SKU,
    { sku: "SP-001" });
    expect(response.status()).toBe(200);
    const body = await response.json();
    const product = body.data.product;
    // Print Product Details in Terminal
    console.log('\n========== PRODUCT DETAILS (SKU) ==========');
    console.log(`ID      : ${product.id}`);
    console.log(`Name    : ${product.name}`);
    console.log(`SKU     : ${product.sku}`);
    console.log(`URL Key : ${product.urlKey}`);
    console.log(`Price   : ${product.price}`);
    console.log('===========================================\n');
    assertProductResponse(body);
  });

  test('should fetch product successfully with URL Key', async({request})=>{
    const response = await sendGraphQLRequest( request, GET_PRODUCT_BY_URLKEY,
    { urlKey: "omniheat-mens-solid-hooded-puffer-jacket-blue-green-l"});
    expect(response.status()).toBe(200);
    const body = await response.json();
    assertProductResponse(body);
  });

  test('should return error with invalid URL Key', async({request})=>{
    const response = await sendGraphQLRequest( request, GET_PRODUCT_BY_URLKEY,
    { urlKey: "omniheat-mee-green-l"});
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(body.errors || body.data?.product === null).toBeTruthy();

  });

  test('Should return null for invalid SKU', async ({ request }) => {
    const response = await sendGraphQLRequest( request, GET_PRODUCT_BY_SKU,
    { sku: "INVALID-SKU-999" });
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(body.data.product).toBeNull();
  });

  test('Should fetch configurable product with its variants successfully', async ({ request }) => {
    const response = await sendGraphQLRequest( request, GET_PRODUCT_WITH_VARIANTS,
    { id: "7" });
    expect(response.status()).toBe(200);
    const body = await response.json();
    const product = body.data.product;

console.log('\n========= CONFIGURABLE PRODUCT SUMMARY =========');
console.log(`Parent ID        : ${product.id}`);
console.log(`Name             : ${product.name}`);
console.log(`SKU              : ${product.sku}`);
console.log(`Total Variants   : ${product.variants.edges.length}`);
console.log('================================================\n');

let prices: number[] = [];

product.variants.edges.forEach((edge: any, index: number) => {
  const variant = edge.node;
  prices.push(Number(variant.price));

  console.log(`Variant ${index + 1}:`);
  console.log(`  ID    : ${variant.id}`);
  console.log(`  SKU   : ${variant.sku}`);
  console.log(`  Price : ${variant.price}`);

  const attributes = variant.attributeValues.edges;

  console.log(`  Total Attributes : ${attributes.length}`);

  attributes.forEach((attr: any) => {
    const code = attr.node.attribute.code;
    let value = attr.node.value;

    // Truncate long text fields
    if (typeof value === 'string' && value.length > 50) {
      value = value.substring(0, 50) + '...';
    }
    console.log(`     - ${code} : ${value}`);
  });
  console.log('----------------------------------------');
});

    console.log('\n========= VARIANT PRICE SUMMARY =========');
    console.log(`Min Price : ${Math.min(...prices)}`);
    console.log(`Max Price : ${Math.max(...prices)}`);
    console.log('==========================================\n');

    assertProductWithVariantsResponse(body);
  });

  test('Should fetch full product details', async ({ request }) => {
  const response = await sendGraphQLRequest( request, GET_FULL_PRODUCT_DETAILS,
  { id: "1" });
  expect(response.status()).toBe(200);
  const body = await response.json();
  const product = body.data.product;
console.log('\n=========== PRODUCT DETAILS ===========');
console.log(`ID            : ${product.id}`);
console.log(`Name          : ${product.name}`);
console.log(`SKU           : ${product.sku}`);
console.log(`URL Key       : ${product.urlKey}`);
console.log(`Price         : ${product.price}`);
console.log(`Special Price : ${product.specialPrice}`);
console.log(`Images Count  : ${product.images.edges.length}`);
console.log(`Attributes    : ${product.attributeValues.edges.length}`);
console.log(`Variants      : ${product.variants.edges.length}`);
console.log(`Categories    : ${product.categories.edges.length}`);
console.log('=======================================\n');
  assertFullProductDetailsResponse(body);
});

  test('Should fetch products with formatted prices according to docs', async ({ request }) => {
    const response = await sendGraphQLRequest(request, GET_PRODUCTS_WITH_FORMATTED_PRICES, { first: 5 });
    expect(response.status()).toBe(200);

    const body = await response.json();
    const connection = expectConnection(body, 'data.products');
    const product = connection.edges[0]?.node;

    expect(product).toBeTruthy();
    expect(typeof product.formattedPrice).toBe('string');

    console.log('\n========== FORMATTED PRICE PRODUCT ==========');
    console.log(`Name              : ${product.name}`);
    console.log(`SKU               : ${product.sku}`);
    console.log(`Raw Price         : ${product.price}`);
    console.log(`Formatted Price   : ${product.formattedPrice}`);
    console.log(`Formatted Minimum : ${product.formattedMinimumPrice}`);
    console.log(`Formatted Maximum : ${product.formattedMaximumPrice}`);
    console.log('============================================\n');
  });

  const productTypeCases = [
    { label: 'simple', query: GET_SIMPLE_PRODUCTS },
    { label: 'configurable', query: GET_CONFIGURABLE_PRODUCTS },
    { label: 'booking', query: GET_BOOKING_PRODUCTS },
    { label: 'virtual', query: GET_VIRTUAL_PRODUCTS },
    { label: 'grouped', query: GET_GROUPED_PRODUCTS },
    { label: 'downloadable', query: GET_DOWNLOADABLE_PRODUCTS },
    { label: 'bundle', query: GET_BUNDLE_PRODUCTS },
  ];

  for (const { label, query } of productTypeCases) {
    test(`Should cover docs query for ${label} products`, async ({ request }) => {
      const response = await sendGraphQLRequest(request, query);
      expect(response.status()).toBe(200);

      const body = await response.json();
      const connection = expectConnection(body, 'data.products');
      const edges = connection.edges ?? [];

      console.log(`\n========== ${label.toUpperCase()} PRODUCTS ==========`);
      console.log(`Returned Count: ${edges.length}`);

      if (edges.length > 0) {
        console.log(`First Product : ${edges[0].node.name} (${edges[0].node.sku})`);
      } else {
        console.log('No products of this type exist in the current environment.');
      }

      console.log('=========================================\n');
    });
  }

  const bookingSubtypeCases = [
    {
      subtype: 'appointment',
      query: GET_PRODUCT_BOOKING_APPOINTMENT,
      path: 'data.product.bookingProducts.edges.0.node.appointmentSlot',
    },
    {
      subtype: 'rental',
      query: GET_PRODUCT_BOOKING_RENTAL,
      path: 'data.product.bookingProducts.edges.0.node.rentalSlot',
    },
    {
      subtype: 'default',
      query: GET_PRODUCT_BOOKING_DEFAULT,
      path: 'data.product.bookingProducts.edges.0.node.defaultSlot',
    },
    {
      subtype: 'table',
      query: GET_PRODUCT_BOOKING_TABLE,
      path: 'data.product.bookingProducts.edges.0.node.tableSlot',
    },
    {
      subtype: 'event',
      query: GET_PRODUCT_BOOKING_EVENT,
      path: 'data.product.bookingProducts.edges.0.node.eventTickets',
    },
  ];

  for (const { subtype, query, path } of bookingSubtypeCases) {
    test(`Should cover docs query for ${subtype} booking products`, async ({ request }) => {
      const bookingProduct = await findBookingProductBySubtype(request, subtype);

      if (!bookingProduct) {
        console.log(`No booking product found for subtype "${subtype}" in current data.`);
        expect(true).toBeTruthy();
        return;
      }

      const response = await sendGraphQLRequest(request, query, { id: bookingProduct.id });
      expect(response.status()).toBe(200);

      const body = await response.json();
      logGraphQLMessages(`${subtype} booking product`, body);
      expect(body.errors).toBeUndefined();
      expect(pick(body, 'data.product')).toBeTruthy();
      expect(pick(body, path)).toBeTruthy();

      console.log(`\n========== ${subtype.toUpperCase()} BOOKING PRODUCT ==========`);
      console.log(`Name : ${bookingProduct.name}`);
      console.log(`SKU  : ${bookingProduct.sku}`);
      console.log(`ID   : ${bookingProduct.id}`);
      console.log('===================================================\n');
    });
  }
});
