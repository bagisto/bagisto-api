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

export const CREATE_WISHLIST = `
  mutation CreateWishlist($input: createWishlistInput!) {
    createWishlist(input: $input) {
      wishlist {
        id
        _id
        createdAt
      }
      clientMutationId
    }
  }
`;

export const TOGGLE_WISHLIST = `
  mutation ToggleWishlist($input: toggleWishlistInput!) {
    toggleWishlist(input: $input) {
      wishlist {
        id
        _id
        product {
          id
          name
          price
        }
        createdAt
      }
      clientMutationId
    }
  }
`;

export const DELETE_WISHLIST = `
  mutation DeleteWishlist($input: deleteWishlistInput!) {
    deleteWishlist(input: $input) {
      wishlist {
        id
        _id
      }
      clientMutationId
    }
  }
`;

export const DELETE_ALL_WISHLISTS = `
  mutation DeleteAllWishlists {
    createDeleteAllWishlists(input: {}) {
      deleteAllWishlists {
        message
        deletedCount
      }
    }
  }
`;

export const MOVE_WISHLIST_TO_CART = `
  mutation MoveWishlistToCart($input: moveWishlistToCartInput!) {
    moveWishlistToCart(input: $input) {
      wishlistToCart {
        message
      }
      clientMutationId
    }
  }
`;
