// tests/graphQL/graphql/Queries/admin/catalog/families.queries.ts
//
// Admin Catalog Attribute Families GraphQL operations. Field shapes verified
// via introspection on 2026-05-26.

export const ADMIN_FAMILIES_LIST_QUERY = `
  query adminAttributeFamilies($first: Int, $after: String) {
    adminAttributeFamilies(first: $first, after: $after) {
      edges {
        node {
          id
          _id
          code
          name
        }
      }
      pageInfo {
        endCursor
        hasNextPage
      }
    }
  }
`;

export const ADMIN_FAMILY_DETAIL_QUERY = `
  query adminAttributeFamily($id: ID!) {
    adminAttributeFamily(id: $id) {
      id
      _id
      code
      name
      attributeGroups
    }
  }
`;

export const ADMIN_FAMILY_CREATE_MUTATION = `
  mutation createAdminAttributeFamily(
    $code: String!
    $name: String!
    $attributeGroups: Iterable!
  ) {
    createAdminAttributeFamily(
      input: { code: $code, name: $name, attributeGroups: $attributeGroups }
    ) {
      adminAttributeFamily {
        id
        _id
        code
        name
      }
    }
  }
`;

export const ADMIN_FAMILY_UPDATE_MUTATION = `
  mutation updateAdminAttributeFamily(
    $id: ID!
    $name: String
    $attributeGroups: Iterable
  ) {
    updateAdminAttributeFamily(
      input: { id: $id, name: $name, attributeGroups: $attributeGroups }
    ) {
      adminAttributeFamily {
        id
        _id
        name
      }
    }
  }
`;

export const ADMIN_FAMILY_DELETE_MUTATION = `
  mutation deleteAdminAttributeFamily($id: ID!) {
    deleteAdminAttributeFamily(input: { id: $id }) {
      adminAttributeFamily {
        id
        _id
      }
    }
  }
`;
