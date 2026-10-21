import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import {
  UNKNOWN_ID,
  createAdminProduct,
  deleteAdminProducts,
  safeJson,
  uniqueStamp,
  updateAdminProduct,
} from '../../rest/helpers/coverage/restGaps.helpers';

test.describe.configure({ timeout: 120_000 });

test.describe('product attribute values rest api', () => {
  let createdIds: number[];
  let productId: number;
  let productName: string;

  test.beforeEach(async ({ request }) => {
    createdIds = [];
    productName = `E2E Attribute Values ${uniqueStamp()}`;

    productId = (await createAdminProduct(request, 'simple')).id;
    createdIds.push(productId);

    await updateAdminProduct(request, productId, { name: productName, status: 1 });
  });

  test.afterEach(async ({ request }) => {
    await deleteAdminProducts(request, createdIds);
  });

  test('should return the locale and channel scoped attribute values saved on a product', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_ATTRIBUTE_VALUES(productId));
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(Array.isArray(body), JSON.stringify(body)).toBe(true);

    const nameRow = body.find((row: any) => row.textValue === productName);

    expect(nameRow, JSON.stringify(body)).toBeTruthy();
    expect(nameRow.locale).toBe('en');
    expect(nameRow).not.toHaveProperty('channel');
    expect(nameRow.product).toBe(`/api/shop/products/${productId}`);

    const statusRow = body.find((row: any) => row.channel === 'default' && row.booleanValue === true);

    expect(statusRow, JSON.stringify(body)).toBeTruthy();
    expect(statusRow).not.toHaveProperty('locale');
    expect(statusRow.value).toBe(true);
    expect(statusRow.product).toBe(`/api/shop/products/${productId}`);
  });

  test('should return an empty list for a product that does not exist', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_ATTRIBUTE_VALUES(UNKNOWN_ID));
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body).toEqual([]);
  });
});
