import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../rest/helpers/adminClient';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import { ADMIN_CMS_COVERAGE } from '../../../rest/endpoints/coverage/adminCatalog.endpoints';

test.describe.configure({ timeout: 90_000 });

const CSV = 'text/csv';
const XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

const EXPORT_HEADER = 'ID,"Page Title","URL Key",Channel,Locale';
const UNSUPPORTED_FORMAT_MESSAGE = 'Only csv, xls and xlsx exports are supported.';

async function safeJson(resp: any): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

function csvLines(text: string): string[] {
  return text.split(/\r?\n/).filter((line) => line.length > 0);
}

async function firstChannel(request: APIRequestContext): Promise<{ id: number; code: string }> {
  const resp = await sendAdminRequest(request, ADMIN_CMS_COVERAGE.CHANNELS);
  expect(resp.status()).toBe(200);
  const rows = (await safeJson(resp)).data;
  expect(rows.length).toBeGreaterThan(0);
  return { id: rows[0].id, code: rows[0].code };
}

test.describe('admin cms pages export rest api', () => {
  let pageId: number;
  let urlKey: string;
  let title: string;
  let channel: { id: number; code: string };

  test.beforeEach(async ({ request }) => {
    pageId = 0;
    const stamp = `${Date.now().toString(36)}${Math.floor(Math.random() * 1e6).toString(36)}`;
    urlKey = `e2e-cov-cms-${stamp}`;
    title = `E2E Coverage ${stamp}`;
    channel = await firstChannel(request);

    const resp = await sendAdminRequest(request, ADMIN_CMS_COVERAGE.PAGES, {
      method: 'POST',
      data: {
        url_key: urlKey,
        page_title: title,
        html_content: '<p>coverage</p>',
        channels: [channel.id],
      },
    });
    expect(resp.status()).toBe(201);
    pageId = (await safeJson(resp)).id;
    expect(typeof pageId).toBe('number');
  });

  test.afterEach(async ({ request }) => {
    if (pageId) {
      await sendAdminRequest(request, ADMIN_CMS_COVERAGE.PAGE(pageId), { method: 'DELETE' });
    }
  });

  test('should export the page filtered by url key as a csv attachment', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CMS_COVERAGE.PAGES_EXPORT, {
      params: { format: 'csv', url_key: urlKey },
      headers: { Accept: CSV },
    });
    expect(resp.status()).toBe(200);
    expect(resp.headers()['content-type']).toContain(CSV);
    expect(resp.headers()['content-disposition']).toBe('attachment; filename="cms-pages.csv"');

    const lines = csvLines(await resp.text());
    expect(lines[0]).toBe(EXPORT_HEADER);
    expect(lines).toHaveLength(2);

    const cells = lines[1].split(',');
    expect(cells).toHaveLength(5);
    expect(cells[0]).toBe(String(pageId));
    expect(cells[1]).toBe(`"${title}"`);
    expect(cells[2]).toBe(urlKey);
    expect(cells[3]).toBe(channel.code);
    expect(cells[4].length).toBeGreaterThan(0);
  });

  test('should filter the export by page id', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CMS_COVERAGE.PAGES_EXPORT, {
      params: { id: String(pageId) },
      headers: { Accept: CSV },
    });
    expect(resp.status()).toBe(200);

    const lines = csvLines(await resp.text());
    expect(lines).toHaveLength(2);
    expect(lines[1].startsWith(`${pageId},"${title}",${urlKey},`)).toBe(true);
  });

  test('should export only the header row once the page is deleted', async ({ request }) => {
    const del = await sendAdminRequest(request, ADMIN_CMS_COVERAGE.PAGE(pageId), { method: 'DELETE' });
    expect([200, 204]).toContain(del.status());
    pageId = 0;

    const resp = await sendAdminRequest(request, ADMIN_CMS_COVERAGE.PAGES_EXPORT, {
      params: { url_key: urlKey },
      headers: { Accept: CSV },
    });
    expect(resp.status()).toBe(200);
    expect(csvLines(await resp.text())).toEqual([EXPORT_HEADER]);
  });

  test('should export the pages as an xlsx download', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CMS_COVERAGE.PAGES_EXPORT, {
      params: { format: 'xlsx', url_key: urlKey },
      headers: { Accept: XLSX },
    });
    expect(resp.status()).toBe(200);
    expect(resp.headers()['content-type']).toContain(XLSX);
    expect(resp.headers()['content-disposition']).toBe('attachment; filename="cms-pages.xlsx"');
    expect((await resp.body()).subarray(0, 2).toString()).toBe('PK');
  });

  test('should reject an unsupported cms pages export format', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_CMS_COVERAGE.PAGES_EXPORT, {
      params: { format: 'pdf' },
      headers: { Accept: CSV },
    });
    expect(resp.status()).toBe(422);
    expect(resp.headers()['content-disposition']).toBeUndefined();
    expect((await safeJson(resp)).detail).toBe(UNSUPPORTED_FORMAT_MESSAGE);
  });

  test('should refuse the cms pages export without an admin token', async ({ request }) => {
    const resp = await sendRestRequest(request, ADMIN_CMS_COVERAGE.PAGES_EXPORT, {
      headers: { Accept: CSV },
    });
    expect(resp.status()).toBe(401);
    expect(resp.headers()['content-disposition']).toBeUndefined();
    expect((await safeJson(resp)).message).toBe('Unauthenticated.');
  });
});
