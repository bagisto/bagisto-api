// Admin Settings — Tax Categories GraphQL operations. No mass-delete.

export const ADMIN_TAX_CATEGORIES_LIST = `
  query adminSettingsTaxCategories($first: Int, $after: String) {
    adminSettingsTaxCategories(first: $first, after: $after) {
      edges { node { id _id code name description } }
    }
  }
`;

export const ADMIN_TAX_CATEGORY_DETAIL = `
  query adminSettingsTaxCategory($id: ID!) {
    adminSettingsTaxCategory(id: $id) { id _id code name description }
  }
`;

export const ADMIN_TAX_CATEGORY_CREATE = `
  mutation createAdminSettingsTaxCategory(
    $code: String!, $name: String!, $description: String!, $taxrates: Iterable!
  ) {
    createAdminSettingsTaxCategory(input: {
      code: $code, name: $name, description: $description, taxrates: $taxrates
    }) {
      adminSettingsTaxCategory { id _id code name }
    }
  }
`;

export const ADMIN_TAX_CATEGORY_UPDATE = `
  mutation updateAdminSettingsTaxCategory(
    $id: ID!, $name: String, $description: String, $taxrates: Iterable
  ) {
    updateAdminSettingsTaxCategory(input: {
      id: $id, name: $name, description: $description, taxrates: $taxrates
    }) {
      adminSettingsTaxCategory { id _id name }
    }
  }
`;

export const ADMIN_TAX_CATEGORY_DELETE = `
  mutation deleteAdminSettingsTaxCategory($id: ID!) {
    deleteAdminSettingsTaxCategory(input: { id: $id }) { adminSettingsTaxCategory { id } }
  }
`;
