// graphql/queries/compare.queries.ts

// Get All Compare Items
export const GET_ALL_COMPARE_ITEMS = `
  query GetAllCompareItems($first: Int, $after: String) {
    compareItems(first: $first, after: $after) {
      edges {
        cursor
        node {
          id
          _id
          product {
            id
            name
            sku
            urlKey
            price
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
      totalCount
    }
  }
`;

export const GET_COMPARE_ITEMS_PAGINATED = `
  query GetCompareItemsPaginated($first: Int, $after: String) {
    compareItems(first: $first, after: $after) {
      edges {
        cursor
        node {
          id
          _id
          product {
            id
            name
            sku
          }
        }
      }
      pageInfo {
        endCursor
        hasNextPage
      }
      totalCount
    }
  }
`;

// Get Single Compare Item
export const GET_COMPARE_ITEM = `
  query GetCompareItem($id: ID!) {
    compareItem(id: $id) {
      id
      _id
      product {
        id
        name
        sku
        urlKey
        price
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
`;

export const CREATE_COMPARE_ITEM = `
  mutation CreateCompareItem($input: createCompareItemInput!) {
    createCompareItem(input: $input) {
      compareItem {
        id
        _id
        createdAt
      }
      clientMutationId
    }
  }
`;

export const DELETE_COMPARE_ITEM = `
  mutation DeleteCompareItem($input: deleteCompareItemInput!) {
    deleteCompareItem(input: $input) {
      compareItem {
        id
        _id
      }
      clientMutationId
    }
  }
`;

export const DELETE_ALL_COMPARE_ITEMS = `
  mutation DeleteAllCompareItems {
    createDeleteAllCompareItems(input: {}) {
      deleteAllCompareItems {
        message
        deletedCount
      }
    }
  }
`;
