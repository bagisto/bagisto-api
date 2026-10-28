import { test, expect, APIRequestContext } from '@playwright/test';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import {
  BINARY_HEADERS,
  REST_GAPS,
  REST_GAPS_MESSAGES as MSG,
  UNKNOWN_ID,
  adminConfigValue,
  collectPages,
  createAdminProduct,
  deleteAdminProducts,
  expectDetail,
  expectMissingStorefrontKey,
  getWithoutStorefrontKey,
  numericIdOf,
  safeJson,
  uniqueStamp,
  updateAdminProduct,
} from '../../../rest/helpers/coverage/restGaps.helpers';

test.describe.configure({ timeout: 120_000 });

const SKU_ATTRIBUTE_ID = 1;

async function getJson(request: APIRequestContext, endpoint: string, params?: Record<string, string>): Promise<any> {
  const response = await sendRestRequest(request, endpoint, params ? { params } : {});
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);

  return body;
}

test.describe('shop country states rest api', () => {
  test('should page through every country state with its country reference', async ({ request }) => {
    const first = await getJson(request, REST_GAPS.COUNTRY_STATES, { page: '1', per_page: '5' });
    const second = await getJson(request, REST_GAPS.COUNTRY_STATES, { page: '2', per_page: '5' });

    expect(Array.isArray(first)).toBe(true);
    expect(first).toHaveLength(5);
    expect(Array.isArray(second)).toBe(true);
    expect(second.length).toBeGreaterThan(0);

    for (const state of [...first, ...second]) {
      expect(typeof state.id).toBe('number');
      expect(typeof state.code).toBe('string');
      expect(typeof state.countryCode).toBe('string');
      expect(typeof state.countryId).toBe('number');
      expect(typeof state.defaultName).toBe('string');
    }

    const firstIds = first.map((state: any) => state.id);
    for (const state of second) {
      expect(firstIds).not.toContain(state.id);
    }
  });

  test('should return a state of its own country by id', async ({ request }) => {
    const [state] = await getJson(request, REST_GAPS.COUNTRY_STATES, { page: '1', per_page: '1' });

    const body = await getJson(request, REST_GAPS.COUNTRY_STATE_OF(state.countryId, state.id));

    expect(body.id).toBe(state.id);
    expect(body.code).toBe(state.code);
    expect(body.countryId).toBe(state.countryId);
    expect(body.countryCode).toBe(state.countryCode);
    expect(body.defaultName).toBe(state.defaultName);
  });

  test('should return 404 for a state that does not exist in the country', async ({ request }) => {
    const [state] = await getJson(request, REST_GAPS.COUNTRY_STATES, { page: '1', per_page: '1' });

    await expectDetail(await sendRestRequest(request, REST_GAPS.COUNTRY_STATE_OF(state.countryId, UNKNOWN_ID)), 404, MSG.GENERIC_NOT_FOUND);
  });

  test('should return 404 for a state requested under a different country', async ({ request }) => {
    const [state] = await getJson(request, REST_GAPS.COUNTRY_STATES, { page: '1', per_page: '1' });
    const countries = await getJson(request, REST_GAPS.COUNTRIES);
    const other = countries.find((country: any) => country.id !== state.countryId);

    expect(other, 'a second country exists').toBeTruthy();

    await expectDetail(await sendRestRequest(request, REST_GAPS.COUNTRY_STATE_OF(other.id, state.id)), 404, MSG.GENERIC_NOT_FOUND);
  });

  test('should refuse the country state listing without a storefront key', async ({ request }) => {
    await expectMissingStorefrontKey(await getWithoutStorefrontKey(request, REST_GAPS.COUNTRY_STATES));
  });

  test('should refuse a country state without a storefront key', async ({ request }) => {
    await expectMissingStorefrontKey(await getWithoutStorefrontKey(request, REST_GAPS.COUNTRY_STATE_OF(1, 1)));
  });
});

