import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import { graphQLErrorMessages } from '../../../../graphql/helpers/testSupport';
import {
  ADMIN_PRODUCT_CREATE_MUTATION,
  ADMIN_PRODUCT_DELETE_MUTATION,
} from '../../../../graphql/Queries/admin/catalog/products.queries';

test.describe.configure({ timeout: 60_000 });

const MESSAGES = {
  PRODUCT_NOT_FOUND: 'Product not found.',
  INVENTORIES_REQUIRED: 'The inventories field is required and must be a non-empty map of inventory_source_id to quantity.',
  SOURCE_NOT_FOUND: (id: number) => `Inventory source ${id} does not exist.`,
  QTY_INVALID: 'Each quantity must be a non-negative integer.',
};

const LIST_INVENTORIES = `
  query adminCatalogProductInventories($productId: Int!) {
    adminCatalogProductInventories(productId: $productId) {
      edges {
        node {
          _id
          sourceId
          sourceCode
          qty
        }
      }
    }
  }
`;

const UPDATE_INVENTORIES = `
  mutation updateAdminCatalogProductInventory($id: ID!, $productId: Int!, $inventories: Iterable!) {
    updateAdminCatalogProductInventory(input: { id: $id, productId: $productId, inventories: $inventories }) {
      adminCatalogProductInventory {
        _id
        sourceId
        sourceCode
        qty
      }
    }
  }
`;

const INVENTORY_SOURCES = `
  query adminSettingsInventorySources($first: Int) {
    adminSettingsInventorySources(first: $first) {
      edges {
        node {
          _id
          code
        }
      }
    }
  }
`;

interface InventorySource {
  id: number;
  code: string;
}

let productId: number;

async function runAdmin(request: APIRequestContext, query: string, variables: Record<string, any> = {}): Promise<any> {
  const response = await sendAdminGraphQLRequest(request, query, variables);

  expect(response.status()).toBe(200);

  return response.json();
}

function expectNoErrors(body: any): void {
  expect(body.errors, graphQLErrorMessages(body).join(' | ')).toBeUndefined();
}

function inventoriesIri(id: number): string {
  return `/api/admin/catalog/products/${id}/inventories`;
}

async function firstInventorySource(request: APIRequestContext): Promise<InventorySource> {
  const body = await runAdmin(request, INVENTORY_SOURCES, { first: 50 });
  expectNoErrors(body);

  const sources = body.data.adminSettingsInventorySources.edges
    .map((edge: any) => ({ id: Number(edge.node._id), code: edge.node.code }))
    .sort((left: InventorySource, right: InventorySource) => left.id - right.id);

  expect(sources.length, 'no inventory source exists').toBeGreaterThan(0);

  return sources[0];
}

async function listInventories(request: APIRequestContext, id: number): Promise<any[]> {
  const body = await runAdmin(request, LIST_INVENTORIES, { productId: id });
  expectNoErrors(body);

  return body.data.adminCatalogProductInventories.edges.map((edge: any) => edge.node);
}

async function updateInventories(request: APIRequestContext, inventories: Record<string, any>): Promise<any> {
  return runAdmin(request, UPDATE_INVENTORIES, { id: inventoriesIri(productId), productId, inventories });
}

test.beforeEach(async ({ request }) => {
  const body = await runAdmin(request, ADMIN_PRODUCT_CREATE_MUTATION, {
    sku: `e2e-inv-${Date.now()}-${Math.floor(Math.random() * 100000)}`,
    attributeFamilyId: 1,
    type: 'simple',
  });
  expectNoErrors(body);

  productId = Number(body.data.createAdminCatalogProduct.adminCatalogProduct._id);
  expect(productId).toBeGreaterThan(0);
});

test.afterEach(async ({ request }) => {
  await sendAdminGraphQLRequest(request, ADMIN_PRODUCT_DELETE_MUTATION, { id: `/api/admin/catalog/products/${productId}` });
});

test.describe('admin catalog product inventory graphql', () => {
  test('should save an inventory quantity for the product and list it', async ({ request }) => {
    const source = await firstInventorySource(request);

    const body = await updateInventories(request, { [String(source.id)]: 7 });
    expectNoErrors(body);

    const row = body.data.updateAdminCatalogProductInventory.adminCatalogProductInventory;

    expect(Number(row._id)).toBeGreaterThan(0);
    expect(row.sourceId).toBe(source.id);
    expect(row.sourceCode).toBe(source.code);
    expect(row.qty).toBe(7);

    const listed = await listInventories(request, productId);

    expect(listed.filter((node: any) => node.sourceId === source.id)).toEqual([
      expect.objectContaining({ _id: Number(row._id), sourceId: source.id, sourceCode: source.code, qty: 7 }),
    ]);
  });

  test('should overwrite an inventory quantity already saved', async ({ request }) => {
    const source = await firstInventorySource(request);

    expectNoErrors(await updateInventories(request, { [String(source.id)]: 7 }));

    const body = await updateInventories(request, { [String(source.id)]: 2 });
    expectNoErrors(body);
    expect(body.data.updateAdminCatalogProductInventory.adminCatalogProductInventory.qty).toBe(2);

    const listed = await listInventories(request, productId);

    expect(listed.filter((node: any) => node.sourceId === source.id).map((node: any) => node.qty)).toEqual([2]);
  });

  test('should report a product that does not exist when listing inventories', async ({ request }) => {
    const body = await runAdmin(request, LIST_INVENTORIES, { productId: 999999999 });

    expect(graphQLErrorMessages(body)).toEqual([MESSAGES.PRODUCT_NOT_FOUND]);
    expect(body.data?.adminCatalogProductInventories ?? null).toBeNull();
  });

  test('should reject an empty inventories map', async ({ request }) => {
    const body = await updateInventories(request, {});

    expect(graphQLErrorMessages(body)).toEqual([MESSAGES.INVENTORIES_REQUIRED]);
    expect(body.data?.updateAdminCatalogProductInventory ?? null).toBeNull();
  });

  test('should reject an inventory source that does not exist', async ({ request }) => {
    const body = await updateInventories(request, { '999999999': 1 });

    expect(graphQLErrorMessages(body)).toEqual([MESSAGES.SOURCE_NOT_FOUND(999999999)]);
    expect(await listInventories(request, productId)).toEqual([]);
  });

  test('should reject a negative inventory quantity', async ({ request }) => {
    const source = await firstInventorySource(request);

    const body = await updateInventories(request, { [String(source.id)]: -1 });

    expect(graphQLErrorMessages(body)).toEqual([MESSAGES.QTY_INVALID]);
    expect((await listInventories(request, productId)).filter((node: any) => node.sourceId === source.id)).toEqual([]);
  });
});
