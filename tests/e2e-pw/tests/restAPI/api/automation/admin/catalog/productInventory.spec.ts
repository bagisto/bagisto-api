import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_CATALOG } from '../../../../rest/endpoints/admin.catalog.endpoints';

test.describe.configure({ timeout: 60_000 });

const DEFAULT_INVENTORY_SOURCE_ID = 1;

async function createSimpleProduct(request: APIRequestContext): Promise<number> {
  const sku = `e2e-inv-${Date.now()}-${Math.floor(Math.random() * 1_000_000)}`;
  const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCTS, {
    method: 'POST',
    data: { sku, type: 'simple', attribute_family_id: 1 },
  });
  expect(resp.status()).toBe(201);
  const body = await resp.json();
  expect(body.sku).toBe(sku);

  return body.id;
}

test.describe('admin catalog product inventory rest api', () => {
  let productId: number;

  test.beforeEach(async ({ request }) => {
    productId = 0;
    productId = await createSimpleProduct(request);
  });

  test.afterEach(async ({ request }) => {
    if (productId) {
      await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT(productId), { method: 'DELETE' });
    }
  });

  test('list inventories for a fresh product returns 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT_INVENTORIES(productId));
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.data).toEqual([]);
    expect(body.meta.total).toBe(0);
    expect(body.meta.totalQty).toBe(0);
  });

  test('list inventories on non-existent product returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT_INVENTORIES(99999999));
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe('Product not found.');
  });

  test('bulk-update inventories with empty body is rejected', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT_INVENTORIES(productId), {
      method: 'PUT',
      data: {},
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe(
      'The inventories field is required and must be a non-empty map of inventory_source_id to quantity.'
    );
  });

  test('bulk-update inventories with valid source 1 succeeds', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT_INVENTORIES(productId), {
      method: 'PUT',
      data: { inventories: { [DEFAULT_INVENTORY_SOURCE_ID]: 25 } },
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.meta.totalQty).toBe(25);
    const row = body.data.find((entry: any) => entry.sourceId === DEFAULT_INVENTORY_SOURCE_ID);
    expect(row.qty).toBe(25);

    const reread = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT_INVENTORIES(productId));
    expect(reread.status()).toBe(200);
    const rereadBody = await reread.json();
    expect(rereadBody.meta.totalQty).toBe(25);
    expect(rereadBody.data.find((entry: any) => entry.sourceId === DEFAULT_INVENTORY_SOURCE_ID).qty).toBe(25);
  });

  test('bulk-update inventories with negative qty rejected', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT_INVENTORIES(productId), {
      method: 'PUT',
      data: { inventories: { [DEFAULT_INVENTORY_SOURCE_ID]: -5 } },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('Each quantity must be a non-negative integer.');

    const reread = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT_INVENTORIES(productId));
    expect((await reread.json()).data).toEqual([]);
  });

  test('bulk-update inventories with unknown source rejected', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT_INVENTORIES(productId), {
      method: 'PUT',
      data: { inventories: { 99999: 10 } },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('Inventory source 99999 does not exist.');

    const reread = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT_INVENTORIES(productId));
    expect((await reread.json()).data).toEqual([]);
  });
});
