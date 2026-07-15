export const FLOW_ATTRIBUTE_CREATE = `
  mutation createAdminAttribute(
    $code: String!
    $adminName: String!
    $type: String!
    $translations: Iterable
  ) {
    createAdminAttribute(
      input: { code: $code, adminName: $adminName, type: $type, translations: $translations }
    ) {
      adminAttribute {
        id
        _id
        code
      }
    }
  }
`;

export const FLOW_PRODUCT_UPDATE_EXTRAS = `
  mutation updateAdminCatalogProduct($id: ID!, $extras: Iterable) {
    updateAdminCatalogProduct(input: { id: $id, extras: $extras }) {
      adminCatalogProduct {
        id
        _id
      }
    }
  }
`;

export const FLOW_PRODUCT_DETAIL = `
  query adminCatalogProduct($id: ID!) {
    adminCatalogProduct(id: $id) {
      id
      _id
      sku
      attributeFamilyId
      attributeValues {
        edges {
          node {
            code
            adminName
            value
          }
        }
      }
    }
  }
`;
