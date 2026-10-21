export const ADMIN_COVERAGE_CUSTOMER_NOTES = `
  query adminCustomerNotes($customerId: Int!) {
    adminCustomerNotes(customerId: $customerId) {
      totalCount
      edges {
        node {
          id
          note
          customerId
          customerNotified
          createdAt
        }
      }
    }
  }
`;

export const ADMIN_COVERAGE_CUSTOMER_CART_ITEMS = `
  query adminCustomerCartItems($customerId: Int!) {
    adminCustomerCartItems(customerId: $customerId) {
      totalCount
      edges {
        node {
          id
          productId
          sku
          type
          name
          quantity
          price
          formattedPrice
          total
          formattedTotal
          additional
        }
      }
    }
  }
`;

export const ADMIN_COVERAGE_CUSTOMER_COMPARE_ITEMS = `
  query adminCustomerCompareItems($customerId: Int!) {
    adminCustomerCompareItems(customerId: $customerId) {
      totalCount
      edges {
        node {
          id
          productId
          sku
          name
          price
          formattedPrice
          productImage
        }
      }
    }
  }
`;

export const ADMIN_COVERAGE_CUSTOMER_WISHLIST_ITEMS = `
  query adminCustomerWishlistItems($customerId: Int!) {
    adminCustomerWishlistItems(customerId: $customerId) {
      totalCount
      edges {
        node {
          id
          productId
          sku
          name
          price
          formattedPrice
          productImage
          additional
        }
      }
    }
  }
`;

export const ADMIN_COVERAGE_CUSTOMER_RECENT_ORDER_ITEMS = `
  query adminCustomerRecentOrderItems($customerId: Int!) {
    adminCustomerRecentOrderItems(customerId: $customerId) {
      totalCount
      edges {
        node {
          id
          productId
          sku
          type
          name
          price
          formattedPrice
          productImage
          additional
        }
      }
    }
  }
`;

export const ADMIN_COVERAGE_SET_DEFAULT_CUSTOMER_ADDRESS = `
  mutation setDefaultAdminCustomerAddress($input: setDefaultAdminCustomerAddressInput!) {
    setDefaultAdminCustomerAddress(input: $input) {
      adminCustomerAddress {
        id
        _id
        customerId
        defaultAddress
        city
      }
    }
  }
`;
