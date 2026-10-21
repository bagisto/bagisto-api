import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../rest/helpers/adminClient';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import { parseCsvRow } from '../../../rest/helpers/coverage/adminSales.helpers';
import {
  BINARY_HEADERS,
  ImportAction,
  REST_GAPS,
  REST_GAPS_MESSAGES as MSG,
  UNKNOWN_ID,
  basenameOf,
  createProductImport,
  deleteImport,
  expectAdminUnauthenticated,
  expectDetail,
  importStats,
  productIdsBySku,
  productImportCsv,
  readImport,
  runImportAction,
  runImportActionOk,
  safeJson,
  uniqueSku,
} from '../../../rest/helpers/coverage/restGaps.helpers';

test.describe.configure({ timeout: 180_000 });

const ACTIONS: ImportAction[] = ['validate', 'start', 'link', 'index'];

function expectBatches(body: any, completed: number): void {
  expect(body.stats.batches.total).toBe(1);
  expect(body.stats.batches.completed).toBe(completed);
  expect(body.stats.batches.remaining).toBe(1 - completed);
  expect(Number(body.stats.progress)).toBe(completed * 100);
}

function csvLines(text: string): string[][] {
  return text
    .replace(/^﻿/, '')
    .split(/\r?\n/)
    .filter(line => line.trim() !== '')
    .map(line => parseCsvRow(line));
}

test.describe('admin data transfer import actions rest api', () => {
  let sku: string;
  let csv: Buffer;
  let importId: number;
  let filePath: string;

  test.beforeEach(async ({ request }) => {
    importId = 0;
    sku = uniqueSku('e2e-gap-import');
    csv = productImportCsv(sku, 'simple');

    const created = await createProductImport(request, sku, csv);

    importId = created.id;
    filePath = created.filePath;
  });

  test.afterEach(async ({ request }) => {
    if (importId) {
      await deleteImport(request, importId);
    }

    for (const id of await productIdsBySku(request, sku)) {
      await sendAdminRequest(request, REST_GAPS.ADMIN_PRODUCT(id), { method: 'DELETE' });
    }
  });

  test('should validate start link and index a product import through to completion', async ({ request }) => {
    const validated = await runImportActionOk(request, importId, 'validate');
    expect(validated.state).toBe('validated');
    expect(validated.isValid).toBe(true);
    expect(validated.code).toBe('products');
    expect(validated.action).toBe('append');
    expect(validated.processedRowsCount).toBe(1);
    expect(validated.invalidRowsCount).toBe(0);
    expect(validated.errorsCount).toBe(0);
    expect(validated.errorFilePath).toBeNull();

    const pendingStats = await importStats(request, importId);
    const pending = await safeJson(pendingStats);
    expect(pendingStats.status(), JSON.stringify(pending)).toBe(200);
    expect(pending.id).toBe(importId);
    expect(pending.state).toBe('validated');
    expectBatches(pending, 0);

    const started = await runImportActionOk(request, importId, 'start');
    expect(started.state).toBe('processing');
    expectBatches(started, 1);
    expect(Number(started.stats.summary.created)).toBe(1);
    expect(await productIdsBySku(request, sku)).toHaveLength(1);

    const processedStats = await importStats(request, importId, 'processed');
    const processed = await safeJson(processedStats);
    expect(processedStats.status(), JSON.stringify(processed)).toBe(200);
    expectBatches(processed, 1);
    expect(Number(processed.stats.summary.created)).toBe(1);

    expect((await runImportActionOk(request, importId, 'start')).state).toBe('linking');

    const linked = await runImportActionOk(request, importId, 'link');
    expect(linked.state).toBe('linking');
    expectBatches(linked, 1);

    expect((await runImportActionOk(request, importId, 'link')).state).toBe('indexing');

    const indexed = await runImportActionOk(request, importId, 'index');
    expect(indexed.state).toBe('indexing');
    expectBatches(indexed, 1);

    const completed = await runImportActionOk(request, importId, 'index');
    expect(completed.state).toBe('completed');

    const finalImport = await readImport(request, importId);
    expect(finalImport.state).toBe('completed');
    expect(finalImport.processedRowsCount).toBe(1);
  });

  test('should report the stats of a pending import with no batches', async ({ request }) => {
    const response = await importStats(request, importId);
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body.id).toBe(importId);
    expect(body.code).toBe('products');
    expect(body.state).toBe('pending');
    expect(body.stats.batches).toEqual({ total: 0, completed: 0, remaining: 0, failed: 0 });
    expect(Number(body.stats.progress)).toBe(0);
  });

  for (const action of ['start', 'link', 'index'] as ImportAction[]) {
    test(`should refuse to ${action} an import that has not been validated`, async ({ request }) => {
      await expectDetail(await runImportAction(request, importId, action), 400, MSG.IMPORT_NOTHING_TO_IMPORT);

      expect((await readImport(request, importId)).state).toBe('pending');
      expect(await productIdsBySku(request, sku)).toEqual([]);
    });
  }

  test('should stream the uploaded import file as an attachment', async ({ request }) => {
    const response = await sendAdminRequest(request, REST_GAPS.IMPORT_ACTION(importId, 'download'), { headers: BINARY_HEADERS });

    expect(response.status()).toBe(200);
    expect(String(response.headers()['content-type'])).toContain('application/octet-stream');
    expect(response.headers()['content-disposition']).toBe(`attachment; filename="${basenameOf(filePath)}"`);
    expect((await response.body()).equals(csv)).toBe(true);
  });

  test('should report a missing error report for an import without errors', async ({ request }) => {
    await runImportActionOk(request, importId, 'validate');

    const response = await sendAdminRequest(request, REST_GAPS.IMPORT_ACTION(importId, 'download-error-report'), {
      headers: BINARY_HEADERS,
    });

    expect(response.headers()['content-disposition']).toBeUndefined();
    await expectDetail(response, 404, MSG.IMPORT_ERROR_REPORT_NOT_FOUND);
  });
});

