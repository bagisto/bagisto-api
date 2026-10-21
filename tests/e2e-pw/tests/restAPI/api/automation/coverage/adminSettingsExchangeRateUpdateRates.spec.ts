import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminRequest } from '../../../rest/helpers/adminClient';
import { sendRestRequest } from '../../../rest/helpers/restClient';
import { ADMIN_SETTINGS_COVERAGE } from '../../../rest/endpoints/coverage/adminSettings.endpoints';

test.describe.configure({ timeout: 90_000 });

const SUCCESS_MESSAGE = 'Exchange rates updated successfully.';
const NOT_CONFIGURED_MESSAGE = 'No external exchange-rate provider is configured.';

interface RateSnapshot {
  id: number;
  rate: number;
}

async function safeJson(resp: any): Promise<any> {
  try { return await resp.json(); } catch { return null; }
}

function errorMessage(body: any): string | undefined {
  return body?.detail ?? body?.message;
}

async function exchangeRates(request: APIRequestContext): Promise<RateSnapshot[]> {
  const rows: RateSnapshot[] = [];

  for (let page = 1; page <= 20; page++) {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS_COVERAGE.EXCHANGE_RATES, {
      params: { page: String(page), per_page: '50' },
    });
    expect(resp.status()).toBe(200);
    const body = await safeJson(resp);
    expect(Array.isArray(body?.data)).toBe(true);

    for (const row of body.data) {
      rows.push({ id: row.id, rate: Number(row.rate) });
    }

    if (page >= Number(body.meta?.lastPage ?? 1)) {
      break;
    }
  }

  return rows;
}

async function restoreRates(request: APIRequestContext, snapshot: RateSnapshot[]) {
  const before = new Map(snapshot.map(row => [row.id, row.rate]));

  for (const row of await exchangeRates(request)) {
    if (!before.has(row.id)) {
      await sendAdminRequest(request, ADMIN_SETTINGS_COVERAGE.EXCHANGE_RATE(row.id), { method: 'DELETE' });
      continue;
    }

    if (before.get(row.id) !== row.rate) {
      await sendAdminRequest(request, ADMIN_SETTINGS_COVERAGE.EXCHANGE_RATE(row.id), {
        method: 'PUT',
        data: { rate: before.get(row.id) },
      });
    }
  }
}

test.describe('admin settings exchange rate update rates rest api', () => {
  let snapshot: RateSnapshot[] = [];

  test.beforeEach(async ({ request }) => {
    snapshot = await exchangeRates(request);
  });

  test.afterEach(async ({ request }) => {
    await restoreRates(request, snapshot);
  });

  test('should refresh the rates or report the provider failure as a 422', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS_COVERAGE.EXCHANGE_RATES_UPDATE_RATES, {
      method: 'POST',
      data: {},
    });
    const body = await safeJson(resp);

    expect([200, 422], JSON.stringify(body)).toContain(resp.status());

    if (resp.status() === 200) {
      expect(body.success).toBe(true);
      expect(body.message).toBe(SUCCESS_MESSAGE);
      expect(body.updated).toBe((await exchangeRates(request)).length);
      return;
    }

    expect(typeof errorMessage(body)).toBe('string');
    expect(errorMessage(body)!.length).toBeGreaterThan(0);
    expect(errorMessage(body)).not.toBe(NOT_CONFIGURED_MESSAGE);
    expect((await exchangeRates(request)).map(row => row.id).sort()).toEqual(snapshot.map(row => row.id).sort());
  });

  test('should refuse to update rates without an admin token', async ({ request }) => {
    const resp = await sendRestRequest(request, ADMIN_SETTINGS_COVERAGE.EXCHANGE_RATES_UPDATE_RATES, {
      method: 'POST',
      data: {},
    });
    expect(resp.status()).toBe(401);
    expect((await safeJson(resp))?.message).toBe('Unauthenticated.');
  });

  test('should refuse to update rates with an invalid admin token', async ({ request }) => {
    const resp = await sendRestRequest(request, ADMIN_SETTINGS_COVERAGE.EXCHANGE_RATES_UPDATE_RATES, {
      method: 'POST',
      data: {},
      headers: { Authorization: 'Bearer 0|not-a-real-token' },
    });
    expect(resp.status()).toBe(401);
    expect((await safeJson(resp))?.message).toBe('Unauthenticated.');
    expect((await exchangeRates(request)).map(row => row.id).sort()).toEqual(snapshot.map(row => row.id).sort());
  });

  test('should not expose update rates over get', async ({ request }) => {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS_COVERAGE.EXCHANGE_RATES_UPDATE_RATES);
    expect([404, 405]).toContain(resp.status());
  });
});
