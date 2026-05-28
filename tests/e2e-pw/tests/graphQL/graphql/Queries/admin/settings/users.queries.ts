// Admin Settings — Users (admins) GraphQL operations. No mass-delete.

export const ADMIN_USERS_LIST = `
  query adminSettingsUsers($first: Int, $after: String, $name: String) {
    adminSettingsUsers(first: $first, after: $after, name: $name) {
      edges { node { id _id name email roleId status } }
    }
  }
`;

export const ADMIN_USER_DETAIL = `
  query adminSettingsUser($id: ID!) {
    adminSettingsUser(id: $id) { id _id name email roleId status }
  }
`;

export const ADMIN_USER_CREATE = `
  mutation createAdminSettingsUser($name: String!, $email: String!, $password: String!, $roleId: Int!, $status: Int) {
    createAdminSettingsUser(input: { name: $name, email: $email, password: $password, roleId: $roleId, status: $status }) {
      adminSettingsUser { id _id name email }
    }
  }
`;

export const ADMIN_USER_UPDATE = `
  mutation updateAdminSettingsUser($id: ID!, $name: String) {
    updateAdminSettingsUser(input: { id: $id, name: $name }) {
      adminSettingsUser { id _id name }
    }
  }
`;

export const ADMIN_USER_DELETE = `
  mutation deleteAdminSettingsUser($id: ID!) {
    deleteAdminSettingsUser(input: { id: $id }) { adminSettingsUser { id } }
  }
`;
