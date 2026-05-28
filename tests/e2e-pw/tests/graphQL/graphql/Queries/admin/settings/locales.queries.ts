// Admin Settings — Locales GraphQL operations.

export const ADMIN_LOCALES_LIST = `
  query adminSettingsLocales($first: Int, $after: String, $code: String) {
    adminSettingsLocales(first: $first, after: $after, code: $code) {
      edges { node { id _id code name direction } }
    }
  }
`;

export const ADMIN_LOCALE_DETAIL = `
  query adminSettingsLocale($id: ID!) {
    adminSettingsLocale(id: $id) { id _id code name direction }
  }
`;

export const ADMIN_LOCALE_CREATE = `
  mutation createAdminSettingsLocale($code: String!, $name: String!, $direction: String!) {
    createAdminSettingsLocale(input: { code: $code, name: $name, direction: $direction }) {
      adminSettingsLocale { id _id code name direction }
    }
  }
`;

export const ADMIN_LOCALE_UPDATE = `
  mutation updateAdminSettingsLocale($id: ID!, $name: String) {
    updateAdminSettingsLocale(input: { id: $id, name: $name }) {
      adminSettingsLocale { id _id name }
    }
  }
`;

export const ADMIN_LOCALE_DELETE = `
  mutation deleteAdminSettingsLocale($id: ID!) {
    deleteAdminSettingsLocale(input: { id: $id }) { adminSettingsLocale { id } }
  }
`;

export const ADMIN_LOCALE_MASS_DELETE = `
  mutation createAdminSettingsLocaleMassDelete($indices: Iterable!) {
    createAdminSettingsLocaleMassDelete(input: { indices: $indices }) {
      adminSettingsLocaleMassDelete { id }
    }
  }
`;
