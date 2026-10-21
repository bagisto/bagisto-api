import { test, expect, APIRequestContext, APIResponse } from '@playwright/test';
import { sendAdminRequest } from '../../../../rest/helpers/adminClient';
import { ADMIN_SETTINGS } from '../../../../rest/endpoints/admin.settings.endpoints';

test.describe.configure({ timeout: 60_000 });

const UNKNOWN_ID = 99999999;

const MSG = {
  NOT_FOUND: 'Exchange rate not found.',
  DELETED: 'Exchange rate deleted successfully.',
  DUPLICATE_PAIR: 'An exchange rate for this target currency already exists. Update the existing row or delete it first.',
  TARGET_CURRENCY_REQUIRED: 'The target currency field is required.',
  RATE_GT_ZERO: 'The rate field must be greater than 0.',
  MASS_DELETE_SUCCESS: 'Exchange rates deleted successfully.',
  MASS_DELETE_INDICES_REQUIRED: 'The indices field is required and must be a non-empty array.',
  CURRENCY_CODE_UNIQUE: 'The code has already been taken.',
};

interface CreatedCurrency {
  id: number;
  code: string;
}

interface CreatedRate {
  id: number;
  currency: CreatedCurrency;
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

    const response = await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCIES, {
      method: 'POST',
      data: { code, name: `E2E ${code} ${Date.now()}`, symbol: '¤' },
    });
    const body = await jsonOf(response);

    if (
      response.status() === 422
      && problemDetail(body) === MSG.CURRENCY_CODE_UNIQUE
    ) {
      continue;
    }

    expect(response.status(), JSON.stringify(body)).toBe(201);
    expect(typeof body.id).toBe('number');

    return { id: body.id, code };
  }

  throw new Error('no free three-letter currency code found after 20 attempts');
}

async function createExchangeRate(request: APIRequestContext, rate = 1.23): Promise<CreatedRate> {
  const currency = await createCurrency(request);

  const response = await sendAdminRequest(request, ADMIN_SETTINGS.EXCHANGE_RATES, {
    method: 'POST',
    data: { target_currency: currency.id, rate },
  });
  const body = await jsonOf(response);

  expect(response.status(), JSON.stringify(body)).toBe(201);
  expect(typeof body.id).toBe('number');
  expect(body.targetCurrency).toBe(currency.id);
  expect(body.targetCurrencyCode).toBe(currency.code);
  expect(body.rate).toBeCloseTo(rate, 4);

  return { id: body.id, currency };
}

async function cleanupCurrency(request: APIRequestContext, currencyId: number): Promise<void> {
  await sendAdminRequest(request, ADMIN_SETTINGS.CURRENCY(currencyId), { method: 'DELETE' });
}

async function expectNotFound(response: APIResponse): Promise<void> {
  const body = await jsonOf(response);

  expect(response.status(), JSON.stringify(body)).toBe(404);
  expect(problemDetail(body)).toBe(MSG.NOT_FOUND);
}

async function expectRejected(response: APIResponse, detail: string): Promise<void> {
  const body = await jsonOf(response);

  expect(response.status(), JSON.stringify(body)).toBe(422);
  expect(problemDetail(body)).toBe(detail);
}

