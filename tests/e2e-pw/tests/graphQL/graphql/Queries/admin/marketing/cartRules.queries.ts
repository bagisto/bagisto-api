// Admin Marketing — Cart Rules GraphQL operations.

export const ADMIN_CART_RULES_QUERY = `
  query adminMarketingCartRules($first: Int, $name: String, $status: Int, $coupon_type: Int) {
    adminMarketingCartRules(first: $first, name: $name, status: $status, coupon_type: $coupon_type) {
      edges { node { id _id name status couponType actionType discountAmount } }
    }
  }
`;

export const ADMIN_CART_RULE_QUERY = `
  query adminMarketingCartRule($id: ID!) {
    adminMarketingCartRule(id: $id) {
      id _id name description status couponType actionType discountAmount channels customerGroups
    }
  }
`;

export const ADMIN_CART_RULE_CREATE_MUTATION = `
  mutation createAdminMarketingCartRule($input: createAdminMarketingCartRuleInput!) {
    createAdminMarketingCartRule(input: $input) {
      adminMarketingCartRule { id _id name status }
    }
  }
`;

export const ADMIN_CART_RULE_UPDATE_MUTATION = `
  mutation updateAdminMarketingCartRule($input: updateAdminMarketingCartRuleInput!) {
    updateAdminMarketingCartRule(input: $input) {
      adminMarketingCartRule { id _id name }
    }
  }
`;

export const ADMIN_CART_RULE_DELETE_MUTATION = `
  mutation deleteAdminMarketingCartRule($input: deleteAdminMarketingCartRuleInput!) {
    deleteAdminMarketingCartRule(input: $input) {
      adminMarketingCartRule { id }
    }
  }
`;

export const ADMIN_CART_RULE_MASS_DELETE_MUTATION = `
  mutation createAdminMarketingCartRuleMassDelete($input: createAdminMarketingCartRuleMassDeleteInput!) {
    createAdminMarketingCartRuleMassDelete(input: $input) {
      adminMarketingCartRuleMassDelete { id }
    }
  }
`;
