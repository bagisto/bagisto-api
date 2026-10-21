import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_SETTINGS } from '../../../../rest/endpoints/admin.settings.endpoints';

test.describe.configure({ timeout: 60_000 });

const SLUG = 'general.general';
const WEIGHT_KEY = 'general.general.locale_options.weight_unit';
const SLUG_NOT_FOUND = 'No configuration section is registered for the given slug.';
const SLUG_REQUIRED = 'Query parameter `slug` is required.';

async function readWeightUnit(request: APIRequestContext): Promise<string> {
  const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CONFIG_VALUES, {
    params: { slug: SLUG, channel: 'default', locale: 'en' },
  });
  expect(resp.status()).toBe(200);
  const body = await resp.json();

  return body[0].values[WEIGHT_KEY] ?? 'kgs';
}

async function writeWeightUnit(request: APIRequestContext, value: string) {
  return sendAdminRequest(request, ADMIN_SETTINGS.CONFIG_UPDATE, {
    method: 'POST',
    data: {
      slug: SLUG,
      channel: 'default',
      locale: 'en',
      values: { [WEIGHT_KEY]: value },
    },
  });
}

test.describe('admin configuration rest api', () => {
  test('menu full tree returns 200 and an array', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CONFIG_MENU);
    expect(resp.status()).toBe(200);
    expect(Array.isArray(await resp.json())).toBe(true);
  });

  test('menu scoped to slug returns 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CONFIG_MENU, {
      params: { slug: SLUG },
    });
    expect(resp.status()).toBe(200);
  });

  test('menu with include_values true returns 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CONFIG_MENU, {
      params: { slug: SLUG, include_values: 'true' },
    });
    expect(resp.status()).toBe(200);
  });

  test('menu unknown slug returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CONFIG_MENU, {
      params: { slug: 'definitely.not.a.real.slug' },
    });
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe(SLUG_NOT_FOUND);
  });

  test('values requires slug param', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CONFIG_VALUES);
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe(SLUG_REQUIRED);
  });

  test('values for general.general returns 200 and payload', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CONFIG_VALUES, {
      params: { slug: SLUG },
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body[0].slug).toBe(SLUG);
    expect(Object.keys(body[0].values)).toContain(WEIGHT_KEY);
  });

  test('update happy path toggles weight unit and restores it', async ({ request }) => {
    const original = await readWeightUnit(request);
    const target = original === 'kgs' ? 'lbs' : 'kgs';

    try {
      const updateResp = await writeWeightUnit(request, target);
      expect(updateResp.status()).toBe(200);
      const body = await updateResp.json();
      expect(body).toEqual(expect.objectContaining({
        success: true,
        message: 'Configuration updated successfully.',
        slug: SLUG,
        channel: 'default',
      }));
      expect(body.values[WEIGHT_KEY]).toBe(target);

      expect(await readWeightUnit(request)).toBe(target);
    } finally {
      await writeWeightUnit(request, original);
    }
  });

  test('update scope-escape rejected with 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CONFIG_UPDATE, {
      method: 'POST',
      data: {
        slug: SLUG,
        channel: 'default',
        locale: 'en',
        values: { 'sales.order_settings.reorder.admin': '1' },
      },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The key `sales.order_settings.reorder.admin` is outside the requested slug subtree.');
  });

  test('update missing slug returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CONFIG_UPDATE, {
      method: 'POST',
      data: { values: { [WEIGHT_KEY]: 'kgs' } },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe(SLUG_REQUIRED);
  });

  test('update empty values returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CONFIG_UPDATE, {
      method: 'POST',
      data: { slug: SLUG, channel: 'default', locale: 'en', values: {} },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('At least one value is required under `values`.');
  });
});
