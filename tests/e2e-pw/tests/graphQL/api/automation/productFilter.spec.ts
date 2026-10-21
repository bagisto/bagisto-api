import { test, expect, APIRequestContext } from '@playwright/test';
import { sendGraphQLRequest } from '../../graphql/helpers/graphqlClient';
import { graphQLErrorMessages } from '../../graphql/helpers/testSupport';
import { findSaleableSimpleProducts } from '../../../restAPI/rest/helpers/coverage/shopCart.helpers';

test.describe.configure({ timeout: 120_000 });

const FILTERED_PRODUCTS = `
  query filteredProducts($filter: String, $first: Int, $after: String, $sortKey: String, $reverse: Boolean) {
    products(filter: $filter, first: $first, after: $after, sortKey: $sortKey, reverse: $reverse) {
      edges {
        node {
          _id
          sku
          name
          type
          price
          new
          featured
        }
      }
      pageInfo {
        hasNextPage
        hasPreviousPage
        startCursor
        endCursor
      }
      totalCount
    }
  }
`;

const PRODUCT_SUBJECT = `
  query productSubject($id: ID!) {
    product(id: $id) {
      _id
      sku
      name
      type
      categories(first: 50) {
        edges {
          node {
            _id
          }
        }
      }
    }
  }
`;

interface ProductSubject {
  id: number;
  sku: string;
  name: string;
  categoryIds: number[];
}

async function filterProducts(
  request: APIRequestContext,
  variables: Record<string, any>
): Promise<any> {
  const response = await sendGraphQLRequest(request, FILTERED_PRODUCTS, variables);

  expect(response.status()).toBe(200);

  const body = await response.json();

  expect(body.errors, graphQLErrorMessages(body).join(' | ')).toBeUndefined();
  expect(Array.isArray(body.data.products.edges)).toBe(true);
  expect(typeof body.data.products.totalCount).toBe('number');

  return body.data.products;
}

function productIds(connection: any): number[] {
  return connection.edges.map((edge: any) => Number(edge.node._id));
}

function expectNoProducts(connection: any): void {
  expect(connection.edges).toEqual([]);
  expect(connection.totalCount).toBe(0);
}

async function productSubject(request: APIRequestContext): Promise<ProductSubject> {
  const [productId] = await findSaleableSimpleProducts(request, 1);
  const response = await sendGraphQLRequest(request, PRODUCT_SUBJECT, { id: String(productId) });

  expect(response.status()).toBe(200);

  const body = await response.json();

  expect(body.errors, graphQLErrorMessages(body).join(' | ')).toBeUndefined();

  const product = body.data.product;

  expect(product._id).toBe(productId);
  expect(product.type).toBe('simple');

  return {
    id: productId,
    sku: product.sku,
    name: product.name,
    categoryIds: product.categories.edges.map((edge: any) => Number(edge.node._id)),
  };
}

test.describe('search products by category id with pagination', () => {
  test('should return only the products of the category', async ({ request }) => {
    const subject = await productSubject(request);

    test.skip(subject.categoryIds.length === 0, `product ${subject.id} is not assigned to a category`);

    const filter = JSON.stringify({ category_id: String(subject.categoryIds[0]), sku: subject.sku });
    const connection = await filterProducts(request, { filter, first: 10 });

    expect(productIds(connection)).toEqual([subject.id]);
    expect(connection.totalCount).toBe(1);
  });

  test('should page through a category with the first and after arguments', async ({ request }) => {
    const subject = await productSubject(request);

    test.skip(subject.categoryIds.length === 0, `product ${subject.id} is not assigned to a category`);

    const filter = JSON.stringify({ category_id: String(subject.categoryIds[0]) });
    const firstPage = await filterProducts(request, { filter, first: 1 });

    expect(firstPage.edges.length).toBe(1);
    expect(firstPage.totalCount).toBeGreaterThanOrEqual(1);
    expect(firstPage.pageInfo.hasPreviousPage).toBe(false);
    expect(firstPage.pageInfo.hasNextPage).toBe(firstPage.totalCount > 1);

    test.skip(firstPage.totalCount < 2, `category ${subject.categoryIds[0]} holds a single product`);

    const secondPage = await filterProducts(request, { filter, first: 1, after: firstPage.pageInfo.endCursor });

    expect(secondPage.edges.length).toBe(1);
    expect(secondPage.totalCount).toBe(firstPage.totalCount);
    expect(productIds(secondPage)).not.toEqual(productIds(firstPage));
  });

  test('should return no products for a category that does not exist', async ({ request }) => {
    expectNoProducts(await filterProducts(request, { filter: JSON.stringify({ category_id: '99999999' }), first: 10 }));
  });

  test('should return no products for a non numeric category id', async ({ request }) => {
    expectNoProducts(await filterProducts(request, { filter: JSON.stringify({ category_id: 'invalid' }), first: 10 }));
  });

  test('should return the unfiltered catalog when the filter is missing', async ({ request }) => {
    const subject = await productSubject(request);
    const connection = await filterProducts(request, { first: 10 });

    expect(connection.totalCount).toBeGreaterThan(0);
    expect(connection.edges.length).toBe(Math.min(10, connection.totalCount));

    const bySku = await filterProducts(request, { filter: JSON.stringify({ sku: subject.sku }) });
    expect(productIds(bySku)).toEqual([subject.id]);
  });
});

