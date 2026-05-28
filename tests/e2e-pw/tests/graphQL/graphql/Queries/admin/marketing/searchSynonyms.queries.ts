// Admin Marketing — Search Synonyms GraphQL operations.

export const ADMIN_SEARCH_SYNONYMS_QUERY = `
  query adminMarketingSearchSynonyms($first: Int, $name: String) {
    adminMarketingSearchSynonyms(first: $first, name: $name) {
      edges { node { id _id name terms } }
    }
  }
`;

export const ADMIN_SEARCH_SYNONYM_QUERY = `
  query adminMarketingSearchSynonym($id: ID!) {
    adminMarketingSearchSynonym(id: $id) {
      id _id name terms
    }
  }
`;

export const ADMIN_SEARCH_SYNONYM_CREATE_MUTATION = `
  mutation createAdminMarketingSearchSynonym($input: createAdminMarketingSearchSynonymInput!) {
    createAdminMarketingSearchSynonym(input: $input) {
      adminMarketingSearchSynonym { id _id name }
    }
  }
`;

export const ADMIN_SEARCH_SYNONYM_UPDATE_MUTATION = `
  mutation updateAdminMarketingSearchSynonym($input: updateAdminMarketingSearchSynonymInput!) {
    updateAdminMarketingSearchSynonym(input: $input) {
      adminMarketingSearchSynonym { id _id name }
    }
  }
`;

export const ADMIN_SEARCH_SYNONYM_DELETE_MUTATION = `
  mutation deleteAdminMarketingSearchSynonym($input: deleteAdminMarketingSearchSynonymInput!) {
    deleteAdminMarketingSearchSynonym(input: $input) {
      adminMarketingSearchSynonym { id }
    }
  }
`;

export const ADMIN_SEARCH_SYNONYM_MASS_DELETE_MUTATION = `
  mutation createAdminMarketingSearchSynonymMassDelete($input: createAdminMarketingSearchSynonymMassDeleteInput!) {
    createAdminMarketingSearchSynonymMassDelete(input: $input) {
      adminMarketingSearchSynonymMassDelete { id }
    }
  }
`;
