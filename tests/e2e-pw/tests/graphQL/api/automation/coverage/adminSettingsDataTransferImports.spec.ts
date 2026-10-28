import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../graphql/helpers/adminGraphqlClient';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import {
  ADMIN_SETTINGS_DATA_TRANSFER_IMPORT_CREATE_MUTATION,
  ADMIN_SETTINGS_DATA_TRANSFER_IMPORT_INDEX_MUTATION,
  ADMIN_SETTINGS_DATA_TRANSFER_IMPORT_LINK_MUTATION,
  ADMIN_SETTINGS_DATA_TRANSFER_IMPORT_START_MUTATION,
  ADMIN_SETTINGS_DATA_TRANSFER_IMPORT_STATS_QUERY,
  ADMIN_SETTINGS_DATA_TRANSFER_IMPORT_VALIDATE_MUTATION,
} from '../../../graphql/Queries/coverage/adminSettings.queries';
import { env } from '../../../config/env';
import { sendAdminMultipartRequest, sendAdminRequest } from '../../../../restAPI/rest/helpers/adminClient';
import {
  ADMIN_SETTINGS_COVERAGE,
  ADMIN_SETTINGS_IMPORT_IRI,
} from '../../../../restAPI/rest/endpoints/coverage/adminSettings.endpoints';

test.describe.configure({ timeout: 180_000 });

const NOT_FOUND_MESSAGE = 'Import not found.';
const NOTHING_TO_IMPORT_MESSAGE = 'There is nothing to import.';
const UNKNOWN_ID = 999999999;

const PRODUCT_COLUMNS = [
  'sku', 'parent_sku', 'locale', 'attribute_family_code', 'type', 'categories', 'images', 'name',
  'description', 'short_description', 'status', 'visible_individually', 'new', 'featured',
  'guest_checkout', 'length', 'width', 'height', 'weight', 'tax_category_name', 'price', 'cost',
  'special_price', 'special_price_from', 'special_price_to', 'customer_group_prices', 'url_key',
  'meta_title', 'meta_keywords', 'meta_description', 'manage_stock', 'inventories', 'related_skus',
  'cross_sell_skus', 'up_sell_skus', 'configurable_variants', 'bundle_options', 'associated_skus',
  'booking_options',
];

type ImportAction = 'validate' | 'start' | 'link' | 'index';

const ACTIONS: Record<ImportAction, { mutation: string; field: string; payload: string }> = {
  validate: {
    mutation: ADMIN_SETTINGS_DATA_TRANSFER_IMPORT_VALIDATE_MUTATION,
    field: 'validateAdminSettingsDataTransferImportValidate',
    payload: 'adminSettingsDataTransferImportValidate',
  },
  start: {
    mutation: ADMIN_SETTINGS_DATA_TRANSFER_IMPORT_START_MUTATION,
    field: 'startAdminSettingsDataTransferImportStart',
    payload: 'adminSettingsDataTransferImportStart',
  },
  link: {
    mutation: ADMIN_SETTINGS_DATA_TRANSFER_IMPORT_LINK_MUTATION,
    field: 'linkAdminSettingsDataTransferImportLink',
    payload: 'adminSettingsDataTransferImportLink',
  },
  index: {
    mutation: ADMIN_SETTINGS_DATA_TRANSFER_IMPORT_INDEX_MUTATION,
    field: 'indexAdminSettingsDataTransferImportIndex',
    payload: 'adminSettingsDataTransferImportIndex',
  },
};

async function safeJson(resp: any): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

function uniqueSku(): string {
  return `e2e-dti-${Date.now().toString(36)}-${Math.floor(Math.random() * 1e6).toString(36)}`;
}

function productCsv(sku: string): Buffer {
  const row: Record<string, string> = {
    sku,
    locale: 'en',
    attribute_family_code: 'default',
    type: 'simple',
    name: `E2E Import ${sku}`,
    description: 'E2E import description',
    short_description: 'E2E import short description',
    status: '1',
    visible_individually: '1',
    new: '0',
    featured: '0',
    guest_checkout: '0',
    weight: '1.23',
    price: '14',
    url_key: sku,
    manage_stock: '0',
  };

  const line = PRODUCT_COLUMNS.map(column => row[column] ?? '').join(',');

  return Buffer.from(`${PRODUCT_COLUMNS.join(',')}\n${line}\n`);
}

async function createImport(request: APIRequestContext, sku: string): Promise<number> {
  const resp = await sendAdminMultipartRequest(request, ADMIN_SETTINGS_COVERAGE.DATA_TRANSFER_IMPORTS, {
    type: 'products',
    action: 'append',
    validation_strategy: 'stop-on-errors',
    allowed_errors: 0,
    field_separator: ',',
    process_in_queue: '0',
    image_source: 'url',
    file: { name: `${sku}.csv`, mimeType: 'text/csv', buffer: productCsv(sku) },
  });
  const body = await safeJson(resp);
  expect(resp.status(), JSON.stringify(body)).toBe(201);
  expect(typeof body?.id).toBe('number');
  expect(body.state).toBe('pending');
  return body.id;
}