test.describe('shop product customizable option prices and translations rest api', () => {
  let createdIds: number[];
  let optionId: number;
  let optionLabel: string;
  let price: any;

  test.beforeEach(async ({ request }) => {
    createdIds = [];
    optionLabel = `E2E Gift Wrap ${uniqueStamp()}`;

    const productId = (await createAdminProduct(request, 'simple')).id;
    createdIds.push(productId);

    const updated = await updateAdminProduct(request, productId, {
      customizable_options: {
        option_0: {
          en: { label: optionLabel },
          type: 'checkbox',
          is_required: '0',
          sort_order: '1',
          prices: {
            price_0: { label: 'Premium', price: '6.00', sort_order: '2' },
          },
        },
      },
    });

    expect(updated.customizableOptions).toHaveLength(1);
    expect(updated.customizableOptions[0].prices).toHaveLength(1);

    optionId = updated.customizableOptions[0].id;
    price = updated.customizableOptions[0].prices[0];
  });

  test.afterEach(async ({ request }) => {
    await deleteAdminProducts(request, createdIds);
  });

  test('should return a customizable option price by id', async ({ request }) => {
    const body = await getJson(request, REST_GAPS.CUSTOMIZABLE_OPTION_PRICE(price.id));

    expect(numericIdOf(body.id)).toBe(price.id);
    expect(price.label).toBe('Premium');
    expect(body.label).toBe('Premium');
    expect(body.price).toBe(Number(price.price));
    expect(body.price).toBe(6);
  });

  test('should return a customizable option translation by id', async ({ request }) => {
    const [listed] = await collectPages(
      request,
      REST_GAPS.CUSTOMIZABLE_OPTION_TRANSLATIONS,
      (row: any) => Number(row.productCustomizableOptionId) === optionId
    );

    expect(listed, 'the translation of the created option is listed').toBeTruthy();

    const translationId = numericIdOf(listed.id);
    const body = await getJson(request, REST_GAPS.CUSTOMIZABLE_OPTION_TRANSLATION(translationId));

    expect(numericIdOf(body.id)).toBe(translationId);
    expect(Number(body.productCustomizableOptionId)).toBe(optionId);
    expect(body.locale).toBe('en');
    expect(body.label).toBe(optionLabel);
  });
});

test.describe('shop product customizable option refusals rest api', () => {
  test('should return 404 for a customizable option price that does not exist', async ({ request }) => {
    await expectDetail(await sendRestRequest(request, REST_GAPS.CUSTOMIZABLE_OPTION_PRICE(UNKNOWN_ID)), 404, MSG.GENERIC_NOT_FOUND);
  });

  test('should return 404 for a customizable option translation that does not exist', async ({ request }) => {
    await expectDetail(await sendRestRequest(request, REST_GAPS.CUSTOMIZABLE_OPTION_TRANSLATION(UNKNOWN_ID)), 404, MSG.GENERIC_NOT_FOUND);
  });

  test('should refuse a customizable option price without a storefront key', async ({ request }) => {
    await expectMissingStorefrontKey(await getWithoutStorefrontKey(request, REST_GAPS.CUSTOMIZABLE_OPTION_PRICE(1)));
  });

  test('should refuse a customizable option translation without a storefront key', async ({ request }) => {
    await expectMissingStorefrontKey(await getWithoutStorefrontKey(request, REST_GAPS.CUSTOMIZABLE_OPTION_TRANSLATION(1)));
  });
});

