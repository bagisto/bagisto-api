import { test, expect, APIRequestContext, APIResponse } from '@playwright/test';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../../graphql/helpers/testSupport';
import { SHOP_CATALOG_QUERIES } from '../../../graphql/Queries/coverage/shopCatalog.queries';
import { sendRestRequest } from '../../../../restAPI/rest/helpers/restClient';
import { sendAdminRequest } from '../../../../restAPI/rest/helpers/adminClient';
import { SHOP_CATALOG } from '../../../../restAPI/rest/endpoints/coverage/shopCatalog.endpoints';
import { env } from '../../../config/env';

test.describe.configure({ timeout: 90_000 });

const MISSING_KEY_MESSAGE = 'X-STOREFRONT-KEY header is required for this operation';

const SECTION_TYPES = [
  'image_carousel',
  'product_carousel',
  'category_carousel',
  'footer_links',
  'static_content',
  'services_content',
];

const FIRST_PRODUCT_QUERY = `
  query firstProduct {
    products(first: 1) {
      edges {
        node {
          _id
        }
      }
    }
  }
`;

async function shopQuery(request: APIRequestContext, query: string, variables: Record<string, any> = {}): Promise<any> {
  const response = await sendGraphQLRequest(request, query, variables);
  expect(response.status()).toBe(200);

  return response.json();
}

async function postWithoutStorefrontKey(request: APIRequestContext, query: string, variables: Record<string, any>): Promise<APIResponse> {
  return request.post(`${env.baseUrl}${env.graphqlEndpoint}`, {
    data: { query, variables },
    headers: { 'Content-Type': 'application/json' },
  });
}

async function filterableAttributes(request: APIRequestContext): Promise<any> {
  const body = await shopQuery(request, SHOP_CATALOG_QUERIES.categoryAttributeFilters, { first: 100 });
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

  return body.data.categoryAttributeFilters;
}

test.describe('shop attribute translations graphql', () => {
  test('should list attribute translations as a cursor connection', async ({ request }) => {
    const body = await shopQuery(request, SHOP_CATALOG_QUERIES.attributeTranslations, { first: 5 });
    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

    const connection = body.data.attributeTranslations;
    expect(connection.totalCount).toBeGreaterThan(0);
    expect(connection.edges.length).toBe(Math.min(5, connection.totalCount));
    expect(connection.pageInfo.hasNextPage).toBe(connection.totalCount > 5);

    for (const { cursor, node } of connection.edges) {
      expect(typeof cursor).toBe('string');
      expect(Number.isInteger(node._id)).toBe(true);
      expect(String(node.id)).toMatch(new RegExp(`/${node._id}$`));
      expect(Number.isInteger(node.attributeId)).toBe(true);
      expect(typeof node.locale).toBe('string');
    }
  });

  test('should return the next attribute translations after a cursor', async ({ request }) => {
    const first = await shopQuery(request, SHOP_CATALOG_QUERIES.attributeTranslations, { first: 2 });
    expect(first.errors, JSON.stringify(first.errors)).toBeUndefined();
    const firstPage = first.data.attributeTranslations;
    expect(firstPage.pageInfo.hasNextPage).toBe(true);

    const next = await shopQuery(request, SHOP_CATALOG_QUERIES.attributeTranslations, {
      first: 2,
      after: firstPage.pageInfo.endCursor,
    });
    expect(next.errors, JSON.stringify(next.errors)).toBeUndefined();

    const firstIds = firstPage.edges.map((edge: any) => edge.node._id);
    const nextIds = next.data.attributeTranslations.edges.map((edge: any) => edge.node._id);
    expect(nextIds.length).toBeGreaterThan(0);
    expect(nextIds.filter((id: number) => firstIds.includes(id))).toEqual([]);
  });

  test('should return a single attribute translation matching the rest resource', async ({ request }) => {
    const list = await shopQuery(request, SHOP_CATALOG_QUERIES.attributeTranslations, { first: 1 });
    expect(list.errors, JSON.stringify(list.errors)).toBeUndefined();
    const listed = list.data.attributeTranslations.edges[0].node;

    const body = await shopQuery(request, SHOP_CATALOG_QUERIES.attributeTranslation, { id: listed.id });
    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
    expect(body.data.attributeTranslation).toEqual(listed);

    const rest = await sendRestRequest(request, `/api/shop/attribute_translations/${listed._id}`);
    expect(rest.status()).toBe(200);
    const restBody = await rest.json();
    expect(Number(restBody.attributeId)).toBe(listed.attributeId);
    expect(restBody.locale).toBe(listed.locale);
    expect(restBody.name).toBe(listed.name);
  });

  test('should report an error for an attribute translation that does not exist', async ({ request }) => {
    const body = await shopQuery(request, SHOP_CATALOG_QUERIES.attributeTranslation, {
      id: '/api/shop/attribute_translations/999999999',
    });

    expect(graphQLErrorMessages(body).length).toBeGreaterThan(0);
    expect(body.data?.attributeTranslation ?? null).toBeNull();
  });
});

