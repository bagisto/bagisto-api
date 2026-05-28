// Admin Marketing — Cart Rule Coupons (sub-resource) GraphQL operations.

export const ADMIN_CART_RULE_COUPONS_QUERY = `
  query adminMarketingCartRuleCoupons($cartRuleId: Int!, $first: Int) {
    adminMarketingCartRuleCoupons(cartRuleId: $cartRuleId, first: $first) {
      edges { node { id _id code usageLimit usagePerCustomer } }
    }
  }
`;

export const ADMIN_CART_RULE_COUPON_CREATE_MUTATION = `
  mutation createAdminMarketingCartRuleCoupon($input: createAdminMarketingCartRuleCouponInput!) {
    createAdminMarketingCartRuleCoupon(input: $input) {
      adminMarketingCartRuleCoupon { id _id code }
    }
  }
`;

export const ADMIN_CART_RULE_COUPON_GENERATE_MUTATION = `
  mutation createAdminMarketingCartRuleCouponGenerate($input: createAdminMarketingCartRuleCouponGenerateInput!) {
    createAdminMarketingCartRuleCouponGenerate(input: $input) {
      adminMarketingCartRuleCouponGenerate { id }
    }
  }
`;

export const ADMIN_CART_RULE_COUPON_DELETE_MUTATION = `
  mutation deleteAdminMarketingCartRuleCoupon($input: deleteAdminMarketingCartRuleCouponInput!) {
    deleteAdminMarketingCartRuleCoupon(input: $input) {
      adminMarketingCartRuleCoupon { id }
    }
  }
`;

export const ADMIN_CART_RULE_COUPON_MASS_DELETE_MUTATION = `
  mutation createAdminMarketingCartRuleCouponMassDelete($input: createAdminMarketingCartRuleCouponMassDeleteInput!) {
    createAdminMarketingCartRuleCouponMassDelete(input: $input) {
      adminMarketingCartRuleCouponMassDelete { id }
    }
  }
`;

// Helper — minimal cart rule create reused by coupon specs.
export const ADMIN_CART_RULE_CREATE_FOR_COUPONS = `
  mutation createAdminMarketingCartRule($input: createAdminMarketingCartRuleInput!) {
    createAdminMarketingCartRule(input: $input) {
      adminMarketingCartRule { id _id }
    }
  }
`;

export const ADMIN_CART_RULE_DELETE_FOR_COUPONS = `
  mutation deleteAdminMarketingCartRule($input: deleteAdminMarketingCartRuleInput!) {
    deleteAdminMarketingCartRule(input: $input) {
      adminMarketingCartRule { id }
    }
  }
`;
