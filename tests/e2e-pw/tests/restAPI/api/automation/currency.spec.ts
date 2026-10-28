import { test, expect, APIRequestContext } from '@playwright/test';
import { sendRestRequest } from '../../rest/helpers/restClient';
import { ENDPOINTS } from '../../rest/endpoints/endpoints';
import { REST_GAPS_MESSAGES, numericIdOf } from '../../rest/helpers/coverage/restGaps.helpers';

const BASE_CURRENCY_CODE = 'USD';

async function findCurrencyByCode(request: APIRequestContext, code: string): Promise<any> {
  const response = await sendRestRequest(request, ENDPOINTS.CURRENCIES, { params: { per_page: '50' } });
  const body = await response.json();

  expect(response.status(), JSON.stringify(body)).toBe(200);

  const currency = body.find((row: any) => row.code === code);

  expect(currency, `currency ${code} missing from ${JSON.stringify(body)}`).toBeTruthy();

  return currency;
}

test.describe('currencies rest api', () => {
  test('should list all currencies', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CURRENCIES);
    const body = await response.json();

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(Array.isArray(body)).toBe(true);
    expect(body.length).toBeGreaterThan(0);
    expect(body.length).toBeLessThanOrEqual(10);

    for (const currency of body) {
      expect(numericIdOf(currency.id)).toBeGreaterThan(0);
      expect(typeof currency.code).toBe('string');
    }
  });

  test('should return paginated currency list', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CURRENCIES, {
      params: { per_page: '1', page: '1' },
    });
    const body = await response.json();

    expect(response.status(), JSON.stringify(body)).toBe(200);
    expect(body).toHaveLength(1);
    expect(response.headers()['x-per-page']).toBe('1');
  });

  test('should return single currency by id', async ({ request }) => {
    const currency = await findCurrencyByCode(request, BASE_CURRENCY_CODE);

    const response = await sendRestRequest(request, ENDPOINTS.CURRENCY(numericIdOf(currency.id)));

    expect(response.status()).toBe(200);
    expect(await response.json()).toEqual(currency);
  });

  test('should return 404 for non-existent currency', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CURRENCY(999999));

    expect(response.status()).toBe(404);
    expect((await response.json()).detail).toBe(REST_GAPS_MESSAGES.GENERIC_NOT_FOUND);
  });

  test('should reject post on currencies as read only', async ({ request }) => {
    const response = await sendRestRequest(request, ENDPOINTS.CURRENCIES, {
      method: 'POST',
      data: { code: 'XYZ', name: 'Hack Coin' },
    });
    const body = await response.json();

    expect(response.status(), JSON.stringify(body)).toBe(405);
    expect(body.message).toContain('The POST method is not supported for route api/shop/currencies.');

    const listing = await sendRestRequest(request, ENDPOINTS.CURRENCIES, { params: { per_page: '50' } });
    expect((await listing.json()).some((row: any) => row.code === 'XYZ')).toBe(false);
  });
});
