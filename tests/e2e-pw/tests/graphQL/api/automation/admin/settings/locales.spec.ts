import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_LOCALE_CREATE,
  ADMIN_LOCALE_DETAIL,
  ADMIN_LOCALE_UPDATE,
} from '../../../../graphql/Queries/admin/settings/locales.queries';

test.describe.configure({ timeout: 60_000 });

const LOCALE_IRI = (id: number) => `/api/admin/settings/locales/${id}`;
const UNKNOWN_IRI = LOCALE_IRI(999999999);

const NOT_FOUND = 'Locale not found.';
const DELETED = 'Locale deleted successfully.';
const MASS_DELETED = 'Locales deleted successfully.';

const LOCALES_LIST = `
  query adminSettingsLocales($first: Int, $code: String) {
    adminSettingsLocales(first: $first, code: $code) {
      totalCount
      edges { node { _id code name direction } }
    }
  }
`;

const LOCALE_DELETE = `
  mutation deleteAdminSettingsLocale($id: ID!) {
    deleteAdminSettingsLocale(input: { id: $id }) { adminSettingsLocale { _id code message } }
  }
`;

const LOCALE_MASS_DELETE = `
  mutation createAdminSettingsLocaleMassDelete($indices: Iterable!) {
    createAdminSettingsLocaleMassDelete(input: { indices: $indices }) {
      adminSettingsLocaleMassDelete { deleted skipped message }
    }
  }
`;

interface CreatedLocale {
  _id: number;
  id: string;
  code: string;
  name: string;
}

function uniqueCode(): string {
  return `e2e_gql_${Date.now().toString(36)}${Math.floor(Math.random() * 1_000_000).toString(36)}`;
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

async function createLocale(request: APIRequestContext): Promise<CreatedLocale> {
  const code = uniqueCode();
  const name = `e2e gql locale ${code}`;
  const locale = dataOf(await gql(request, ADMIN_LOCALE_CREATE, { code, name, direction: 'ltr' }), 'createAdminSettingsLocale')
    .adminSettingsLocale;
  expect(locale).toEqual({ id: LOCALE_IRI(locale._id), _id: locale._id, code, name, direction: 'ltr' });

  return { _id: locale._id, id: locale.id, code, name };
}

async function localeCount(request: APIRequestContext, code: string): Promise<number> {
  return dataOf(await gql(request, LOCALES_LIST, { first: 5, code }), 'adminSettingsLocales').totalCount;
}

test.describe('admin settings locales graphql api', () => {
  let created: CreatedLocale[];

  test.beforeEach(() => {
    created = [];
  });

  test.afterEach(async ({ request }) => {
    for (const locale of created) {
      await sendAdminGraphQLRequest(request, LOCALE_DELETE, { id: locale.id });
    }
  });

  test('listing filtered by code returns only the created locale', async ({ request }) => {
    const locale = await createLocale(request);
    created.push(locale);

    const connection = dataOf(await gql(request, LOCALES_LIST, { first: 5, code: locale.code }), 'adminSettingsLocales');
    expect(connection.totalCount).toBe(1);
    expect(connection.edges[0].node).toEqual({ _id: locale._id, code: locale.code, name: locale.name, direction: 'ltr' });
  });

  test('detail returns the created locale', async ({ request }) => {
    const locale = await createLocale(request);
    created.push(locale);

    const node = dataOf(await gql(request, ADMIN_LOCALE_DETAIL, { id: locale.id }), 'adminSettingsLocale');
    expect(node).toEqual({ id: locale.id, _id: locale._id, code: locale.code, name: locale.name, direction: 'ltr' });
  });

  test('detail of an unknown locale reports not found', async ({ request }) => {
    const body = await gql(request, ADMIN_LOCALE_DETAIL, { id: UNKNOWN_IRI });
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(body.data?.adminSettingsLocale ?? null).toBeNull();
  });

  test('update renames the locale and delete removes it', async ({ request }) => {
    const locale = await createLocale(request);
    created.push(locale);
    const renamed = `e2e gql renamed ${locale.code}`;

    const updated = dataOf(await gql(request, ADMIN_LOCALE_UPDATE, { id: locale.id, name: renamed }), 'updateAdminSettingsLocale')
      .adminSettingsLocale;
    expect(updated).toEqual({ id: locale.id, _id: locale._id, name: renamed });

    const reread = dataOf(await gql(request, ADMIN_LOCALE_DETAIL, { id: locale.id }), 'adminSettingsLocale');
    expect(reread).toEqual(expect.objectContaining({ code: locale.code, name: renamed, direction: 'ltr' }));

    const deleted = dataOf(await gql(request, LOCALE_DELETE, { id: locale.id }), 'deleteAdminSettingsLocale').adminSettingsLocale;
    expect(deleted).toEqual({ _id: locale._id, code: locale.code, message: DELETED });
    created = [];

    expect(firstError(await gql(request, ADMIN_LOCALE_DETAIL, { id: locale.id }))).toBe(NOT_FOUND);
  });

  test('create with an invalid direction is rejected', async ({ request }) => {
    const code = uniqueCode();

    expect(firstError(await gql(request, ADMIN_LOCALE_CREATE, { code, name: 'e2e gql bad direction', direction: 'xyz' }))).toBe(
      'The selected direction is invalid.',
    );
    expect(await localeCount(request, code)).toBe(0);
  });

  test('create with a taken code is rejected', async ({ request }) => {
    const locale = await createLocale(request);
    created.push(locale);

    expect(firstError(await gql(request, ADMIN_LOCALE_CREATE, { code: locale.code, name: 'e2e gql twin', direction: 'ltr' }))).toBe(
      'The code has already been taken.',
    );
    expect(await localeCount(request, locale.code)).toBe(1);
  });

  test('mass-delete removes the freshly created locale', async ({ request }) => {
    const locale = await createLocale(request);
    created.push(locale);

    const result = dataOf(await gql(request, LOCALE_MASS_DELETE, { indices: [locale._id] }), 'createAdminSettingsLocaleMassDelete')
      .adminSettingsLocaleMassDelete;
    expect(result).toEqual({ deleted: [locale._id], skipped: [], message: MASS_DELETED });
    created = [];

    expect(firstError(await gql(request, ADMIN_LOCALE_DETAIL, { id: locale.id }))).toBe(NOT_FOUND);
  });

  test('mass-delete without indices is rejected', async ({ request }) => {
    expect(firstError(await gql(request, LOCALE_MASS_DELETE, { indices: [] }))).toBe(
      'The indices field is required and must be a non-empty array.',
    );
  });
});
