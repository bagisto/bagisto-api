import { test, expect, APIRequestContext } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { sendAdminRequest } from '../../rest/helpers/adminClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import { ADMIN_APPEARANCE } from '../../rest/endpoints/admin.appearance.endpoints';
import {
  REST_GAPS_MESSAGES as MSG,
  UNKNOWN_ID,
  expectDetail,
  safeJson,
  uniqueStamp,
} from '../../rest/helpers/coverage/restGaps.helpers';

test.describe.configure({ timeout: 60_000 });

const DEFAULT_CHANNEL_ID = 1;

const SECTIONS_PAGE_SIZE = 50;

function themeOf(body: any): any {
  return Array.isArray(body) ? body[0] : (body?.data?.[0] ?? body);
}

async function channelThemeCode(request: APIRequestContext): Promise<string> {
  const response = await sendRestRequest(request, ENDPOINTS.THEME);
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);

  const code = themeOf(body)?.code;

  expect(typeof code, JSON.stringify(body)).toBe('string');

  return code;
}

async function createSection(request: APIRequestContext, themeCode: string, name: string): Promise<number> {
  const response = await sendAdminRequest(request, ADMIN_APPEARANCE.THEME_SECTIONS(themeCode), {
    method: 'POST',
    data: { name, type: 'static_content', channel: DEFAULT_CHANNEL_ID },
  });
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(201);
  expect(typeof body?.id, JSON.stringify(body)).toBe('number');

  return body.id;
}

async function publishSection(request: APIRequestContext, id: number): Promise<void> {
  const response = await sendAdminRequest(request, ADMIN_APPEARANCE.SECTION(id), {
    method: 'PUT',
    data: { status: true, locale: 'en', options: { html: '<p>E2E section</p>', css: '' } },
  });
  const body = await safeJson(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);
  expect(body.status, JSON.stringify(body)).toBe(1);
}

async function listSections(request: APIRequestContext, params: Record<string, string> = {}): Promise<any[]> {
  const rows: any[] = [];

  for (let page = 1; page <= 50; page++) {
    const response = await sendRestRequest(request, ENDPOINTS.SECTIONS, {
      params: { ...params, page: String(page), per_page: String(SECTIONS_PAGE_SIZE) },
    });
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(Array.isArray(body), JSON.stringify(body)).toBe(true);

    rows.push(...body);

    if (body.length < SECTIONS_PAGE_SIZE) {
      break;
    }
  }

  return rows;
}

test.describe('sections rest api', () => {
  let createdIds: number[];
  let sectionId: number;
  let sectionName: string;

  test.beforeEach(async ({ request }) => {
    createdIds = [];
    sectionName = `E2E Shop Section ${uniqueStamp()}`;

    sectionId = await createSection(request, await channelThemeCode(request), sectionName);
    createdIds.push(sectionId);
  });

  test.afterEach(async ({ request }) => {
    for (const id of createdIds) {
      await sendAdminRequest(request, ADMIN_APPEARANCE.SECTION(id), { method: 'DELETE' });
    }
  });

  test('should hide an unpublished section from the listing and the detail', async ({ request }) => {
    const rows = await listSections(request);

    expect(rows.find((row: any) => row.id === sectionId)).toBeUndefined();

    await expectDetail(await sendRestRequest(request, ENDPOINTS.SECTION(sectionId)), 404, MSG.GENERIC_NOT_FOUND);
  });

  test('should list a published section of the channel theme', async ({ request }) => {
    await publishSection(request, sectionId);

    const listed = (await listSections(request)).find((row: any) => row.id === sectionId);

    expect(listed, 'the published section is listed').toBeTruthy();
    expect(listed.name).toBe(sectionName);
    expect(listed.type).toBe('static_content');
  });

  test('should return a published section by id', async ({ request }) => {
    await publishSection(request, sectionId);

    const response = await sendRestRequest(request, ENDPOINTS.SECTION(sectionId));
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body.id).toBe(sectionId);
    expect(body.name).toBe(sectionName);
    expect(body.type).toBe('static_content');
  });

  test('should accept the locale parameter on the listing', async ({ request }) => {
    await publishSection(request, sectionId);

    const listed = (await listSections(request, { locale: 'en' })).find((row: any) => row.id === sectionId);

    expect(listed, 'the published section is listed for the en locale').toBeTruthy();
  });
});

test.describe('sections rest api without owned data', () => {
  test('should return 404 for a section that does not exist', async ({ request }) => {
    await expectDetail(await sendRestRequest(request, ENDPOINTS.SECTION(UNKNOWN_ID)), 404, MSG.GENERIC_NOT_FOUND);
  });

  test('should return the theme the channel runs', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.THEME);
    const body = await safeJson(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);

    const theme = themeOf(body);

    expect(typeof theme.code).toBe('string');
    expect(Array.isArray(theme.sectionTypes)).toBe(true);
  });
});
