import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import { ADMIN_TAX_CATEGORY_CREATE } from '../../../../graphql/Queries/admin/settings/taxCategories.queries';
import { ADMIN_TAX_RATE_CREATE, ADMIN_TAX_RATE_DELETE } from '../../../../graphql/Queries/admin/settings/taxRates.queries';

test.describe.configure({ timeout: 60_000 });

const CATEGORY_IRI = (id: number) => `/api/admin/settings/tax-categories/${id}`;
const RATE_IRI = (id: number) => `/api/admin/settings/tax-rates/${id}`;
const UNKNOWN_IRI = CATEGORY_IRI(999999999);

const NOT_FOUND = 'Tax category not found.';
const DELETED = 'Tax category deleted successfully.';
const IN_USE = 'This tax category still has tax rates attached and cannot be deleted.';
const CODE_TAKEN = 'The code has already been taken.';

const CATEGORIES_LIST = `
  query adminSettingsTaxCategories($first: Int, $code: String) {
    adminSettingsTaxCategories(first: $first, code: $code) {
      totalCount
      edges { node { _id code name description } }
    }
  }
`;

const CATEGORY_DETAIL = `
  query adminSettingsTaxCategory($id: ID!) {
    adminSettingsTaxCategory(id: $id) {
      _id
      code
      name
      description
      taxRates { edges { node { _id identifier taxRate } } }
    }
  }
`;

const CATEGORY_UPDATE = `
  mutation updateAdminSettingsTaxCategory($id: ID!, $code: String, $name: String, $description: String, $taxrates: Iterable) {
    updateAdminSettingsTaxCategory(input: { id: $id, code: $code, name: $name, description: $description, taxrates: $taxrates }) {
      adminSettingsTaxCategory { _id code name description }
    }
  }
`;

const CATEGORY_DELETE = `
  mutation deleteAdminSettingsTaxCategory($id: ID!) {
    deleteAdminSettingsTaxCategory(input: { id: $id }) { adminSettingsTaxCategory { _id code message } }
  }
`;

interface TaxRate {
  _id: number;
  identifier: string;
}

