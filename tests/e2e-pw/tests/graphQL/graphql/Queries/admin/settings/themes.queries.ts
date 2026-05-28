// Admin Settings — Themes GraphQL operations. Includes mass-delete + mass-update-status.

export const ADMIN_THEMES_LIST = `
  query adminSettingsThemes($first: Int, $after: String) {
    adminSettingsThemes(first: $first, after: $after) {
      edges { node { id _id name type themeCode channelId status sortOrder } }
    }
  }
`;

export const ADMIN_THEME_DETAIL = `
  query adminSettingsTheme($id: ID!) {
    adminSettingsTheme(id: $id) { id _id name type themeCode channelId status }
  }
`;

export const ADMIN_THEME_CREATE = `
  mutation createAdminSettingsTheme(
    $name: String!, $sortOrder: Int!, $type: String!,
    $channelId: Int!, $themeCode: String!, $status: Int
  ) {
    createAdminSettingsTheme(input: {
      name: $name, sortOrder: $sortOrder, type: $type,
      channelId: $channelId, themeCode: $themeCode, status: $status
    }) {
      adminSettingsTheme { id _id name type }
    }
  }
`;

export const ADMIN_THEME_UPDATE = `
  mutation updateAdminSettingsTheme($id: ID!, $name: String) {
    updateAdminSettingsTheme(input: { id: $id, name: $name }) {
      adminSettingsTheme { id _id name }
    }
  }
`;

export const ADMIN_THEME_DELETE = `
  mutation deleteAdminSettingsTheme($id: ID!) {
    deleteAdminSettingsTheme(input: { id: $id }) { adminSettingsTheme { id } }
  }
`;

export const ADMIN_THEME_MASS_DELETE = `
  mutation createAdminSettingsThemeMassDelete($indices: Iterable!) {
    createAdminSettingsThemeMassDelete(input: { indices: $indices }) {
      adminSettingsThemeMassDelete { id }
    }
  }
`;

export const ADMIN_THEME_MASS_UPDATE_STATUS = `
  mutation createAdminSettingsThemeMassUpdateStatus($indices: Iterable!, $value: Int!) {
    createAdminSettingsThemeMassUpdateStatus(input: { indices: $indices, value: $value }) {
      adminSettingsThemeMassUpdateStatus { id }
    }
  }
`;
