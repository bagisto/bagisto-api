export const ADMIN_PRODUCTS_PICKER_COVERAGE_QUERY = `
  query adminProducts($first: Int, $after: String, $query: String, $sku: String, $type: String, $status: Int) {
    adminProducts(first: $first, after: $after, query: $query, sku: $sku, type: $type, status: $status) {
      totalCount
      pageInfo {
        hasNextPage
        endCursor
      }
      edges {
        cursor
        node {
          _id
          sku
          type
          name
          status
          price
          formattedPrice
          baseImageUrl
          isSaleable
        }
      }
    }
  }
`;

export const ADMIN_PRODUCTS_PICKER_INVALID_STATUS_QUERY = `
  query adminProducts {
    adminProducts(first: 1, status: "enabled") {
      totalCount
    }
  }
`;

export const ADMIN_PRODUCT_VIDEO_CREATE_COVERAGE_MUTATION = `
  mutation createAdminCatalogProductVideo($input: createAdminCatalogProductVideoInput!) {
    createAdminCatalogProductVideo(input: $input) {
      adminCatalogProductVideo {
        _id
        productId
        path
      }
    }
  }
`;

export const ADMIN_PRODUCT_VIDEO_DELETE_COVERAGE_MUTATION = `
  mutation deleteAdminCatalogProductVideo($input: deleteAdminCatalogProductVideoInput!) {
    deleteAdminCatalogProductVideo(input: $input) {
      adminCatalogProductVideo {
        _id
        success
        message
      }
    }
  }
`;

export const ADMIN_PRODUCT_IMAGE_UPDATE_COVERAGE_MUTATION = `
  mutation updateAdminCatalogProductImage($input: updateAdminCatalogProductImageInput!) {
    updateAdminCatalogProductImage(input: $input) {
      adminCatalogProductImage {
        _id
        productId
        position
        altText
        path
        success
        message
      }
    }
  }
`;
