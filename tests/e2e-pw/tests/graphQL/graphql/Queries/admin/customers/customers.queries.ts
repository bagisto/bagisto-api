// tests/graphQL/graphql/Queries/admin/customers/customers.queries.ts
//
// Admin Customers GraphQL operations (W2). Schema verified 2026-05-26 via
// introspection. All custom args (`name`, `email`, `phone`, `customer_group_id`,
// `status`, `channel_id`, `sort`, `order`) are exposed via `extraArgs:` on the
// QueryCollection — variable names use snake_case to match the schema.

export const ADMIN_CUSTOMERS_LIST = `
  query adminCustomers(
    $first: Int
    $after: String
    $name: String
    $email: String
    $phone: String
    $customer_group_id: Int
    $status: Int
    $channel_id: Int
    $sort: String
    $order: String
  ) {
    adminCustomers(
      first: $first
      after: $after
      name: $name
      email: $email
      phone: $phone
      customer_group_id: $customer_group_id
      status: $status
      channel_id: $channel_id
      sort: $sort
      order: $order
    ) {
      edges {
        node {
          id
          _id
          firstName
          lastName
          name
          email
          phone
          group {
            _id
            code
            name
          }
          channelId
          status
          totalAddresses
          totalOrders
        }
      }
      pageInfo {
        hasNextPage
        endCursor
      }
    }
  }
`;

export const ADMIN_CUSTOMER_DETAIL = `
  query adminCustomer($id: ID!) {
    adminCustomer(id: $id) {
      id
      _id
      firstName
      lastName
      email
      phone
      group {
        _id
        code
        name
      }
      status
      totalAddresses
      totalOrders
      totalAmountSpent
    }
  }
`;

export const ADMIN_CUSTOMER_CREATE = `
  mutation createAdminCustomer(
    $firstName: String!
    $lastName: String!
    $email: String!
    $customerGroupId: Int!
    $channelId: Int!
    $sendPassword: Boolean
    $password: String
  ) {
    createAdminCustomer(
      input: {
        firstName: $firstName
        lastName: $lastName
        email: $email
        customerGroupId: $customerGroupId
        channelId: $channelId
        sendPassword: $sendPassword
        password: $password
      }
    ) {
      adminCustomer {
        id
        _id
        firstName
        lastName
        email
        group {
          _id
          code
          name
        }
      }
    }
  }
`;

export const ADMIN_CUSTOMER_UPDATE = `
  mutation updateAdminCustomer(
    $id: ID!
    $firstName: String
    $lastName: String
    $email: String
    $customerGroupId: Int
  ) {
    updateAdminCustomer(
      input: {
        id: $id
        firstName: $firstName
        lastName: $lastName
        email: $email
        customerGroupId: $customerGroupId
      }
    ) {
      adminCustomer {
        id
        _id
        firstName
        lastName
        email
      }
    }
  }
`;

export const ADMIN_CUSTOMER_DELETE = `
  mutation deleteAdminCustomer($id: ID!) {
    deleteAdminCustomer(input: { id: $id }) {
      adminCustomer {
        id
      }
    }
  }
`;

export const ADMIN_CUSTOMER_MASS_DELETE = `
  mutation createAdminCustomerMassDelete($indices: Iterable!) {
    createAdminCustomerMassDelete(input: { indices: $indices }) {
      adminCustomerMassDelete {
        id
        _id
      }
    }
  }
`;

export const ADMIN_CUSTOMER_MASS_UPDATE_STATUS = `
  mutation createAdminCustomerMassUpdateStatus($indices: Iterable!, $value: Int!) {
    createAdminCustomerMassUpdateStatus(input: { indices: $indices, value: $value }) {
      adminCustomerMassUpdateStatus {
        id
        _id
      }
    }
  }
`;
