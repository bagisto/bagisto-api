import { test, expect, APIResponse } from '@playwright/test';
import { sendAdminRequest } from '../../../rest/helpers/adminClient';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import { ADMIN_REPORTING_COVERAGE } from '../../../rest/endpoints/coverage/adminReporting.endpoints';

test.describe.configure({ timeout: 90_000 });

const CSV = 'text/csv';
const XLS = 'application/vnd.ms-excel';
const XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

const INVALID_TYPE_MESSAGE = 'Unknown reporting stat type for this entity.';
const UNSUPPORTED_FORMAT_MESSAGE = 'Only the csv, xls and xlsx export formats are supported.';

const SALES_TOTAL_COLUMNS = [
  { key: 'label', label: 'Interval' },
  { key: 'count', label: 'Orders' },
  { key: 'formatted_total', label: 'Total' },
];

const PRODUCTS_SOLD_COLUMNS = [
  { key: 'label', label: 'Interval' },
  { key: 'total', label: 'Quantities' },
];

const PRODUCTS_REVENUE_COLUMNS = [
  { key: 'id', label: 'Id' },
  { key: 'name', label: 'Name' },
  { key: 'formatted_price', label: 'Price' },
  { key: 'formatted_revenue', label: 'Revenue' },
];

async function safeJson(resp: any): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

async function readView(resp: APIResponse): Promise<any> {
  expect(resp.status()).toBe(200);
  const body = await safeJson(resp);
  expect(Array.isArray(body)).toBe(true);
  expect(body).toHaveLength(1);
  return body[0];
}

function expectTable(row: any, columns: { key: string; label: string }[]) {
  expect(row.statistics.columns).toEqual(columns);
  expect(Array.isArray(row.statistics.records)).toBe(true);
  for (const record of row.statistics.records) {
    for (const column of columns) {
      expect(record, `record has ${column.key}`).toHaveProperty(column.key);
    }
  }
}

function expectAttachment(resp: APIResponse, mime: string, fileName: string) {
  expect(resp.status()).toBe(200);
  expect(resp.headers()['content-type']).toContain(mime);
  expect(resp.headers()['content-disposition']).toBe(`attachment; filename="${fileName}"`);
}

function csvLines(text: string): string[] {
  return text.split(/\r?\n/).filter((line) => line.length > 0);
}

