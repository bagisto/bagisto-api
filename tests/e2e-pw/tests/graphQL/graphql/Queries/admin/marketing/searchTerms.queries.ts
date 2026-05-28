// Admin Marketing — Search Terms GraphQL operations.
// No create — entries are recorded by storefront search.

export const ADMIN_SEARCH_TERMS_QUERY = `
  query adminMarketingSearchTerms($first: Int, $term: String) {
    adminMarketingSearchTerms(first: $first, term: $term) {
      edges { node { id _id term results uses redirectUrl } }
    }
  }
`;

export const ADMIN_SEARCH_TERM_QUERY = `
  query adminMarketingSearchTerm($id: ID!) {
    adminMarketingSearchTerm(id: $id) {
      id _id term results uses redirectUrl
    }
  }
`;

export const ADMIN_SEARCH_TERM_UPDATE_MUTATION = `
  mutation updateAdminMarketingSearchTerm($input: updateAdminMarketingSearchTermInput!) {
    updateAdminMarketingSearchTerm(input: $input) {
      adminMarketingSearchTerm { id _id term }
    }
  }
`;

export const ADMIN_SEARCH_TERM_DELETE_MUTATION = `
  mutation deleteAdminMarketingSearchTerm($input: deleteAdminMarketingSearchTermInput!) {
    deleteAdminMarketingSearchTerm(input: $input) {
      adminMarketingSearchTerm { id }
    }
  }
`;

export const ADMIN_SEARCH_TERM_MASS_DELETE_MUTATION = `
  mutation createAdminMarketingSearchTermMassDelete($input: createAdminMarketingSearchTermMassDeleteInput!) {
    createAdminMarketingSearchTermMassDelete(input: $input) {
      adminMarketingSearchTermMassDelete { id }
    }
  }
`;