function uniqueSuffix(): string {
  return `${Date.now().toString(36)}${Math.floor(Math.random() * 1_000_000).toString(36)}`;
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

async function createRate(request: APIRequestContext): Promise<TaxRate> {
  const identifier = `e2e-gql-tc-rate-${uniqueSuffix()}`;
  const rate = dataOf(
    await gql(request, ADMIN_TAX_RATE_CREATE, { identifier, country: 'US', state: 'NY', taxRate: 8.5, isZip: false, zipCode: '10001' }),
    'createAdminSettingsTaxRate',
  ).adminSettingsTaxRate;
  expect(rate.identifier).toBe(identifier);

  return { _id: rate._id, identifier };
}

async function categoryCount(request: APIRequestContext, code: string): Promise<number> {
  return dataOf(await gql(request, CATEGORIES_LIST, { first: 5, code }), 'adminSettingsTaxCategories').totalCount;
}

test.describe('admin settings tax categories graphql api', () => {
  let rate: TaxRate;
  let rateDeleted: boolean;
  let categoryIds: number[];

  test.beforeEach(async ({ request }) => {
    categoryIds = [];
    rateDeleted = false;
    rate = await createRate(request);
  });

  test.afterEach(async ({ request }) => {
    if (!rateDeleted) {
      await sendAdminGraphQLRequest(request, ADMIN_TAX_RATE_DELETE, { id: RATE_IRI(rate._id) });
    }

    for (const id of categoryIds) {
      await sendAdminGraphQLRequest(request, CATEGORY_DELETE, { id: CATEGORY_IRI(id) });
    }
  });

  async function createCategory(request: APIRequestContext): Promise<{ _id: number; code: string; name: string }> {
    const code = `e2e-gql-tc-${uniqueSuffix()}`;
    const name = `e2e gql tax category ${code}`;
    const category = dataOf(
      await gql(request, ADMIN_TAX_CATEGORY_CREATE, { code, name, description: 'e2e description', taxrates: [rate._id] }),
      'createAdminSettingsTaxCategory',
    ).adminSettingsTaxCategory;
    expect(category).toEqual({ id: CATEGORY_IRI(category._id), _id: category._id, code, name });
    categoryIds.push(category._id);

    return { _id: category._id, code, name };
  }

  test('listing filtered by code returns only the created category', async ({ request }) => {
    const category = await createCategory(request);

    const connection = dataOf(await gql(request, CATEGORIES_LIST, { first: 5, code: category.code }), 'adminSettingsTaxCategories');
    expect(connection.totalCount).toBe(1);
    expect(connection.edges[0].node).toEqual({ _id: category._id, code: category.code, name: category.name, description: 'e2e description' });
  });

  test('detail returns the created category with its attached rate', async ({ request }) => {
    const category = await createCategory(request);

    const node = dataOf(await gql(request, CATEGORY_DETAIL, { id: CATEGORY_IRI(category._id) }), 'adminSettingsTaxCategory');
    expect(node).toEqual(expect.objectContaining({ _id: category._id, code: category.code, name: category.name, description: 'e2e description' }));
    expect(node.taxRates.edges.map((edge: any) => edge.node)).toEqual([{ _id: rate._id, identifier: rate.identifier, taxRate: 8.5 }]);
  });

  test('detail of an unknown category reports not found', async ({ request }) => {
    const body = await gql(request, CATEGORY_DETAIL, { id: UNKNOWN_IRI });
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(body.data?.adminSettingsTaxCategory ?? null).toBeNull();
  });

  test('update with the full payload renames the category', async ({ request }) => {
    const category = await createCategory(request);
    const renamed = `${category.name} renamed`;

    const updated = dataOf(
      await gql(request, CATEGORY_UPDATE, {
        id: CATEGORY_IRI(category._id),
        code: category.code,
        name: renamed,
        description: 'updated description',
        taxrates: [rate._id],
      }),
      'updateAdminSettingsTaxCategory',
    ).adminSettingsTaxCategory;
    expect(updated).toEqual({ _id: category._id, code: category.code, name: renamed, description: 'updated description' });

    const reread = dataOf(await gql(request, CATEGORY_DETAIL, { id: CATEGORY_IRI(category._id) }), 'adminSettingsTaxCategory');
    expect(reread).toEqual(expect.objectContaining({ name: renamed, description: 'updated description' }));
    expect(reread.taxRates.edges.map((edge: any) => edge.node._id)).toEqual([rate._id]);
  });

  test('update without a code is rejected and keeps the category', async ({ request }) => {
    const category = await createCategory(request);

    const body = await gql(request, CATEGORY_UPDATE, {
      id: CATEGORY_IRI(category._id),
      name: `${category.name} renamed`,
      description: 'updated description',
      taxrates: [rate._id],
    });
    expect(firstError(body)).toBe('The code field is required.');
    expect(dataOf(await gql(request, CATEGORY_DETAIL, { id: CATEGORY_IRI(category._id) }), 'adminSettingsTaxCategory').name).toBe(category.name);
  });

  test('create with a taken code is rejected', async ({ request }) => {
    const category = await createCategory(request);

    const body = await gql(request, ADMIN_TAX_CATEGORY_CREATE, {
      code: category.code,
      name: 'e2e gql twin',
      description: 'twin',
      taxrates: [rate._id],
    });
    expect(firstError(body)).toBe(CODE_TAKEN);
    expect(await categoryCount(request, category.code)).toBe(1);
  });

  test('delete is refused while a rate is attached and succeeds once the rate is removed', async ({ request }) => {
    const category = await createCategory(request);

    expect(firstError(await gql(request, CATEGORY_DELETE, { id: CATEGORY_IRI(category._id) }))).toBe(IN_USE);
    expect(await categoryCount(request, category.code)).toBe(1);

    expect(dataOf(await gql(request, ADMIN_TAX_RATE_DELETE, { id: RATE_IRI(rate._id) }), 'deleteAdminSettingsTaxRate')).toBeTruthy();
    rateDeleted = true;

    const deleted = dataOf(await gql(request, CATEGORY_DELETE, { id: CATEGORY_IRI(category._id) }), 'deleteAdminSettingsTaxCategory')
      .adminSettingsTaxCategory;
    expect(deleted).toEqual({ _id: category._id, code: category.code, message: DELETED });
    categoryIds = [];

    expect(firstError(await gql(request, CATEGORY_DETAIL, { id: CATEGORY_IRI(category._id) }))).toBe(NOT_FOUND);
  });
});
