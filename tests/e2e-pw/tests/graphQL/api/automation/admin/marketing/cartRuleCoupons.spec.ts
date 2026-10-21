import { test, expect, APIRequestContext } from '@playwright/test';
import { sendAdminGraphQLRequest } from '../../../../graphql/helpers/adminGraphqlClient';
import { ADMIN_CART_RULE_CREATE_FOR_COUPONS } from '../../../../graphql/Queries/admin/marketing/cartRuleCoupons.queries';

test.describe.configure({ timeout: 120_000 });

const CART_RULE_NOT_FOUND = 'Cart rule not found.';

const DEFAULT_CHANNEL_ID = 1;

const GENERAL_CUSTOMER_GROUP_ID = 2;

const COUPONS_QUERY = `
  query adminMarketingCartRuleCoupons($cartRuleId: Int!, $first: Int) {
    adminMarketingCartRuleCoupons(cartRuleId: $cartRuleId, first: $first) {
      totalCount
      edges { node { _id code cartRuleId usageLimit usagePerCustomer isPrimary } }
    }
  }
`;

const COUPON_CREATE_MUTATION = `
  mutation createAdminMarketingCartRuleCoupon($input: createAdminMarketingCartRuleCouponInput!) {
    createAdminMarketingCartRuleCoupon(input: $input) {
      adminMarketingCartRuleCoupon { _id code cartRuleId usageLimit usagePerCustomer success message }
    }
  }
`;

const COUPON_GENERATE_MUTATION = `
  mutation createAdminMarketingCartRuleCouponGenerate($input: createAdminMarketingCartRuleCouponGenerateInput!) {
    createAdminMarketingCartRuleCouponGenerate(input: $input) {
      adminMarketingCartRuleCouponGenerate { cartRuleId generated coupons success message }
    }
  }
`;

