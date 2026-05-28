// tests/graphQL/graphql/Queries/admin/catalog/productInventory.queries.ts
//
// Admin Catalog Product Inventories GraphQL operations. Verified via
// introspection 2026-05-26.
//
// adminCatalogProductInventories(productId: Int!) is a cursor connection but
// does NOT accept `first` / `after` args — fetch the full list per product.

export const ADMIN_PRODUCT_INVENTORIES_QUERY = `
  query adminCatalogProductInventories($productId: Int!) {
    adminCatalogProductInventories(productId: $productId) {
      edges {
        node {
          id
          _id
          sourceId
          sourceCode
          sourceName
          qty
        }
      }
    }
  }
`;

export const ADMIN_PRODUCT_INVENTORY_UPDATE_MUTATION = `
  mutation updateAdminCatalogProductInventory(
    $id: ID!
    $productId: Int!
    $inventories: Iterable!
  ) {
    updateAdminCatalogProductInventory(
      input: { id: $id, productId: $productId, inventories: $inventories }
    ) {
      adminCatalogProductInventory {
        id
        _id
        qty
      }
    }
  }
`;
