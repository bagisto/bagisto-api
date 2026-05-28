// Admin Settings — Inventory Sources GraphQL operations.

export const ADMIN_INV_SOURCES_LIST = `
  query adminSettingsInventorySources($first: Int, $after: String, $code: String) {
    adminSettingsInventorySources(first: $first, after: $after, code: $code) {
      edges { node { id _id code name status } }
    }
  }
`;

export const ADMIN_INV_SOURCE_DETAIL = `
  query adminSettingsInventorySource($id: ID!) {
    adminSettingsInventorySource(id: $id) { id _id code name status }
  }
`;

export const ADMIN_INV_SOURCE_CREATE = `
  mutation createAdminSettingsInventorySource(
    $code: String!, $name: String!, $contactName: String!, $contactEmail: String,
    $contactNumber: String!, $country: String!, $state: String!, $city: String!,
    $street: String!, $postcode: String!, $priority: Int, $status: Int
  ) {
    createAdminSettingsInventorySource(input: {
      code: $code, name: $name, contactName: $contactName, contactEmail: $contactEmail,
      contactNumber: $contactNumber, country: $country, state: $state, city: $city,
      street: $street, postcode: $postcode, priority: $priority, status: $status
    }) {
      adminSettingsInventorySource { id _id code }
    }
  }
`;

export const ADMIN_INV_SOURCE_UPDATE = `
  mutation updateAdminSettingsInventorySource($id: ID!, $name: String) {
    updateAdminSettingsInventorySource(input: { id: $id, name: $name }) {
      adminSettingsInventorySource { id _id name }
    }
  }
`;

export const ADMIN_INV_SOURCE_DELETE = `
  mutation deleteAdminSettingsInventorySource($id: ID!) {
    deleteAdminSettingsInventorySource(input: { id: $id }) { adminSettingsInventorySource { id } }
  }
`;

export const ADMIN_INV_SOURCE_MASS_DELETE = `
  mutation createAdminSettingsInventorySourceMassDelete($indices: Iterable!) {
    createAdminSettingsInventorySourceMassDelete(input: { indices: $indices }) {
      adminSettingsInventorySourceMassDelete { id }
    }
  }
`;