test.describe('admin data transfer invalid import rest api', () => {
  let sku: string;
  let importId: number;

  test.beforeEach(async ({ request }) => {
    importId = 0;
    sku = uniqueSku('e2e-gap-bad-import');

    importId = (await createProductImport(request, sku, productImportCsv(sku, 'not-a-product-type'))).id;
  });

  test.afterEach(async ({ request }) => {
    if (importId) {
      await deleteImport(request, importId);
    }
  });

  test('should report an invalid row on validation and stream its error report', async ({ request }) => {
    const validated = await runImportActionOk(request, importId, 'validate');

    expect(validated.state).toBe('validated');
    expect(validated.isValid).toBe(false);
    expect(validated.processedRowsCount).toBe(1);
    expect(validated.invalidRowsCount).toBe(1);
    expect(validated.errorsCount).toBe(1);
    expect(validated.errors.some((error: string) => error.endsWith(MSG.IMPORT_INVALID_PRODUCT_TYPE))).toBe(true);
    expect(validated.errorFilePath).toMatch(new RegExp(`^imports/${importId}/processed/.+-validation-errors\\.csv$`));

    const response = await sendAdminRequest(request, REST_GAPS.IMPORT_ACTION(importId, 'download-error-report'), {
      headers: BINARY_HEADERS,
    });

    expect(response.status()).toBe(200);
    expect(String(response.headers()['content-type'])).toContain('application/octet-stream');
    expect(response.headers()['content-disposition']).toBe(`attachment; filename="${basenameOf(validated.errorFilePath)}"`);

    const [header, row] = csvLines(await response.text());
    expect(header[0]).toBe('sku');
    expect(header[header.length - 1]).toBe('errors');
    expect(row[0]).toBe(sku);
    expect(row[row.length - 1]).toBe(MSG.IMPORT_INVALID_PRODUCT_TYPE);
  });

  test('should refuse to start an import that failed validation', async ({ request }) => {
    await runImportActionOk(request, importId, 'validate');

    await expectDetail(await runImportAction(request, importId, 'start'), 400, MSG.IMPORT_NOT_VALID);

    const after = await readImport(request, importId);
    expect(after.state).toBe('validated');
    expect(await productIdsBySku(request, sku)).toEqual([]);
  });
});

test.describe('admin data transfer import refusals rest api', () => {
  for (const action of ACTIONS) {
    test(`should return not found when asked to ${action} an unknown import`, async ({ request }) => {
      await expectDetail(await runImportAction(request, UNKNOWN_ID, action), 404, MSG.IMPORT_NOT_FOUND);
    });

    test(`should refuse to ${action} an import without an admin token`, async ({ request }) => {
      await expectAdminUnauthenticated(
        await sendRestRequest(request, REST_GAPS.IMPORT_ACTION(UNKNOWN_ID, action), { method: 'POST', data: {} })
      );
    });
  }

  test('should return not found for the stats of an unknown import', async ({ request }) => {
    await expectDetail(await importStats(request, UNKNOWN_ID), 404, MSG.IMPORT_NOT_FOUND);
  });

  test('should refuse import stats without an admin token', async ({ request }) => {
    await expectAdminUnauthenticated(await sendRestRequest(request, REST_GAPS.IMPORT_ACTION(UNKNOWN_ID, 'stats')));
  });

  for (const download of ['download', 'download-error-report']) {
    test(`should return not found and no file for the ${download} of an unknown import`, async ({ request }) => {
      const response = await sendAdminRequest(request, REST_GAPS.IMPORT_ACTION(UNKNOWN_ID, download), { headers: BINARY_HEADERS });

      expect(response.headers()['content-disposition']).toBeUndefined();
      await expectDetail(response, 404, MSG.IMPORT_NOT_FOUND);
    });

    test(`should refuse the ${download} of an import without an admin token`, async ({ request }) => {
      const response = await sendRestRequest(request, REST_GAPS.IMPORT_ACTION(UNKNOWN_ID, download), { headers: BINARY_HEADERS });

      expect(response.headers()['content-disposition']).toBeUndefined();
      await expectAdminUnauthenticated(response);
    });
  }
});
