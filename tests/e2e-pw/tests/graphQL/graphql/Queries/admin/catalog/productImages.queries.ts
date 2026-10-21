export const ADMIN_PRODUCT_IMAGE_CREATE_MUTATION = `
  mutation createAdminCatalogProductImage($productId: Int!) {
    createAdminCatalogProductImage(input: { productId: $productId }) {
      adminCatalogProductImage {
        id
        _id
      }
    }
  }
`;

export const ADMIN_PRODUCT_IMAGE_REORDER_MUTATION = `
  mutation reorderAdminCatalogProductImage(
    $id: ID!
    $productId: Int!
    $order: Iterable!
  ) {
    reorderAdminCatalogProductImage(
      input: { id: $id, productId: $productId, order: $order }
    ) {
      adminCatalogProductImage {
        id
        _id
      }
    }
  }
`;

export const ADMIN_PRODUCT_IMAGE_DELETE_MUTATION = `
  mutation deleteAdminCatalogProductImage($id: ID!) {
    deleteAdminCatalogProductImage(input: { id: $id }) {
      adminCatalogProductImage {
        id
        _id
      }
    }
  }
`;
