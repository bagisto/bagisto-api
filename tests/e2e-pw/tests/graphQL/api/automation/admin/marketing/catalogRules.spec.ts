import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import {
  ADMIN_CATALOG_RULE_QUERY,
  ADMIN_CATALOG_RULE_CREATE_MUTATION,
} from '../../../../graphql/Queries/admin/marketing/catalogRules.queries';

test.describe.configure({ timeout: 120_000 });

const UNKNOWN_IRI = '/api/admin/marketing/catalog-rules/999999999';

const NOT_FOUND = 'Catalog rule not found.';

const DEFAULT_CHANNEL_ID = 1;

const GENERAL_CUSTOMER_GROUP_ID = 2;

const CATALOG_RULES_FILTER_QUERY = `
  query adminMarketingCatalogRules($first: Int, $name: String, $status: Int) {
    adminMarketingCatalogRules(first: $first, name: $name, status: $status) {
      totalCount
      edges { node { _id name status actionType discountAmount } }
    }
  }
`;

const CATALOG_RULE_DELETE_MUTATION = `
  mutation deleteAdminMarketingCatalogRule($input: deleteAdminMarketingCatalogRuleInput!) {
    deleteAdminMarketingCatalogRule(input: $input) {
      adminMarketingCatalogRule { _id message }
    }
  }
`;

const CATALOG_RULE_MASS_DELETE_MUTATION = `
  mutation createAdminMarketingCatalogRuleMassDelete($input: createAdminMarketingCatalogRuleMassDeleteInput!) {
    createAdminMarketingCatalogRuleMassDelete(input: $input) {
      adminMarketingCatalogRuleMassDelete { deleted skipped message }
    }
  }
`;

function uniqueName(): string {
  return `e2e_gql_cr_${Date.now()}_${Math.floor(Math.random() * 1_000_000)}`;
}

function ruleInput(name: string): Record<string, any> {
  return {
    name,
    description: 'gql e2e',
    channels: [DEFAULT_CHANNEL_ID],
    customerGroups: [GENERAL_CUSTOMER_GROUP_ID],
    actionType: 'by_percent',
    discountAmount: 10,
    sortOrder: 0,
    status: 1,
    conditions: [],
  };
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

async function catalogRules(request: APIRequestContext, variables: Record<string, any>): Promise<any> {
  return dataOf(await gql(request, CATALOG_RULES_FILTER_QUERY, { first: 5, ...variables }), 'adminMarketingCatalogRules');
}

test.describe('admin marketing catalog rules graphql', () => {
  let created: string[];

  test.beforeEach(() => {
    created = [];
  });

  test.afterEach(async ({ request }) => {
    for (const id of created) {
      await sendAdminGraphQLRequest(request, CATALOG_RULE_DELETE_MUTATION, { input: { id } });
    }
  });

  async function createRule(request: APIRequestContext, input: Record<string, any>): Promise<any> {
    const rule = dataOf(await gql(request, ADMIN_CATALOG_RULE_CREATE_MUTATION, { input }), 'createAdminMarketingCatalogRule').adminMarketingCatalogRule;
    created.push(rule.id);
    return rule;
  }

  test('listing filtered by name returns only the created rule', async ({ request }) => {
    const name = uniqueName();
    const rule = await createRule(request, ruleInput(name));

    const connection = await catalogRules(request, { name });
    expect(connection.totalCount).toBe(1);
    expect(connection.edges[0].node).toEqual({ _id: rule._id, name, status: 1, actionType: 'by_percent', discountAmount: 10 });
  });

  test('listing with a name nobody uses returns no rules', async ({ request }) => {
    const connection = await catalogRules(request, { name: uniqueName() });
    expect(connection.totalCount).toBe(0);
    expect(connection.edges).toEqual([]);
  });

  test('detail unknown id reports the rule as not found', async ({ request }) => {
    const body = await gql(request, ADMIN_CATALOG_RULE_QUERY, { id: UNKNOWN_IRI });
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(body.data?.adminMarketingCatalogRule ?? null).toBeNull();
  });

  test('create empty payload is rejected with the name requirement', async ({ request }) => {
    const body = await gql(request, ADMIN_CATALOG_RULE_CREATE_MUTATION, { input: {} });
    expect(firstError(body)).toBe('The name field is required.');
    expect(body.data?.createAdminMarketingCatalogRule ?? null).toBeNull();
  });

  test('create + delete round trip', async ({ request }) => {
    const name = uniqueName();
    const rule = await createRule(request, ruleInput(name));
    expect(rule).toEqual({
      id: `/api/admin/marketing/catalog-rules/${rule._id}`,
      _id: rule._id,
      name,
      status: 1,
      actionType: 'by_percent',
      discountAmount: 10,
    });

    const read = dataOf(await gql(request, ADMIN_CATALOG_RULE_QUERY, { id: rule.id }), 'adminMarketingCatalogRule');
    expect(read).toEqual(expect.objectContaining({ _id: rule._id, name, description: 'gql e2e', status: 1 }));
    expect(read.channels.edges.map((edge: any) => edge.node._id)).toEqual([DEFAULT_CHANNEL_ID]);
    expect(read.customerGroups.edges.map((edge: any) => edge.node._id)).toEqual([GENERAL_CUSTOMER_GROUP_ID]);

    const deleted = dataOf(await gql(request, CATALOG_RULE_DELETE_MUTATION, { input: { id: rule.id } }), 'deleteAdminMarketingCatalogRule')
      .adminMarketingCatalogRule;
    expect(deleted).toEqual({ _id: rule._id, message: 'Catalog rule deleted successfully.' });
    created = [];

    expect(firstError(await gql(request, ADMIN_CATALOG_RULE_QUERY, { id: rule.id }))).toBe(NOT_FOUND);
  });

  test('create by_percent above 100 is rejected and nothing is stored', async ({ request }) => {
    const name = uniqueName();
    const body = await gql(request, ADMIN_CATALOG_RULE_CREATE_MUTATION, { input: { ...ruleInput(name), discountAmount: 200 } });
    expect(firstError(body)).toBe('The discount amount field must not be greater than 100.');
    expect(body.data?.createAdminMarketingCatalogRule ?? null).toBeNull();
    expect((await catalogRules(request, { name })).totalCount).toBe(0);
  });

  test('mass-delete empty indices is rejected', async ({ request }) => {
    const body = await gql(request, CATALOG_RULE_MASS_DELETE_MUTATION, { input: { indices: [] } });
    expect(firstError(body)).toBe('No catalog rule IDs supplied for mass-delete.');
    expect(body.data?.createAdminMarketingCatalogRuleMassDelete ?? null).toBeNull();
  });

  test('mass-delete removes the created rules and skips unknown ids', async ({ request }) => {
    const first = await createRule(request, ruleInput(uniqueName()));
    const second = await createRule(request, ruleInput(uniqueName()));

    const result = dataOf(
      await gql(request, CATALOG_RULE_MASS_DELETE_MUTATION, { input: { indices: [first._id, second._id, 999999999] } }),
      'createAdminMarketingCatalogRuleMassDelete',
    ).adminMarketingCatalogRuleMassDelete;
    expect(result).toEqual({
      deleted: [first._id, second._id],
      skipped: [999999999],
      message: 'Selected catalog rules deleted successfully.',
    });
    created = [];

    expect(firstError(await gql(request, ADMIN_CATALOG_RULE_QUERY, { id: first.id }))).toBe(NOT_FOUND);
  });
});
