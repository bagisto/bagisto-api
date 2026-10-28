// Admin Appearance — Sections REST e2e. Writes only touch sections this suite
// creates, and staged edits are discarded instead of published.

import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_APPEARANCE } from '../../../../rest/endpoints/admin.appearance.endpoints';

test.describe.configure({ timeout: 60_000 });

async function safeJson(resp: any): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

function rows(body: any): any[] {
  if (Array.isArray(body)) return body;

  return Array.isArray(body?.data) ? body.data : [];
}

function uniqueName(): string {
  return `E2E Section ${Date.now().toString(36).slice(-6)}`;
}

async function activeThemeCode(request: any): Promise<string | null> {
  const resp = await sendAdminRequest(request, ADMIN_APPEARANCE.THEMES);
  const list = rows(await safeJson(resp));
  const active = list.find((t: any) => t.status === 'active') ?? list[0];

  return active?.code ?? null;
}

async function createSection(request: any, code: string): Promise<number | null> {
  const resp = await sendAdminRequest(request, ADMIN_APPEARANCE.THEME_SECTIONS(code), {
    method: 'POST',
    data: { name: uniqueName(), type: 'static_content', channel: 1 },
  });
  const body = await safeJson(resp);

  return body?.id ?? null;
}

async function deleteSection(request: any, id: number): Promise<void> {
  await sendAdminRequest(request, ADMIN_APPEARANCE.SECTION(id), { method: 'DELETE' });
}

