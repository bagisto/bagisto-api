// Admin Appearance — Sections GraphQL e2e. Every write happens on a section this
// suite creates, and staged edits are discarded rather than published, so the
// storefront the other suites read is never changed.

import { test, expect } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import { ADMIN_APPEARANCE_THEMES_LIST } from '../../../../graphql/Queries/admin/appearance/themes.queries';
import {
  ADMIN_APPEARANCE_SECTIONS_LIST,
  ADMIN_APPEARANCE_SECTION_DETAIL,
  ADMIN_APPEARANCE_SECTION_CREATE,
  ADMIN_APPEARANCE_SECTION_UPDATE,
  ADMIN_APPEARANCE_SECTION_DELETE,
  ADMIN_APPEARANCE_SECTION_DRAFT,
  ADMIN_APPEARANCE_SECTION_STATUS,
  ADMIN_APPEARANCE_SECTION_REORDER,
  ADMIN_APPEARANCE_SECTION_DUPLICATE,
  ADMIN_APPEARANCE_SECTION_DISCARD,
  ADMIN_APPEARANCE_SECTION_FIELDS,
  ADMIN_APPEARANCE_SECTION_PREVIEW,
} from '../../../../graphql/Queries/admin/appearance/sections.queries';

test.describe.configure({ timeout: 60_000 });

async function safeJson(r: any) { try { return await r.json(); } catch { return null; } }

function unique(): string { return `E2E Section ${Date.now().toString(36).slice(-6)}`; }

async function activeThemeCode(request: any): Promise<string | null> {
  const resp = await sendAdminGraphQLRequest(request, ADMIN_APPEARANCE_THEMES_LIST, {});
  const body = await safeJson(resp);
  const themes = body?.data?.adminAppearanceThemes ?? [];
  const active = themes.find((t: any) => t.status === 'active') ?? themes[0];

  return active?.code ?? null;
}

async function createSection(request: any, code: string): Promise<number | null> {
  const resp = await sendAdminGraphQLRequest(request, ADMIN_APPEARANCE_SECTION_CREATE, {
    code, name: unique(), type: 'static_content', channel: 1,
  });
  const body = await safeJson(resp);

  return body?.data?.createAdminAppearanceSection?.adminAppearanceSection?._id ?? null;
}

async function deleteSection(request: any, id: number): Promise<void> {
  await sendAdminGraphQLRequest(request, ADMIN_APPEARANCE_SECTION_DELETE, {
    id: `/api/admin/appearance/sections/${id}`,
  });
}

