import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { sendAdminRequest } from '../../rest/helpers/adminClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import { ADMIN_CATALOG } from '../../rest/endpoints/admin.catalog.endpoints';
import {
  UNKNOWN_ID,
  createAdminProduct,
  deleteAdminProducts,
  safeJson,
} from '../../rest/helpers/coverage/restGaps.helpers';

test.describe.configure({ timeout: 120_000 });

test.describe('product group prices rest api', () => {
  let createdIds: number[];
  let productId: number;
  let groupPriceId: number;

  test.beforeEach(async ({ request }) => {
    createdIds = [];

    productId = (await createAdminProduct(request, 'simple')).id;
    createdIds.push(productId);

    const response = await sendAdminRequest(request, ADMIN_CATALOG.PRODUCT_CUSTOMER_GROUP_PRICES(productId), {
      method: 'POST',
      data: { qty: 5, value_type: 'fixed', value: 9.99, customer_group_id: null },
    });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(typeof body?.id, JSON.stringify(body)).toBe('number');

    groupPriceId = body.id;
  });

  test.afterEach(async ({ request }) => {
    await deleteAdminProducts(request, createdIds);
  });

  test('should return the customer group prices of a product', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_GROUP_PRICES(productId));
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(Array.isArray(body), JSON.stringify(body)).toBe(true);
    expect(body).toHaveLength(1);
    expect(body[0].id).toBe(groupPriceId);
    expect(Number(body[0].qty)).toBe(5);
    expect(body[0].valueType).toBe('fixed');
    expect(Number(body[0].value)).toBe(9.99);
    expect(body[0].customerGroupId).toBeNull();
  });
});

test.describe('product group prices of a missing product rest api', () => {
  test('should return an empty list for a product that does not exist', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_GROUP_PRICES(UNKNOWN_ID));
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body).toEqual([]);
  });
});
