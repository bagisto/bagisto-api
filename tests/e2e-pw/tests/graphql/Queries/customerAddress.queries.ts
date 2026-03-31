export const GET_CUSTOMER_ADDRESSES = `
  query getCustomerAddresses($first: Int, $after: String) {
    getCustomerAddresses(first: $first, after: $after) {
      edges {
        cursor
        node {
          id
          _id
          addressType
          companyName
          name
          firstName
          lastName
          email
          address
          city
          state
          country
          postcode
          phone
          vatId
          defaultAddress
          useForShipping
          additional
          createdAt
          updatedAt
        }
      }
      pageInfo {
        hasNextPage
        hasPreviousPage
        startCursor
        endCursor
      }
      totalCount
    }
  }
`;

export const GET_CUSTOMER_ADDRESSES_PAGINATED = `
  query getCustomerAddressesPaginated($first: Int, $after: String) {
    getCustomerAddresses(first: $first, after: $after) {
      edges {
        cursor
        node {
          _id
          firstName
          lastName
          address
          city
          state
          country
          postcode
          defaultAddress
        }
      }
      pageInfo {
        hasNextPage
        endCursor
      }
      totalCount
    }
  }
`;

export const GET_CUSTOMER_ADDRESSES_MINIMAL = `
  query getCustomerAddressesMinimal($first: Int) {
    getCustomerAddresses(first: $first) {
      edges {
        node {
          id
          name
          address
          city
          country
          postcode
        }
      }
      totalCount
    }
  }
`;

export const GET_CUSTOMER_ADDRESSES_WITH_COMPANY = `
  query getCustomerAddressesWithCompany($first: Int) {
    getCustomerAddresses(first: $first) {
      edges {
        node {
          id
          companyName
          vatId
          firstName
          lastName
          email
          phone
          address
          city
          state
          country
          postcode
        }
      }
      totalCount
    }
  }
`;

// Get Customer Address by ID
export const GET_CUSTOMER_ADDRESS = `
  query GetCustomerAddress($id: ID!) {
    customerAddress(id: $id) {
      id
      firstName
      lastName
      name
      companyName
      address1
      address2
      country
      state
      city
      postcode
      phone
      email
      defaultAddress
      createdAt
      updatedAt
    }
  }
`;
