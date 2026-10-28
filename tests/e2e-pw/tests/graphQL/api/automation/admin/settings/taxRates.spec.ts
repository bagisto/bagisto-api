import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_TAX_RATE_CREATE,
  ADMIN_TAX_RATE_DETAIL,
  ADMIN_TAX_RATE_UPDATE,
} from '../../../../graphql/Queries/admin/settings/taxRates.queries';

test.describe.configure({ timeout: 60_000 });

const RATE_IRI = (id: number) => `/api/admin/settings/tax-rates/${id}`;
const UNKNOWN_IRI = RATE_IRI(999999999);

const NOT_FOUND = 'Tax rate not found.';
const DELETED = 'Tax rate deleted successfully.';
const ZIP_CODE_REQUIRED = 'The zip_code field is required when is_zip is false.';
const ZIP_RANGE_REQUIRED = 'Both zip_from and zip_to are required when is_zip is true.';

const RATES_LIST = `
  query adminSettingsTaxRates($first: Int, $identifier: String) {
    adminSettingsTaxRates(first: $first, identifier: $identifier) {
      totalCount
      edges { node { _id identifier country state taxRate isZip zipCode } }
    }
  }
`;

const RATE_DELETE = `
  mutation deleteAdminSettingsTaxRate($id: ID!) {
    deleteAdminSettingsTaxRate(input: { id: $id }) { adminSettingsTaxRate { _id identifier message } }
  }
`;

function uniqueIdentifier(): string {
  return `e2e-gql-tr-${Date.now().toString(36)}${Math.floor(Math.random() * 1_000_000).toString(36)}`;
}

function zipCodeRate(identifier: string): Record<string, any> {
  return { identifier, country: 'US', state: 'NY', taxRate: 8.5, isZip: false, zipCode: '10001' };
}

function zipRangeRate(identifier: string): Record<string, any> {
  return { identifier, country: 'US', state: 'NY', taxRate: 8.5, isZip: true, zipFrom: '10000', zipTo: '10999' };
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

async function createRate(request: APIRequestContext, variables: Record<string, any>): Promise<any> {
  const rate = dataOf(await gql(request, ADMIN_TAX_RATE_CREATE, variables), 'createAdminSettingsTaxRate').adminSettingsTaxRate;
  expect(rate.identifier).toBe(variables.identifier);
  expect(rate.id).toBe(RATE_IRI(rate._id));

  return rate;
}

async function rateCount(request: APIRequestContext, identifier: string): Promise<number> {
  return dataOf(await gql(request, RATES_LIST, { first: 5, identifier }), 'adminSettingsTaxRates').totalCount;
}

test.describe('admin settings tax rates graphql api', () => {
  let createdIds: number[];

  test.beforeEach(() => {
    createdIds = [];
  });

  test.afterEach(async ({ request }) => {
    for (const id of createdIds) {
      await sendAdminGraphQLRequest(request, RATE_DELETE, { id: RATE_IRI(id) });
    }
  });

  test('listing filtered by identifier returns only the created rate', async ({ request }) => {
    const identifier = uniqueIdentifier();
    const rate = await createRate(request, zipCodeRate(identifier));
    createdIds.push(rate._id);

    const connection = dataOf(await gql(request, RATES_LIST, { first: 5, identifier }), 'adminSettingsTaxRates');
    expect(connection.totalCount).toBe(1);
    expect(connection.edges[0].node).toEqual({
      _id: rate._id,
      identifier,
      country: 'US',
      state: 'NY',
      taxRate: 8.5,
      isZip: false,
      zipCode: '10001',
    });
  });

  test('detail of an unknown tax rate reports not found', async ({ request }) => {
    const body = await gql(request, ADMIN_TAX_RATE_DETAIL, { id: UNKNOWN_IRI });
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(body.data?.adminSettingsTaxRate ?? null).toBeNull();
  });

  test('create with a zip code then update the rate and delete it', async ({ request }) => {
    const identifier = uniqueIdentifier();
    const rate = await createRate(request, zipCodeRate(identifier));
    createdIds.push(rate._id);

    const updated = dataOf(await gql(request, ADMIN_TAX_RATE_UPDATE, { id: rate.id, taxRate: 9 }), 'updateAdminSettingsTaxRate')
      .adminSettingsTaxRate;
    expect(updated).toEqual(expect.objectContaining({ _id: rate._id, taxRate: 9 }));

    const reread = dataOf(await gql(request, ADMIN_TAX_RATE_DETAIL, { id: rate.id }), 'adminSettingsTaxRate');
    expect(reread).toEqual(expect.objectContaining({ identifier, taxRate: 9, isZip: false, zipCode: '10001', zipFrom: null, zipTo: null }));

    const deleted = dataOf(await gql(request, RATE_DELETE, { id: rate.id }), 'deleteAdminSettingsTaxRate').adminSettingsTaxRate;
    expect(deleted).toEqual({ _id: rate._id, identifier, message: DELETED });
    createdIds = [];

    expect(firstError(await gql(request, ADMIN_TAX_RATE_DETAIL, { id: rate.id }))).toBe(NOT_FOUND);
  });

  test('create with a zip range stores the range and clears the zip code', async ({ request }) => {
    const identifier = uniqueIdentifier();
    const rate = await createRate(request, { ...zipRangeRate(identifier), zipCode: '99999' });
    createdIds.push(rate._id);

    const detail = dataOf(await gql(request, ADMIN_TAX_RATE_DETAIL, { id: rate.id }), 'adminSettingsTaxRate');
    expect(detail).toEqual(expect.objectContaining({ identifier, isZip: true, zipCode: null, zipFrom: '10000', zipTo: '10999' }));
  });

  test('create without a zip code when is zip is false is rejected', async ({ request }) => {
    const identifier = uniqueIdentifier();
    expect(firstError(await gql(request, ADMIN_TAX_RATE_CREATE, { ...zipCodeRate(identifier), zipCode: null }))).toBe(ZIP_CODE_REQUIRED);
    expect(await rateCount(request, identifier)).toBe(0);
  });

  test('create without a zip range when is zip is true is rejected', async ({ request }) => {
    const identifier = uniqueIdentifier();

    expect(firstError(await gql(request, ADMIN_TAX_RATE_CREATE, { ...zipRangeRate(identifier), zipTo: null }))).toBe(ZIP_RANGE_REQUIRED);
    expect(await rateCount(request, identifier)).toBe(0);
  });

  test('create with a taken identifier is rejected', async ({ request }) => {
    const identifier = uniqueIdentifier();
    const rate = await createRate(request, zipCodeRate(identifier));
    createdIds.push(rate._id);

    expect(firstError(await gql(request, ADMIN_TAX_RATE_CREATE, zipCodeRate(identifier)))).toBe('The identifier has already been taken.');
    expect(await rateCount(request, identifier)).toBe(1);
  });

  test('update of an unknown tax rate reports not found', async ({ request }) => {
    expect(firstError(await gql(request, ADMIN_TAX_RATE_UPDATE, { id: UNKNOWN_IRI, taxRate: 5 }))).toBe(NOT_FOUND);
  });
});
