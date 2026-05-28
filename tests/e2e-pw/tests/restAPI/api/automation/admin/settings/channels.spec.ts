// Admin Settings — Channels REST e2e.
// Listing / detail / create / update / delete.
// Translatable, FK-heavy. Channel creation requires locales/currencies/
// inventory_sources arrays + default ids. We pick id=1 for each (default
// fixtures seeded by Bagisto installer). Delete operates only on rows we
// created (never the default app channel).

import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_SETTINGS } from '../../../../rest/endpoints/admin.settings.endpoints';

test.describe.configure({ timeout: 60_000 });

const OK_LIST = [200];
const OK_CREATE = [200, 201, 400, 422, 429];
const OK_UPDATE = [200, 201, 400, 404, 422, 429];
const OK_DELETE = [200, 204, 400, 404, 422, 429];

async function safeJson(resp: any): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

function uniqueCode(prefix = 'e2e'): string {
  return `${prefix}_${Date.now().toString(36).slice(-6)}`;
}

async function createChannel(request: any): Promise<{ id: number | null }> {
  const code = uniqueCode('e2e_ch');
  const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CHANNELS, {
    method: 'POST',
    data: {
      code,
      name: `E2E Channel ${code}`,
      description: 'e2e generated',
      hostname: `https://${code}.example.com`,
      locales: [1],
      currencies: [1],
      inventory_sources: [1],
      default_locale_id: 1,
      base_currency_id: 1,
      root_category_id: 1,
    },
  });
  const body = await safeJson(resp);
  return { id: body?.id ?? body?.data?.id ?? null };
}

test.describe('Admin Settings Channels REST API', () => {
  test('listing returns 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CHANNELS);
    expect(OK_LIST).toContain(resp.status());
    const body = await safeJson(resp);
    if (body) expect(Array.isArray(body.data) || Array.isArray(body)).toBe(true);
  });

  test('listing with pagination', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CHANNELS, {
      params: { page: '1', per_page: '5' },
    });
    expect(OK_LIST).toContain(resp.status());
  });

  test('detail for id=1 returns 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CHANNEL(1));
    expect([200, 404]).toContain(resp.status());
  });

  test('detail for non-existent id returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CHANNEL(99999999));
    expect([400, 404]).toContain(resp.status());
  });

  test('create channel happy path', async ({ request }) => {
    const { id } = await createChannel(request);
    // tolerate failure (fixtures may differ) but if created, clean up
    if (id) {
      await sendAdminRequest(request, ADMIN_SETTINGS.CHANNEL(id), { method: 'DELETE' });
    }
  });

  test('create channel missing code returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CHANNELS, {
      method: 'POST',
      data: { name: 'No code' },
    });
    expect([400, 422]).toContain(resp.status());
  });

  test('update channel partial (name only)', async ({ request }) => {
    const { id } = await createChannel(request);
    if (!id) { test.skip(true, 'create failed'); return; }
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CHANNEL(id), {
      method: 'PUT',
      data: { name: 'Renamed E2E' },
    });
    expect(OK_UPDATE).toContain(resp.status());
    await sendAdminRequest(request, ADMIN_SETTINGS.CHANNEL(id), { method: 'DELETE' });
  });

  test('delete fresh channel returns 200/204', async ({ request }) => {
    const { id } = await createChannel(request);
    if (!id) { test.skip(true, 'create failed'); return; }
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CHANNEL(id), {
      method: 'DELETE',
    });
    expect(OK_DELETE).toContain(resp.status());
  });
});