test.describe('filter products by type', () => {
  test('should return a simple product under the simple type filter', async ({ request }) => {
    const subject = await productSubject(request);

    const connection = await filterProducts(request, { filter: JSON.stringify({ type: 'simple', sku: subject.sku }) });

    expect(productIds(connection)).toEqual([subject.id]);
  });

  test('should exclude a simple product from the configurable type filter', async ({ request }) => {
    const subject = await productSubject(request);

    expectNoProducts(await filterProducts(request, { filter: JSON.stringify({ type: 'configurable', sku: subject.sku }) }));
  });

  test('should only return products of the requested type', async ({ request }) => {
    for (const type of ['simple', 'configurable']) {
      const connection = await filterProducts(request, { filter: JSON.stringify({ type }), first: 50 });

      for (const edge of connection.edges) {
        expect(edge.node.type).toBe(type);
      }
    }
  });

  test('should return no products for an unknown product type', async ({ request }) => {
    expectNoProducts(await filterProducts(request, { filter: JSON.stringify({ type: 'invalid_type' }) }));
  });

  test('should treat an empty filter object as no filter', async ({ request }) => {
    const unfiltered = await filterProducts(request, { sortKey: 'ID', first: 5 });
    const emptyFilter = await filterProducts(request, { filter: JSON.stringify({}), sortKey: 'ID', first: 5 });

    expect(unfiltered.totalCount).toBeGreaterThan(0);
    expect(productIds(emptyFilter)).toEqual(productIds(unfiltered));
  });
});

test.describe('filter products by attribute', () => {
  test('should match a product by its exact name attribute', async ({ request }) => {
    const subject = await productSubject(request);

    const connection = await filterProducts(request, { filter: JSON.stringify({ name: subject.name, sku: subject.sku }) });

    expect(productIds(connection)).toEqual([subject.id]);
  });

  test('should not match a product by a name it does not carry', async ({ request }) => {
    const subject = await productSubject(request);

    expectNoProducts(
      await filterProducts(request, { filter: JSON.stringify({ name: `${subject.name} not-a-match`, sku: subject.sku }) })
    );
  });

  test('should return no products for a color option that does not exist', async ({ request }) => {
    expectNoProducts(await filterProducts(request, { filter: JSON.stringify({ color: '99999999' }) }));
  });

  test('should return no products for a non numeric color option', async ({ request }) => {
    expectNoProducts(await filterProducts(request, { filter: JSON.stringify({ color: 'invalid' }) }));
  });
});

test.describe('filter products by multiple attributes', () => {
  test('should require every attribute of the filter to match', async ({ request }) => {
    const subject = await productSubject(request);

    const matching = await filterProducts(request, { filter: JSON.stringify({ name: subject.name, sku: subject.sku }), first: 10 });
    expect(productIds(matching)).toEqual([subject.id]);

    expectNoProducts(
      await filterProducts(request, { filter: JSON.stringify({ name: subject.name, sku: subject.sku, color: '99999999' }), first: 10 })
    );
  });

  test('should limit the multi attribute results to the first argument', async ({ request }) => {
    const connection = await filterProducts(request, { filter: JSON.stringify({ type: 'simple' }), first: 3 });

    expect(connection.edges.length).toBe(Math.min(3, connection.totalCount));
  });

  test('should return no products for a combination that does not exist', async ({ request }) => {
    expectNoProducts(
      await filterProducts(request, { filter: JSON.stringify({ color: '99999999', size: '99999999', brand: '99999999' }), first: 10 })
    );
  });
});

test.describe('filter products by storefront flags', () => {
  test('should return only new products for the new filter', async ({ request }) => {
    const connection = await filterProducts(request, {
      filter: JSON.stringify({ new: '1' }),
      sortKey: 'CREATED_AT',
      reverse: true,
      first: 10,
    });

    expect(connection.edges.length).toBe(Math.min(10, connection.totalCount));

    for (const edge of connection.edges) {
      expect(['1', 'true']).toContain(String(edge.node.new));
    }
  });

  test('should return only featured products for the featured filter', async ({ request }) => {
    const connection = await filterProducts(request, {
      filter: JSON.stringify({ featured: '1' }),
      sortKey: 'CREATED_AT',
      reverse: true,
      first: 12,
    });

    expect(connection.edges.length).toBe(Math.min(12, connection.totalCount));

    for (const edge of connection.edges) {
      expect(['1', 'true']).toContain(String(edge.node.featured));
    }
  });

  test('should run the docs brand filter query', async ({ request }) => {
    const connection = await filterProducts(request, {
      filter: JSON.stringify({ brand: '25' }),
      sortKey: 'CREATED_AT',
      reverse: true,
      first: 12,
    });

    expect(connection.edges.length).toBe(Math.min(12, connection.totalCount));
  });
});
