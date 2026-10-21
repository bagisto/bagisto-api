import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_CATALOG } from '../../../../rest/endpoints/admin.catalog.endpoints';

test.describe.configure({ timeout: 60_000 });

const PRODUCT_NOT_FOUND = 'Product not found.';

async function createSimpleProduct(request: APIRequestContext): Promise<number> {
  const sku = `e2e-cgp-${Date.now()}-${Math.floor(Math.random() * 1_000_000)}`;
  const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCTS, {
    method: 'POST',
    data: { sku, type: 'simple', attribute_family_id: 1 },
  });
  expect(resp.status()).toBe(201);
  const body = await resp.json();
  expect(body.sku).toBe(sku);

  return body.id;
}

async function listPrices(request: APIRequestContext, productId: number): Promise<any[]> {
  const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT_CUSTOMER_GROUP_PRICES(productId));
  expect(resp.status()).toBe(200);

  return (await resp.json()).data;
}

test.describe('admin catalog product customer-group prices rest api', () => {
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

  test('list cgp on fresh product returns 200 (empty)', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT_CUSTOMER_GROUP_PRICES(productId));
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.data).toEqual([]);
    expect(body.meta.total).toBe(0);
  });

  test('list cgp on non-existent product returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT_CUSTOMER_GROUP_PRICES(99999999));
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe(PRODUCT_NOT_FOUND);
  });

  test('create cgp happy path + update + delete', async ({ request }) => {
    const createResp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT_CUSTOMER_GROUP_PRICES(productId), {
      method: 'POST',
      data: {
        qty: 5,
        value_type: 'fixed',
        value: 9.99,
        customer_group_id: null,
      },
    });
    expect(createResp.status()).toBe(201);
    const created = await createResp.json();
    expect(typeof created.id).toBe('number');
    expect(created.productId).toBe(productId);
    expect(created.qty).toBe(5);
    expect(created.valueType).toBe('fixed');
    expect(created.value).toBe(9.99);
    expect(created.customerGroupId).toBeNull();

    expect((await listPrices(request, productId)).map((row) => row.id)).toEqual([created.id]);

    const upd = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT_CUSTOMER_GROUP_PRICE(productId, created.id), {
      method: 'PUT',
      data: { qty: 10, value: 7.5 },
    });
    expect(upd.status()).toBe(200);
    const updated = await upd.json();
    expect(updated.id).toBe(created.id);
    expect(updated.qty).toBe(10);
    expect(updated.value).toBe(7.5);
    expect(updated.valueType).toBe('fixed');

    const del = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT_CUSTOMER_GROUP_PRICE(productId, created.id), {
      method: 'DELETE',
    });
    expect(del.status()).toBe(200);
    expect((await del.json()).message).toBe('Customer-group price deleted successfully.');

    expect(await listPrices(request, productId)).toEqual([]);
  });

  test('create cgp with invalid value_type is rejected', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT_CUSTOMER_GROUP_PRICES(productId), {
      method: 'POST',
      data: { qty: 5, value_type: 'banana', value: 1, customer_group_id: null },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The value_type field must be either "fixed" or "discount".');
    expect(await listPrices(request, productId)).toEqual([]);
  });

  test('create cgp with qty=0 is rejected', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT_CUSTOMER_GROUP_PRICES(productId), {
      method: 'POST',
      data: { qty: 0, value_type: 'fixed', value: 5, customer_group_id: null },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The qty field must be an integer greater than or equal to 1.');
    expect(await listPrices(request, productId)).toEqual([]);
  });

  test('delete cgp across products returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT_CUSTOMER_GROUP_PRICE(99999999, 99999998), {
      method: 'DELETE',
    });
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe(PRODUCT_NOT_FOUND);
  });
});