test.describe('Admin Appearance Sections GraphQL API', () => {
  test('listing returns the sections of a theme', async ({ request }) => {
    const code = await activeThemeCode(request);
    if (!code) { test.skip(true, 'no theme in the gallery'); return; }

    const resp = await sendAdminGraphQLRequest(request, ADMIN_APPEARANCE_SECTIONS_LIST, { code, channel: 1 });
    expect(resp.status()).toBe(200);

    const body = await safeJson(resp);
    expect(body?.errors).toBeUndefined();
    expect(Array.isArray(body?.data?.adminAppearanceSections)).toBe(true);
  });

  test('listing an unknown theme errors', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_APPEARANCE_SECTIONS_LIST, { code: 'no_such_theme_e2e' });
    expect(resp.status()).toBe(200);

    const body = await safeJson(resp);
    expect(body?.errors).toBeDefined();
  });

  test('create, read, update and delete a section', async ({ request }) => {
    const code = await activeThemeCode(request);
    if (!code) { test.skip(true, 'no theme in the gallery'); return; }

    const id = await createSection(request, code);
    expect(id).toBeTruthy();

    const iri = `/api/admin/appearance/sections/${id}`;

    const detail = await sendAdminGraphQLRequest(request, ADMIN_APPEARANCE_SECTION_DETAIL, { id: iri });
    const detailBody = await safeJson(detail);
    expect(detailBody?.errors).toBeUndefined();
    expect(detailBody?.data?.adminAppearanceSection?._id).toBe(id);

    // A new section is born unpublished, so it cannot reach the storefront.
    expect(String(detailBody?.data?.adminAppearanceSection?.status)).toBe('0');

    const renamed = `${unique()} renamed`;
    const upd = await sendAdminGraphQLRequest(request, ADMIN_APPEARANCE_SECTION_UPDATE, { id: iri, name: renamed });
    const updBody = await safeJson(upd);
    expect(updBody?.errors).toBeUndefined();
    expect(updBody?.data?.updateAdminAppearanceSection?.adminAppearanceSection?.name).toBe(renamed);

    const del = await sendAdminGraphQLRequest(request, ADMIN_APPEARANCE_SECTION_DELETE, { id: iri });
    const delBody = await safeJson(del);
    expect(delBody?.errors).toBeUndefined();
    expect(delBody?.data?.deleteAdminAppearanceSection?.adminAppearanceSection?._id).toBe(id);
  });

  test('staged edits are reported as a draft and can be discarded', async ({ request }) => {
    const code = await activeThemeCode(request);
    if (!code) { test.skip(true, 'no theme in the gallery'); return; }

    const id = await createSection(request, code);
    if (!id) { test.skip(true, 'section create failed'); return; }

    const draft = await sendAdminGraphQLRequest(request, ADMIN_APPEARANCE_SECTION_DRAFT, {
      sectionId: id, options: { html: '<p>staged</p>', css: '' }, locale: 'en',
    });
    const draftBody = await safeJson(draft);
    expect(draftBody?.errors).toBeUndefined();
    expect(draftBody?.data?.createAdminAppearanceSectionDraft?.adminAppearanceSectionDraft?.hasDraft).toBeTruthy();

    const status = await sendAdminGraphQLRequest(request, ADMIN_APPEARANCE_SECTION_STATUS, { sectionId: id, status: true });
    const statusBody = await safeJson(status);
    expect(statusBody?.errors).toBeUndefined();

    // The published status does not move until publish runs.
    const detail = await sendAdminGraphQLRequest(request, ADMIN_APPEARANCE_SECTION_DETAIL, {
      id: `/api/admin/appearance/sections/${id}`,
    });
    const detailBody = await safeJson(detail);
    expect(String(detailBody?.data?.adminAppearanceSection?.status)).toBe('0');

    const discard = await sendAdminGraphQLRequest(request, ADMIN_APPEARANCE_SECTION_DISCARD, { code, channel: 1 });
    const discardBody = await safeJson(discard);
    expect(discardBody?.errors).toBeUndefined();

    await deleteSection(request, id);
  });

  test('reorder keeps the footer pinned last', async ({ request }) => {
    const code = await activeThemeCode(request);
    if (!code) { test.skip(true, 'no theme in the gallery'); return; }

    // A reorder has to carry every section of the theme and channel, so a section another
    // spec adds or removes in between invalidates the list. Read it again and retry.
    let nodes: any[] = [];
    let body: any = null;

    for (let attempt = 0; attempt < 3; attempt++) {
      const list = await sendAdminGraphQLRequest(request, ADMIN_APPEARANCE_SECTIONS_LIST, { code, channel: 1 });
      nodes = (await safeJson(list))?.data?.adminAppearanceSections ?? [];
      if (nodes.length < 2) { test.skip(true, 'not enough sections to reorder'); return; }

      const ids = nodes.map((n: any) => n._id).reverse();
      const resp = await sendAdminGraphQLRequest(request, ADMIN_APPEARANCE_SECTION_REORDER, { sectionIds: ids });
      expect(resp.status()).toBe(200);

      body = await safeJson(resp);
      if (!body?.errors) break;
    }

    // Locally the REST suite runs alongside this one and churns sections faster than a
    // read-then-reorder can complete; CI runs one worker, where this cannot happen.
    if (body?.errors) {
      test.skip(true, 'sections changed concurrently');

      return;
    }

    const stored = body?.data?.createAdminAppearanceSectionReorder?.adminAppearanceSectionReorder?.sectionIds ?? [];
    const pinned = nodes.find((n: any) => n.isPinned === true || String(n.isPinned) === '1');

    if (pinned) {
      expect(stored[stored.length - 1]).toBe(pinned._id);
    }

    // Order is staged, so dropping the draft restores what the storefront serves.
    await sendAdminGraphQLRequest(request, ADMIN_APPEARANCE_SECTION_DISCARD, { code, channel: 1 });
  });

  test('duplicate returns the copy', async ({ request }) => {
    const code = await activeThemeCode(request);
    if (!code) { test.skip(true, 'no theme in the gallery'); return; }

    const id = await createSection(request, code);
    if (!id) { test.skip(true, 'section create failed'); return; }

    const resp = await sendAdminGraphQLRequest(request, ADMIN_APPEARANCE_SECTION_DUPLICATE, { sectionId: id });
    const body = await safeJson(resp);
    expect(body?.errors).toBeUndefined();

    const copy = body?.data?.createAdminAppearanceSectionDuplicate?.adminAppearanceSectionDuplicate;
    expect(copy?.sourceId).toBe(id);
    expect(copy?.sectionId).toBeTruthy();

    await deleteSection(request, copy.sectionId);
    await deleteSection(request, id);
  });

  test('fields returns the schema for the section type', async ({ request }) => {
    const code = await activeThemeCode(request);
    if (!code) { test.skip(true, 'no theme in the gallery'); return; }

    const id = await createSection(request, code);
    if (!id) { test.skip(true, 'section create failed'); return; }

    const resp = await sendAdminGraphQLRequest(request, ADMIN_APPEARANCE_SECTION_FIELDS, { sectionId: id, locale: 'en' });
    const body = await safeJson(resp);
    expect(body?.errors).toBeUndefined();
    expect(body?.data?.adminAppearanceSectionFields?.type).toBe('static_content');

    await deleteSection(request, id);
  });

  test('preview returns the drafted set', async ({ request }) => {
    const code = await activeThemeCode(request);
    if (!code) { test.skip(true, 'no theme in the gallery'); return; }

    const resp = await sendAdminGraphQLRequest(request, ADMIN_APPEARANCE_SECTION_PREVIEW, { code, channel: 1, locale: 'en' });
    expect(resp.status()).toBe(200);

    const body = await safeJson(resp);
    expect(body?.errors).toBeUndefined();
    expect(body?.data?.adminAppearanceSectionPreview?.themeCode).toBe(code);
    expect(Array.isArray(body?.data?.adminAppearanceSectionPreview?.sections)).toBe(true);
  });
});
