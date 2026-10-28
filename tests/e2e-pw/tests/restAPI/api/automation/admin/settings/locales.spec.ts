import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_SETTINGS } from '../../../../rest/endpoints/admin.settings.endpoints';

test.describe.configure({ timeout: 60_000 });

const NOT_FOUND = 'Locale not found.';
const DELETED = 'Locale deleted successfully.';
const MASS_DELETED = 'Locales deleted successfully.';
const INDICES_REQUIRED = 'The indices field is required and must be a non-empty array.';

function uniqueLocaleCode(): string {
  return `e2e_${Date.now().toString(36)}${Math.floor(Math.random() * 1_000_000).toString(36)}`;
}

async function createLocale(request: APIRequestContext, code: string): Promise<any> {
  const resp = await sendAdminRequest(request, ADMIN_SETTINGS.LOCALES, {
    method: 'POST',
    data: { code, name: `E2E ${code}`, direction: 'ltr' },
  });
  const body = await resp.json();
  expect(resp.status(), JSON.stringify(body)).toBe(201);

  return body;
}

async function deleteLocale(request: APIRequestContext, id: number): Promise<void> {
  await sendAdminRequest(request, ADMIN_SETTINGS.LOCALE(id), { method: 'DELETE' });
}

async function codeMatches(request: APIRequestContext, code: string): Promise<number> {
  const resp = await sendAdminRequest(request, ADMIN_SETTINGS.LOCALES, { params: { code } });
  expect(resp.status()).toBe(200);

  return (await resp.json()).data.length;
}

test.describe('admin settings locales rest api', () => {
  let code: string;

  test.beforeEach(() => {
    code = uniqueLocaleCode();
  });

  test('listing returns 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.LOCALES);
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(Array.isArray(body.data)).toBe(true);
    expect(typeof body.meta.total).toBe('number');
  });

  test('listing with code filter', async ({ request }) => {
    const created = await createLocale(request, code);

    try {
      const resp = await sendAdminRequest(request, ADMIN_SETTINGS.LOCALES, { params: { code } });
      expect(resp.status()).toBe(200);
      const body = await resp.json();
      expect(body.data).toHaveLength(1);
      expect(body.data[0].id).toBe(created.id);
      expect(body.data[0].code).toBe(code);
    } finally {
      await deleteLocale(request, created.id);
    }
  });

  test('detail of a created locale returns 200', async ({ request }) => {
    const created = await createLocale(request, code);

    try {
      const resp = await sendAdminRequest(request, ADMIN_SETTINGS.LOCALE(created.id));
      expect(resp.status()).toBe(200);
      const body = await resp.json();
      expect(body.id).toBe(created.id);
      expect(body.code).toBe(code);
      expect(body.name).toBe(`E2E ${code}`);
      expect(body.direction).toBe('ltr');
    } finally {
      await deleteLocale(request, created.id);
    }
  });

  test('detail non-existent id returns 404', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.LOCALE(99999999));
    expect(resp.status()).toBe(404);
    expect((await resp.json()).detail).toBe(NOT_FOUND);
  });

  test('create locale happy path', async ({ request }) => {
    const created = await createLocale(request, code);

    try {
      expect(typeof created.id).toBe('number');
      expect(created.code).toBe(code);
      expect(created.name).toBe(`E2E ${code}`);
      expect(created.direction).toBe('ltr');
      expect(await codeMatches(request, code)).toBe(1);
    } finally {
      await deleteLocale(request, created.id);
    }
  });

  test('create locale missing fields returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.LOCALES, {
      method: 'POST',
      data: {},
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The code field is required.');
  });

  test('create locale invalid direction returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.LOCALES, {
      method: 'POST',
      data: { code, name: 'E2E Bad', direction: 'wrong' },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe('The selected direction is invalid.');
    expect(await codeMatches(request, code)).toBe(0);
  });

  test('update locale partial name only', async ({ request }) => {
    const created = await createLocale(request, code);

    try {
      const resp = await sendAdminRequest(request, ADMIN_SETTINGS.LOCALE(created.id), {
        method: 'PUT',
        data: { name: 'Renamed E2E' },
      });
      expect(resp.status()).toBe(200);
      const body = await resp.json();
      expect(body.id).toBe(created.id);
      expect(body.name).toBe('Renamed E2E');
      expect(body.code).toBe(code);
      expect(body.direction).toBe('ltr');

      const detail = await sendAdminRequest(request, ADMIN_SETTINGS.LOCALE(created.id));
      expect((await detail.json()).name).toBe('Renamed E2E');
    } finally {
      await deleteLocale(request, created.id);
    }
  });

  test('delete fresh locale returns 200', async ({ request }) => {
    const created = await createLocale(request, code);

    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.LOCALE(created.id), {
      method: 'DELETE',
    });
    expect(resp.status()).toBe(200);
    expect((await resp.json()).message).toBe(DELETED);

    const detail = await sendAdminRequest(request, ADMIN_SETTINGS.LOCALE(created.id));
    expect(detail.status()).toBe(404);
  });

  test('mass-delete with single fresh id', async ({ request }) => {
    const created = await createLocale(request, code);

    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.LOCALES_MASS_DELETE, {
      method: 'POST',
      data: { indices: [created.id] },
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
    expect(body.message).toBe(MASS_DELETED);
    expect(body.deleted).toEqual([created.id]);
    expect(body.skipped).toEqual([]);

    const detail = await sendAdminRequest(request, ADMIN_SETTINGS.LOCALE(created.id));
    expect(detail.status()).toBe(404);
  });

  test('mass-delete empty indices returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.LOCALES_MASS_DELETE, {
      method: 'POST',
      data: { indices: [] },
    });
    expect(resp.status()).toBe(422);
    expect((await resp.json()).detail).toBe(INDICES_REQUIRED);
  });
});