const COUPON_MASS_DELETE_MUTATION = `
  mutation createAdminMarketingCartRuleCouponMassDelete($input: createAdminMarketingCartRuleCouponMassDeleteInput!) {
    createAdminMarketingCartRuleCouponMassDelete(input: $input) {
      adminMarketingCartRuleCouponMassDelete { cartRuleId deleted skipped success message }
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

function uniqueSuffix(): string {
  return `${Date.now()}${Math.floor(Math.random() * 1_000_000)}`;
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

test.describe('admin marketing cart rule coupons graphql', () => {
  let rule: { id: string; _id: number };

  test.beforeEach(async ({ request }) => {
    rule = dataOf(
      await gql(request, ADMIN_CART_RULE_CREATE_FOR_COUPONS, {
        input: {
          name: `e2e_gql_crc_rule_${uniqueSuffix()}`,
          description: 'gql e2e',
          channels: [DEFAULT_CHANNEL_ID],
          customerGroups: [GENERAL_CUSTOMER_GROUP_ID],
          couponType: 1,
          useAutoGeneration: 1,
          actionType: 'by_percent',
          discountAmount: 5,
          sortOrder: 0,
          status: 1,
          conditions: [],
          usagePerCustomer: 0,
          usesPerCoupon: 0,
        },
      }),
      'createAdminMarketingCartRule',
    ).adminMarketingCartRule;
  });

  test.afterEach(async ({ request }) => {
    await sendAdminGraphQLRequest(request, CART_RULE_DELETE_MUTATION, { input: { id: rule.id } });
  });

  async function couponsOf(request: APIRequestContext): Promise<any> {
    return dataOf(await gql(request, COUPONS_QUERY, { cartRuleId: rule._id, first: 50 }), 'adminMarketingCartRuleCoupons');
  }

  test('listing for an unknown rule reports the rule as not found', async ({ request }) => {
    const body = await gql(request, COUPONS_QUERY, { cartRuleId: 999999999, first: 5 });
    expect(firstError(body)).toBe(CART_RULE_NOT_FOUND);
    expect(body.data?.adminMarketingCartRuleCoupons ?? null).toBeNull();
  });

  test('listing for an auto generation rule starts without coupons', async ({ request }) => {
    const connection = await couponsOf(request);
    expect(connection.totalCount).toBe(0);
    expect(connection.edges).toEqual([]);
  });

  test('create single coupon happy path', async ({ request }) => {
    const code = `E2EGQL${uniqueSuffix()}`;
    const coupon = dataOf(
      await gql(request, COUPON_CREATE_MUTATION, { input: { cartRuleId: rule._id, code, usageLimit: 3, usagePerCustomer: 1 } }),
      'createAdminMarketingCartRuleCoupon',
    ).adminMarketingCartRuleCoupon;
    expect(coupon).toEqual(expect.objectContaining({
      code,
      cartRuleId: rule._id,
      usageLimit: 3,
      usagePerCustomer: 1,
      success: true,
      message: 'Coupon created successfully.',
    }));

    const connection = await couponsOf(request);
    expect(connection.totalCount).toBe(1);
    expect(connection.edges[0].node).toEqual({
      _id: coupon._id,
      code,
      cartRuleId: rule._id,
      usageLimit: 3,
      usagePerCustomer: 1,
      isPrimary: false,
    });
  });

  test('create coupon with a code already in use is rejected', async ({ request }) => {
    const code = `E2EGQL${uniqueSuffix()}`;
    dataOf(await gql(request, COUPON_CREATE_MUTATION, { input: { cartRuleId: rule._id, code } }), 'createAdminMarketingCartRuleCoupon');

    const body = await gql(request, COUPON_CREATE_MUTATION, { input: { cartRuleId: rule._id, code } });
    expect(firstError(body)).toBe('A coupon with this code already exists.');
    expect((await couponsOf(request)).totalCount).toBe(1);
  });

  test('bulk-generate 3 coupons', async ({ request }) => {
    const result = dataOf(
      await gql(request, COUPON_GENERATE_MUTATION, {
        input: { cartRuleId: rule._id, length: 10, format: 'alphanumeric', prefix: 'GQL', suffix: '', couponQty: 3 },
      }),
      'createAdminMarketingCartRuleCouponGenerate',
    ).adminMarketingCartRuleCouponGenerate;
    expect(result).toEqual(expect.objectContaining({
      cartRuleId: rule._id,
      generated: 3,
      success: true,
      message: 'Generated 3 coupon(s) successfully.',
    }));
    expect(result.coupons).toHaveLength(3);
    for (const coupon of result.coupons) {
      expect(coupon.code.startsWith('GQL')).toBe(true);
      expect(coupon.cartRuleId).toBe(rule._id);
    }

    const connection = await couponsOf(request);
    expect(connection.totalCount).toBe(3);
    expect(connection.edges.map((edge: any) => edge.node.code).sort()).toEqual(result.coupons.map((coupon: any) => coupon.code).sort());
  });

  test('create coupon empty body is rejected with the code requirement', async ({ request }) => {
    const body = await gql(request, COUPON_CREATE_MUTATION, { input: { cartRuleId: rule._id } });
    expect(firstError(body)).toBe('The code field is required.');
    expect(body.data?.createAdminMarketingCartRuleCoupon ?? null).toBeNull();
    expect((await couponsOf(request)).totalCount).toBe(0);
  });

  test('mass-delete empty indices is rejected', async ({ request }) => {
    const body = await gql(request, COUPON_MASS_DELETE_MUTATION, { input: { cartRuleId: rule._id, indices: [] } });
    expect(firstError(body)).toBe('The indices field is required and must be a non-empty array of coupon IDs.');
    expect(body.data?.createAdminMarketingCartRuleCouponMassDelete ?? null).toBeNull();
  });

  test('mass-delete removes the rule coupons and skips ids it does not own', async ({ request }) => {
    const code = `E2EGQL${uniqueSuffix()}`;
    const coupon = dataOf(
      await gql(request, COUPON_CREATE_MUTATION, { input: { cartRuleId: rule._id, code } }),
      'createAdminMarketingCartRuleCoupon',
    ).adminMarketingCartRuleCoupon;

    const result = dataOf(
      await gql(request, COUPON_MASS_DELETE_MUTATION, { input: { cartRuleId: rule._id, indices: [coupon._id, 999999999] } }),
      'createAdminMarketingCartRuleCouponMassDelete',
    ).adminMarketingCartRuleCouponMassDelete;
    expect(result).toEqual({
      cartRuleId: rule._id,
      deleted: 1,
      skipped: [999999999],
      success: true,
      message: 'Deleted 1 coupon(s) successfully.',
    });

    expect((await couponsOf(request)).totalCount).toBe(0);
  });
});
