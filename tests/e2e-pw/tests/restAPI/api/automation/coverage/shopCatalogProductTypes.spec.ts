import { test, expect, APIRequestContext, APIResponse } from '@playwright/test';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import { sendAdminRequest } from '../../../rest/helpers/adminClient';
import { SHOP_CATALOG } from '../../../rest/endpoints/coverage/shopCatalog.endpoints';
import { env } from '../../../config/env';

test.describe.configure({ timeout: 120_000 });

const MISSING_KEY_MESSAGE = 'X-STOREFRONT-KEY header is required';

const UNKNOWN_ID = 999999999;

async function safeJson(response: APIResponse): Promise<any> {
  try {
    return await response.json();
  } catch {
    return null;
  }
}

function uniqueSku(prefix: string): string {
  return `${prefix}-${Date.now()}-${Math.floor(Math.random() * 1e6)}`;
}

function numericIdOf(value: any): number {
  if (typeof value === 'number') {
    return value;
  }

  if (value && typeof value === 'object') {
    return numericIdOf(value._id ?? value.id);
  }

  return Number(String(value ?? '').split('/').pop());
}

async function createProduct(request: APIRequestContext, type: string): Promise<number> {
  const response = await sendAdminRequest(request, SHOP_CATALOG.ADMIN_PRODUCTS, {
    method: 'POST',
    data: { sku: uniqueSku(`e2e-${type}`), type, attribute_family_id: 1 },
  });
  expect(response.status()).toBe(201);

  const body = await safeJson(response);
  expect(typeof body?.id).toBe('number');

  return body.id;
}

async function deleteProducts(request: APIRequestContext, ids: number[]): Promise<void> {
  for (const id of ids) {
    await sendAdminRequest(request, SHOP_CATALOG.ADMIN_PRODUCT(id), { method: 'DELETE' });
  }
}

async function updateProduct(request: APIRequestContext, id: number, data: Record<string, any>): Promise<any> {
  const response = await sendAdminRequest(request, SHOP_CATALOG.ADMIN_PRODUCT(id), {
    method: 'PUT',
    data,
  });
  expect(response.status()).toBe(200);

  return safeJson(response);
}

async function allBundleOptionTranslations(request: APIRequestContext): Promise<any[]> {
  const rows: any[] = [];

  for (let page = 1; page <= 200; page++) {
    const response = await sendRestRequest(request, SHOP_CATALOG.BUNDLE_OPTION_TRANSLATIONS, {
      params: { page: String(page), per_page: '50' },
    });
    expect(response.status()).toBe(200);

    const body = await safeJson(response);
    expect(Array.isArray(body)).toBe(true);
    rows.push(...body);

    if (body.length < 50) {
      break;
    }
  }

  return rows;
}

async function getWithoutStorefrontKey(request: APIRequestContext, endpoint: string): Promise<APIResponse> {
  return request.get(`${env.baseUrl}${endpoint}`, {
    headers: { Accept: 'application/json' },
  });
}

test.describe('shop product bundle option translations rest api', () => {
  let createdIds: number[] = [];
  let optionId = 0;
  let label = '';

  test.beforeEach(async ({ request }) => {
    createdIds = [];
    label = `E2E Bundle Option ${Date.now()}-${Math.floor(Math.random() * 1e6)}`;

    const childId = await createProduct(request, 'simple');
    createdIds.push(childId);

    const bundleId = await createProduct(request, 'bundle');
    createdIds.push(bundleId);

    const updated = await updateProduct(request, bundleId, {
      bundle_options: {
        option_0: {
          en: { label },
          type: 'select',
          is_required: 1,
          sort_order: 2,
          products: {
            product_0: { product_id: childId, qty: 2, is_default: 1, sort_order: 1 },
          },
        },
      },
    });

    expect(Array.isArray(updated?.bundleOptions)).toBe(true);
    expect(updated.bundleOptions).toHaveLength(1);
    expect(updated.bundleOptions[0].label).toBe(label);
    optionId = updated.bundleOptions[0].id;
  });

  test.afterEach(async ({ request }) => {
    await deleteProducts(request, [...createdIds].reverse());
  });

  test('should list the translation of a newly created bundle option', async ({ request }) => {
    const rows = (await allBundleOptionTranslations(request)).filter(
      (row: any) => Number(row.productBundleOptionId) === optionId
    );

    expect(rows).toHaveLength(1);
    expect(rows[0].label).toBe(label);
    expect(rows[0].locale).toBe('en');
    expect(Number.isInteger(numericIdOf(rows[0].id))).toBe(true);
  });

  test('should cap the bundle option translation listing at the requested page size', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_CATALOG.BUNDLE_OPTION_TRANSLATIONS, {
      params: { per_page: '1' },
    });
    expect(response.status()).toBe(200);

    const body = await safeJson(response);
    expect(Array.isArray(body)).toBe(true);
    expect(body).toHaveLength(1);
    expect(typeof body[0].label).toBe('string');
  });

  test('should return a single bundle option translation by id', async ({ request }) => {
    const listed = (await allBundleOptionTranslations(request)).find(
      (row: any) => Number(row.productBundleOptionId) === optionId
    );
    expect(listed, 'translation of the created option is listed').toBeTruthy();
    const translationId = numericIdOf(listed.id);

    const response = await sendRestRequest(request, SHOP_CATALOG.BUNDLE_OPTION_TRANSLATION(translationId));
    expect(response.status()).toBe(200);

    const body = await safeJson(response);
    expect(numericIdOf(body.id)).toBe(translationId);
    expect(body.label).toBe(label);
    expect(body.locale).toBe('en');
    expect(Number(body.productBundleOptionId)).toBe(optionId);
  });

  test('should return 404 for a bundle option translation that does not exist', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_CATALOG.BUNDLE_OPTION_TRANSLATION(UNKNOWN_ID));
    expect(response.status()).toBe(404);

    const body = await safeJson(response);
    expect(body?.label).toBeUndefined();
  });
});

