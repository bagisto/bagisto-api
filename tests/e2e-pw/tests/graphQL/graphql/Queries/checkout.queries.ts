export const GET_CHECKOUT_ADDRESSES = `
  query collectionGetCheckoutAddresses {
    collectionGetCheckoutAddresses {
      edges {
        node {
          id
          _id
          addressType
          parentAddressId
          firstName
          lastName
          gender
          companyName
          address
          city
          state
          country
          postcode
          email
          phone
          vatId
          defaultAddress
          useForShipping
          additional
          createdAt
          updatedAt
          name
        }
      }
    }
  }
`;