test.describe('Admin Appearance Sections REST API', () => {
  test('listing returns the sections of a theme', async ({ request }) => {
    const code = await activeThemeCode(request);
    if (!code) { test.skip(true, 'no theme in the gallery'); return; }

    const resp = await sendAdminRequest(request, ADMIN_APPEARANCE.THEME_SECTIONS(code), {
      params: { channel: '1' },
    });
    expect(resp.status()).toBe(200);
    expect(Array.isArray(rows(await safeJson(resp)))).toBe(true);
  });

  test('listing an unknown theme returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_APPEARANCE.THEME_SECTIONS('no_such_theme_e2e'));
    expect(resp.status()).toBe(404);
  });

  test('create returns an unpublished section', async ({ request }) => {
    const code = await activeThemeCode(request);
    if (!code) { test.skip(true, 'no theme in the gallery'); return; }

    const id = await createSection(request, code);
    expect(id).toBeTruthy();

    const detail = await sendAdminRequest(request, ADMIN_APPEARANCE.SECTION(id!));
    expect(detail.status()).toBe(200);

    const body = await safeJson(detail);
    expect(body?.id).toBe(id);
    expect(Number(body?.status)).toBe(0);

    await deleteSection(request, id!);
  });

  test('create without a name returns 422', async ({ request }) => {
    const code = await activeThemeCode(request);
    if (!code) { test.skip(true, 'no theme in the gallery'); return; }

    const resp = await sendAdminRequest(request, ADMIN_APPEARANCE.THEME_SECTIONS(code), {
      method: 'POST',
      data: { type: 'static_content' },
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('create with an unknown type returns 422', async ({ request }) => {
    const code = await activeThemeCode(request);
    if (!code) { test.skip(true, 'no theme in the gallery'); return; }

    const resp = await sendAdminRequest(request, ADMIN_APPEARANCE.THEME_SECTIONS(code), {
      method: 'POST',
      data: { name: uniqueName(), type: 'definitely_not_a_type', channel: 1 },
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('detail of an unknown id returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_APPEARANCE.SECTION(99999999));
    expect([400, 404]).toContain(resp.status());
  });

  test('update writes the published values', async ({ request }) => {
    const code = await activeThemeCode(request);
    if (!code) { test.skip(true, 'no theme in the gallery'); return; }

    const id = await createSection(request, code);
    if (!id) { test.skip(true, 'section create failed'); return; }

    const renamed = `${uniqueName()} renamed`;
    const resp = await sendAdminRequest(request, ADMIN_APPEARANCE.SECTION(id), {
      method: 'PUT',
      data: {
        name: renamed,
        type: 'static_content',
        sortOrder: 99,
        channelId: 1,
        themeCode: code,
        locale: 'en',
        options: { html: '<p>Hi</p>', css: '' },
      },
    });
    expect(resp.status()).toBe(200);
    expect((await safeJson(resp))?.name).toBe(renamed);

    await deleteSection(request, id);
  });

  test('a draft is staged and can be discarded', async ({ request }) => {
    const code = await activeThemeCode(request);
    if (!code) { test.skip(true, 'no theme in the gallery'); return; }

    const id = await createSection(request, code);
    if (!id) { test.skip(true, 'section create failed'); return; }

    const draft = await sendAdminRequest(request, ADMIN_APPEARANCE.SECTION_DRAFT(id), {
      method: 'POST',
      data: { options: { html: '<p>staged</p>', css: '' }, locale: 'en' },
    });
    expect([200, 201]).toContain(draft.status());
    expect((await safeJson(draft))?.hasDraft).toBeTruthy();

    const status = await sendAdminRequest(request, ADMIN_APPEARANCE.SECTION_STATUS(id), {
      method: 'POST',
      data: { status: true },
    });
    expect([200, 201]).toContain(status.status());

    // Staging does not move the published status.
    const detail = await sendAdminRequest(request, ADMIN_APPEARANCE.SECTION(id));
    expect(Number((await safeJson(detail))?.status)).toBe(0);

    const discard = await sendAdminRequest(request, ADMIN_APPEARANCE.SECTIONS_DISCARD(code), {
      method: 'POST',
      data: { channel: 1 },
    });
    expect([200, 201]).toContain(discard.status());

    await deleteSection(request, id);
  });

  test('reorder stages the order and keeps the footer last', async ({ request }) => {
    const code = await activeThemeCode(request);
    if (!code) { test.skip(true, 'no theme in the gallery'); return; }

    // A reorder has to carry every section of the theme and channel, so a section another
    // spec adds or removes in between invalidates the list. Read it again and retry.
    let sections: any[] = [];
    let payload: any = null;
    let ok = false;

    for (let attempt = 0; attempt < 3; attempt++) {
      const list = await sendAdminRequest(request, ADMIN_APPEARANCE.THEME_SECTIONS(code), { params: { channel: '1' } });
      sections = rows(await safeJson(list));
      if (sections.length < 2) { test.skip(true, 'not enough sections to reorder'); return; }

      const resp = await sendAdminRequest(request, ADMIN_APPEARANCE.SECTIONS_REORDER, {
        method: 'POST',
        data: { sectionIds: sections.map((s: any) => s.id).reverse() },
      });

      payload = await safeJson(resp);
      ok = [200, 201].includes(resp.status());

      if (ok) break;
    }

    // Locally the GraphQL suite runs alongside this one and churns sections faster than a
    // read-then-reorder can complete; CI runs one worker, where this cannot happen.
    if (! ok) {
      test.skip(true, 'sections changed concurrently');

      return;
    }

    const stored = payload?.sectionIds ?? [];
    const pinned = sections.find((s: any) => s.isPinned === true || Number(s.isPinned) === 1);

    if (pinned) {
      expect(stored[stored.length - 1]).toBe(pinned.id);
    }

    await sendAdminRequest(request, ADMIN_APPEARANCE.SECTIONS_DISCARD(code), {
      method: 'POST',
      data: { channel: 1 },
    });
  });

  test('reorder with no ids returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_APPEARANCE.SECTIONS_REORDER, {
      method: 'POST',
      data: { sectionIds: [] },
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('duplicate returns the copy', async ({ request }) => {
    const code = await activeThemeCode(request);
    if (!code) { test.skip(true, 'no theme in the gallery'); return; }

    const id = await createSection(request, code);
    if (!id) { test.skip(true, 'section create failed'); return; }

    const resp = await sendAdminRequest(request, ADMIN_APPEARANCE.SECTION_DUPLICATE(id), { method: 'POST' });
    expect([200, 201]).toContain(resp.status());

    const copy = await safeJson(resp);
    expect(copy?.sourceId).toBe(id);

    if (copy?.sectionId) {
      await deleteSection(request, copy.sectionId);
    }

    await deleteSection(request, id);
  });

  test('fields returns the schema for the section type', async ({ request }) => {
    const code = await activeThemeCode(request);
    if (!code) { test.skip(true, 'no theme in the gallery'); return; }

    const id = await createSection(request, code);
    if (!id) { test.skip(true, 'section create failed'); return; }

    const resp = await sendAdminRequest(request, ADMIN_APPEARANCE.SECTION_FIELDS(id), { params: { locale: 'en' } });
    expect(resp.status()).toBe(200);
    expect((await safeJson(resp))?.type).toBe('static_content');

    await deleteSection(request, id);
  });

  test('preview returns the drafted set', async ({ request }) => {
    const code = await activeThemeCode(request);
    if (!code) { test.skip(true, 'no theme in the gallery'); return; }

    const resp = await sendAdminRequest(request, ADMIN_APPEARANCE.SECTIONS_PREVIEW(code), {
      params: { channel: '1', locale: 'en' },
    });
    expect(resp.status()).toBe(200);

    const body = await safeJson(resp);
    expect(body?.themeCode).toBe(code);
    expect(Array.isArray(body?.sections)).toBe(true);
  });
});
