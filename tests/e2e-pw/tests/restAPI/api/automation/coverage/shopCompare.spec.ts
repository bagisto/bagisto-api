import { test, expect, APIRequestContext } from '@playwright/test';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import {
  SHOP_CART_ENDPOINTS,
  SHOP_CART_MESSAGES,
  addCompareItem,
  bearer,
  expectProblem,
  listCompareItemIds,
  listProductIds,
  readJson,
  referenceId,
  registerShopCustomer,
} from '../../../rest/helpers/coverage/shopCart.helpers';

test.describe.configure({ timeout: 120_000 });

async function twoProductIds(request: APIRequestContext): Promise<[number, number]> {
  const ids = await listProductIds(request, { per_page: '5' });

  test.skip(ids.length < 2, 'the catalog needs at least two storefront products');

  return [ids[0], ids[1]];
}

test.describe('shop compare items rest api', () => {
  test('should add a product to the customer compare list', async ({ request }) => {
    const [productId] = await twoProductIds(request);
    const customer = await registerShopCustomer(request);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.COMPARE_ITEMS, {
      method: 'POST',
      data: { productId },
      headers: bearer(customer.token),
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(typeof body.id).toBe('number');
    expect(referenceId(body.product)).toBe(productId);
    expect(referenceId(body.customer)).toBe(customer.id);
  });

  test('should refuse a product that is already in the compare list', async ({ request }) => {
    const [productId] = await twoProductIds(request);
    const customer = await registerShopCustomer(request);
    await addCompareItem(request, customer, productId);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.COMPARE_ITEMS, {
      method: 'POST',
      data: { productId },
      headers: bearer(customer.token),
    });

    await expectProblem(response, 400, SHOP_CART_MESSAGES.COMPARE_ALREADY_EXISTS);
    expect(await listCompareItemIds(request, customer)).toHaveLength(1);
  });

  test('should require a product id', async ({ request }) => {
    const customer = await registerShopCustomer(request);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.COMPARE_ITEMS, {
      method: 'POST',
      data: {},
      headers: bearer(customer.token),
    });

    await expectProblem(response, 400, SHOP_CART_MESSAGES.COMPARE_PRODUCT_ID_REQUIRED);
  });

  test('should report a product that does not exist', async ({ request }) => {
    const customer = await registerShopCustomer(request);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.COMPARE_ITEMS, {
      method: 'POST',
      data: { productId: 2147480000 },
      headers: bearer(customer.token),
    });

    await expectProblem(response, 404, SHOP_CART_MESSAGES.COMPARE_PRODUCT_NOT_FOUND);
  });

  test('should refuse to add a compare item without a customer token', async ({ request }) => {
    const [productId] = await twoProductIds(request);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.COMPARE_ITEMS, {
      method: 'POST',
      data: { productId },
    });

    await expectProblem(response, 403, SHOP_CART_MESSAGES.UNAUTHENTICATED);
  });

  test('should list only the compare items of the customer', async ({ request }) => {
    const [firstProductId, secondProductId] = await twoProductIds(request);
    const customer = await registerShopCustomer(request);
    const first = await addCompareItem(request, customer, firstProductId);
    const second = await addCompareItem(request, customer, secondProductId);
    const other = await registerShopCustomer(request);
    const foreign = await addCompareItem(request, other, firstProductId);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.COMPARE_ITEMS, {
      headers: bearer(customer.token),
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body.map((item: any) => Number(item.id)).sort()).toEqual([first, second].sort());
    expect(body.map((item: any) => Number(item.id))).not.toContain(foreign);
    expect(body.map((item: any) => referenceId(item.product)).sort()).toEqual([firstProductId, secondProductId].sort());
  });

  test('should refuse to list compare items without a customer token', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.COMPARE_ITEMS);

    await expectProblem(response, 403, SHOP_CART_MESSAGES.UNAUTHENTICATED);
  });

  test('should return a single compare item of the customer', async ({ request }) => {
    const [productId] = await twoProductIds(request);
    const customer = await registerShopCustomer(request);
    const compareItemId = await addCompareItem(request, customer, productId);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.COMPARE_ITEM(compareItemId), {
      headers: bearer(customer.token),
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body.id).toBe(compareItemId);
    expect(referenceId(body.product)).toBe(productId);
    expect(referenceId(body.customer)).toBe(customer.id);
    expect(body.createdAt).toMatch(/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/);
  });

  test('should not return a compare item of another customer', async ({ request }) => {
    const [productId] = await twoProductIds(request);
    const owner = await registerShopCustomer(request);
    const compareItemId = await addCompareItem(request, owner, productId);
    const intruder = await registerShopCustomer(request);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.COMPARE_ITEM(compareItemId), {
      headers: bearer(intruder.token),
    });

    await expectProblem(response, 404, SHOP_CART_MESSAGES.COMPARE_ITEM_NOT_FOUND);
  });

  test('should refuse to read a compare item without a customer token', async ({ request }) => {
    const [productId] = await twoProductIds(request);
    const owner = await registerShopCustomer(request);
    const compareItemId = await addCompareItem(request, owner, productId);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.COMPARE_ITEM(compareItemId));

    await expectProblem(response, 403, SHOP_CART_MESSAGES.UNAUTHENTICATED);
  });

  test('should delete a compare item of the customer', async ({ request }) => {
    const [firstProductId, secondProductId] = await twoProductIds(request);
    const customer = await registerShopCustomer(request);
    const removed = await addCompareItem(request, customer, firstProductId);
    const kept = await addCompareItem(request, customer, secondProductId);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.COMPARE_ITEM(removed), {
      method: 'DELETE',
      headers: bearer(customer.token),
    });

    expect(response.status(), await response.text()).toBe(204);
    expect(await listCompareItemIds(request, customer)).toEqual([kept]);

    const lookup = await sendRestRequest(request, SHOP_CART_ENDPOINTS.COMPARE_ITEM(removed), {
      headers: bearer(customer.token),
    });

    await expectProblem(lookup, 404, SHOP_CART_MESSAGES.COMPARE_ITEM_NOT_FOUND);
  });

  test('should refuse to delete a compare item of another customer', async ({ request }) => {
    const [productId] = await twoProductIds(request);
    const owner = await registerShopCustomer(request);
    const compareItemId = await addCompareItem(request, owner, productId);
    const intruder = await registerShopCustomer(request);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.COMPARE_ITEM(compareItemId), {
      method: 'DELETE',
      headers: bearer(intruder.token),
    });

    await expectProblem(response, 403, SHOP_CART_MESSAGES.CANNOT_UPDATE_OTHER_PROFILE);
    expect(await listCompareItemIds(request, owner)).toEqual([compareItemId]);
  });

  test('should refuse to delete a compare item without a customer token', async ({ request }) => {
    const [productId] = await twoProductIds(request);
    const owner = await registerShopCustomer(request);
    const compareItemId = await addCompareItem(request, owner, productId);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.COMPARE_ITEM(compareItemId), {
      method: 'DELETE',
    });

    await expectProblem(response, 403, SHOP_CART_MESSAGES.UNAUTHENTICATED);
    expect(await listCompareItemIds(request, owner)).toEqual([compareItemId]);
  });
});

