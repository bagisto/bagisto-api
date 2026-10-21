import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_CURRENCY_CREATE,
  ADMIN_CURRENCY_UPDATE,
} from '../../../../graphql/Queries/admin/settings/currencies.queries';

test.describe.configure({ timeout: 60_000 });

const CURRENCY_IRI = (id: number) => `/api/admin/settings/currencies/${id}`;
const UNKNOWN_IRI = CURRENCY_IRI(999999999);

const NOT_FOUND = 'Currency not found.';
const DELETED = 'Currency deleted successfully.';
const MASS_DELETED = 'Currencies deleted successfully.';
const CODE_TAKEN = 'The code has already been taken.';

const CURRENCIES_LIST = `
  query adminSettingsCurrencies($first: Int, $code: String) {
    adminSettingsCurrencies(first: $first, code: $code) {
      totalCount
      edges { node { id _id code name symbol decimal } }
    }
  }
`;

const CURRENCY_DETAIL = `
  query adminSettingsCurrency($id: ID!) {
    adminSettingsCurrency(id: $id) { id _id code name symbol decimal }
  }
`;

const CURRENCY_DELETE = `
  mutation deleteAdminSettingsCurrency($id: ID!) {
    deleteAdminSettingsCurrency(input: { id: $id }) { adminSettingsCurrency { _id code message } }
  }
`;

const CURRENCY_MASS_DELETE = `
  mutation createAdminSettingsCurrencyMassDelete($indices: Iterable!) {
    createAdminSettingsCurrencyMassDelete(input: { indices: $indices }) {
      adminSettingsCurrencyMassDelete { deleted message }
    }
  }
`;

