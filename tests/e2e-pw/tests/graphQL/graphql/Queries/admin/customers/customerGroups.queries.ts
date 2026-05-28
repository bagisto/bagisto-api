// tests/graphQL/graphql/Queries/admin/customers/customerGroups.queries.ts
//
// Admin Customer Groups GraphQL operations (W2). Schema verified 2026-05-26.
// NOTE: `isUserDefined` is currently being returned as `null` over GraphQL
// (project-wide camelCase scalar nullability quirk noted in CLAUDE.md). Tests
// avoid asserting that field's value directly.

export const ADMIN_CUSTOMER_GROUPS_LIST = `
  query adminCustomerGroups(
    $first: Int
    $after: String
    $code: String
    $name: String
    $is_user_defined: Int
    $sort: String
    $order: String
  ) {
    adminCustomerGroups(
      first: $first
      after: $after
      code: $code
      name: $name
      is_user_defined: $is_user_defined
      sort: $sort
      order: $order
    ) {
      edges {
        node {
          id
          _id
          code
          name
          isUserDefined
        }
      }
      pageInfo {
        hasNextPage
        endCursor
      }
    }
  }
`;

export const ADMIN_CUSTOMER_GROUP_DETAIL = `
  query adminCustomerGroup($id: ID!) {
    adminCustomerGroup(id: $id) {
      id
      _id
      code
      name
      isUserDefined
      customersCount
    }
  }
`;

export const ADMIN_CUSTOMER_GROUP_CREATE = `
  mutation createAdminCustomerGroup($code: String!, $name: String!) {
    createAdminCustomerGroup(input: { code: $code, name: $name }) {
      adminCustomerGroup {
        id
        _id
        code
        name
      }
    }
  }
`;

export const ADMIN_CUSTOMER_GROUP_UPDATE = `
  mutation updateAdminCustomerGroup($id: ID!, $code: String, $name: String) {
    updateAdminCustomerGroup(input: { id: $id, code: $code, name: $name }) {
      adminCustomerGroup {
        id
        _id
        code
        name
      }
    }
  }
`;

export const ADMIN_CUSTOMER_GROUP_DELETE = `
  mutation deleteAdminCustomerGroup($id: ID!) {
    deleteAdminCustomerGroup(input: { id: $id }) {
      adminCustomerGroup {
        id
      }
    }
  }
`;

export const ADMIN_CUSTOMER_GROUP_MASS_DELETE = `
  mutation createAdminCustomerGroupMassDelete($indices: Iterable!) {
    createAdminCustomerGroupMassDelete(input: { indices: $indices }) {
      adminCustomerGroupMassDelete {
        id
      }
    }
  }
`;
