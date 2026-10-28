import { test, expect, APIRequestContext, APIResponse } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_SETTINGS } from '../../../../rest/endpoints/admin.settings.endpoints';

test.describe.configure({ timeout: 60_000 });

const UNKNOWN_ID = 99999999;

const MSG = {
  NOT_FOUND: 'Channel not found.',
  DELETED: 'Channel deleted successfully.',
  CODE_REQUIRED: 'The code field is required.',
};

interface CreatedChannel {
  id: number;
  code: string;
  name: string;
  hostname: string;
}

function uniqueCode(): string {
  return `e2e_ch_${Date.now().toString(36)}${Math.floor(Math.random() * 1_000_000).toString(36)}`;
}

async function jsonOf(response: APIResponse): Promise<any> {
  return JSON.parse(await response.text());
}

async function createChannel(request: APIRequestContext): Promise<CreatedChannel> {
  const code = uniqueCode();
  const name = `E2E Channel ${code}`;
  const hostname = `https://${code.replace(/_/g, '-')}.example.com`;

  const response = await sendAdminRequest(request, ADMIN_SETTINGS.CHANNELS, {
    method: 'POST',
    data: {
      code,
      name,
      description: 'e2e generated',
      hostname,
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
  expect(body.code).toBe(code);

  return { id: body.id, code, name, hostname };
}

async function deleteChannel(request: APIRequestContext, id: number): Promise<void> {
  await sendAdminRequest(request, ADMIN_SETTINGS.CHANNEL(id), { method: 'DELETE' });
}

async function expectNotFound(response: APIResponse): Promise<void> {
  const body = await jsonOf(response);

  expect(response.status(), JSON.stringify(body)).toBe(404);
  expect(body.detail ?? body.message).toBe(MSG.NOT_FOUND);
}

test.describe('admin settings channels rest api', () => {
  test('listing filtered by code returns the default channel', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CHANNELS, { params: { code: 'default' } });
    const body = await jsonOf(resp);

    expect(resp.status(), JSON.stringify(body)).toBe(200);
    expect(Array.isArray(body.data)).toBe(true);
    expect(body.data.map((row: any) => row.code)).toContain('default');
  });

  test('listing with pagination', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CHANNELS, {
      params: { page: '1', per_page: '5' },
    });
    const body = await jsonOf(resp);

    expect(resp.status(), JSON.stringify(body)).toBe(200);
    expect(body.data.length).toBeLessThanOrEqual(5);
  });

  test('detail returns a created channel', async ({ request }) => {
    const channel = await createChannel(request);

    try {
      const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CHANNEL(channel.id));
      const body = await jsonOf(resp);

      expect(resp.status(), JSON.stringify(body)).toBe(200);
      expect(body.id).toBe(channel.id);
      expect(body.code).toBe(channel.code);
      expect(body.name).toBe(channel.name);
      expect(body.hostname).toBe(channel.hostname);
      expect(body.defaultLocaleId).toBe(1);
      expect(body.baseCurrencyId).toBe(1);
      expect(body.rootCategoryId).toBe(1);
      expect(body.locales.map((locale: any) => locale.id)).toEqual([1]);
      expect(body.currencies.map((currency: any) => currency.id)).toEqual([1]);
    } finally {
      await deleteChannel(request, channel.id);
    }
  });

  test('detail for non-existent id returns 404', async ({ request }) => {
    await expectNotFound(await sendAdminRequest(request, ADMIN_SETTINGS.CHANNEL(UNKNOWN_ID)));
  });

  test('create channel missing code returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CHANNELS, {
      method: 'POST',
      data: { name: 'No code' },
    });
    const body = await jsonOf(resp);

    expect(resp.status(), JSON.stringify(body)).toBe(422);
    expect(body.detail ?? body.message).toBe(MSG.CODE_REQUIRED);
  });

  test('update channel partial (name only)', async ({ request }) => {
    const channel = await createChannel(request);
    const renamed = `Renamed ${channel.code}`;

    try {
      const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CHANNEL(channel.id), {
        method: 'PUT',
        data: { name: renamed },
      });
      const body = await jsonOf(resp);

      expect(resp.status(), JSON.stringify(body)).toBe(200);
      expect(body.id).toBe(channel.id);
      expect(body.name).toBe(renamed);
      expect(body.code).toBe(channel.code);

      const detail = await jsonOf(await sendAdminRequest(request, ADMIN_SETTINGS.CHANNEL(channel.id)));
      expect(detail.name).toBe(renamed);
      expect(detail.locales.map((locale: any) => locale.id)).toEqual([1]);
    } finally {
      await deleteChannel(request, channel.id);
    }
  });

  test('delete fresh channel returns 200', async ({ request }) => {
    const channel = await createChannel(request);

    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CHANNEL(channel.id), {
      method: 'DELETE',
    });
    const body = await jsonOf(resp);

    expect(resp.status(), JSON.stringify(body)).toBe(200);
    expect(body.message).toBe(MSG.DELETED);
    await expectNotFound(await sendAdminRequest(request, ADMIN_SETTINGS.CHANNEL(channel.id)));
  });

  test('delete non-existent channel returns 404', async ({ request }) => {
    await expectNotFound(await sendAdminRequest(request, ADMIN_SETTINGS.CHANNEL(UNKNOWN_ID), { method: 'DELETE' }));
  });
});