test.describe('shop product grouped products rest api', () => {
  let createdIds: number[] = [];
  let groupedId = 0;
  let childId = 0;
  let rowId = 0;

  test.beforeEach(async ({ request }) => {
    createdIds = [];

    childId = await createProduct(request, 'simple');
    createdIds.push(childId);

    groupedId = await createProduct(request, 'grouped');
    createdIds.push(groupedId);

    const updated = await updateProduct(request, groupedId, {
      links: {
        link_0: { associated_product_id: childId, qty: 3, sort_order: 1 },
      },
    });

    expect(Array.isArray(updated?.linkedProducts)).toBe(true);
    expect(updated.linkedProducts).toHaveLength(1);
    expect(updated.linkedProducts[0].associatedProductId).toBe(childId);
    rowId = updated.linkedProducts[0].id;
  });

  test.afterEach(async ({ request }) => {
    await deleteProducts(request, [...createdIds].reverse());
  });

  test('should return a grouped product member by id', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_CATALOG.GROUPED_PRODUCT(rowId));
    expect(response.status()).toBe(200);

    const body = await safeJson(response);
    expect(numericIdOf(body.id)).toBe(rowId);
    expect(body.qty).toBe(3);
    expect(body.sortOrder).toBe(1);
    expect(numericIdOf(body.product)).toBe(groupedId);
    expect(numericIdOf(body.associatedProduct)).toBe(childId);
  });

  test('should return 404 for a grouped product member that does not exist', async ({ request }) => {
    const response = await sendRestRequest(request, SHOP_CATALOG.GROUPED_PRODUCT(UNKNOWN_ID));
    expect(response.status()).toBe(404);

    const body = await safeJson(response);
    expect(body?.qty).toBeUndefined();
  });

  test('should return 404 for a grouped product member once its link is removed', async ({ request }) => {
    const updated = await updateProduct(request, groupedId, { links: {} });
    expect(updated.linkedProducts).toEqual([]);

    const response = await sendRestRequest(request, SHOP_CATALOG.GROUPED_PRODUCT(rowId));
    expect(response.status()).toBe(404);
  });
});

test.describe('shop catalog product types rest api without a storefront key', () => {
  const endpoints = [
    { label: 'the bundle option translation listing', endpoint: SHOP_CATALOG.BUNDLE_OPTION_TRANSLATIONS },
    { label: 'a single bundle option translation', endpoint: SHOP_CATALOG.BUNDLE_OPTION_TRANSLATION(1) },
    { label: 'a single grouped product member', endpoint: SHOP_CATALOG.GROUPED_PRODUCT(1) },
  ];

  for (const { label, endpoint } of endpoints) {
    test(`should refuse ${label} without a storefront key`, async ({ request }) => {
      const response = await getWithoutStorefrontKey(request, endpoint);
      expect(response.status()).toBe(401);

      const body = await safeJson(response);
      expect(body.message).toBe(MISSING_KEY_MESSAGE);
      expect(body.error).toBe('missing_key');
    });
  }
});
