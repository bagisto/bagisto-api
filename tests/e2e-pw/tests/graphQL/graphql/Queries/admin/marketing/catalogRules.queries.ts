// Admin Marketing — Catalog Rules GraphQL operations.

export const ADMIN_CATALOG_RULES_QUERY = `
  query adminMarketingCatalogRules($first: Int, $name: String, $status: Int) {
    adminMarketingCatalogRules(first: $first, name: $name, status: $status) {
      edges { node { id _id name status actionType discountAmount } }
    }
  }
`;

export const ADMIN_CATALOG_RULE_QUERY = `
  query adminMarketingCatalogRule($id: ID!) {
    adminMarketingCatalogRule(id: $id) {
      id _id name description status actionType discountAmount
      channels { edges { node { id _id code name } } }
      customerGroups { edges { node { id _id code name } } }
    }
  }
`;

export const ADMIN_CATALOG_RULE_CREATE_MUTATION = `
  mutation createAdminMarketingCatalogRule($input: createAdminMarketingCatalogRuleInput!) {
    createAdminMarketingCatalogRule(input: $input) {
      adminMarketingCatalogRule { id _id name status actionType discountAmount }
    }
  }
`;

export const ADMIN_CATALOG_RULE_UPDATE_MUTATION = `
  mutation updateAdminMarketingCatalogRule($input: updateAdminMarketingCatalogRuleInput!) {
    updateAdminMarketingCatalogRule(input: $input) {
      adminMarketingCatalogRule { id _id name }
    }
  }
`;

export const ADMIN_CATALOG_RULE_DELETE_MUTATION = `
  mutation deleteAdminMarketingCatalogRule($input: deleteAdminMarketingCatalogRuleInput!) {
    deleteAdminMarketingCatalogRule(input: $input) {
      adminMarketingCatalogRule { id }
    }
  }
`;

export const ADMIN_CATALOG_RULE_MASS_DELETE_MUTATION = `
  mutation createAdminMarketingCatalogRuleMassDelete($input: createAdminMarketingCatalogRuleMassDeleteInput!) {
    createAdminMarketingCatalogRuleMassDelete(input: $input) {
      adminMarketingCatalogRuleMassDelete { id }
    }
  }
`;
