import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../graphql/helpers/adminGraphqlClient';
import { sendGraphQLRequest } from '../../../graphql/helpers/graphqlClient';
import { ADMIN_SETTINGS_EXCHANGE_RATE_UPDATE_RATES_MUTATION } from '../../../graphql/Queries/coverage/adminSettings.queries';
import { env } from '../../../config/env';
import { sendAdminRequest } from '../../../../restAPI/rest/helpers/adminClient';
import { ADMIN_SETTINGS_COVERAGE } from '../../../../restAPI/rest/endpoints/coverage/adminSettings.endpoints';

test.describe.configure({ timeout: 90_000 });

const SUCCESS_MESSAGE = 'Exchange rates updated successfully.';
const NOT_CONFIGURED_MESSAGE = 'No external exchange-rate provider is configured.';

interface RateSnapshot {
  id: number;
  rate: number;
}

async function exchangeRates(request: APIRequestContext): Promise<RateSnapshot[]> {
  const rows: RateSnapshot[] = [];

  for (let page = 1; page <= 20; page++) {
    const resp = await sendAdminRequest(request, ADMIN_SETTINGS_COVERAGE.EXCHANGE_RATES, {
      params: { page: String(page), per_page: '50' },
    });
    expect(resp.status()).toBe(200);
    const body = await resp.json();
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

test.describe('admin settings exchange rate update rates graphql', () => {
  let snapshot: RateSnapshot[] = [];

  test.beforeEach(async ({ request }) => {
    snapshot = await exchangeRates(request);
  });

  test.afterEach(async ({ request }) => {
    await restoreRates(request, snapshot);
  });

  test('should refresh the rates or report the provider failure as an error', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_SETTINGS_EXCHANGE_RATE_UPDATE_RATES_MUTATION, {
      input: { confirm: true },
    });
    expect(resp.status()).toBe(200);

    const body = await resp.json();
    const result = body.data?.createAdminSettingsExchangeRateUpdateRates?.adminSettingsExchangeRateUpdateRates;

    if (!body.errors) {
      expect(result.success).toBe(true);
      expect(result.message).toBe(SUCCESS_MESSAGE);
      expect(result.updated).toBe((await exchangeRates(request)).length);
      return;
    }

    expect(result ?? null).toBeNull();
    expect(typeof body.errors[0]?.message).toBe('string');
    expect(body.errors[0].message.length).toBeGreaterThan(0);
    expect(body.errors[0].message).not.toBe(NOT_CONFIGURED_MESSAGE);
    expect((await exchangeRates(request)).map(row => row.id).sort()).toEqual(snapshot.map(row => row.id).sort());
  });

  test('should refuse to update rates without an admin token', async ({ request }) => {
    const resp = await sendGraphQLRequest(
      request,
      ADMIN_SETTINGS_EXCHANGE_RATE_UPDATE_RATES_MUTATION,
      { input: { confirm: true } },
      {},
      env.adminGraphqlEndpoint,
    );
    expect(resp.status()).toBe(401);
    expect((await resp.json())?.message).toBe('Unauthenticated.');
  });

  test('should reject the mutation when the input is missing', async ({ request }) => {
    const resp = await sendAdminGraphQLRequest(request, ADMIN_SETTINGS_EXCHANGE_RATE_UPDATE_RATES_MUTATION, {});
    const body = await resp.json();

    expect(body.data?.createAdminSettingsExchangeRateUpdateRates ?? null).toBeNull();
    expect(body.errors?.[0]?.message).toContain('$input');
    expect((await exchangeRates(request)).map(row => row.id).sort()).toEqual(snapshot.map(row => row.id).sort());
  });
});
