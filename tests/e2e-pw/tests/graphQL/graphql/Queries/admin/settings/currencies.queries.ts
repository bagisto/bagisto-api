// Admin Settings — Currencies GraphQL operations.
// Verified via introspection 2026-05-26.

export const ADMIN_CURRENCIES_LIST = `
  query adminSettingsCurrencies($first: Int, $after: String, $code: String, $name: String) {
    adminSettingsCurrencies(first: $first, after: $after, code: $code, name: $name) {
      edges { node { id _id code name symbol decimal } }
    }
  }
`;

export const ADMIN_CURRENCY_DETAIL = `
  query adminSettingsCurrency($id: ID!) {
    adminSettingsCurrency(id: $id) { id _id code name symbol decimal }
  }
`;

export const ADMIN_CURRENCY_CREATE = `
  mutation createAdminSettingsCurrency($code: String!, $name: String!, $symbol: String, $decimal: Int) {
    createAdminSettingsCurrency(input: { code: $code, name: $name, symbol: $symbol, decimal: $decimal }) {
      adminSettingsCurrency { id _id code name symbol }
    }
  }
`;

export const ADMIN_CURRENCY_UPDATE = `
  mutation updateAdminSettingsCurrency($id: ID!, $name: String) {
    updateAdminSettingsCurrency(input: { id: $id, name: $name }) {
      adminSettingsCurrency { id _id code name }
    }
  }
`;

export const ADMIN_CURRENCY_DELETE = `
  mutation deleteAdminSettingsCurrency($id: ID!) {
    deleteAdminSettingsCurrency(input: { id: $id }) { adminSettingsCurrency { id } }
  }
`;

export const ADMIN_CURRENCY_MASS_DELETE = `
  mutation createAdminSettingsCurrencyMassDelete($indices: Iterable!) {
    createAdminSettingsCurrencyMassDelete(input: { indices: $indices }) {
      adminSettingsCurrencyMassDelete { id }
    }
  }
`;
