export const ADMIN_SEARCH_TERM_CREATE_COVERAGE_MUTATION = `
  mutation createAdminMarketingSearchTerm($input: createAdminMarketingSearchTermInput!) {
    createAdminMarketingSearchTerm(input: $input) {
      adminMarketingSearchTerm {
        id
        _id
        term
        locale
        redirectUrl
        results
        uses
        channel {
          _id
          code
        }
      }
    }
  }
`;

export const ADMIN_SEARCH_TERM_READ_COVERAGE_QUERY = `
  query adminMarketingSearchTerm($id: ID!) {
    adminMarketingSearchTerm(id: $id) {
      _id
      term
      locale
      redirectUrl
    }
  }
`;
