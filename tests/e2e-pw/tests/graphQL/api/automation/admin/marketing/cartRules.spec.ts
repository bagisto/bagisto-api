import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import { ADMIN_CART_RULE_CREATE_MUTATION } from '../../../../graphql/Queries/admin/marketing/cartRules.queries';

test.describe.configure({ timeout: 120_000 });

const UNKNOWN_IRI = '/api/admin/marketing/cart-rules/999999999';

const NOT_FOUND = 'Cart rule not found.';

const DEFAULT_CHANNEL_ID = 1;

const GENERAL_CUSTOMER_GROUP_ID = 2;

const CART_RULES_FILTER_QUERY = `
  query adminMarketingCartRules($first: Int, $name: String, $status: Int) {
    adminMarketingCartRules(first: $first, name: $name, status: $status) {
      totalCount
      edges { node { _id name status couponType actionType } }
    }
  }
`;

const CART_RULE_DETAIL_QUERY = `
  query adminMarketingCartRule($id: ID!) {
    adminMarketingCartRule(id: $id) {
      _id name description status couponType actionType discountAmount
    }
  }
`;

const CART_RULE_DELETE_MUTATION = `
  mutation deleteAdminMarketingCartRule($input: deleteAdminMarketingCartRuleInput!) {
    deleteAdminMarketingCartRule(input: $input) {
      clientMutationId
    }
  }
`;

const CART_RULE_MASS_DELETE_MUTATION = `
  mutation createAdminMarketingCartRuleMassDelete($input: createAdminMarketingCartRuleMassDeleteInput!) {
    createAdminMarketingCartRuleMassDelete(input: $input) {
      adminMarketingCartRuleMassDelete { deleted skipped message }
    }
  }
`;

function uniqueName(): string {
  return `e2e_gql_cartrule_${Date.now()}_${Math.floor(Math.random() * 1_000_000)}`;
}

function ruleInput(name: string): Record<string, any> {
  return {
    name,
    description: 'gql e2e',
    channels: [DEFAULT_CHANNEL_ID],
    customerGroups: [GENERAL_CUSTOMER_GROUP_ID],
    couponType: 0,
    useAutoGeneration: 0,
    actionType: 'by_percent',
    discountAmount: 5,
    sortOrder: 0,
    status: 1,
    conditions: [],
    usagePerCustomer: 0,
    usesPerCoupon: 0,
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

async function cartRules(request: APIRequestContext, variables: Record<string, any>): Promise<any> {
  return dataOf(await gql(request, CART_RULES_FILTER_QUERY, { first: 5, ...variables }), 'adminMarketingCartRules');
}

test.describe('admin marketing cart rules graphql', () => {
  let created: string[];

  test.beforeEach(() => {
    created = [];
  });

  test.afterEach(async ({ request }) => {
    for (const id of created) {
      await sendAdminGraphQLRequest(request, CART_RULE_DELETE_MUTATION, { input: { id } });
    }
  });

  async function createRule(request: APIRequestContext, input: Record<string, any>): Promise<any> {
    const rule = dataOf(await gql(request, ADMIN_CART_RULE_CREATE_MUTATION, { input }), 'createAdminMarketingCartRule').adminMarketingCartRule;
    created.push(rule.id);
    return rule;
  }

  test('listing filtered by name returns only the created rule', async ({ request }) => {
    const name = uniqueName();
    const rule = await createRule(request, ruleInput(name));

    const connection = await cartRules(request, { name });
    expect(connection.totalCount).toBe(1);
    expect(connection.edges[0].node).toEqual({ _id: rule._id, name, status: 1, couponType: 0, actionType: 'by_percent' });
  });

  test('listing status filter keeps or drops the created rule by its status', async ({ request }) => {
    const name = uniqueName();
    await createRule(request, ruleInput(name));

    expect((await cartRules(request, { name, status: 1 })).totalCount).toBe(1);
    expect((await cartRules(request, { name, status: 0 })).totalCount).toBe(0);
  });

  test('detail unknown id reports the rule as not found', async ({ request }) => {
    const body = await gql(request, CART_RULE_DETAIL_QUERY, { id: UNKNOWN_IRI });
    expect(firstError(body)).toBe(NOT_FOUND);
    expect(body.data?.adminMarketingCartRule ?? null).toBeNull();
  });

  test('create empty payload is rejected with the name requirement', async ({ request }) => {
    const body = await gql(request, ADMIN_CART_RULE_CREATE_MUTATION, { input: {} });
    expect(firstError(body)).toBe('The name field is required.');
    expect(body.data?.createAdminMarketingCartRule ?? null).toBeNull();
  });

  test('create + delete round trip', async ({ request }) => {
    const name = uniqueName();
    const rule = await createRule(request, ruleInput(name));
    expect(rule).toEqual({ id: `/api/admin/marketing/cart-rules/${rule._id}`, _id: rule._id, name, status: 1 });

    const read = dataOf(await gql(request, CART_RULE_DETAIL_QUERY, { id: rule.id }), 'adminMarketingCartRule');
    expect(read).toEqual({
      _id: rule._id,
      name,
      description: 'gql e2e',
      status: 1,
      couponType: 0,
      actionType: 'by_percent',
      discountAmount: 5,
    });

    const deleted = dataOf(
      await gql(request, CART_RULE_DELETE_MUTATION, { input: { id: rule.id, clientMutationId: 'e2e-delete-cart-rule' } }),
      'deleteAdminMarketingCartRule',
    );
    expect(deleted.clientMutationId).toBe('e2e-delete-cart-rule');
    created = [];

    expect(firstError(await gql(request, CART_RULE_DETAIL_QUERY, { id: rule.id }))).toBe(NOT_FOUND);
  });

  test('create coupon_type=1 without coupon_code is rejected and nothing is stored', async ({ request }) => {
    const name = uniqueName();
    const body = await gql(request, ADMIN_CART_RULE_CREATE_MUTATION, {
      input: { ...ruleInput(name), couponType: 1, useAutoGeneration: 0 },
    });
    expect(firstError(body)).toBe('The coupon code field is required.');
    expect(body.data?.createAdminMarketingCartRule ?? null).toBeNull();
    expect((await cartRules(request, { name })).totalCount).toBe(0);
  });

  test('mass-delete empty indices is rejected', async ({ request }) => {
    const body = await gql(request, CART_RULE_MASS_DELETE_MUTATION, { input: { indices: [] } });
    expect(firstError(body)).toBe('The indices field is required and must be a non-empty array.');
    expect(body.data?.createAdminMarketingCartRuleMassDelete ?? null).toBeNull();
  });

  test('mass-delete removes the created rules and ignores unknown ids', async ({ request }) => {
    const first = await createRule(request, ruleInput(uniqueName()));
    const second = await createRule(request, ruleInput(uniqueName()));

    const result = dataOf(
      await gql(request, CART_RULE_MASS_DELETE_MUTATION, { input: { indices: [first._id, second._id, 999999999] } }),
      'createAdminMarketingCartRuleMassDelete',
    ).adminMarketingCartRuleMassDelete;
    expect(result).toEqual({ deleted: [first._id, second._id], skipped: [], message: 'Cart rules deleted successfully.' });
    created = [];

    expect(firstError(await gql(request, CART_RULE_DETAIL_QUERY, { id: second.id }))).toBe(NOT_FOUND);
  });
});
