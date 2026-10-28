import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import { ADMIN_CURRENCY_CREATE, ADMIN_CURRENCY_DELETE } from '../../../../graphql/Queries/admin/settings/currencies.queries';
import {
  ADMIN_EXCHANGE_RATE_CREATE,
  ADMIN_EXCHANGE_RATE_UPDATE,
} from '../../../../graphql/Queries/admin/settings/exchangeRates.queries';

test.describe.configure({ timeout: 60_000 });

const RATE_IRI = (id: number) => `/api/admin/settings/exchange-rates/${id}`;
const CURRENCY_IRI = (id: number) => `/api/admin/settings/currencies/${id}`;
const UNKNOWN_IRI = RATE_IRI(999999999);

const NOT_FOUND = 'Exchange rate not found.';
const DELETED = 'Exchange rate deleted successfully.';
const MASS_DELETED = 'Exchange rates deleted successfully.';
const DUPLICATE_PAIR = 'An exchange rate for this target currency already exists. Update the existing row or delete it first.';
const CODE_TAKEN = 'The code has already been taken.';

const RATES_LIST = `
  query adminSettingsExchangeRates($first: Int, $targetCurrency: Int) {
    adminSettingsExchangeRates(first: $first, target_currency: $targetCurrency) {
      totalCount
      edges { node { _id targetCurrency targetCurrencyCode rate } }
    }
  }
`;

const RATE_DETAIL = `
  query adminSettingsExchangeRate($id: ID!) {
    adminSettingsExchangeRate(id: $id) { id _id targetCurrency targetCurrencyCode rate }
  }
`;

const RATE_DELETE = `
  mutation deleteAdminSettingsExchangeRate($id: ID!) {
    deleteAdminSettingsExchangeRate(input: { id: $id }) { adminSettingsExchangeRate { _id targetCurrency message } }
  }
`;

const RATE_MASS_DELETE = `
  mutation createAdminSettingsExchangeRateMassDelete($indices: Iterable!) {
    createAdminSettingsExchangeRateMassDelete(input: { indices: $indices }) {
      adminSettingsExchangeRateMassDelete { deleted message }
    }
  }
`;

interface FreshCurrency {
  _id: number;
  code: string;
}

function randomCode(): string {
  const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  return [0, 1, 2].map(() => letters[Math.floor(Math.random() * 26)]).join('');
}

async function gql(request: APIRequestContext, query: string, variables: Record<string, any> = {}): Promise<any> {
  const resp = await sendAdminGraphQLRequest(request, query, variables);
  expect(resp.status()).toBe(200);
  return resp.json();
}

function dataOf(body: any, field: string): any {
  expect(body.errors, JSON.stringify(body.errors)).toBeUndefined();
  return body.data[field];
}

function firstError(body: any): string | undefined {
  return body.errors?.[0]?.message;
}

async function createCurrency(request: APIRequestContext): Promise<FreshCurrency> {
  for (let attempt = 0; attempt < 10; attempt++) {
    const code = randomCode();
    const body = await gql(request, ADMIN_CURRENCY_CREATE, { code, name: `e2e gql exchange ${code}`, symbol: '¤', decimal: 2 });

    if (firstError(body) === CODE_TAKEN) {
      continue;
    }

    const currency = dataOf(body, 'createAdminSettingsCurrency').adminSettingsCurrency;
    expect(currency.code).toBe(code);

    return { _id: currency._id, code };
  }

  throw new Error('could not find a free three letter currency code in ten attempts');
}

async function createRate(request: APIRequestContext, currency: FreshCurrency, rate: number): Promise<any> {
  const created = dataOf(
    await gql(request, ADMIN_EXCHANGE_RATE_CREATE, { targetCurrency: currency._id, rate }),
    'createAdminSettingsExchangeRate',
  ).adminSettingsExchangeRate;
  expect(created).toEqual(expect.objectContaining({ targetCurrency: currency._id, rate }));
  expect(created.id).toBe(RATE_IRI(created._id));

  return created;
}

