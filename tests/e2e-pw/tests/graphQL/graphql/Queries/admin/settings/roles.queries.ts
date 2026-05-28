// Admin Settings — Roles GraphQL operations. No mass-delete.

export const ADMIN_ROLES_LIST = `
  query adminSettingsRoles($first: Int, $after: String, $name: String) {
    adminSettingsRoles(first: $first, after: $after, name: $name) {
      edges { node { id _id name description permissionType } }
    }
  }
`;

export const ADMIN_ROLE_DETAIL = `
  query adminSettingsRole($id: ID!) {
    adminSettingsRole(id: $id) { id _id name description permissionType }
  }
`;

export const ADMIN_ROLE_CREATE = `
  mutation createAdminSettingsRole($name: String!, $description: String!, $permissionType: String!, $permissions: Iterable) {
    createAdminSettingsRole(input: { name: $name, description: $description, permissionType: $permissionType, permissions: $permissions }) {
      adminSettingsRole { id _id name }
    }
  }
`;

export const ADMIN_ROLE_UPDATE = `
  mutation updateAdminSettingsRole($id: ID!, $name: String, $description: String, $permissionType: String) {
    updateAdminSettingsRole(input: { id: $id, name: $name, description: $description, permissionType: $permissionType }) {
      adminSettingsRole { id _id name }
    }
  }
`;

export const ADMIN_ROLE_DELETE = `
  mutation deleteAdminSettingsRole($id: ID!) {
    deleteAdminSettingsRole(input: { id: $id }) { adminSettingsRole { id } }
  }
`;