interface CreatedCurrency {
  _id: number;
  id: string;
  code: string;
  name: string;
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

async function createCurrency(request: APIRequestContext): Promise<CreatedCurrency> {
  for (let attempt = 0; attempt < 10; attempt++) {
    const code = randomCode();
    const name = `e2e gql currency ${code} ${Date.now()}`;
    const body = await gql(request, ADMIN_CURRENCY_CREATE, { code, name, symbol: '¤', decimal: 2 });

    if (firstError(body) === CODE_TAKEN) {
      continue;
    }

    const currency = dataOf(body, 'createAdminSettingsCurrency').adminSettingsCurrency;
    expect(currency).toEqual(expect.objectContaining({ code, name, symbol: '¤' }));
    expect(currency.id).toBe(CURRENCY_IRI(currency._id));

    return { _id: currency._id, id: currency.id, code, name };
  }

  throw new Error('could not find a free three letter currency code in ten attempts');
}

test.describe('admin settings currencies graphql api', () => {
  let created: CreatedCurrency[];

  test.beforeEach(() => {
    created = [];
  });

  test.afterEach(async ({ request }) => {
    for (const currency of created) {
      await sendAdminGraphQLRequest(request, CURRENCY_DELETE, { id: currency.id });
    }
  });

  test('listing filtered by code returns only the created currency', async ({ request }) => {
    const currency = await createCurrency(request);
    created.push(currency);

    const connection = dataOf(await gql(request, CURRENCIES_LIST, { code: currency.code, first: 5 }), 'adminSettingsCurrencies');
    expect(connection.totalCount).toBe(1);
    expect(connection.edges[0].node).toEqual(expect.objectContaining({
      _id: currency._id,
      code: currency.code,
      name: currency.name,
      symbol: '¤',
      decimal: 2,
    }));
  });

  test('detail returns the created currency by iri', async ({ request }) => {
    const currency = await createCurrency(request);
    created.push(currency);

    const node = dataOf(await gql(request, CURRENCY_DETAIL, { id: currency.id }), 'adminSettingsCurrency');
    expect(node).toEqual(expect.objectContaining({ _id: currency._id, code: currency.code, name: currency.name, decimal: 2 }));
  });

  test('detail of an unknown currency reports not found', async ({ request }) => {
    const body = await gql(request, CURRENCY_DETAIL, { id: UNKNOWN_IRI });
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(body.data?.adminSettingsCurrency ?? null).toBeNull();
  });

  test('create currency stores the code and name', async ({ request }) => {
    const currency = await createCurrency(request);
    created.push(currency);

    const connection = dataOf(await gql(request, CURRENCIES_LIST, { code: currency.code, first: 5 }), 'adminSettingsCurrencies');
    expect(connection.edges.map((edge: any) => edge.node._id)).toEqual([currency._id]);
  });

  test('create currency with an empty code is rejected', async ({ request }) => {
    const name = `e2e gql no code ${Date.now()}`;
    const body = await gql(request, ADMIN_CURRENCY_CREATE, { code: '', name, symbol: '¤', decimal: 2 });
    expect(firstError(body)).toBe('The code field is required.');
    expect(body.data?.createAdminSettingsCurrency ?? null).toBeNull();
  });

  test('create currency with a taken code is rejected', async ({ request }) => {
    const currency = await createCurrency(request);
    created.push(currency);

    const body = await gql(request, ADMIN_CURRENCY_CREATE, { code: currency.code, name: `${currency.name} again`, symbol: '¤', decimal: 2 });
    expect(firstError(body)).toBe(CODE_TAKEN);

    const connection = dataOf(await gql(request, CURRENCIES_LIST, { code: currency.code, first: 5 }), 'adminSettingsCurrencies');
    expect(connection.totalCount).toBe(1);
  });

  test('update renames the currency and delete removes it', async ({ request }) => {
    const currency = await createCurrency(request);
    created.push(currency);
    const renamed = `e2e gql renamed ${currency.code} ${Date.now()}`;

    const updated = dataOf(await gql(request, ADMIN_CURRENCY_UPDATE, { id: currency.id, name: renamed }), 'updateAdminSettingsCurrency').adminSettingsCurrency;
    expect(updated).toEqual(expect.objectContaining({ _id: currency._id, code: currency.code, name: renamed }));
    expect(dataOf(await gql(request, CURRENCY_DETAIL, { id: currency.id }), 'adminSettingsCurrency').name).toBe(renamed);

    const deleted = dataOf(await gql(request, CURRENCY_DELETE, { id: currency.id }), 'deleteAdminSettingsCurrency').adminSettingsCurrency;
    expect(deleted).toEqual(expect.objectContaining({ _id: currency._id, code: currency.code, message: DELETED }));
    created = [];

    expect(firstError(await gql(request, CURRENCY_DETAIL, { id: currency.id }))).toBe(NOT_FOUND);
  });

  test('update of an unknown currency reports not found', async ({ request }) => {
    const body = await gql(request, ADMIN_CURRENCY_UPDATE, { id: UNKNOWN_IRI, name: 'e2e gql nobody' });
    expect(firstError(body)).toBe(NOT_FOUND);
  });

  test('mass-delete removes the freshly created currencies', async ({ request }) => {
    const first = await createCurrency(request);
    const second = await createCurrency(request);
    created.push(first, second);

    const result = dataOf(
      await gql(request, CURRENCY_MASS_DELETE, { indices: [first._id, second._id] }),
      'createAdminSettingsCurrencyMassDelete',
    ).adminSettingsCurrencyMassDelete;
    expect([...result.deleted].sort((a: number, b: number) => a - b)).toEqual([first._id, second._id].sort((a, b) => a - b));
    expect(result.message).toBe(MASS_DELETED);
    created = [];

    expect(firstError(await gql(request, CURRENCY_DETAIL, { id: first.id }))).toBe(NOT_FOUND);
    expect(firstError(await gql(request, CURRENCY_DETAIL, { id: second.id }))).toBe(NOT_FOUND);
  });

  test('mass-delete without indices is rejected', async ({ request }) => {
    const body = await gql(request, CURRENCY_MASS_DELETE, { indices: [] });
    expect(firstError(body)).toBe('The indices field is required and must be a non-empty array.');
  });
});
