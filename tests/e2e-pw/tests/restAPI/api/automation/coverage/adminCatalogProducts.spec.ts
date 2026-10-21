import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../rest/helpers/adminClient';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import { ADMIN_CATALOG_COVERAGE } from '../../../rest/endpoints/coverage/adminCatalog.endpoints';

test.describe.configure({ timeout: 90_000 });

const CSV = 'text/csv';
const XLS = 'application/vnd.ms-excel';
const XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

const EXPORT_HEADER = 'ID,Name,SKU,"Attribute Family",Price,Quantity,Status,Category,Type';
const UNSUPPORTED_FORMAT_MESSAGE = 'Only csv, xls and xlsx exports are supported.';
const PICKER_ROW_KEYS = ['baseImageUrl', 'formattedPrice', 'id', 'isSaleable', 'name', 'price', 'sku', 'status', 'type'];
const PICKER_META_KEYS = ['currentPage', 'from', 'lastPage', 'perPage', 'to', 'total'];

async function safeJson(resp: any): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

function uniqueSku(prefix: string): string {
  return `${prefix}-${Date.now().toString(36)}-${Math.floor(Math.random() * 1e6).toString(36)}`;
}

function parseCsvLine(line: string): string[] {
  const cells: string[] = [];
  let cell = '';
  let quoted = false;

  for (let index = 0; index < line.length; index++) {
    const char = line[index];

    if (quoted) {
      if (char === '"' && line[index + 1] === '"') {
        cell += '"';
        index++;
      } else if (char === '"') {
        quoted = false;
      } else {
        cell += char;
      }
    } else if (char === '"') {
      quoted = true;
    } else if (char === ',') {
      cells.push(cell);
      cell = '';
    } else {
      cell += char;
    }
  }

  cells.push(cell);

  return cells;
}

function csvLines(text: string): string[] {
  return text.split(/\r?\n/).filter((line) => line.length > 0);
}

async function createSimpleProduct(request: APIRequestContext, sku: string): Promise<number> {
  const resp = await sendAdminRequest(request, ADMIN_CATALOG_COVERAGE.PRODUCTS, {
    method: 'POST',
    data: { sku, type: 'simple', attribute_family_id: 1 },
  });
  expect(resp.status()).toBe(201);
  const body = await safeJson(resp);
  expect(body.sku).toBe(sku);
  expect(typeof body.id).toBe('number');
  return body.id;
}

