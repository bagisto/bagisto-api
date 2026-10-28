import { test, expect, APIRequestContext, APIResponse } from '@playwright/test';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../../graphql/helpers/testSupport';
import { SHOP_CATALOG_QUERIES } from '../../../graphql/Queries/coverage/shopCatalog.queries';
import { sendAdminRequest } from '../../../../restAPI/rest/helpers/adminClient';
import { SHOP_CATALOG } from '../../../../restAPI/rest/endpoints/coverage/shopCatalog.endpoints';
import { env } from '../../../config/env';

test.describe.configure({ timeout: 120_000 });

const MISSING_KEY_MESSAGE = 'X-STOREFRONT-KEY header is required for this operation';

function uniqueSku(prefix: string): string {
  return `${prefix}-${Date.now()}-${Math.floor(Math.random() * 1e6)}`;
}

async function createProduct(request: APIRequestContext, type: string): Promise<number> {
  const response = await sendAdminRequest(request, SHOP_CATALOG.ADMIN_PRODUCTS, {
    method: 'POST',
    data: { sku: uniqueSku(`e2e-gql-${type}`), type, attribute_family_id: 1 },
  });
  expect(response.status()).toBe(201);

  const body = await response.json();
  expect(typeof body?.id).toBe('number');

  return body.id;
}

async function updateProduct(request: APIRequestContext, id: number, data: Record<string, any>): Promise<any> {
  const response = await sendAdminRequest(request, SHOP_CATALOG.ADMIN_PRODUCT(id), {
    method: 'PUT',
    data,
  });
  expect(response.status()).toBe(200);

  return response.json();
}

async function deleteProducts(request: APIRequestContext, ids: number[]): Promise<void> {
  for (const id of ids) {
    await sendAdminRequest(request, SHOP_CATALOG.ADMIN_PRODUCT(id), { method: 'DELETE' });
  }
}

async function shopQuery(request: APIRequestContext, query: string, variables: Record<string, any> = {}): Promise<any> {
  const response = await sendGraphQLRequest(request, query, variables);
  expect(response.status()).toBe(200);

  return response.json();
}

async function findNode(request: APIRequestContext, query: string, field: string, id: number): Promise<any> {
  let after: string | null = null;

  for (let page = 0; page < 200; page++) {
    const body = await shopQuery(request, query, { first: 50, after });
    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

    const connection = body.data[field];
    expect(typeof connection.totalCount).toBe('number');

    const match = connection.edges.map((edge: any) => edge.node).find((node: any) => node._id === id);

    if (match) {
      return match;
    }

    if (!connection.pageInfo.hasNextPage) {
      return undefined;
    }

    after = connection.pageInfo.endCursor;
  }

  return undefined;
}

async function postWithoutStorefrontKey(request: APIRequestContext, query: string, variables: Record<string, any>): Promise<APIResponse> {
  return request.post(`${env.baseUrl}${env.graphqlEndpoint}`, {
    data: { query, variables },
    headers: { 'Content-Type': 'application/json' },
  });
}

async function expectConnectionCap(request: APIRequestContext, query: string, field: string): Promise<void> {
  const body = await shopQuery(request, query, { first: 1 });
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

  const connection = body.data[field];
  expect(connection.totalCount).toBeGreaterThan(0);
  expect(connection.edges).toHaveLength(1);
  expect(connection.pageInfo.hasNextPage).toBe(connection.totalCount > 1);
}