test.describe('admin settings exchange rates graphql api', () => {
  let currency: FreshCurrency;
  let rateIds: number[];

  test.beforeEach(async ({ request }) => {
    rateIds = [];
    currency = await createCurrency(request);
  });

  test.afterEach(async ({ request }) => {
    for (const id of rateIds) {
      await sendAdminGraphQLRequest(request, RATE_DELETE, { id: RATE_IRI(id) });
    }

    await sendAdminGraphQLRequest(request, ADMIN_CURRENCY_DELETE, { id: CURRENCY_IRI(currency._id) });
  });

  test('listing filtered by target currency returns only the created rate', async ({ request }) => {
    const created = await createRate(request, currency, 1.2345);
    rateIds.push(created._id);

    const connection = dataOf(await gql(request, RATES_LIST, { first: 5, targetCurrency: currency._id }), 'adminSettingsExchangeRates');
    expect(connection.totalCount).toBe(1);
    expect(connection.edges[0].node).toEqual({
      _id: created._id,
      targetCurrency: currency._id,
      targetCurrencyCode: currency.code,
      rate: 1.2345,
    });
  });

  test('detail of an unknown exchange rate reports not found', async ({ request }) => {
    const body = await gql(request, RATE_DETAIL, { id: UNKNOWN_IRI });
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(body.data?.adminSettingsExchangeRate ?? null).toBeNull();
  });

  test('create update and delete lifecycle', async ({ request }) => {
    const created = await createRate(request, currency, 1.2345);
    rateIds.push(created._id);

    const detail = dataOf(await gql(request, RATE_DETAIL, { id: created.id }), 'adminSettingsExchangeRate');
    expect(detail).toEqual(expect.objectContaining({ _id: created._id, targetCurrency: currency._id, targetCurrencyCode: currency.code, rate: 1.2345 }));

    const updated = dataOf(await gql(request, ADMIN_EXCHANGE_RATE_UPDATE, { id: created.id, rate: 1.5 }), 'updateAdminSettingsExchangeRate')
      .adminSettingsExchangeRate;
    expect(updated).toEqual(expect.objectContaining({ _id: created._id, rate: 1.5 }));
    expect(dataOf(await gql(request, RATE_DETAIL, { id: created.id }), 'adminSettingsExchangeRate').rate).toBe(1.5);

    const deleted = dataOf(await gql(request, RATE_DELETE, { id: created.id }), 'deleteAdminSettingsExchangeRate').adminSettingsExchangeRate;
    expect(deleted).toEqual({ _id: created._id, targetCurrency: currency._id, message: DELETED });
    rateIds = [];

    expect(firstError(await gql(request, RATE_DETAIL, { id: created.id }))).toBe(NOT_FOUND);
  });

  test('create a second rate for the same target currency is rejected', async ({ request }) => {
    const created = await createRate(request, currency, 1.2345);
    rateIds.push(created._id);

    expect(firstError(await gql(request, ADMIN_EXCHANGE_RATE_CREATE, { targetCurrency: currency._id, rate: 2 }))).toBe(DUPLICATE_PAIR);

    const connection = dataOf(await gql(request, RATES_LIST, { first: 5, targetCurrency: currency._id }), 'adminSettingsExchangeRates');
    expect(connection.totalCount).toBe(1);
  });

  test('create with a zero rate is rejected', async ({ request }) => {
    const body = await gql(request, ADMIN_EXCHANGE_RATE_CREATE, { targetCurrency: currency._id, rate: 0 });
    expect(firstError(body)).toBe('The rate field must be greater than 0.');

    const connection = dataOf(await gql(request, RATES_LIST, { first: 5, targetCurrency: currency._id }), 'adminSettingsExchangeRates');
    expect(connection.totalCount).toBe(0);
  });

  test('mass-delete removes the created rate', async ({ request }) => {
    const created = await createRate(request, currency, 1.75);
    rateIds.push(created._id);

    const result = dataOf(await gql(request, RATE_MASS_DELETE, { indices: [created._id] }), 'createAdminSettingsExchangeRateMassDelete')
      .adminSettingsExchangeRateMassDelete;
    expect(result).toEqual({ deleted: [created._id], message: MASS_DELETED });
    rateIds = [];

    expect(firstError(await gql(request, RATE_DETAIL, { id: created.id }))).toBe(NOT_FOUND);
  });

  test('mass-delete with empty indices is rejected', async ({ request }) => {
    expect(firstError(await gql(request, RATE_MASS_DELETE, { indices: [] }))).toBe(
      'The indices field is required and must be a non-empty array.',
    );
  });
});
