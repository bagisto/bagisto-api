// graphql/queries/wishlist.queries.ts

// Get All Wishlists
export const GET_ALL_WISHLISTS = `
  query GetAllWishlists($first: Int, $after: String) {
    wishlists(first: $first, after: $after) {
      edges {
        cursor
        node {
          id
          _id
          product {
            id
            name
            price
            sku
            type
            description
            baseImageUrl
          }
          customer {
            id
            email
          }
          channel {
            id
            code
          }
          createdAt
          updatedAt
        }
      }
      pageInfo {
        endCursor
        startCursor
        hasNextPage
        hasPreviousPage
      }
      totalCount
    }
  }
`;

// Get Single Wishlist
export const GET_WISHLIST = `
  query GetWishlist($id: ID!) {
    wishlist(id: $id) {
      id
      _id
      product {
        id
        name
        price
        sku
        type
        description
        baseImageUrl
        urlKey
      }
      customer {
        id
        email
      }
      channel {
        id
        code
      }
      createdAt
      updatedAt
    }
  }
`;