test.describe('shop product bundle options graphql', () => {
  let createdIds: number[] = [];
  let optionId = 0;
  let optionProductId = 0;

  test.beforeEach(async ({ request }) => {
    createdIds = [];

    const childId = await createProduct(request, 'simple');
    createdIds.push(childId);

    const bundleId = await createProduct(request, 'bundle');
    createdIds.push(bundleId);

    const updated = await updateProduct(request, bundleId, {
      bundle_options: {
        option_0: {
          en: { label: `E2E GraphQL Bundle Option ${Date.now()}` },
          type: 'select',
          is_required: 1,
          sort_order: 2,
          products: {
            product_0: { product_id: childId, qty: 2, is_default: 1, sort_order: 1 },
          },
        },
      },
    });

    expect(updated.bundleOptions).toHaveLength(1);
    expect(updated.bundleOptions[0].products).toHaveLength(1);
    optionId = updated.bundleOptions[0].id;
    optionProductId = updated.bundleOptions[0].products[0].id;
  });

  test.afterEach(async ({ request }) => {
    await deleteProducts(request, [...createdIds].reverse());
  });

  test('should list a newly created bundle option', async ({ request }) => {
    const node = await findNode(request, SHOP_CATALOG_QUERIES.productBundleOptions, 'productBundleOptions', optionId);

    expect(node, 'created bundle option is listed').toBeTruthy();
    expect(node.type).toBe('select');
    expect(node.isRequired).toBe(true);
    expect(node.sortOrder).toBe(2);
    expect(String(node.id)).toMatch(new RegExp(`/${optionId}$`));
  });

  test('should cap the bundle option connection at the requested size', async ({ request }) => {
    await expectConnectionCap(request, SHOP_CATALOG_QUERIES.productBundleOptions, 'productBundleOptions');
  });

  test('should return a single bundle option with its option products', async ({ request }) => {
    const body = await shopQuery(request, SHOP_CATALOG_QUERIES.productBundleOption, {
      id: `/api/shop/product_bundle_options/${optionId}`,
    });
    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

    const option = body.data.productBundleOption;
    expect(option._id).toBe(optionId);
    expect(option.type).toBe('select');
    expect(option.isRequired).toBe(true);
    expect(option.sortOrder).toBe(2);
    expect(option.bundleOptionProducts.edges.map((edge: any) => edge.node)).toEqual([
      { _id: optionProductId, qty: 2, isDefault: true, sortOrder: 1 },
    ]);
  });

  test('should report an error for a bundle option that does not exist', async ({ request }) => {
    const body = await shopQuery(request, SHOP_CATALOG_QUERIES.productBundleOption, {
      id: '/api/shop/product_bundle_options/999999999',
    });

    expect(graphQLErrorMessages(body).length).toBeGreaterThan(0);
    expect(body.data?.productBundleOption ?? null).toBeNull();
  });

  test('should list a newly created bundle option product', async ({ request }) => {
    const node = await findNode(
      request,
      SHOP_CATALOG_QUERIES.productBundleOptionProducts,
      'productBundleOptionProducts',
      optionProductId
    );

    expect(node, 'created bundle option product is listed').toBeTruthy();
    expect(node.qty).toBe(2);
    expect(node.isDefault).toBe(true);
    expect(node.sortOrder).toBe(1);
  });

  test('should cap the bundle option product connection at the requested size', async ({ request }) => {
    await expectConnectionCap(request, SHOP_CATALOG_QUERIES.productBundleOptionProducts, 'productBundleOptionProducts');
  });

  test('should return a single bundle option product by its iri', async ({ request }) => {
    const listed = await findNode(
      request,
      SHOP_CATALOG_QUERIES.productBundleOptionProducts,
      'productBundleOptionProducts',
      optionProductId
    );
    expect(listed, 'created bundle option product is listed').toBeTruthy();

    const body = await shopQuery(request, SHOP_CATALOG_QUERIES.productBundleOptionProduct, { id: listed.id });
    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

    const optionProduct = body.data.productBundleOptionProduct;
    expect(optionProduct._id).toBe(optionProductId);
    expect(optionProduct.id).toBe(listed.id);
    expect(optionProduct.qty).toBe(2);
    expect(optionProduct.isDefault).toBe(true);
    expect(typeof optionProduct.isUserDefined).toBe('boolean');
    expect(optionProduct.sortOrder).toBe(1);
  });

  test('should report an error for a bundle option product that does not exist', async ({ request }) => {
    const body = await shopQuery(request, SHOP_CATALOG_QUERIES.productBundleOptionProduct, {
      id: '/api/shop/product-bundle-option-products/999999999',
    });

    expect(graphQLErrorMessages(body).length).toBeGreaterThan(0);
    expect(body.data?.productBundleOptionProduct ?? null).toBeNull();
  });
});