test.describe('admin catalog products export and picker rest api', () => {
  let productId: number;
  let sku: string;

  test.beforeEach(async ({ request }) => {
    productId = 0;
    sku = uniqueSku('e2e-cov-prod');
    productId = await createSimpleProduct(request, sku);
  });

  test.afterEach(async ({ request }) => {
    if (productId) {
      await sendAdminRequest(request, ADMIN_CATALOG_COVERAGE.PRODUCT(productId), { method: 'DELETE' });
    }
  });

  test('should export the products filtered by sku as a csv attachment', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG_COVERAGE.PRODUCTS_EXPORT, {
      params: { format: 'csv', sku },
      headers: { Accept: CSV },
    });
    expect(resp.status()).toBe(200);
    expect(resp.headers()['content-type']).toContain(CSV);
    expect(resp.headers()['content-disposition']).toBe('attachment; filename="products.csv"');

    const lines = csvLines(await resp.text());
    expect(lines[0]).toBe(EXPORT_HEADER);
    expect(lines).toHaveLength(2);

    const row = parseCsvLine(lines[1]);
    expect(row).toHaveLength(9);
    expect(row[0]).toBe(String(productId));
    expect(row[2]).toBe(sku);
    expect(row[5]).toBe('0');
    expect(['Active', 'Disabled']).toContain(row[6]);
    expect(row[8]).toBe('simple');
  });

  test('should export only the header row when no product matches the filter', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG_COVERAGE.PRODUCTS_EXPORT, {
      params: { sku: uniqueSku('e2e-cov-none') },
      headers: { Accept: CSV },
    });
    expect(resp.status()).toBe(200);
    expect(resp.headers()['content-disposition']).toBe('attachment; filename="products.csv"');
    expect(csvLines(await resp.text())).toEqual([EXPORT_HEADER]);
  });

  test('should export the products as xls and xlsx downloads', async ({ request }) => {
    const xls = await sendAdminRequest(request, ADMIN_CATALOG_COVERAGE.PRODUCTS_EXPORT, {
      params: { format: 'xls', sku },
      headers: { Accept: XLS },
    });
    expect(xls.status()).toBe(200);
    expect(xls.headers()['content-type']).toContain(XLS);
    expect(xls.headers()['content-disposition']).toBe('attachment; filename="products.xls"');
    expect((await xls.body()).subarray(0, 4).toString('hex')).toBe('d0cf11e0');

    const xlsx = await sendAdminRequest(request, ADMIN_CATALOG_COVERAGE.PRODUCTS_EXPORT, {
      params: { format: 'xlsx', sku },
      headers: { Accept: XLSX },
    });
    expect(xlsx.status()).toBe(200);
    expect(xlsx.headers()['content-type']).toContain(XLSX);
    expect(xlsx.headers()['content-disposition']).toBe('attachment; filename="products.xlsx"');
    expect((await xlsx.body()).subarray(0, 2).toString()).toBe('PK');
  });

  test('should reject an unsupported products export format', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG_COVERAGE.PRODUCTS_EXPORT, {
      params: { format: 'pdf' },
      headers: { Accept: CSV },
    });
    expect(resp.status()).toBe(422);
    expect(resp.headers()['content-disposition']).toBeUndefined();
    expect((await safeJson(resp)).detail).toBe(UNSUPPORTED_FORMAT_MESSAGE);
  });

  test('should refuse the products export without an admin token', async ({ request }) => {
    const resp = await sendRestRequest(request, ADMIN_CATALOG_COVERAGE.PRODUCTS_EXPORT, {
      headers: { Accept: CSV },
    });
    expect(resp.status()).toBe(401);
    expect(resp.headers()['content-disposition']).toBeUndefined();
    expect((await safeJson(resp)).message).toBe('Unauthenticated.');
  });

  test('should find the created product in the admin product picker by exact sku', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG_COVERAGE.PRODUCT_PICKER, {
      params: { sku },
    });
    expect(resp.status()).toBe(200);

    const body = await safeJson(resp);
    expect(Object.keys(body.meta).sort()).toEqual(PICKER_META_KEYS);
    expect(body.meta.total).toBe(1);
    expect(body.meta.perPage).toBe(30);
    expect(body.meta.currentPage).toBe(1);
    expect(body.data).toHaveLength(1);

    const row = body.data[0];
    expect(Object.keys(row).sort()).toEqual(PICKER_ROW_KEYS);
    expect(row.id).toBe(productId);
    expect(row.sku).toBe(sku);
    expect(row.type).toBe('simple');
    expect(typeof row.isSaleable).toBe('boolean');
  });

  test('should match the created product in the picker by a partial sku search', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG_COVERAGE.PRODUCT_PICKER, {
      params: { query: sku.slice(4), type: 'simple' },
    });
    expect(resp.status()).toBe(200);

    const body = await safeJson(resp);
    expect(body.data.map((row: any) => row.id)).toContain(productId);
    for (const row of body.data) {
      expect(row.type).toBe('simple');
    }
  });

  test('should exclude the created product when the picker filters on another type', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG_COVERAGE.PRODUCT_PICKER, {
      params: { sku, type: 'configurable' },
    });
    expect(resp.status()).toBe(200);

    const body = await safeJson(resp);
    expect(body.data).toEqual([]);
    expect(body.meta.total).toBe(0);
  });

  test('should cap the picker page size at fifty rows', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CATALOG_COVERAGE.PRODUCT_PICKER, {
      params: { per_page: '500' },
    });
    expect(resp.status()).toBe(200);

    const body = await safeJson(resp);
    expect(body.meta.perPage).toBe(50);
    expect(body.data.length).toBeLessThanOrEqual(50);
  });

  test('should refuse the product picker without an admin token', async ({ request }) => {
    const resp = await sendRestRequest(request, ADMIN_CATALOG_COVERAGE.PRODUCT_PICKER, {
      params: { sku },
    });
    expect(resp.status()).toBe(401);
    const body = await safeJson(resp);
    expect(body.message).toBe('Unauthenticated.');
    expect(body.data).toBeUndefined();
  });
});