test.describe('admin reporting view and export rest api', () => {
  test('should return the total sales view as a table of columns and records', async ({ request }) => {
    const row = await readView(await sendAdminRequest(request, ADMIN_REPORTING_COVERAGE.SALES_VIEW, {
      params: { type: 'total-sales' },
    }));

    expect(row.entity).toBe('sales');
    expect(row.type).toBe('total-sales');
    expect(Object.keys(row.dateRange).sort()).toEqual(['current', 'previous']);
    expectTable(row, SALES_TOTAL_COLUMNS);
    expect(row.statistics.records.length).toBeGreaterThan(0);
  });

  test('should default the sales view to the total sales table', async ({ request }) => {
    const row = await readView(await sendAdminRequest(request, ADMIN_REPORTING_COVERAGE.SALES_VIEW));

    expect(row.type).toBe('total-sales');
    expectTable(row, SALES_TOTAL_COLUMNS);
  });

  test('should scope the sales view to the requested date range', async ({ request }) => {
    const row = await readView(await sendAdminRequest(request, ADMIN_REPORTING_COVERAGE.SALES_VIEW, {
      params: { type: 'total-sales', start: '2026-01-05', end: '2026-01-11' },
    }));

    expect(row.dateRange.current).toBe('05 Jan 2026 - 11 Jan 2026');
    expectTable(row, SALES_TOTAL_COLUMNS);
  });

  test('should reject an unknown sales view type', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_REPORTING_COVERAGE.SALES_VIEW, {
      params: { type: 'bogus' },
    });
    expect(resp.status()).toBe(400);
    expect((await safeJson(resp)).detail).toBe(INVALID_TYPE_MESSAGE);
  });

  test('should refuse the sales view without an admin token', async ({ request }) => {
    const resp = await sendRestRequest(request, ADMIN_REPORTING_COVERAGE.SALES_VIEW);
    expect(resp.status()).toBe(401);
    expect((await safeJson(resp)).message).toBe('Unauthenticated.');
  });

  test('should return the products views as tables of columns and records', async ({ request }) => {
    const sold = await readView(await sendAdminRequest(request, ADMIN_REPORTING_COVERAGE.PRODUCTS_VIEW));
    expect(sold.entity).toBe('products');
    expect(sold.type).toBe('total-sold-quantities');
    expectTable(sold, PRODUCTS_SOLD_COLUMNS);

    const revenue = await readView(await sendAdminRequest(request, ADMIN_REPORTING_COVERAGE.PRODUCTS_VIEW, {
      params: { type: 'top-selling-products-by-revenue' },
    }));
    expect(revenue.entity).toBe('products');
    expect(revenue.type).toBe('top-selling-products-by-revenue');
    expectTable(revenue, PRODUCTS_REVENUE_COLUMNS);
  });

  test('should reject a sales type on the products view', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_REPORTING_COVERAGE.PRODUCTS_VIEW, {
      params: { type: 'total-sales' },
    });
    expect(resp.status()).toBe(400);
    expect((await safeJson(resp)).detail).toBe(INVALID_TYPE_MESSAGE);
  });

  test('should refuse the products view without an admin token', async ({ request }) => {
    const resp = await sendRestRequest(request, ADMIN_REPORTING_COVERAGE.PRODUCTS_VIEW);
    expect(resp.status()).toBe(401);
    expect((await safeJson(resp)).message).toBe('Unauthenticated.');
  });

  test('should export the total sales table as a csv attachment with its header row', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_REPORTING_COVERAGE.SALES_EXPORT, {
      params: { type: 'total-sales', format: 'csv' },
      headers: { Accept: CSV },
    });
    expectAttachment(resp, CSV, 'sales-total-sales.csv');

    const view = await readView(await sendAdminRequest(request, ADMIN_REPORTING_COVERAGE.SALES_VIEW, {
      params: { type: 'total-sales' },
    }));

    const lines = csvLines(await resp.text());
    expect(lines[0]).toBe('Interval,Orders,Total');
    expect(lines.length - 1).toBe(view.statistics.records.length);
  });

  test('should export the sales table as xls and xlsx downloads', async ({ request }) => {
    const xls = await sendAdminRequest(request, ADMIN_REPORTING_COVERAGE.SALES_EXPORT, {
      params: { type: 'total-sales', format: 'xls' },
      headers: { Accept: XLS },
    });
    expectAttachment(xls, XLS, 'sales-total-sales.xls');
    expect((await xls.body()).subarray(0, 4).toString('hex')).toBe('d0cf11e0');

    const xlsx = await sendAdminRequest(request, ADMIN_REPORTING_COVERAGE.SALES_EXPORT, {
      params: { type: 'total-sales', format: 'xlsx' },
      headers: { Accept: XLSX },
    });
    expectAttachment(xlsx, XLSX, 'sales-total-sales.xlsx');
    expect((await xlsx.body()).subarray(0, 2).toString()).toBe('PK');
  });

  test('should reject an unsupported sales export format', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_REPORTING_COVERAGE.SALES_EXPORT, {
      params: { type: 'total-sales', format: 'pdf' },
      headers: { Accept: CSV },
    });
    expect(resp.status()).toBe(422);
    expect(resp.headers()['content-disposition']).toBeUndefined();
    expect((await safeJson(resp)).detail).toBe(UNSUPPORTED_FORMAT_MESSAGE);
  });

  test('should reject an unknown sales export type', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_REPORTING_COVERAGE.SALES_EXPORT, {
      params: { type: 'bogus', format: 'csv' },
      headers: { Accept: CSV },
    });
    expect(resp.status()).toBe(400);
    expect(resp.headers()['content-disposition']).toBeUndefined();
    expect((await safeJson(resp)).detail).toBe(INVALID_TYPE_MESSAGE);
  });

  test('should refuse the sales export without an admin token', async ({ request }) => {
    const resp = await sendRestRequest(request, ADMIN_REPORTING_COVERAGE.SALES_EXPORT, {
      params: { type: 'total-sales' },
      headers: { Accept: CSV },
    });
    expect(resp.status()).toBe(401);
    expect(resp.headers()['content-disposition']).toBeUndefined();
    expect((await safeJson(resp)).message).toBe('Unauthenticated.');
  });

  test('should export the top selling products table as a csv attachment with its header row', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_REPORTING_COVERAGE.PRODUCTS_EXPORT, {
      params: { type: 'top-selling-products-by-revenue' },
      headers: { Accept: CSV },
    });
    expectAttachment(resp, CSV, 'products-top-selling-products-by-revenue.csv');

    const view = await readView(await sendAdminRequest(request, ADMIN_REPORTING_COVERAGE.PRODUCTS_VIEW, {
      params: { type: 'top-selling-products-by-revenue' },
    }));

    const lines = csvLines(await resp.text());
    expect(lines[0]).toBe('Id,Name,Price,Revenue');
    expect(lines.length - 1).toBeGreaterThanOrEqual(view.statistics.records.length);
  });

  test('should name the products export after the default type', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_REPORTING_COVERAGE.PRODUCTS_EXPORT, {
      headers: { Accept: CSV },
    });
    expectAttachment(resp, CSV, 'products-total-sold-quantities.csv');
    expect(csvLines(await resp.text())[0]).toBe('Interval,Quantities');
  });

  test('should reject an unsupported products export format', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_REPORTING_COVERAGE.PRODUCTS_EXPORT, {
      params: { format: 'json' },
      headers: { Accept: CSV },
    });
    expect(resp.status()).toBe(422);
    expect((await safeJson(resp)).detail).toBe(UNSUPPORTED_FORMAT_MESSAGE);
  });

  test('should reject a customers type on the products export', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_REPORTING_COVERAGE.PRODUCTS_EXPORT, {
      params: { type: 'total-customers' },
      headers: { Accept: CSV },
    });
    expect(resp.status()).toBe(400);
    expect((await safeJson(resp)).detail).toBe(INVALID_TYPE_MESSAGE);
  });

  test('should refuse the products export without an admin token', async ({ request }) => {
    const resp = await sendRestRequest(request, ADMIN_REPORTING_COVERAGE.PRODUCTS_EXPORT, {
      headers: { Accept: CSV },
    });
    expect(resp.status()).toBe(401);
    expect((await safeJson(resp)).message).toBe('Unauthenticated.');
  });
});