async function latestImportId(request: APIRequestContext): Promise<number | null> {
  const resp = await sendAdminRequest(request, ADMIN_SETTINGS_COVERAGE.DATA_TRANSFER_IMPORTS, {
    params: { sort: 'id', order: 'desc', per_page: '1' },
  });
  expect(resp.status()).toBe(200);
  const body = await safeJson(resp);
  expect(Array.isArray(body?.data)).toBe(true);
  return body.data[0]?.id ?? null;
}

async function productIdsBySku(request: APIRequestContext, sku: string): Promise<number[]> {
  const resp = await sendAdminRequest(request, ADMIN_SETTINGS_COVERAGE.CATALOG_PRODUCTS, {
    params: { sku, per_page: '50' },
  });
  expect(resp.status()).toBe(200);
  const body = await safeJson(resp);
  const rows = Array.isArray(body?.data) ? body.data : [];
  return rows.filter((row: any) => row.sku === sku).map((row: any) => row.id);
}

async function runAction(request: APIRequestContext, action: ImportAction, importId: number) {
  const { mutation, field, payload } = ACTIONS[action];
  const resp = await sendAdminGraphQLRequest(request, mutation, {
    input: { id: ADMIN_SETTINGS_IMPORT_IRI(importId, action), importId },
  });
  expect(resp.status()).toBe(200);
  const body = await resp.json();
  return { body, node: body.data?.[field]?.[payload] ?? null };
}

async function runActionOk(request: APIRequestContext, action: ImportAction, importId: number) {
  const { body, node } = await runAction(request, action, importId);
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
  expect(node._id).toBe(importId);
  expect(node.success).toBe(true);
  return node;
}

async function queryStats(request: APIRequestContext, importId: number) {
  const resp = await sendAdminGraphQLRequest(request, ADMIN_SETTINGS_DATA_TRANSFER_IMPORT_STATS_QUERY, {
    id: ADMIN_SETTINGS_IMPORT_IRI(importId, 'stats'),
  });
  expect(resp.status()).toBe(200);
  return resp.json();
}

function expectBatches(node: any, completed: number) {
  expect(node.stats.batches.total).toBe(1);
  expect(node.stats.batches.completed).toBe(completed);
  expect(node.stats.batches.remaining).toBe(1 - completed);
  expect(Number(node.stats.progress)).toBe(completed * 100);
}

test.describe('admin settings data transfer import graphql', () => {
  let sku: string;
  let importId: number;

  test.beforeEach(async ({ request }) => {
    importId = 0;
    sku = uniqueSku();
    importId = await createImport(request, sku);
  });

  test.afterEach(async ({ request }) => {
    if (importId) {
      await sendAdminRequest(request, ADMIN_SETTINGS_COVERAGE.DATA_TRANSFER_IMPORT(importId), { method: 'DELETE' });
    }

    for (const id of await productIdsBySku(request, sku)) {
      await sendAdminRequest(request, ADMIN_SETTINGS_COVERAGE.CATALOG_PRODUCT(id), { method: 'DELETE' });
    }
  });

  test('should validate, start, link and index a product import through to completion', async ({ request }) => {
    const validated = await runActionOk(request, 'validate', importId);
    expect(validated.state).toBe('validated');
    expect(validated.isValid).toBe(true);
    expect(validated.code).toBe('products');
    expect(validated.action).toBe('append');
    expect(validated.processedRowsCount).toBe(1);
    expect(validated.invalidRowsCount).toBe(0);
    expect(validated.errorsCount).toBe(0);

    const pendingStats = await queryStats(request, importId);
    expect(pendingStats.errors, JSON.stringify(pendingStats.errors)).toBeUndefined();
    expect(pendingStats.data.adminSettingsDataTransferImportStats._id).toBe(importId);
    expect(pendingStats.data.adminSettingsDataTransferImportStats.state).toBe('validated');
    expectBatches(pendingStats.data.adminSettingsDataTransferImportStats, 0);

    const started = await runActionOk(request, 'start', importId);
    expect(started.state).toBe('processing');
    expectBatches(started, 1);
    expect(Number(started.stats.summary.created)).toBe(1);
    expect(await productIdsBySku(request, sku)).toHaveLength(1);

    const processedStats = await queryStats(request, importId);
    expect(processedStats.errors, JSON.stringify(processedStats.errors)).toBeUndefined();
    expectBatches(processedStats.data.adminSettingsDataTransferImportStats, 1);

    expect((await runActionOk(request, 'start', importId)).state).toBe('linking');

    const linked = await runActionOk(request, 'link', importId);
    expect(linked.state).toBe('linking');
    expectBatches(linked, 1);

    expect((await runActionOk(request, 'link', importId)).state).toBe('indexing');

    const indexed = await runActionOk(request, 'index', importId);
    expect(indexed.state).toBe('indexing');
    expectBatches(indexed, 1);

    expect((await runActionOk(request, 'index', importId)).state).toBe('completed');

    const finalStats = await queryStats(request, importId);
    expect(finalStats.errors, JSON.stringify(finalStats.errors)).toBeUndefined();
    expect(finalStats.data.adminSettingsDataTransferImportStats.state).toBe('completed');
    expect(finalStats.data.adminSettingsDataTransferImportStats.processedRowsCount).toBe(1);
  });

  for (const action of ['start', 'link', 'index'] as ImportAction[]) {
    test(`should refuse to ${action} an import that has not been validated`, async ({ request }) => {
      const { body, node } = await runAction(request, action, importId);

      expect(node).toBeNull();
      expect(body.errors?.[0]?.message).toBe(NOTHING_TO_IMPORT_MESSAGE);

      const stats = await queryStats(request, importId);
      expect(stats.data.adminSettingsDataTransferImportStats.state).toBe('pending');
      expect(await productIdsBySku(request, sku)).toEqual([]);
    });
  }

  test('should report the stats of a pending import with no batches', async ({ request }) => {
    const body = await queryStats(request, importId);

    expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
    const node = body.data.adminSettingsDataTransferImportStats;
    expect(node._id).toBe(importId);
    expect(node.code).toBe('products');
    expect(node.state).toBe('pending');
    expect(node.stats.batches).toEqual({ total: 0, completed: 0, remaining: 0, failed: 0 });
    expect(Number(node.stats.progress)).toBe(0);
  });
});

