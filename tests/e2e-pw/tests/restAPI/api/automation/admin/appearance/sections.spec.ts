import { test, expect, APIRequestContext, APIResponse } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_APPEARANCE } from '../../../../rest/endpoints/admin.appearance.endpoints';
import { ADMIN_SETTINGS } from '../../../../rest/endpoints/admin.settings.endpoints';

test.describe.configure({ timeout: 60_000 });

const DEFAULT_CHANNEL_ID = 1;

const UNKNOWN_ID = 99999999;

const MSG = {
  THEME_NOT_FOUND: 'Theme not found.',
  SECTION_NOT_FOUND: 'Section not found.',
  NAME_REQUIRED: 'The name field is required.',
  TYPE_INVALID: 'The selected type is invalid.',
  SECTION_IDS_REQUIRED: 'At least one section is required.',
  REORDER_INCOMPLETE: 'Reordering needs every section of the theme and channel, in the order they should render.',
  CREATED: 'Section created successfully.',
  UPDATED: 'Section updated successfully.',
  DRAFT_SAVED: 'Section changes staged successfully.',
  STATUS_STAGED: 'Section status staged successfully.',
  ORDER_STAGED: 'Section order staged successfully.',
  DUPLICATED: 'Section copied successfully.',
  DISCARDED: 'Section changes discarded successfully.',
};

interface Scope {
  themeCode: string;
  channelId: number;
}

function uniqueSuffix(): string {
  return `${Date.now().toString(36)}${Math.floor(Math.random() * 1_000_000).toString(36)}`;
}

function uniqueName(): string {
  return `e2e section ${uniqueSuffix()}`;
}

async function jsonOf(response: APIResponse): Promise<any> {
  const text = await response.text();

  return text === '' ? null : JSON.parse(text);
}

async function expectProblem(response: APIResponse, status: number, detail: string): Promise<void> {
  const body = await jsonOf(response);

  expect(response.status(), JSON.stringify(body)).toBe(status);
  expect(body.detail, JSON.stringify(body)).toBe(detail);
}

async function themeActiveOnDefaultChannel(request: APIRequestContext): Promise<string> {
  const response = await sendAdminRequest(request, ADMIN_APPEARANCE.THEMES);
  const body = await jsonOf(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);
  expect(Array.isArray(body), JSON.stringify(body)).toBe(true);

  const theme = body.find((row: any) => (row.activeOn ?? []).some((channel: any) => Number(channel.id) === DEFAULT_CHANNEL_ID));

  expect(theme, `no theme is active on channel ${DEFAULT_CHANNEL_ID}: ${JSON.stringify(body)}`).toBeTruthy();

  return theme.code;
}

async function createChannel(request: APIRequestContext): Promise<number> {
  const code = `e2e_sec_${uniqueSuffix()}`;

  const response = await sendAdminRequest(request, ADMIN_SETTINGS.CHANNELS, {
    method: 'POST',
    data: {
      code,
      name: `e2e sections ${code}`,
      description: 'e2e generated',
      hostname: `https://${code.replace(/_/g, '-')}.example.com`,
      locales: [1],
      currencies: [1],
      inventory_sources: [1],
      default_locale_id: 1,
      base_currency_id: 1,
      root_category_id: 1,
    },
  });
  const body = await jsonOf(response);

  expect(response.status(), JSON.stringify(body)).toBe(201);
  expect(typeof body.id).toBe('number');

  return body.id;
}

async function createSection(
  request: APIRequestContext,
  scope: Scope,
  type = 'static_content',
  name = uniqueName()
): Promise<any> {
  const response = await sendAdminRequest(request, ADMIN_APPEARANCE.THEME_SECTIONS(scope.themeCode), {
    method: 'POST',
    data: { name, type, channel: scope.channelId },
  });
  const body = await jsonOf(response);

  expect(response.status(), JSON.stringify(body)).toBe(201);
  expect(body.name).toBe(name);
  expect(body.type).toBe(type);
  expect(body.channelId).toBe(scope.channelId);
  expect(body.themeCode).toBe(scope.themeCode);

  return body;
}

