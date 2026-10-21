import { test, expect, APIRequestContext, APIResponse } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import { ADMIN_APPEARANCE_THEMES_LIST } from '../../../../graphql/Queries/admin/appearance/themes.queries';
import {
  ADMIN_APPEARANCE_SECTIONS_LIST,
  ADMIN_APPEARANCE_SECTION_DELETE,
  ADMIN_APPEARANCE_SECTION_DRAFT,
  ADMIN_APPEARANCE_SECTION_STATUS,
  ADMIN_APPEARANCE_SECTION_REORDER,
  ADMIN_APPEARANCE_SECTION_DUPLICATE,
  ADMIN_APPEARANCE_SECTION_DISCARD,
  ADMIN_APPEARANCE_SECTION_FIELDS,
  ADMIN_APPEARANCE_SECTION_PREVIEW,
} from '../../../../graphql/Queries/admin/appearance/sections.queries';
import { sendAdminRequest } from '../../../../../restAPI/rest/helpers/adminClient';
import { ADMIN_SETTINGS } from '../../../../../restAPI/rest/endpoints/admin.settings.endpoints';

test.describe.configure({ timeout: 60_000 });

const DEFAULT_CHANNEL_ID = 1;

const UNKNOWN_ID = 99999999;

const MSG = {
  THEME_NOT_FOUND: 'Theme not found.',
  SECTION_NOT_FOUND: 'Section not found.',
  TYPE_INVALID: 'The selected type is invalid.',
  REORDER_INCOMPLETE: 'Reordering needs every section of the theme and channel, in the order they should render.',
  CREATED: 'Section created successfully.',
  UPDATED: 'Section updated successfully.',
  DELETED: 'Section deleted successfully.',
  DRAFT_SAVED: 'Section changes staged successfully.',
  STATUS_STAGED: 'Section status staged successfully.',
  ORDER_STAGED: 'Section order staged successfully.',
  DUPLICATED: 'Section copied successfully.',
  DISCARDED: 'Section changes discarded successfully.',
};

const SECTION_CREATE = `
  mutation createAdminAppearanceSection($code: String!, $name: String!, $type: String!, $channel: Int) {
    createAdminAppearanceSection(input: { code: $code, name: $name, type: $type, channel: $channel }) {
      adminAppearanceSection {
        _id
        name
        type
        themeCode
        channelId
        status
        draftStatus
        hasDraft
        isPinned
        message
      }
    }
  }
`;

const SECTION_DETAIL = `
  query adminAppearanceSection($id: ID!) {
    adminAppearanceSection(id: $id) {
      _id
      name
      type
      channelId
      sortOrder
      status
      draftStatus
      draftSortOrder
      hasDraft
    }
  }
`;

const SECTION_UPDATE = `
  mutation updateAdminAppearanceSection($id: ID!, $name: String, $sortOrder: Int) {
    updateAdminAppearanceSection(input: { id: $id, name: $name, sortOrder: $sortOrder }) {
      adminAppearanceSection {
        _id
        name
        sortOrder
        message
      }
    }
  }
`;

interface Scope {
  themeCode: string;
  channelId: number;
}

function uniqueSuffix(): string {
  return `${Date.now().toString(36)}${Math.floor(Math.random() * 1_000_000).toString(36)}`;
}

function uniqueName(): string {
  return `e2e gql section ${uniqueSuffix()}`;
}

function sectionIri(id: number): string {
  return `/api/admin/appearance/sections/${id}`;
}

async function gql(request: APIRequestContext, query: string, variables: Record<string, any>): Promise<any> {
  const response: APIResponse = await sendAdminGraphQLRequest(request, query, variables);
  const body = JSON.parse(await response.text());

  expect(response.status(), JSON.stringify(body)).toBe(200);

  return body;
}

async function gqlData(request: APIRequestContext, query: string, variables: Record<string, any>): Promise<any> {
  const body = await gql(request, query, variables);

  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();

  return body.data;
}