test.describe('shop delete all compare items rest api', () => {
  test('should clear the compare list of the customer only', async ({ request }) => {
    const [firstProductId, secondProductId] = await twoProductIds(request);
    const customer = await registerShopCustomer(request);
    await addCompareItem(request, customer, firstProductId);
    await addCompareItem(request, customer, secondProductId);
    const other = await registerShopCustomer(request);
    const foreign = await addCompareItem(request, other, firstProductId);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.DELETE_ALL_COMPARE_ITEMS, {
      method: 'POST',
      data: {},
      headers: bearer(customer.token),
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.deletedCount).toBe(2);
    expect(body.message).toBe(SHOP_CART_MESSAGES.COMPARE_DELETE_ALL_SUCCESS);
    expect(await listCompareItemIds(request, customer)).toEqual([]);
    expect(await listCompareItemIds(request, other)).toEqual([foreign]);
  });

  test('should report zero deleted items for an empty compare list', async ({ request }) => {
    const customer = await registerShopCustomer(request);

    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.DELETE_ALL_COMPARE_ITEMS, {
      method: 'POST',
      data: {},
      headers: bearer(customer.token),
    });
    const body = await readJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.deletedCount).toBe(0);
  });

  test('should refuse to clear the compare list without a customer token', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_CART_ENDPOINTS.DELETE_ALL_COMPARE_ITEMS, {
      method: 'POST',
      data: {},
    });

    await expectProblem(response, 403, SHOP_CART_MESSAGES.UNAUTHENTICATED);
  });
});