async function readSection(request: APIRequestContext, id: number): Promise<any> {
  const response = await sendAdminRequest(request, ADMIN_APPEARANCE.SECTION(id));
  const body = await jsonOf(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);
  expect(body.id).toBe(id);

  return body;
}

async function listSectionIds(request: APIRequestContext, scope: Scope): Promise<number[]> {
  const response = await sendAdminRequest(request, ADMIN_APPEARANCE.THEME_SECTIONS(scope.themeCode), {
    params: { channel: String(scope.channelId) },
  });
  const body = await jsonOf(response);

  expect(response.status(), JSON.stringify(body)).toBe(200);
  expect(Array.isArray(body), JSON.stringify(body)).toBe(true);

  return body.map((row: any) => row.id);
}

async function deleteSection(request: APIRequestContext, id: number): Promise<void> {
  await sendAdminRequest(request, ADMIN_APPEARANCE.SECTION(id), { method: 'DELETE' });
}

async function deleteChannel(request: APIRequestContext, id: number): Promise<void> {
  await sendAdminRequest(request, ADMIN_SETTINGS.CHANNEL(id), { method: 'DELETE' });
}

test.describe('admin appearance sections rest api', () => {
  let themeCode: string;
  let createdSectionIds: number[];
  let createdChannelIds: number[];

  test.beforeEach(async ({ request }) => {
    themeCode = await themeActiveOnDefaultChannel(request);
    createdSectionIds = [];
    createdChannelIds = [];
  });

  test.afterEach(async ({ request }) => {
    for (const id of createdSectionIds) {
      await deleteSection(request, id);
    }

    for (const id of createdChannelIds) {
      await deleteChannel(request, id);
    }
  });

  async function ownSection(request: APIRequestContext, scope: Scope, type = 'static_content'): Promise<any> {
    const section = await createSection(request, scope, type);

    createdSectionIds.push(section.id);

    return section;
  }

  async function ownChannelScope(request: APIRequestContext): Promise<Scope> {
    const channelId = await createChannel(request);

    createdChannelIds.push(channelId);

    return { themeCode, channelId };
  }

  test('listing returns the sections of a channel in render order with the footer last', async ({ request }) => {
    const scope = await ownChannelScope(request);
    const footer = await ownSection(request, scope, 'footer_links');
    const first = await ownSection(request, scope);
    const second = await ownSection(request, scope);

    const response = await sendAdminRequest(request, ADMIN_APPEARANCE.THEME_SECTIONS(themeCode), {
      params: { channel: String(scope.channelId) },
    });
    const body = await jsonOf(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body.map((row: any) => row.id)).toEqual([first.id, second.id, footer.id]);
    expect(body.map((row: any) => row.isPinned)).toEqual([false, false, true]);
  });

  test('listing an unknown theme returns 404', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_APPEARANCE.THEME_SECTIONS('no_such_theme_e2e'));

    await expectProblem(response, 404, MSG.THEME_NOT_FOUND);
  });

  test('create returns an unpublished section staged to be switched on', async ({ request }) => {
    const created = await ownSection(request, await ownChannelScope(request));

    expect(created.message).toBe(MSG.CREATED);
    expect(created.status).toBe(0);
    expect(created.draftStatus).toBe(true);
    expect(created.hasDraft).toBe(true);
    expect(created.isPinned).toBe(false);

    const stored = await readSection(request, created.id);

    expect(stored.name).toBe(created.name);
    expect(stored.status).toBe(0);
    expect(stored.draftStatus).toBe(true);
  });

  test('delete removes the section', async ({ request }) => {
    const created = await createSection(request, await ownChannelScope(request));

    const response = await sendAdminRequest(request, ADMIN_APPEARANCE.SECTION(created.id), { method: 'DELETE' });

    expect(response.status()).toBe(204);

    await expectProblem(await sendAdminRequest(request, ADMIN_APPEARANCE.SECTION(created.id)), 404, MSG.SECTION_NOT_FOUND);
  });

  test('create without a name returns 422 and creates nothing', async ({ request }) => {
    const scope = await ownChannelScope(request);

    const response = await sendAdminRequest(request, ADMIN_APPEARANCE.THEME_SECTIONS(themeCode), {
      method: 'POST',
      data: { type: 'static_content', channel: scope.channelId },
    });

    await expectProblem(response, 422, MSG.NAME_REQUIRED);
    expect(await listSectionIds(request, scope)).toEqual([]);
  });

  test('create with an unknown type returns 422 and creates nothing', async ({ request }) => {
    const scope = await ownChannelScope(request);

    const response = await sendAdminRequest(request, ADMIN_APPEARANCE.THEME_SECTIONS(themeCode), {
      method: 'POST',
      data: { name: uniqueName(), type: 'definitely_not_a_type', channel: scope.channelId },
    });

    await expectProblem(response, 422, MSG.TYPE_INVALID);
    expect(await listSectionIds(request, scope)).toEqual([]);
  });

  test('detail of an unknown id returns 404', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_APPEARANCE.SECTION(UNKNOWN_ID));

    await expectProblem(response, 404, MSG.SECTION_NOT_FOUND);
  });

  test('update writes the published values', async ({ request }) => {
    const created = await ownSection(request, await ownChannelScope(request));
    const renamed = `${created.name} renamed`;

    const response = await sendAdminRequest(request, ADMIN_APPEARANCE.SECTION(created.id), {
      method: 'PUT',
      data: {
        name: renamed,
        type: 'static_content',
        sortOrder: 99,
        channelId: created.channelId,
        themeCode,
        locale: 'en',
        options: { html: '<p>Hi</p>', css: '' },
      },
    });
    const body = await jsonOf(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body.message).toBe(MSG.UPDATED);
    expect(body.name).toBe(renamed);
    expect(body.sortOrder).toBe(99);

    const stored = await readSection(request, created.id);

    expect(stored.name).toBe(renamed);
    expect(stored.sortOrder).toBe(99);
  });

  test('a draft is staged without publishing and is dropped by discard', async ({ request }) => {
    const scope = await ownChannelScope(request);
    const created = await ownSection(request, scope);

    const draft = await sendAdminRequest(request, ADMIN_APPEARANCE.SECTION_DRAFT(created.id), {
      method: 'POST',
      data: { options: { html: '<p>staged</p>', css: '' }, locale: 'en' },
    });
    const draftBody = await jsonOf(draft);

    expect(draft.status(), JSON.stringify(draftBody)).toBe(201);
    expect(draftBody.sectionId).toBe(created.id);
    expect(draftBody.locale).toBe('en');
    expect(draftBody.hasDraft).toBe(true);
    expect(draftBody.message).toBe(MSG.DRAFT_SAVED);

    const status = await sendAdminRequest(request, ADMIN_APPEARANCE.SECTION_STATUS(created.id), {
      method: 'POST',
      data: { status: true },
    });
    const statusBody = await jsonOf(status);

    expect(status.status(), JSON.stringify(statusBody)).toBe(201);
    expect(statusBody.sectionId).toBe(created.id);
    expect(statusBody.draftStatus).toBe(true);
    expect(statusBody.message).toBe(MSG.STATUS_STAGED);

    const staged = await readSection(request, created.id);

    expect(staged.status).toBe(0);
    expect(staged.hasDraft).toBe(true);

    const discard = await sendAdminRequest(request, ADMIN_APPEARANCE.SECTIONS_DISCARD(themeCode), {
      method: 'POST',
      data: { channel: scope.channelId },
    });
    const discardBody = await jsonOf(discard);

    expect(discard.status(), JSON.stringify(discardBody)).toBe(201);
    expect(discardBody.themeCode).toBe(themeCode);
    expect(discardBody.channelId).toBe(scope.channelId);
    expect(discardBody.sectionIds).toEqual([created.id]);
    expect(discardBody.message).toBe(MSG.DISCARDED);

    const discarded = await readSection(request, created.id);

    expect(discarded.status).toBe(0);
    expect(discarded.draftStatus).toBeNull();
    expect(discarded.hasDraft).toBe(false);
  });

  test('reorder stages the order and keeps the footer last', async ({ request }) => {
    const scope = await ownChannelScope(request);
    const footer = await ownSection(request, scope, 'footer_links');
    const first = await ownSection(request, scope);
    const second = await ownSection(request, scope);

    expect(await listSectionIds(request, scope)).toEqual([first.id, second.id, footer.id]);

    const response = await sendAdminRequest(request, ADMIN_APPEARANCE.SECTIONS_REORDER, {
      method: 'POST',
      data: { sectionIds: [footer.id, second.id, first.id] },
    });
    const body = await jsonOf(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.sectionIds).toEqual([second.id, first.id, footer.id]);
    expect(body.message).toBe(MSG.ORDER_STAGED);

    expect(await listSectionIds(request, scope)).toEqual([first.id, second.id, footer.id]);
    expect((await readSection(request, second.id)).draftSortOrder).toBe(1);
    expect((await readSection(request, first.id)).draftSortOrder).toBe(2);
    expect((await readSection(request, footer.id)).draftSortOrder).toBeNull();
  });

  test('reorder that leaves out a section of the channel returns 422', async ({ request }) => {
    const scope = await ownChannelScope(request);
    const first = await ownSection(request, scope);

    await ownSection(request, scope);

    const response = await sendAdminRequest(request, ADMIN_APPEARANCE.SECTIONS_REORDER, {
      method: 'POST',
      data: { sectionIds: [first.id] },
    });

    await expectProblem(response, 422, MSG.REORDER_INCOMPLETE);
  });

  test('reorder with no ids returns 422', async ({ request }) => {
    const response = await sendAdminRequest(request, ADMIN_APPEARANCE.SECTIONS_REORDER, {
      method: 'POST',
      data: { sectionIds: [] },
    });

    await expectProblem(response, 422, MSG.SECTION_IDS_REQUIRED);
  });

  test('duplicate returns an unpublished copy below the source', async ({ request }) => {
    const created = await ownSection(request, await ownChannelScope(request));

    const response = await sendAdminRequest(request, ADMIN_APPEARANCE.SECTION_DUPLICATE(created.id), { method: 'POST' });
    const body = await jsonOf(response);

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(body.sourceId).toBe(created.id);
    expect(typeof body.sectionId).toBe('number');
    expect(body.sectionId).not.toBe(created.id);

    createdSectionIds.push(body.sectionId);

    expect(body.name).toBe(`${created.name} Copy`);
    expect(body.type).toBe('static_content');
    expect(body.message).toBe(MSG.DUPLICATED);

    const copy = await readSection(request, body.sectionId);

    expect(copy.status).toBe(0);
    expect(copy.draftStatus).toBe(true);
    expect(copy.sortOrder).toBe((await readSection(request, created.id)).sortOrder + 1);
  });

  test('fields returns the schema for the section type', async ({ request }) => {
    const created = await ownSection(request, await ownChannelScope(request));

    const response = await sendAdminRequest(request, ADMIN_APPEARANCE.SECTION_FIELDS(created.id), { params: { locale: 'en' } });
    const body = await jsonOf(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body.sectionId).toBe(created.id);
    expect(body.type).toBe('static_content');
    expect(body.locale).toBe('en');
    expect(body.schema).toEqual(expect.any(Object));
  });

  test('preview shows a staged section as switched on', async ({ request }) => {
    const scope = await ownChannelScope(request);
    const created = await ownSection(request, scope);

    const response = await sendAdminRequest(request, ADMIN_APPEARANCE.SECTIONS_PREVIEW(themeCode), {
      params: { channel: String(scope.channelId), locale: 'en' },
    });
    const body = await jsonOf(response);

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body.themeCode).toBe(themeCode);
    expect(body.channelId).toBe(scope.channelId);
    expect(body.locale).toBe('en');
    expect(body.sections.map((section: any) => section.id)).toEqual([created.id]);
    expect(body.sections[0].status).toBe(true);
    expect(body.sections[0].hasDraft).toBe(true);
  });
});
