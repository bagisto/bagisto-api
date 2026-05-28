// tests/graphQL/graphql/Queries/admin/customers/customerAddresses.queries.ts
//
// Admin Customer Addresses GraphQL operations (W2). Schema verified
// 2026-05-26 via introspection. `adminCustomerAddresses(customerId: Int)` is
// a sub-resource query keyed by the parent customer's INTEGER id (not IRI).

export const ADMIN_CUSTOMER_ADDRESSES_LIST = `
  query adminCustomerAddresses($customerId: Int!) {
    adminCustomerAddresses(customerId: $customerId) {
      edges {
        node {
          id
          _id
          customerId
          firstName
          lastName
          companyName
          address
          city
          state
          country
          postcode
          phone
          defaultAddress
        }
      }
      pageInfo {
        hasNextPage
        endCursor
      }
    }
  }
`;

export const ADMIN_CUSTOMER_ADDRESS_DETAIL = `
  query adminCustomerAddress($customerId: Int!, $id: ID!) {
    adminCustomerAddress(customerId: $customerId, id: $id) {
      id
      _id
      customerId
      firstName
      lastName
      companyName
      address
      city
      state
      country
      postcode
      phone
    }
  }
`;

export const ADMIN_CUSTOMER_ADDRESS_CREATE = `
  mutation createAdminCustomerAddress(
    $customerId: Int!
    $firstName: String!
    $lastName: String!
    $companyName: String
    $address: String!
    $city: String!
    $state: String!
    $country: String!
    $postcode: String!
    $phone: String!
    $defaultAddress: Boolean
  ) {
    createAdminCustomerAddress(
      input: {
        customerId: $customerId
        firstName: $firstName
        lastName: $lastName
        companyName: $companyName
        address: $address
        city: $city
        state: $state
        country: $country
        postcode: $postcode
        phone: $phone
        defaultAddress: $defaultAddress
      }
    ) {
      adminCustomerAddress {
        id
        _id
        customerId
        city
        companyName
      }
    }
  }
`;

export const ADMIN_CUSTOMER_ADDRESS_UPDATE = `
  mutation updateAdminCustomerAddress(
    $id: ID!
    $customerId: Int
    $city: String
    $companyName: String
  ) {
    updateAdminCustomerAddress(
      input: {
        id: $id
        customerId: $customerId
        city: $city
        companyName: $companyName
      }
    ) {
      adminCustomerAddress {
        id
        _id
        city
        companyName
      }
    }
  }
`;

export const ADMIN_CUSTOMER_ADDRESS_DELETE = `
  mutation deleteAdminCustomerAddress($id: ID!) {
    deleteAdminCustomerAddress(input: { id: $id }) {
      adminCustomerAddress {
        id
      }
    }
  }
`;