test.describe('admin settings data transfer import graphql refusals', () => {
  for (const action of ['validate', 'start', 'link', 'index'] as ImportAction[]) {
    test(`should return not found when asked to ${action} an unknown import`, async ({ request }) => {
      const { body, node } = await runAction(request, action, UNKNOWN_ID);

      expect(node).toBeNull();
      expect(body.errors?.[0]?.message).toBe(NOT_FOUND_MESSAGE);
    });

    test(`should refuse to ${action} an import without an admin token`, async ({ request }) => {
      const resp = await sendGraphQLRequest(
        request,
        ACTIONS[action].mutation,
        { input: { id: ADMIN_SETTINGS_IMPORT_IRI(UNKNOWN_ID, action), importId: UNKNOWN_ID } },
        {},
        env.adminGraphqlEndpoint,
      );

      expect(resp.status()).toBe(401);
      expect((await resp.json())?.message).toBe('Unauthenticated.');
    });
  }

  test('should return not found for the stats of an unknown import', async ({ request }) => {
    const body = await queryStats(request, UNKNOWN_ID);

    expect(body.data?.adminSettingsDataTransferImportStats ?? null).toBeNull();
    expect(body.errors?.[0]?.message).toBe(NOT_FOUND_MESSAGE);
  });

  test('should refuse import stats without an admin token', async ({ request }) => {
    const resp = await sendGraphQLRequest(
      request,
      ADMIN_SETTINGS_DATA_TRANSFER_IMPORT_STATS_QUERY,
      { id: ADMIN_SETTINGS_IMPORT_IRI(UNKNOWN_ID, 'stats') },
      {},
      env.adminGraphqlEndpoint,
    );

    expect(resp.status()).toBe(401);
    expect((await resp.json())?.message).toBe('Unauthenticated.');
  });

  test('should refuse to create an import over graphql and store nothing', async ({ request }) => {
    const before = await latestImportId(request);

    const resp = await sendAdminGraphQLRequest(request, ADMIN_SETTINGS_DATA_TRANSFER_IMPORT_CREATE_MUTATION, {
      input: {
        type: 'products',
        action: 'append',
        validationStrategy: 'stop-on-errors',
        allowedErrors: 0,
        fieldSeparator: ',',
      },
    });
    expect(resp.status()).toBe(200);

    const body = await resp.json();
    expect(body.data?.createAdminSettingsDataTransferImport ?? null).toBeNull();
    expect(body.errors?.[0]?.message).toMatch(/upload over GraphQL is not supported/);
    expect(await latestImportId(request)).toBe(before);
  });

  test('should refuse to create an import over graphql without an admin token', async ({ request }) => {
    const resp = await sendGraphQLRequest(
      request,
      ADMIN_SETTINGS_DATA_TRANSFER_IMPORT_CREATE_MUTATION,
      { input: { type: 'products', action: 'append' } },
      {},
      env.adminGraphqlEndpoint,
    );

    expect(resp.status()).toBe(401);
    expect((await resp.json())?.message).toBe('Unauthenticated.');
  });
});
