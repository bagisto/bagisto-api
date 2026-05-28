// tests/graphQL/graphql/Queries/admin/catalog/attributes.queries.ts
//
// Admin Catalog Attributes + Attribute Options GraphQL operations. Field
// shapes verified via introspection on 2026-05-26.

export const ADMIN_ATTRIBUTES_LIST_QUERY = `
  query adminAttributes($first: Int, $after: String) {
    adminAttributes(first: $first, after: $after) {
      edges {
        node {
          id
          _id
          code
          type
          adminName
          isRequired
          isUnique
        }
      }
      pageInfo {
        endCursor
        hasNextPage
      }
    }
  }
`;

export const ADMIN_ATTRIBUTE_DETAIL_QUERY = `
  query adminAttribute($id: ID!) {
    adminAttribute(id: $id) {
      id
      _id
      code
      type
      adminName
      isRequired
      isUnique
      isFilterable
      options
    }
  }
`;

export const ADMIN_ATTRIBUTE_CREATE_MUTATION = `
  mutation createAdminAttribute(
    $code: String!
    $adminName: String!
    $type: String!
  ) {
    createAdminAttribute(input: { code: $code, adminName: $adminName, type: $type }) {
      adminAttribute {
        id
        _id
        code
        type
        adminName
      }
    }
  }
`;

export const ADMIN_ATTRIBUTE_UPDATE_MUTATION = `
  mutation updateAdminAttribute($id: ID!, $adminName: String) {
    updateAdminAttribute(input: { id: $id, adminName: $adminName }) {
      adminAttribute {
        id
        _id
        adminName
      }
    }
  }
`;

export const ADMIN_ATTRIBUTE_DELETE_MUTATION = `
  mutation deleteAdminAttribute($id: ID!) {
    deleteAdminAttribute(input: { id: $id }) {
      adminAttribute {
        id
        _id
      }
    }
  }
`;

export const ADMIN_ATTRIBUTE_MASS_DELETE_MUTATION = `
  mutation createAdminAttributeMassDelete($indices: Iterable!) {
    createAdminAttributeMassDelete(input: { indices: $indices }) {
      adminAttributeMassDelete {
        id
        _id
      }
    }
  }
`;

// --- Attribute Options sub-resource ---

export const ADMIN_ATTRIBUTE_OPTION_CREATE_MUTATION = `
  mutation createAdminAttributeOption(
    $attributeId: String!
    $adminName: String!
    $sortOrder: Int
  ) {
    createAdminAttributeOption(
      input: { attributeId: $attributeId, adminName: $adminName, sortOrder: $sortOrder }
    ) {
      adminAttributeOption {
        id
        _id
      }
    }
  }
`;

export const ADMIN_ATTRIBUTE_OPTION_UPDATE_MUTATION = `
  mutation updateAdminAttributeOption(
    $id: ID!
    $adminName: String
    $sortOrder: Int
  ) {
    updateAdminAttributeOption(
      input: { id: $id, adminName: $adminName, sortOrder: $sortOrder }
    ) {
      adminAttributeOption {
        id
        _id
      }
    }
  }
`;

export const ADMIN_ATTRIBUTE_OPTION_DELETE_MUTATION = `
  mutation deleteAdminAttributeOption(
    $id: ID!
    $attributeId: Int!
    $optionId: Int!
  ) {
    deleteAdminAttributeOption(
      input: { id: $id, attributeId: $attributeId, optionId: $optionId }
    ) {
      adminAttributeOption {
        id
        _id
      }
    }
  }
`;