test.describe('shop product grouped products graphql', () => {
  let createdIds: number[] = [];
  let rowId = 0;

  test.beforeEach(async ({ request }) => {
    createdIds = [];

    const childId = await createProduct(request, 'simple');
    createdIds.push(childId);

    const groupedId = await createProduct(request, 'grouped');
    createdIds.push(groupedId);

    const updated = await updateProduct(request, groupedId, {
      links: {
        link_0: { associated_product_id: childId, qty: 3, sort_order: 1 },
      },
    });

    expect(updated.linkedProducts).toHaveLength(1);
    expect(updated.linkedProducts[0].associatedProductId).toBe(childId);
    rowId = updated.linkedProducts[0].id;
  });

  test.afterEach(async ({ request }) => {
    await deleteProducts(request, [...createdIds].reverse());
  });

  test('should list a newly created grouped product member', async ({ request }) => {
    const node = await findNode(request, SHOP_CATALOG_QUERIES.productGroupedProducts, 'productGroupedProducts', rowId);

    expect(node, 'created grouped product member is listed').toBeTruthy();
    expect(node.qty).toBe(3);
    expect(node.sortOrder).toBe(1);
    expect(String(node.id)).toMatch(new RegExp(`/${rowId}$`));
  });

  test('should cap the grouped product connection at the requested size', async ({ request }) => {
    await expectConnectionCap(request, SHOP_CATALOG_QUERIES.productGroupedProducts, 'productGroupedProducts');
  });

  test('should return a single grouped product member by its iri', async ({ request }) => {
    const body = await shopQuery(request, SHOP_CATALOG_QUERIES.productGroupedProduct, {
      id: `/api/shop/product_grouped_products/${rowId}`,
    });
    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

    const member = body.data.productGroupedProduct;
    expect(member._id).toBe(rowId);
    expect(member.qty).toBe(3);
    expect(member.sortOrder).toBe(1);
  });

  test('should report an error for a grouped product member that does not exist', async ({ request }) => {
    const body = await shopQuery(request, SHOP_CATALOG_QUERIES.productGroupedProduct, {
      id: '/api/shop/product_grouped_products/999999999',
    });

    expect(graphQLErrorMessages(body).length).toBeGreaterThan(0);
    expect(body.data?.productGroupedProduct ?? null).toBeNull();
  });
});

test.describe('shop catalog product types graphql without a storefront key', () => {
  const operations = [
    { label: 'the bundle option connection', query: SHOP_CATALOG_QUERIES.productBundleOptions, variables: { first: 1 } },
    { label: 'a single bundle option', query: SHOP_CATALOG_QUERIES.productBundleOption, variables: { id: '/api/shop/product_bundle_options/1' } },
    { label: 'the bundle option product connection', query: SHOP_CATALOG_QUERIES.productBundleOptionProducts, variables: { first: 1 } },
    { label: 'a single bundle option product', query: SHOP_CATALOG_QUERIES.productBundleOptionProduct, variables: { id: '/api/shop/product-bundle-option-products/1' } },
    { label: 'the grouped product connection', query: SHOP_CATALOG_QUERIES.productGroupedProducts, variables: { first: 1 } },
    { label: 'a single grouped product member', query: SHOP_CATALOG_QUERIES.productGroupedProduct, variables: { id: '/api/shop/product_grouped_products/1' } },
  ];

  for (const { label, query, variables } of operations) {
    test(`should refuse ${label} without a storefront key`, async ({ request }) => {
      const response = await postWithoutStorefrontKey(request, query, variables);
      expect(response.status()).toBe(401);

      const body = await response.json();
      expect(body.message).toBe(MISSING_KEY_MESSAGE);
      expect(body.errors[0].extensions.code).toBe('UNAUTHENTICATED');
      expect(body.data).toBeUndefined();
    });
  }
});
