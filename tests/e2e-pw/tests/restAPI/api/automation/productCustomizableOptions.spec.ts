import { test, expect } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import {
  UNKNOWN_ID,
  collectPages,
  createAdminProduct,
  deleteAdminProducts,
  numericIdOf,
  safeJson,
  uniqueStamp,
  updateAdminProduct,
} from '../../rest/helpers/coverage/restGaps.helpers';

test.describe.configure({ timeout: 120_000 });

test.describe('product customizable options rest api', () => {
  let createdIds: number[];
  let productId: number;
  let optionId: number;
  let optionLabel: string;
  let priceId: number;

  test.beforeEach(async ({ request }) => {
    createdIds = [];
    optionLabel = `E2E Engraving ${uniqueStamp()}`;

    productId = (await createAdminProduct(request, 'simple')).id;
    createdIds.push(productId);

    const updated = await updateAdminProduct(request, productId, {
      customizable_options: {
        option_0: {
          en: { label: optionLabel },
          type: 'checkbox',
          is_required: '0',
          sort_order: '1',
          prices: {
            price_0: { label: 'Gold', price: '4.00', sort_order: '1' },
          },
        },
      },
    });

    expect(updated.customizableOptions).toHaveLength(1);
    expect(updated.customizableOptions[0].prices).toHaveLength(1);

    optionId = updated.customizableOptions[0].id;
    priceId = updated.customizableOptions[0].prices[0].id;
  });

  test.afterEach(async ({ request }) => {
    await deleteAdminProducts(request, createdIds);
  });

  test('should return the customizable options of a product', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_CUSTOMIZABLE_OPTIONS(productId));
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(Array.isArray(body), JSON.stringify(body)).toBe(true);
    expect(body).toHaveLength(1);
    expect(numericIdOf(body[0].id)).toBe(optionId);
    expect(body[0].type).toBe('checkbox');
    expect(body[0].label).toBe(optionLabel);
  });

  test('should list the price of a newly created customizable option', async ({ request }) => {
    const rows = await collectPages(
      request,
      ENDPOINTS.PRODUCT_CUSTOMIZABLE_OPTION_PRICES,
      (row: any) => numericIdOf(row.id) === priceId
    );

    expect(rows).toHaveLength(1);
    expect(rows[0].label).toBe('Gold');
    expect(rows[0].price).toBe(4);
  });
});

test.describe('product customizable options of a missing product rest api', () => {
  test('should return an empty list for a product that does not exist', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.PRODUCT_CUSTOMIZABLE_OPTIONS(UNKNOWN_ID));
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body).toEqual([]);
  });
});
