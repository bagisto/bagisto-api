export const GET_CUSTOMER_DOWNLOADABLE_PRODUCTS = `
  query GetCustomerDownloadableProducts($first: Int, $after: String, $last: Int, $before: String, $status: String) {
    customerDownloadableProducts(first: $first, after: $after, last: $last, before: $before, status: $status) {
      edges {
        cursor
        node {
          _id
          productName
          name
          fileName
          type
          downloadBought
          downloadUsed
          downloadCanceled
          status
          remainingDownloads
          order {
            _id
            incrementId
            status
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

export const GET_CUSTOMER_DOWNLOADABLE_PRODUCTS_BY_STATUS = `
  query GetAvailableDownloads($first: Int, $status: String) {
    customerDownloadableProducts(first: $first, status: $status) {
      edges {
        cursor
        node {
          _id
          productName
          name
          status
          downloadBought
          downloadUsed
          remainingDownloads
        }
      }
      totalCount
    }
  }
`;

export const GET_CUSTOMER_DOWNLOADABLE_PRODUCTS_PAGINATED = `
  query GetCustomerDownloadableProductsNextPage($first: Int, $after: String) {
    customerDownloadableProducts(first: $first, after: $after) {
      edges {
        cursor
        node {
          _id
          productName
          name
          status
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

export const GET_CUSTOMER_DOWNLOADABLE_PRODUCT = `
  query GetCustomerDownloadableProduct($id: ID!) {
    customerDownloadableProduct(id: $id) {
      _id
      productName
      name
      fileName
      type
      downloadBought
      downloadUsed
      downloadCanceled
      status
      remainingDownloads
      order {
        _id
        incrementId
        status
        grandTotal
      }
      createdAt
      updatedAt
    }
  }
`;
