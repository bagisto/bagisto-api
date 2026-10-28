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

test.describe('get products by search and filter api', () => {

  test('should return products matching search query with valid parameters', async ({ request }) => {
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

  test('should return products sorted by title descending (reverse=true)', async ({ request }) => {
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

  test('should return limited number of products when first parameter is specified', async ({ request }) => {
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

  test('should return products without search query (all products)', async ({ request }) => {
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

  test('should return no products for non-existent search term', async ({ request }) => {
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

  test('should handle special characters in search query', async ({ request }) => {
    const variables = {
      query: "!@#$%^&*()",
      sortKey: "TITLE",
      reverse: false,
      first: 10
    };

    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SEARCH_FILTER, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    expect(body.errors).toBeUndefined();
    expect(body.data.products).toBeDefined();
  });

  test('should handle empty search query gracefully', async ({ request }) => {
    const variables = {
      query: null,
      sortKey: "TITLE",
      reverse: false,
      first: 10
    };

    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SEARCH_FILTER, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    expect(body.errors).toBeUndefined();
  });

  test('should handle large first parameter value', async ({ request }) => {
    const variables = {
      query: "knit",
      sortKey: "TITLE",
      reverse: false,
      first: 1000
    };

    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SEARCH_FILTER, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    expect(body.errors).toBeUndefined();
    expect(body.data.products.edges.length).toBeGreaterThanOrEqual(0);
  });

  test('should handle invalid sortkey gracefully', async ({ request }) => {
    const variables = {
      query: "",
      sortKey: "INVALID_SORT_KEY",
      reverse: false,
      first: 10
    };

    const response = await sendGraphQLRequest(request, GET_PRODUCTS_SEARCH_FILTER, variables);
    expect(response.status()).toBe(200);
    const body = await response.json();
    
    expect(body.errors).toBeUndefined();
    expect(body.data.products.edges.length).toBeGreaterThan(0);
    body.data.products.edges.forEach((edge: any) => {
      assertProductNode(edge.node);
    });
  });
});

test.describe('get product by id api', () => {

  test('should fetch product successfully with valid id', async ({ request }) => {
    const response = await sendGraphQLRequest( request, GET_PRODUCT_BY_ID,
    { id: "1" });
    expect(response.status()).toBe(200);
     const body = await response.json();
  const product = body.data.product;
  console.log('\n========== PRODUCT DETAILS ==========');
  console.log(`ID      : ${product.id}`);
  console.log(`Name    : ${product.name}`);
  console.log(`SKU     : ${product.sku}`);
  console.log(`URL Key : ${product.urlKey}`);
  console.log(`Price   : ${product.price}`);
  console.log('=====================================\n');
  assertProductResponse(body);
  });

  test('should return null for invalid product id', async ({ request }) => {
    const response = await sendGraphQLRequest( request, GET_PRODUCT_BY_ID,
    { id: "999999" });
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(body.data.product).toBeNull();
    console.log(body.errors);
  });

  test('should fetch product successfully with valid sku', async ({ request }) => {
    const listBody = await (await sendGraphQLRequest(request, GET_PRODUCTS_SEARCH_FILTER, { query: "", first: 1 })).json();
    const sku = listBody.data.products.edges[0].node.sku;

    const response = await sendGraphQLRequest( request, GET_PRODUCT_BY_SKU,
    { sku });
    expect(response.status()).toBe(200);
    const body = await response.json();
    const product = body.data.product;
    expect(product.sku).toBe(sku);
    console.log('\n========== PRODUCT DETAILS (SKU) ==========');
    console.log(`ID      : ${product.id}`);
    console.log(`Name    : ${product.name}`);
    console.log(`SKU     : ${product.sku}`);
    console.log(`URL Key : ${product.urlKey}`);
    console.log(`Price   : ${product.price}`);
    console.log('===========================================\n');
    assertProductResponse(body);
  });

  test('should fetch product successfully with url key', async({request})=>{
    const cfgBody = await (await sendGraphQLRequest(request, GET_CONFIGURABLE_PRODUCTS, {})).json();
    const urlKey = cfgBody.data.products.edges[0].node.urlKey;

    const response = await sendGraphQLRequest( request, GET_PRODUCT_BY_URLKEY,
    { urlKey });
    expect(response.status()).toBe(200);
    const body = await response.json();
    assertProductResponse(body);
  });

  test('should return error with invalid url key', async({request})=>{
    const response = await sendGraphQLRequest( request, GET_PRODUCT_BY_URLKEY,
    { urlKey: `no-such-product-${Date.now()}-${Math.floor(Math.random() * 1e6)}` });
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(graphQLErrorMessages(body)).toEqual(['No product found with URL key']);
    expect(body.data.product).toBeNull();
  });

  test('should return null for invalid sku', async ({ request }) => {
    const response = await sendGraphQLRequest( request, GET_PRODUCT_BY_SKU,
    { sku: "INVALID-SKU-999" });
    expect(response.status()).toBe(200);
    const body = await response.json();
    expect(body.data.product).toBeNull();
    expect(graphQLErrorMessages(body)).toEqual(['No product found with SKU']);
  });

  test('should fetch configurable product with its variants successfully', async ({ request }) => {
    const cfgBody = await (await sendGraphQLRequest(request, GET_CONFIGURABLE_PRODUCTS, {})).json();
    const id = String(cfgBody.data.products.edges[0].node.id).split('/').pop();

    const response = await sendGraphQLRequest( request, GET_PRODUCT_WITH_VARIANTS,
    { id });
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

  test('should fetch full product details', async ({ request }) => {
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

  test('should fetch products with formatted prices according to docs', async ({ request }) => {
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
    test(`should cover docs query for ${label} products`, async ({ request }) => {
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
    test(`should cover docs query for ${subtype} booking products`, async ({ request }) => {
      const bookingProduct = await findBookingProductBySubtype(request, subtype);

      test.skip(!bookingProduct, `no ${subtype} booking product is listed by the storefront products query`);

      const response = await sendGraphQLRequest(request, query, { id: bookingProduct.id });
      expect(response.status()).toBe(200);

      const body = await response.json();
      logGraphQLMessages(`${subtype} booking product`, body);
      expect(body.errors).toBeUndefined();
      expect(pick(body, 'data.product')).toBeTruthy();
      expect(pick(body, 'data.product.id')).toBe(bookingProduct.id);
      expect(pick(body, 'data.product.bookingProducts.edges.0.node.type')).toBe(subtype);
      expect(pick(body, path)).toBeTruthy();

      console.log(`\n========== ${subtype.toUpperCase()} BOOKING PRODUCT ==========`);
      console.log(`Name : ${bookingProduct.name}`);
      console.log(`SKU  : ${bookingProduct.sku}`);
      console.log(`ID   : ${bookingProduct.id}`);
      console.log('===================================================\n');
    });
  }
});