test.describe('shop category attribute filters graphql', () => {
  let categoryId = 0;

  test.afterEach(async ({ request }) => {
    if (categoryId) {
      await sendAdminRequest(request, SHOP_CATALOG.ADMIN_CATEGORY(categoryId), { method: 'DELETE' });
      categoryId = 0;
    }
  });

  test('should list every filterable attribute in id order when no category is given', async ({ request }) => {
    const connection = await filterableAttributes(request);

    expect(connection.totalCount).toBeGreaterThan(0);
    expect(connection.edges).toHaveLength(Math.min(100, connection.totalCount));

    const ids = connection.edges.map((edge: any) => edge.node._id);
    expect(ids).toEqual([...ids].sort((a: number, b: number) => a - b));

    for (const { node } of connection.edges) {
      expect(node.isFilterable).toBe(true);
      expect(typeof node.code).toBe('string');
      expect(typeof node.adminName).toBe('string');
      expect(typeof node.type).toBe('string');
    }
  });

  test('should narrow the filters to the attributes a category is filtered by', async ({ request }) => {
    const all = await filterableAttributes(request);
    const attribute = all.edges[0].node;
    const slug = `e2e-filter-${Date.now()}-${Math.floor(Math.random() * 1e6)}`;

    const created = await sendAdminRequest(request, SHOP_CATALOG.ADMIN_CATEGORIES, {
      method: 'POST',
      data: {
        slug,
        name: `E2E Filter ${slug}`,
        position: 1,
        status: 1,
        display_mode: 'products_only',
        parent_id: 1,
        attributes: [attribute._id],
      },
    });
    expect(created.status()).toBe(201);
    categoryId = (await created.json()).id;
    expect(typeof categoryId).toBe('number');

    const body = await shopQuery(request, SHOP_CATALOG_QUERIES.categoryAttributeFilters, { categorySlug: slug });
    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

    const connection = body.data.categoryAttributeFilters;
    expect(connection.totalCount).toBe(1);
    expect(connection.edges).toHaveLength(1);
    expect(connection.edges[0].node._id).toBe(attribute._id);
    expect(connection.edges[0].node.code).toBe(attribute.code);
  });

  test('should refuse a category slug that is not a string', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_CATALOG_QUERIES.categoryAttributeFiltersWithIntegerSlug);
    const body = await response.json();

    expect(graphQLErrorMessages(body).join(' ')).toContain('String cannot represent a non string value: 123');
    expect(body.data?.categoryAttributeFilters ?? null).toBeNull();
  });
});

test.describe('shop theme graphql', () => {
  test('should return the active theme of the channel matching the rest resource', async ({ request }) => {
    const body = await shopQuery(request, SHOP_CATALOG_QUERIES.theme);
    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

    const theme = body.data.theme;
    expect(typeof theme.code).toBe('string');
    expect(theme.code.length).toBeGreaterThan(0);
    expect(typeof theme.name).toBe('string');
    expect(theme.sectionTypes).toEqual(SECTION_TYPES);

    const rest = await sendRestRequest(request, SHOP_CATALOG.THEME);
    expect(rest.status()).toBe(200);
    const [restTheme] = await rest.json();
    expect(restTheme.code).toBe(theme.code);
    expect(restTheme.name).toBe(theme.name);
  });

  test('should refuse an argument the theme query does not take', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_CATALOG_QUERIES.themeWithArgument);
    const body = await response.json();

    expect(graphQLErrorMessages(body).join(' ')).toContain('Unknown argument "id" on field "theme"');
    expect(body.data?.theme ?? null).toBeNull();
  });
});

test.describe('shop product attribute file graphql', () => {
  test('should report a product that does not exist as not found', async ({ request }) => {
    const body = await shopQuery(request, SHOP_CATALOG_QUERIES.productAttributeFile, {
      productId: 999999999,
      attributeId: 1,
    });

    expect(graphQLErrorMessages(body)).toContain('Product not found');
    expect(body.data?.productAttributeFile ?? null).toBeNull();
  });

  test('should refuse an attribute that is not a file or image attribute', async ({ request }) => {
    const products = await shopQuery(request, FIRST_PRODUCT_QUERY);
    expect(products.errors, JSON.stringify(products.errors)).toBeUndefined();
    const productId = products.data.products.edges[0]?.node?._id;
    expect(Number.isInteger(productId)).toBe(true);

    const filters = await filterableAttributes(request);
    const textLike = filters.edges.map((edge: any) => edge.node).find((node: any) => !['file', 'image'].includes(node.type));
    expect(textLike).toBeTruthy();

    const body = await shopQuery(request, SHOP_CATALOG_QUERIES.productAttributeFile, {
      productId,
      attributeId: textLike._id,
    });

    expect(graphQLErrorMessages(body)).toContain('Product not found');
    expect(body.data?.productAttributeFile ?? null).toBeNull();
  });

  test('should refuse a lookup without the attribute id', async ({ request }) => {
    const response = await sendGraphQLRequest(request, SHOP_CATALOG_QUERIES.productAttributeFile, { productId: 1 });
    const body = await response.json();

    expect(graphQLErrorMessages(body).join(' ')).toContain('Variable "$attributeId" of required type "Int!" was not provided');
    expect(body.data?.productAttributeFile ?? null).toBeNull();
  });
});

test.describe('shop catalog attributes graphql without a storefront key', () => {
  const operations = [
    { label: 'the attribute translation connection', query: SHOP_CATALOG_QUERIES.attributeTranslations, variables: { first: 1 } },
    { label: 'a single attribute translation', query: SHOP_CATALOG_QUERIES.attributeTranslation, variables: { id: '/api/shop/attribute_translations/1' } },
    { label: 'the category attribute filters', query: SHOP_CATALOG_QUERIES.categoryAttributeFilters, variables: { first: 1 } },
    { label: 'the active theme', query: SHOP_CATALOG_QUERIES.theme, variables: {} },
    { label: 'a product attribute file', query: SHOP_CATALOG_QUERIES.productAttributeFile, variables: { productId: 1, attributeId: 1 } },
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
