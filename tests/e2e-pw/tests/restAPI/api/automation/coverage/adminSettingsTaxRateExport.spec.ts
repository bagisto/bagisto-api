import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../rest/helpers/adminClient';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import { ADMIN_SETTINGS_COVERAGE } from '../../../rest/endpoints/coverage/adminSettings.endpoints';
import { parseCsvRow } from '../../../rest/helpers/coverage/adminSales.helpers';

test.describe.configure({ timeout: 60_000 });

const CSV_HEADER = ['ID', 'Identifier', 'State', 'Country', 'Zip Code', 'Zip From', 'Zip To', 'Tax Rate'];
const UNSUPPORTED_FORMAT_MESSAGE = 'Only csv, xls and xlsx exports are supported.';

const FORMATS = {
  csv: 'text/csv',
  xls: 'application/vnd.ms-excel',
  xlsx: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
};

async function safeJson(resp: any): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

function uniqueIdentifier(): string {
  return `e2e-trx-${Date.now().toString(36)}-${Math.floor(Math.random() * 1e6).toString(36)}`;
}

async function createTaxRate(request: APIRequestContext, identifier: string): Promise<number> {
  const resp = await sendAdminRequest(request, ADMIN_SETTINGS_COVERAGE.TAX_RATES, {
    method: 'POST',
    data: {
      identifier,
      is_zip: false,
      zip_code: '94105',
      state: 'CA',
      country: 'US',
      tax_rate: 8.5,
    },
  });
  expect(resp.status()).toBe(201);
  const body = await safeJson(resp);
  expect(typeof body?.id).toBe('number');
  return body.id;
}

async function exportTaxRates(request: APIRequestContext, format: keyof typeof FORMATS, params: Record<string, string> = {}) {
  return sendAdminRequest(request, ADMIN_SETTINGS_COVERAGE.TAX_RATES_EXPORT, {
    params: { format, ...params },
    headers: { Accept: FORMATS[format] },
  });
}

function csvRows(text: string): string[][] {
  return text.replace(/^\uFEFF/, '').split(/\r?\n/).filter(line => line.length > 0).map(parseCsvRow);
}

test.describe('admin settings tax rate export rest api', () => {
  let identifier: string;
  let taxRateId: number;

  test.beforeEach(async ({ request }) => {
    taxRateId = 0;
    identifier = uniqueIdentifier();
    taxRateId = await createTaxRate(request, identifier);
  });

  test.afterEach(async ({ request }) => {
    if (taxRateId) {
      await sendAdminRequest(request, ADMIN_SETTINGS_COVERAGE.TAX_RATE(taxRateId), { method: 'DELETE' });
    }
  });

  test('should download the filtered tax rates as a csv attachment with a header row', async ({ request }) => {
    const resp = await exportTaxRates(request, 'csv', { identifier });

    expect(resp.status()).toBe(200);
    expect(resp.headers()['content-type']).toContain('text/csv');
    expect(resp.headers()['content-disposition']).toBe('attachment; filename="tax-rates.csv"');

    const rows = csvRows(await resp.text());
    expect(rows[0]).toEqual(CSV_HEADER);
    expect(rows).toHaveLength(2);

    const [id, rowIdentifier, state, country, zipCode, zipFrom, zipTo, taxRate] = rows[1];
    expect(Number(id)).toBe(taxRateId);
    expect(rowIdentifier).toBe(identifier);
    expect(state).toBe('CA');
    expect(country).toBe('US');
    expect(zipCode).toBe('94105');
    expect(zipFrom).toBe('');
    expect(zipTo).toBe('');
    expect(Number(taxRate)).toBe(8.5);
  });

  test('should leave out tax rates that do not match the filter', async ({ request }) => {
    const resp = await exportTaxRates(request, 'csv', { identifier: `${identifier}-absent` });

    expect(resp.status()).toBe(200);
    expect(csvRows(await resp.text())).toEqual([CSV_HEADER]);
  });

  test('should download the tax rates as an xlsx attachment', async ({ request }) => {
    const resp = await exportTaxRates(request, 'xlsx', { identifier });

    expect(resp.status()).toBe(200);
    expect(resp.headers()['content-type']).toContain('spreadsheetml.sheet');
    expect(resp.headers()['content-disposition']).toBe('attachment; filename="tax-rates.xlsx"');

    const bytes = await resp.body();
    expect(bytes.subarray(0, 2).toString('latin1')).toBe('PK');
  });

  test('should download the tax rates as an xls attachment', async ({ request }) => {
    const resp = await exportTaxRates(request, 'xls', { identifier });

    expect(resp.status()).toBe(200);
    expect(resp.headers()['content-type']).toContain('application/vnd.ms-excel');
    expect(resp.headers()['content-disposition']).toBe('attachment; filename="tax-rates.xls"');

    const bytes = await resp.body();
    expect(bytes.subarray(0, 4).toString('hex')).toBe('d0cf11e0');
  });

  test('should refuse an unsupported export format with a 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS_COVERAGE.TAX_RATES_EXPORT, {
      params: { format: 'pdf' },
      headers: { Accept: FORMATS.csv },
    });

    expect(resp.status()).toBe(422);
    expect(resp.headers()['content-disposition']).toBeUndefined();
    expect(await resp.text()).toContain(UNSUPPORTED_FORMAT_MESSAGE);
  });

  test('should refuse the export without an admin token', async ({ request }) => {
    const resp = await sendRestRequest(request, ADMIN_SETTINGS_COVERAGE.TAX_RATES_EXPORT, {
      params: { format: 'csv' },
      headers: { Accept: FORMATS.csv },
    });

    expect(resp.status()).toBe(401);
    expect(resp.headers()['content-disposition']).toBeUndefined();
    expect((await safeJson(resp))?.message).toBe('Unauthenticated.');
  });
});
