// tests/graphQL/graphql/Queries/admin/catalog/productCustomerGroupPrices.queries.ts
//
// Admin Catalog Product Customer Group Prices GraphQL operations. Verified
// via introspection 2026-05-26.
//
// adminCatalogProductCustomerGroupPrices(productId: Int!) returns a plain
// list of objects (NOT a connection — no edges/node wrapper).

export const ADMIN_PRODUCT_CGP_LIST_QUERY = `
  query adminCatalogProductCustomerGroupPrices($productId: Int!) {
    adminCatalogProductCustomerGroupPrices(productId: $productId) {
      id
      _id
      productId
      qty
      valueType
      value
      customerGroupId
      customerGroupName
    }
  }
`;

export const ADMIN_PRODUCT_CGP_CREATE_MUTATION = `
  mutation createAdminCatalogProductCustomerGroupPrice(
    $productId: Int!
    $qty: Int!
    $valueType: String!
    $value: Float!
    $customerGroupId: Int
  ) {
    createAdminCatalogProductCustomerGroupPrice(
      input: {
        productId: $productId
        qty: $qty
        valueType: $valueType
        value: $value
        customerGroupId: $customerGroupId
      }
    ) {
      adminCatalogProductCustomerGroupPrice {
        id
        _id
        productId
        qty
        value
      }
    }
  }
`;

export const ADMIN_PRODUCT_CGP_UPDATE_MUTATION = `
  mutation updateAdminCatalogProductCustomerGroupPrice(
    $id: ID!
    $productId: Int!
    $qty: Int
    $valueType: String
    $value: Float
    $customerGroupId: Int
  ) {
    updateAdminCatalogProductCustomerGroupPrice(
      input: {
        id: $id
        productId: $productId
        qty: $qty
        valueType: $valueType
        value: $value
        customerGroupId: $customerGroupId
      }
    ) {
      adminCatalogProductCustomerGroupPrice {
        id
        _id
        qty
        value
      }
    }
  }
`;

export const ADMIN_PRODUCT_CGP_DELETE_MUTATION = `
  mutation deleteAdminCatalogProductCustomerGroupPrice(
    $id: ID!
    $productId: Int!
  ) {
    deleteAdminCatalogProductCustomerGroupPrice(
      input: { id: $id, productId: $productId }
    ) {
      adminCatalogProductCustomerGroupPrice {
        id
        _id
      }
    }
  }
`;
