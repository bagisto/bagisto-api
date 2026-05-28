// tests/graphQL/graphql/Queries/admin/catalog/productImages.queries.ts
//
// Admin Catalog Product Images GraphQL operations.
//
// IMPORTANT: image UPLOAD (createAdminCatalogProductImage) is REST-only —
// the API explicitly rejects it from GraphQL with "file-upload-rest-only"
// (CLAUDE.md "Phase 5.11"). reorder + delete are usable over GraphQL but
// only by clients that already know image ids — there is no admin
// product-images list query in the schema (the AdminCatalogProductImage
// type is not exposed beyond the mutation payload wrappers).
//
// Tests for this menu group are intentionally minimal — the spec file
// documents the REST-only constraint via test.skip().

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
