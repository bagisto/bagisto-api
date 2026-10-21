import { test, expect, APIRequestContext, APIResponse } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_SETTINGS } from '../../../../rest/endpoints/admin.settings.endpoints';

test.describe.configure({ timeout: 60_000 });

const UNKNOWN_ID = 99999999;

const MSG = {
  NOT_FOUND: 'Currency not found.',
  DELETED: 'Currency deleted successfully.',
  CODE_REQUIRED: 'The code field is required.',
  CODE_UNIQUE: 'The code has already been taken.',
  MASS_DELETE_SUCCESS: 'Currencies deleted successfully.',
  MASS_DELETE_INDICES_REQUIRED: 'The indices field is required and must be a non-empty array.',
};

interface CreatedCurrency {
  id: number;
  code: string;
  name: string;
}

function randomCurrencyCode(): string {
  const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

  return Array.from({ length: 3 }, () => letters[Math.floor(Math.random() * letters.length)]).join('');
}

async function jsonOf(response: APIResponse): Promise<any> {
  return JSON.parse(await response.text());
}

function problemDetail(body: any): string {
  return String(body?.detail ?? body?.message ?? '');
}

async function createCurrency(request: APIRequestContext): Promise<CreatedCurrency> {
  for (let attempt = 0; attempt < 20; attempt++) {
    const code = randomCurrencyCode();
    const name = `E2E ${code} ${Date.now()}`;

    const response = await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCIES, {
      method: 'POST',
      data: { code, name, symbol: '¤', decimal: 2 },
    });
    const body = await jsonOf(response);

    if (
      response.status() === 422
      && problemDetail(body) === MSG.CODE_UNIQUE
    ) {
      continue;
    }

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(typeof body.id).toBe('number');
    expect(body.code).toBe(code);
    expect(body.name).toBe(name);

    return { id: body.id, code, name };
  }

  throw new Error('no free three-letter currency code found after 20 attempts');
}

async function deleteCurrency(request: APIRequestContext, id: number): Promise<void> {
  await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCY(id), { method: 'DELETE' });
}

async function expectNotFound(response: APIResponse): Promise<void> {
  const body = await jsonOf(response);

  expect(response.status(), JSON.stringify(body)).toBe(404);
  expect(problemDetail(body)).toBe(MSG.NOT_FOUND);
}

test.describe('admin settings currencies rest api', () => {
  test('listing returns 200 + envelope shape', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCIES);
    const body = await jsonOf(resp);

    expect(resp.status(), JSON.stringify(body)).toBe(200);
    expect(Array.isArray(body.data)).toBe(true);
    expect(body).toHaveProperty('meta');
  });

  test('listing with pagination params', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCIES, {
      params: { page: '1', per_page: '5' },
    });
    const body = await jsonOf(resp);

    expect(resp.status(), JSON.stringify(body)).toBe(200);
    expect(body.data.length).toBeLessThanOrEqual(5);
  });

  test('listing with code filter', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCIES, {
      params: { code: 'USD' },
    });
    const body = await jsonOf(resp);

    expect(resp.status(), JSON.stringify(body)).toBe(200);
    expect(body.data.map((row: any) => row.code)).toContain('USD');
    for (const row of body.data) {
      expect(row.code.toUpperCase()).toContain('USD');
    }
  });

  test('detail returns a created currency', async ({ request }) => {
    const currency = await createCurrency(request);

    try {
      const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCY(currency.id));
      const body = await jsonOf(resp);

      expect(resp.status(), JSON.stringify(body)).toBe(200);
      expect(body.id).toBe(currency.id);
      expect(body.code).toBe(currency.code);
      expect(body.name).toBe(currency.name);
      expect(body.symbol).toBe('¤');
      expect(body.decimal).toBe(2);
    } finally {
      await deleteCurrency(request, currency.id);
    }
  });

  test('detail for non-existent id returns 404', async ({ request }) => {
    await expectNotFound(await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCY(UNKNOWN_ID)));
  });

  test('create currency happy path', async ({ request }) => {
    const currency = await createCurrency(request);

    try {
      const list = await jsonOf(await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCIES, {
        params: { code: currency.code },
      }));

      expect(list.data.map((row: any) => row.id)).toContain(currency.id);
    } finally {
      await deleteCurrency(request, currency.id);
    }
  });

  test('create currency with a taken code returns 422', async ({ request }) => {
    const currency = await createCurrency(request);

    try {
      const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCIES, {
        method: 'POST',
        data: { code: currency.code, name: `Duplicate ${currency.code}` },
      });
      const body = await jsonOf(resp);

      expect(resp.status(), JSON.stringify(body)).toBe(422);
      expect(problemDetail(body)).toBe(MSG.CODE_UNIQUE);
    } finally {
      await deleteCurrency(request, currency.id);
    }
  });

  test('create currency missing code returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCIES, {
      method: 'POST',
      data: { name: 'no code' },
    });
    const body = await jsonOf(resp);

    expect(resp.status(), JSON.stringify(body)).toBe(422);
    expect(problemDetail(body)).toBe(MSG.CODE_REQUIRED);
  });

  test('update currency name (code immutable)', async ({ request }) => {
    const currency = await createCurrency(request);
    const renamed = `Renamed ${currency.code} ${Date.now()}`;

    try {
      const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCY(currency.id), {
        method: 'PUT',
        data: { name: renamed, code: 'ZZZ' },
      });
      const body = await jsonOf(resp);

      expect(resp.status(), JSON.stringify(body)).toBe(200);
      expect(body.id).toBe(currency.id);
      expect(body.name).toBe(renamed);
      expect(body.code).toBe(currency.code);

      const detail = await jsonOf(await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCY(currency.id)));
      expect(detail.name).toBe(renamed);
      expect(detail.code).toBe(currency.code);
    } finally {
      await deleteCurrency(request, currency.id);
    }
  });

  test('delete fresh currency returns 200', async ({ request }) => {
    const currency = await createCurrency(request);

    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCY(currency.id), {
      method: 'DELETE',
    });
    const body = await jsonOf(resp);

    expect(resp.status(), JSON.stringify(body)).toBe(200);
    expect(body.message).toBe(MSG.DELETED);
    await expectNotFound(await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCY(currency.id)));
  });

  test('delete non-existent currency returns 404', async ({ request }) => {
    await expectNotFound(await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCY(UNKNOWN_ID), { method: 'DELETE' }));
  });

  test('mass-delete with single fresh id', async ({ request }) => {
    const currency = await createCurrency(request);

    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCIES_MASS_DELETE, {
      method: 'POST',
      data: { indices: [currency.id] },
    });
    const body = await jsonOf(resp);

    expect(resp.status(), JSON.stringify(body)).toBe(200);
    expect(body.deleted).toEqual([currency.id]);
    expect(body.message).toBe(MSG.MASS_DELETE_SUCCESS);
    await expectNotFound(await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCY(currency.id)));
  });

  test('mass-delete empty indices returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCIES_MASS_DELETE, {
      method: 'POST',
      data: { indices: [] },
    });
    const body = await jsonOf(resp);

    expect(resp.status(), JSON.stringify(body)).toBe(422);
    expect(problemDetail(body)).toBe(MSG.MASS_DELETE_INDICES_REQUIRED);
  });
});