test.describe('admin settings exchange rates rest api', () => {
  test('listing returns 200', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.EXCHANGE_RATES);
    const body = await jsonOf(resp);

    expect(resp.status(), JSON.stringify(body)).toBe(200);
    expect(Array.isArray(body.data)).toBe(true);
    expect(body).toHaveProperty('meta');
  });

  test('listing with pagination', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.EXCHANGE_RATES, {
      params: { page: '1', per_page: '5' },
    });
    const body = await jsonOf(resp);

    expect(resp.status(), JSON.stringify(body)).toBe(200);
    expect(body.data.length).toBeLessThanOrEqual(5);
  });

  test('detail returns a created exchange rate', async ({ request }) => {
    const created = await createExchangeRate(request);

    try {
      const resp = await sendAdminRequest(request, ADMIN_SETTINGS.EXCHANGE_RATE(created.id));
      const body = await jsonOf(resp);

      expect(resp.status(), JSON.stringify(body)).toBe(200);
      expect(body.id).toBe(created.id);
      expect(body.targetCurrency).toBe(created.currency.id);
      expect(body.targetCurrencyCode).toBe(created.currency.code);
      expect(body.rate).toBeCloseTo(1.23, 4);
    } finally {
      await sendAdminRequest(request, ADMIN_SETTINGS.EXCHANGE_RATE(created.id), { method: 'DELETE' });
      await cleanupCurrency(request, created.currency.id);
    }
  });

  test('detail for non-existent id returns 404', async ({ request }) => {
    await expectNotFound(await sendAdminRequest(request, ADMIN_SETTINGS.EXCHANGE_RATE(UNKNOWN_ID)));
  });

  test('create exchange rate happy path refuses a second rate for the same currency', async ({ request }) => {
    const created = await createExchangeRate(request);

    try {
      const duplicate = await sendAdminRequest(request, ADMIN_SETTINGS.EXCHANGE_RATES, {
        method: 'POST',
        data: { target_currency: created.currency.id, rate: 4.56 },
      });

      await expectRejected(duplicate, MSG.DUPLICATE_PAIR);

      const detail = await jsonOf(await sendAdminRequest(request, ADMIN_SETTINGS.EXCHANGE_RATE(created.id)));
      expect(detail.rate).toBeCloseTo(1.23, 4);
    } finally {
      await sendAdminRequest(request, ADMIN_SETTINGS.EXCHANGE_RATE(created.id), { method: 'DELETE' });
      await cleanupCurrency(request, created.currency.id);
    }
  });

  test('create exchange rate missing fields returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.EXCHANGE_RATES, {
      method: 'POST',
      data: {},
    });

    await expectRejected(resp, MSG.TARGET_CURRENCY_REQUIRED);
  });

  test('create with rate=0 returns 422', async ({ request }) => {
    const currency = await createCurrency(request);

    try {
      const resp = await sendAdminRequest(request, ADMIN_SETTINGS.EXCHANGE_RATES, {
        method: 'POST',
        data: { target_currency: currency.id, rate: 0 },
      });

      await expectRejected(resp, MSG.RATE_GT_ZERO);
    } finally {
      await cleanupCurrency(request, currency.id);
    }
  });

  test('update rate partial', async ({ request }) => {
    const created = await createExchangeRate(request);

    try {
      const resp = await sendAdminRequest(request, ADMIN_SETTINGS.EXCHANGE_RATE(created.id), {
        method: 'PUT',
        data: { rate: 2.5 },
      });
      const body = await jsonOf(resp);

      expect(resp.status(), JSON.stringify(body)).toBe(200);
      expect(body.id).toBe(created.id);
      expect(body.rate).toBeCloseTo(2.5, 4);
      expect(body.targetCurrency).toBe(created.currency.id);

      const detail = await jsonOf(await sendAdminRequest(request, ADMIN_SETTINGS.EXCHANGE_RATE(created.id)));
      expect(detail.rate).toBeCloseTo(2.5, 4);
    } finally {
      await sendAdminRequest(request, ADMIN_SETTINGS.EXCHANGE_RATE(created.id), { method: 'DELETE' });
      await cleanupCurrency(request, created.currency.id);
    }
  });

  test('delete fresh exchange rate returns 200', async ({ request }) => {
    const created = await createExchangeRate(request);

    try {
      const resp = await sendAdminRequest(request, ADMIN_SETTINGS.EXCHANGE_RATE(created.id), {
        method: 'DELETE',
      });
      const body = await jsonOf(resp);

      expect(resp.status(), JSON.stringify(body)).toBe(200);
      expect(body.message).toBe(MSG.DELETED);
      await expectNotFound(await sendAdminRequest(request, ADMIN_SETTINGS.EXCHANGE_RATE(created.id)));
    } finally {
      await cleanupCurrency(request, created.currency.id);
    }
  });

  test('delete non-existent exchange rate returns 404', async ({ request }) => {
    await expectNotFound(await sendAdminRequest(request, ADMIN_SETTINGS.EXCHANGE_RATE(UNKNOWN_ID), { method: 'DELETE' }));
  });

  test('mass-delete with one fresh id', async ({ request }) => {
    const created = await createExchangeRate(request);

    try {
      const resp = await sendAdminRequest(request, ADMIN_SETTINGS.EXCHANGE_RATES_MASS_DELETE, {
        method: 'POST',
        data: { indices: [created.id, UNKNOWN_ID] },
      });
      const body = await jsonOf(resp);

      expect(resp.status(), JSON.stringify(body)).toBe(200);
      expect(body.deleted).toEqual([created.id]);
      expect(body.message).toBe(MSG.MASS_DELETE_SUCCESS);
      await expectNotFound(await sendAdminRequest(request, ADMIN_SETTINGS.EXCHANGE_RATE(created.id)));
    } finally {
      await cleanupCurrency(request, created.currency.id);
    }
  });

  test('mass-delete empty indices returns 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS.EXCHANGE_RATES_MASS_DELETE, {
      method: 'POST',
      data: { indices: [] },
    });

    await expectRejected(resp, MSG.MASS_DELETE_INDICES_REQUIRED);
  });
});