async function expectGqlError(request: APIRequestContext, query: string, variables: Record<string, any>, message: string): Promise<any> {
  const body = await gql(request, query, variables);

  expect(body.errors?.[0]?.message, JSON.stringify(body)).toBe(message);

  return body.data;
}

async function themeActiveOnDefaultChannel(request: APIRequestContext): Promise<string> {
  const data = await gqlData(request, ADMIN_APPEARANCE_THEMES_LIST, {});
  const theme = data.adminAppearanceThemes.find((row: any) =>
    (row.activeOn ?? []).some((channel: any) => Number(channel.id) === DEFAULT_CHANNEL_ID)
  );

  expect(theme, `no theme is active on channel ${DEFAULT_CHANNEL_ID}: ${JSON.stringify(data)}`).toBeTruthy();

  return theme.code;
}

async function createChannel(request: APIRequestContext): Promise<number> {
  const code = `e2e_gsec_${uniqueSuffix()}`;

  const response = await sendAdminRequest(request, ADMIN_SETTINGS.CHANNELS, {
    method: 'POST',
    data: {
      code,
      name: `e2e gql sections ${code}`,
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
  const body = JSON.parse(await response.text());

  expect(response.status(), JSON.stringify(body)).toBe(201);
  expect(typeof body.id).toBe('number');

  return body.id;
}

async function listSectionIds(request: APIRequestContext, scope: Scope): Promise<number[]> {
  const data = await gqlData(request, ADMIN_APPEARANCE_SECTIONS_LIST, { code: scope.themeCode, channel: scope.channelId });

  return data.adminAppearanceSections.map((row: any) => row._id);
}

async function readSection(request: APIRequestContext, id: number): Promise<any> {
  const data = await gqlData(request, SECTION_DETAIL, { id: sectionIri(id) });

  expect(data.adminAppearanceSection._id).toBe(id);

  return data.adminAppearanceSection;
}

test.describe('admin appearance sections graphql api', () => {
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
      await sendAdminGraphQLRequest(request, ADMIN_APPEARANCE_SECTION_DELETE, { id: sectionIri(id) });
    }

    for (const id of createdChannelIds) {
      await sendAdminRequest(request, ADMIN_SETTINGS.CHANNEL(id), { method: 'DELETE' });
    }
  });

  async function ownChannelScope(request: APIRequestContext): Promise<Scope> {
    const channelId = await createChannel(request);

    createdChannelIds.push(channelId);

    return { themeCode, channelId };
  }

  async function createSection(request: APIRequestContext, scope: Scope, type = 'static_content'): Promise<any> {
    const name = uniqueName();
    const data = await gqlData(request, SECTION_CREATE, { code: scope.themeCode, name, type, channel: scope.channelId });
    const section = data.createAdminAppearanceSection.adminAppearanceSection;

    expect(typeof section._id).toBe('number');
    expect(section.name).toBe(name);
    expect(section.type).toBe(type);
    expect(section.channelId).toBe(scope.channelId);
    expect(section.themeCode).toBe(scope.themeCode);

    return section;
  }

  async function ownSection(request: APIRequestContext, scope: Scope, type = 'static_content'): Promise<any> {
    const section = await createSection(request, scope, type);

    createdSectionIds.push(section._id);

    return section;
  }

  test('listing returns the sections of a channel in render order with the footer last', async ({ request }) => {
    const scope = await ownChannelScope(request);
    const footer = await ownSection(request, scope, 'footer_links');
    const first = await ownSection(request, scope);
    const second = await ownSection(request, scope);

    const data = await gqlData(request, ADMIN_APPEARANCE_SECTIONS_LIST, { code: themeCode, channel: scope.channelId });

    expect(data.adminAppearanceSections.map((row: any) => row._id)).toEqual([first._id, second._id, footer._id]);
    expect(data.adminAppearanceSections.map((row: any) => String(row.isPinned))).toEqual(['0', '0', '1']);
  });

  test('listing an unknown theme reports the theme as not found', async ({ request }) => {
    const data = await expectGqlError(request, ADMIN_APPEARANCE_SECTIONS_LIST, { code: 'no_such_theme_e2e' }, MSG.THEME_NOT_FOUND);

    expect(data?.adminAppearanceSections ?? null).toBeNull();
  });

  test('create, read, update and delete a section', async ({ request }) => {
    const scope = await ownChannelScope(request);
    const created = await createSection(request, scope);

    expect(created.message).toBe(MSG.CREATED);
    expect(created.status).toBe(0);
    expect(created.draftStatus).toBe(true);
    expect(String(created.hasDraft)).toBe('1');
    expect(String(created.isPinned)).toBe('0');

    const stored = await readSection(request, created._id);

    expect(stored.name).toBe(created.name);
    expect(stored.status).toBe(0);

    const renamed = `${created.name} renamed`;
    const updated = (await gqlData(request, SECTION_UPDATE, { id: sectionIri(created._id), name: renamed, sortOrder: 99 }))
      .updateAdminAppearanceSection.adminAppearanceSection;

    expect(updated._id).toBe(created._id);
    expect(updated.name).toBe(renamed);
    expect(updated.sortOrder).toBe(99);
    expect(updated.message).toBe(MSG.UPDATED);

    const reread = await readSection(request, created._id);

    expect(reread.name).toBe(renamed);
    expect(reread.sortOrder).toBe(99);

    const deleted = (await gqlData(request, ADMIN_APPEARANCE_SECTION_DELETE, { id: sectionIri(created._id) }))
      .deleteAdminAppearanceSection.adminAppearanceSection;

    expect(deleted._id).toBe(created._id);
    expect(deleted.message).toBe(MSG.DELETED);

    await expectGqlError(request, SECTION_DETAIL, { id: sectionIri(created._id) }, MSG.SECTION_NOT_FOUND);
    expect(await listSectionIds(request, scope)).toEqual([]);
  });

  test('create with an unknown type is rejected and creates nothing', async ({ request }) => {
    const scope = await ownChannelScope(request);

    await expectGqlError(
      request,
      SECTION_CREATE,
      { code: themeCode, name: uniqueName(), type: 'definitely_not_a_type', channel: scope.channelId },
      MSG.TYPE_INVALID
    );

    expect(await listSectionIds(request, scope)).toEqual([]);
  });

  test('detail of an unknown id reports the section as not found', async ({ request }) => {
    await expectGqlError(request, SECTION_DETAIL, { id: sectionIri(UNKNOWN_ID) }, MSG.SECTION_NOT_FOUND);
  });

  test('staged edits are reported as a draft and can be discarded', async ({ request }) => {
    const scope = await ownChannelScope(request);
    const created = await ownSection(request, scope);

    const draft = (await gqlData(request, ADMIN_APPEARANCE_SECTION_DRAFT, {
      sectionId: created._id, options: { html: '<p>staged</p>', css: '' }, locale: 'en',
    })).createAdminAppearanceSectionDraft.adminAppearanceSectionDraft;

    expect(draft.sectionId).toBe(created._id);
    expect(draft.locale).toBe('en');
    expect(draft.hasDraft).toBe(true);
    expect(draft.message).toBe(MSG.DRAFT_SAVED);

    const status = (await gqlData(request, ADMIN_APPEARANCE_SECTION_STATUS, { sectionId: created._id, status: true }))
      .createAdminAppearanceSectionStatus.adminAppearanceSectionStatus;

    expect(status.sectionId).toBe(created._id);
    expect(status.draftStatus).toBe(true);
    expect(status.hasDraft).toBe(true);
    expect(status.message).toBe(MSG.STATUS_STAGED);

    expect((await readSection(request, created._id)).status).toBe(0);

    const discard = (await gqlData(request, ADMIN_APPEARANCE_SECTION_DISCARD, { code: themeCode, channel: scope.channelId }))
      .createAdminAppearanceSectionDiscard.adminAppearanceSectionDiscard;

    expect(discard.themeCode).toBe(themeCode);
    expect(discard.channelId).toBe(scope.channelId);
    expect(discard.sectionIds).toEqual([created._id]);
    expect(discard.message).toBe(MSG.DISCARDED);

    const discarded = await readSection(request, created._id);

    expect(discarded.status).toBe(0);
    expect(discarded.draftStatus).toBeNull();
    expect(String(discarded.hasDraft)).toBe('0');
  });

  test('reorder stages the order and keeps the footer pinned last', async ({ request }) => {
    const scope = await ownChannelScope(request);
    const footer = await ownSection(request, scope, 'footer_links');
    const first = await ownSection(request, scope);
    const second = await ownSection(request, scope);

    expect(await listSectionIds(request, scope)).toEqual([first._id, second._id, footer._id]);

    const reorder = (await gqlData(request, ADMIN_APPEARANCE_SECTION_REORDER, { sectionIds: [footer._id, second._id, first._id] }))
      .createAdminAppearanceSectionReorder.adminAppearanceSectionReorder;

    expect(reorder.sectionIds).toEqual([second._id, first._id, footer._id]);
    expect(reorder.message).toBe(MSG.ORDER_STAGED);

    expect(await listSectionIds(request, scope)).toEqual([first._id, second._id, footer._id]);
    expect((await readSection(request, second._id)).draftSortOrder).toBe(1);
    expect((await readSection(request, first._id)).draftSortOrder).toBe(2);
    expect((await readSection(request, footer._id)).draftSortOrder).toBeNull();
  });

  test('reorder that leaves out a section of the channel is rejected', async ({ request }) => {
    const scope = await ownChannelScope(request);
    const first = await ownSection(request, scope);

    await ownSection(request, scope);

    await expectGqlError(request, ADMIN_APPEARANCE_SECTION_REORDER, { sectionIds: [first._id] }, MSG.REORDER_INCOMPLETE);
  });

  test('duplicate returns an unpublished copy', async ({ request }) => {
    const scope = await ownChannelScope(request);
    const created = await ownSection(request, scope);

    const copy = (await gqlData(request, ADMIN_APPEARANCE_SECTION_DUPLICATE, { sectionId: created._id }))
      .createAdminAppearanceSectionDuplicate.adminAppearanceSectionDuplicate;

    expect(copy.sourceId).toBe(created._id);
    expect(typeof copy.sectionId).toBe('number');
    expect(copy.sectionId).not.toBe(created._id);

    createdSectionIds.push(copy.sectionId);

    expect(copy.name).toBe(`${created.name} Copy`);
    expect(copy.type).toBe('static_content');
    expect(copy.message).toBe(MSG.DUPLICATED);

    const stored = await readSection(request, copy.sectionId);

    expect(stored.status).toBe(0);
    expect(stored.draftStatus).toBe(true);
    expect(await listSectionIds(request, scope)).toEqual([created._id, copy.sectionId]);
  });

  test('fields returns the schema for the section type', async ({ request }) => {
    const scope = await ownChannelScope(request);
    const created = await ownSection(request, scope);

    const fields = (await gqlData(request, ADMIN_APPEARANCE_SECTION_FIELDS, { sectionId: created._id, locale: 'en' }))
      .adminAppearanceSectionFields;

    expect(fields.sectionId).toBe(created._id);
    expect(fields.type).toBe('static_content');
    expect(fields.locale).toBe('en');
    expect(fields.schema).toEqual(expect.any(Object));
  });

  test('preview shows a staged section as switched on', async ({ request }) => {
    const scope = await ownChannelScope(request);
    const created = await ownSection(request, scope);

    const preview = (await gqlData(request, ADMIN_APPEARANCE_SECTION_PREVIEW, { code: themeCode, channel: scope.channelId, locale: 'en' }))
      .adminAppearanceSectionPreview;

    expect(preview.themeCode).toBe(themeCode);
    expect(preview.channelId).toBe(scope.channelId);
    expect(preview.locale).toBe('en');
    expect(preview.sections.map((section: any) => section.id)).toEqual([created._id]);
    expect(preview.sections[0].status).toBe(true);
    expect(preview.sections[0].hasDraft).toBe(true);
  });
});
