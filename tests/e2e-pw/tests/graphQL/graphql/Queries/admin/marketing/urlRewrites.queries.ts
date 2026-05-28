// Admin Marketing — URL Rewrites GraphQL operations.

export const ADMIN_URL_REWRITES_QUERY = `
  query adminMarketingUrlRewrites($first: Int, $entity_type: String, $locale: String) {
    adminMarketingUrlRewrites(first: $first, entity_type: $entity_type, locale: $locale) {
      edges { node { id _id entityType requestPath targetPath redirectType locale } }
    }
  }
`;

export const ADMIN_URL_REWRITE_QUERY = `
  query adminMarketingUrlRewrite($id: ID!) {
    adminMarketingUrlRewrite(id: $id) {
      id _id entityType requestPath targetPath redirectType locale
    }
  }
`;

export const ADMIN_URL_REWRITE_CREATE_MUTATION = `
  mutation createAdminMarketingUrlRewrite($input: createAdminMarketingUrlRewriteInput!) {
    createAdminMarketingUrlRewrite(input: $input) {
      adminMarketingUrlRewrite { id _id entityType requestPath }
    }
  }
`;

export const ADMIN_URL_REWRITE_UPDATE_MUTATION = `
  mutation updateAdminMarketingUrlRewrite($input: updateAdminMarketingUrlRewriteInput!) {
    updateAdminMarketingUrlRewrite(input: $input) {
      adminMarketingUrlRewrite { id _id redirectType }
    }
  }
`;

export const ADMIN_URL_REWRITE_DELETE_MUTATION = `
  mutation deleteAdminMarketingUrlRewrite($input: deleteAdminMarketingUrlRewriteInput!) {
    deleteAdminMarketingUrlRewrite(input: $input) {
      adminMarketingUrlRewrite { id }
    }
  }
`;

export const ADMIN_URL_REWRITE_MASS_DELETE_MUTATION = `
  mutation createAdminMarketingUrlRewriteMassDelete($input: createAdminMarketingUrlRewriteMassDeleteInput!) {
    createAdminMarketingUrlRewriteMassDelete(input: $input) {
      adminMarketingUrlRewriteMassDelete { id }
    }
  }
`;
