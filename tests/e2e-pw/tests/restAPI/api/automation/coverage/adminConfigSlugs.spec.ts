import { test, expect } from '@playwright/test';
import { sendAdminRequest } from '../../../rest/helpers/adminClient';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import { ADMIN_MENU_COVERAGE } from '../../../rest/endpoints/coverage/adminMenu.endpoints';

test.describe.configure({ timeout: 60_000 });

async function safeJson(resp: any): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

test.describe('admin configuration slugs rest api', () => {
  test('should list every registered configuration slug with its label and shape', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MENU_COVERAGE.CONFIGURATION_SLUGS);
    expect(resp.status()).toBe(200);

    const body = await safeJson(resp);
    expect(Array.isArray(body)).toBe(true);
    expect(body).toHaveLength(1);
    expect(body[0].id).toBe('configuration-slugs');

    const slugs = body[0].slugs;
    expect(Array.isArray(slugs)).toBe(true);
    expect(slugs.length).toBeGreaterThan(0);

    for (const entry of slugs) {
      expect(Object.keys(entry).sort()).toEqual(['hasChildren', 'hasFields', 'name', 'slug', 'sort']);
      expect(typeof entry.slug).toBe('string');
      expect(typeof entry.hasFields).toBe('boolean');
      expect(typeof entry.hasChildren).toBe('boolean');
    }

    const keys = slugs.map((entry: any) => entry.slug);
    expect(new Set(keys).size).toBe(keys.length);

    expect(slugs.find((entry: any) => entry.slug === 'general')).toEqual({
      slug: 'general',
      name: 'General',
      sort: 1,
      hasFields: false,
      hasChildren: true,
    });

    expect(slugs.find((entry: any) => entry.slug === 'sales.order_settings')).toMatchObject({
      name: 'Order Settings',
      hasFields: false,
      hasChildren: true,
    });

    expect(slugs.find((entry: any) => entry.slug === 'sales.order_settings.order_number')).toMatchObject({
      name: 'Order Number Settings',
      hasFields: true,
      hasChildren: false,
    });
  });

  test('should list a parent slug before each of its children', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_MENU_COVERAGE.CONFIGURATION_SLUGS);
    expect(resp.status()).toBe(200);

    const keys: string[] = (await safeJson(resp))[0].slugs.map((entry: any) => entry.slug);

    for (const [index, key] of keys.entries()) {
      if (!key.includes('.')) {
        continue;
      }
      const parent = key.slice(0, key.lastIndexOf('.'));
      expect(keys.indexOf(parent), `${parent} is listed before ${key}`).toBeGreaterThanOrEqual(0);
      expect(keys.indexOf(parent)).toBeLessThan(index);
    }
  });

  test('should refuse the slug listing without an admin token', async ({ request }) => {
    const resp = await sendRestRequest(request, ADMIN_MENU_COVERAGE.CONFIGURATION_SLUGS);
    expect(resp.status()).toBe(401);
    const body = await safeJson(resp);
    expect(body.message).toBe('Unauthenticated.');
    expect(body.slugs).toBeUndefined();
  });
});
