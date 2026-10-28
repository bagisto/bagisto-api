import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../rest/helpers/adminClient';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import { ADMIN_RMA } from '../../../rest/endpoints/coverage/adminRma.endpoints';

test.describe.configure({ timeout: 60_000 });

const BINARY_HEADERS = { 'Accept': 'application/octet-stream' };

test.describe('admin data transfer import sample rest api', () => {
  test('should refuse a sample download without an admin token', async ({ request }) => {
    const resp = await sendRestRequest(request, ADMIN_RMA.IMPORT_SAMPLE('products', 'csv'), { headers: BINARY_HEADERS });
    expect(resp.status()).toBe(401);
    expect(resp.headers()['content-disposition']).toBeUndefined();
  });

  test('should stream the products csv sample as an attachment', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_RMA.IMPORT_SAMPLE('products', 'csv'), { headers: BINARY_HEADERS });
    expect(resp.status()).toBe(200);
    expect(String(resp.headers()['content-type'])).toContain('application/octet-stream');
    expect(resp.headers()['content-disposition']).toBe('attachment; filename="products.csv"');

    const header = (await resp.text()).split(/\r?\n/)[0].split(',');
    expect(header[0]).toBe('sku');
    expect(header).toEqual(expect.arrayContaining(['type', 'attribute_family_code', 'price']));
  });

  test('should stream the tax rates csv sample with its own columns', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_RMA.IMPORT_SAMPLE('tax_rates', 'csv'), { headers: BINARY_HEADERS });
    expect(resp.status()).toBe(200);
    expect(resp.headers()['content-disposition']).toBe('attachment; filename="tax-rates.csv"');
    expect((await resp.text()).split(/\r?\n/)[0]).toBe('identifier,is_zip_range,zip_code,zip_from,zip_to,state,country,tax_rate');
  });

  test('should stream a non empty customers xlsx sample', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_RMA.IMPORT_SAMPLE('customers', 'xlsx'), { headers: BINARY_HEADERS });
    expect(resp.status()).toBe(200);
    expect(resp.headers()['content-disposition']).toBe('attachment; filename="customers.xlsx"');
    const body = await resp.body();
    expect(body.subarray(0, 2).toString('latin1')).toBe('PK');
  });

  test('should refuse a sample in an unsupported format', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_RMA.IMPORT_SAMPLE('products', 'pdf'), { headers: BINARY_HEADERS });
    expect(resp.status()).toBe(422);
    expect(resp.headers()['content-disposition']).toBeUndefined();
  });

  test('should return 404 for an importer type that has no sample', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_RMA.IMPORT_SAMPLE('invoices', 'csv'), { headers: BINARY_HEADERS });
    expect(resp.status()).toBe(404);
    expect(resp.headers()['content-disposition']).toBeUndefined();
  });
});