test.describe('shop product bundle option products rest api', () => {
  let createdIds: number[];
  let bundleId: number;
  let childId: number;
  let optionId: number;
  let optionProductId: number;

  test.beforeEach(async ({ request }) => {
    createdIds = [];

    childId = (await createAdminProduct(request, 'simple')).id;
    createdIds.push(childId);

    bundleId = (await createAdminProduct(request, 'bundle')).id;
    createdIds.push(bundleId);

    const updated = await updateAdminProduct(request, bundleId, {
      bundle_options: {
        option_0: {
          en: { label: `E2E Bundle Option ${uniqueStamp()}` },
          type: 'select',
          is_required: 1,
          sort_order: 1,
          products: {
            product_0: { product_id: childId, qty: 2, is_default: 1, sort_order: 3 },
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
    await deleteAdminProducts(request, createdIds);
  });

  test('should return a bundle option product by id', async ({ request }) => {
    const body = await getJson(request, REST_GAPS.BUNDLE_OPTION_PRODUCT(optionProductId));

    expect(numericIdOf(body.id)).toBe(optionProductId);
    expect(body.qty).toBe(2);
    expect(body.isDefault).toBe(true);
    expect(body.sortOrder).toBe(3);
    expect(numericIdOf(body.product)).toBe(childId);
    expect(numericIdOf(body.bundleOption)).toBe(optionId);
  });

  test('should return 404 for a bundle option product once its option is removed', async ({ request }) => {
    const updated = await updateAdminProduct(request, bundleId, { bundle_options: {} });
    expect(updated.bundleOptions).toEqual([]);

    await expectDetail(await sendRestRequest(request, REST_GAPS.BUNDLE_OPTION_PRODUCT(optionProductId)), 404, MSG.GENERIC_NOT_FOUND);
  });
});

test.describe('shop product bundle option product refusals rest api', () => {
  test('should return 404 for a bundle option product that does not exist', async ({ request }) => {
    await expectDetail(await sendRestRequest(request, REST_GAPS.BUNDLE_OPTION_PRODUCT(UNKNOWN_ID)), 404, MSG.GENERIC_NOT_FOUND);
  });

  test('should refuse a bundle option product without a storefront key', async ({ request }) => {
    await expectMissingStorefrontKey(await getWithoutStorefrontKey(request, REST_GAPS.BUNDLE_OPTION_PRODUCT(1)));
  });
});

test.describe('shop product attribute files rest api', () => {
  let createdIds: number[];
  let productId: number;

  test.beforeEach(async ({ request }) => {
    createdIds = [];
    productId = (await createAdminProduct(request, 'simple')).id;
    createdIds.push(productId);
  });

  test.afterEach(async ({ request }) => {
    await deleteAdminProducts(request, createdIds);
  });

  test('should refuse an attribute that is not a file or image attribute', async ({ request }) => {
    const response = await sendRestRequest(request, REST_GAPS.PRODUCT_ATTRIBUTE_FILE(productId, SKU_ATTRIBUTE_ID), { headers: BINARY_HEADERS });

    expect(response.headers()['content-disposition']).toBeUndefined();
    await expectDetail(response, 404, MSG.SHOP_PRODUCT_NOT_FOUND);
  });

  test('should report a product that does not exist as not found', async ({ request }) => {
    const response = await sendRestRequest(request, REST_GAPS.PRODUCT_ATTRIBUTE_FILE(UNKNOWN_ID, SKU_ATTRIBUTE_ID), { headers: BINARY_HEADERS });

    expect(response.headers()['content-disposition']).toBeUndefined();
    await expectDetail(response, 404, MSG.SHOP_PRODUCT_NOT_FOUND);
  });

  test('should refuse a product attribute file without a storefront key', async ({ request }) => {
    await expectMissingStorefrontKey(await getWithoutStorefrontKey(request, REST_GAPS.PRODUCT_ATTRIBUTE_FILE(productId, SKU_ATTRIBUTE_ID)));
  });
});

test.describe('shop product review translation rest api', () => {
  test('should refuse to translate a review while review translation is disabled', async ({ request }) => {
    const magicAi = await adminConfigValue(request, 'magic_ai.general.settings', 'magic_ai.general.settings.enabled');
    const reviewTranslation = await adminConfigValue(
      request,
      'magic_ai.storefront_features.review_translation',
      'magic_ai.storefront_features.review_translation.enabled'
    );

    test.skip(
      magicAi === '1' && reviewTranslation === '1',
      'magic ai review translation is enabled on this store, so the disabled gate cannot be observed'
    );

    const response = await sendRestRequest(request, REST_GAPS.REVIEW_TRANSLATION(UNKNOWN_ID, UNKNOWN_ID));
    const body = await expectDetail(response, 403, MSG.REVIEW_TRANSLATION_FORBIDDEN);

    expect(body?.content).toBeUndefined();
  });

  test('should refuse a review translation without a storefront key', async ({ request }) => {
    await expectMissingStorefrontKey(await getWithoutStorefrontKey(request, REST_GAPS.REVIEW_TRANSLATION(1, 1)));
  });
});
