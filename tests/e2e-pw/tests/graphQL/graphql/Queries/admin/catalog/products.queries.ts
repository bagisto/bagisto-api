// tests/graphQL/graphql/Queries/admin/catalog/products.queries.ts
//
// Admin Catalog Products GraphQL operations. Field shapes verified via
// introspection on 2026-05-26.

export const ADMIN_PRODUCTS_LIST_QUERY = `
  query adminCatalogProducts($first: Int, $after: String) {
    adminCatalogProducts(first: $first, after: $after) {
      edges {
        node {
          id
          _id
          sku
          name
          type
          status
          price
          formattedPrice
          attributeFamilyId
        }
      }
      pageInfo {
        endCursor
        hasNextPage
      }
    }
  }
`;

export const ADMIN_PRODUCT_DETAIL_QUERY = `
  query adminCatalogProduct($id: ID!) {
    adminCatalogProduct(id: $id) {
      id
      _id
      sku
      name
      type
      status
      price
      attributeFamilyId
      urlKey
    }
  }
`;

export const ADMIN_PRODUCT_CREATE_MUTATION = `
  mutation createAdminCatalogProduct(
    $sku: String!
    $attributeFamilyId: Int!
    $type: String
    $superAttributes: Iterable
  ) {
    createAdminCatalogProduct(
      input: {
        sku: $sku
        attributeFamilyId: $attributeFamilyId
        type: $type
        superAttributes: $superAttributes
      }
    ) {
      adminCatalogProduct {
        id
        _id
        sku
        type
      }
    }
  }
`;

export const ADMIN_PRODUCT_UPDATE_MUTATION = `
  mutation updateAdminCatalogProduct(
    $id: ID!
    $sku: String
    $urlKey: String
    $status: Int
    $price: String
  ) {
    updateAdminCatalogProduct(
      input: { id: $id, sku: $sku, urlKey: $urlKey, status: $status, price: $price }
    ) {
      adminCatalogProduct {
        id
        _id
        sku
      }
    }
  }
`;

export const ADMIN_PRODUCT_DELETE_MUTATION = `
  mutation deleteAdminCatalogProduct($id: ID!) {
    deleteAdminCatalogProduct(input: { id: $id }) {
      adminCatalogProduct {
        id
        _id
      }
    }
  }
`;

export const ADMIN_PRODUCT_COPY_MUTATION = `
  mutation createAdminCatalogProductCopy($sourceId: Int!) {
    createAdminCatalogProductCopy(input: { sourceId: $sourceId }) {
      adminCatalogProductCopy {
        id
        _id
      }
    }
  }
`;

export const ADMIN_PRODUCT_MASS_DELETE_MUTATION = `
  mutation createAdminCatalogProductMassDelete($indices: Iterable!) {
    createAdminCatalogProductMassDelete(input: { indices: $indices }) {
      adminCatalogProductMassDelete {
        id
        _id
      }
    }
  }
`;

export const ADMIN_PRODUCT_MASS_UPDATE_STATUS_MUTATION = `
  mutation createAdminCatalogProductMassUpdateStatus($indices: Iterable!, $value: Int!) {
    createAdminCatalogProductMassUpdateStatus(input: { indices: $indices, value: $value }) {
      adminCatalogProductMassUpdateStatus {
        id
        _id
      }
    }
  }
`;
