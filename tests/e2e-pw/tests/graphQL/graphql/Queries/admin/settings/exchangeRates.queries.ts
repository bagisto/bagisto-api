// Admin Settings — Exchange Rates GraphQL operations.

export const ADMIN_EXCHANGE_RATES_LIST = `
  query adminSettingsExchangeRates($first: Int, $after: String) {
    adminSettingsExchangeRates(first: $first, after: $after) {
      edges { node { id _id targetCurrency rate targetCurrencyCode } }
    }
  }
`;

export const ADMIN_EXCHANGE_RATE_DETAIL = `
  query adminSettingsExchangeRate($id: ID!) {
    adminSettingsExchangeRate(id: $id) { id _id targetCurrency rate }
  }
`;

export const ADMIN_EXCHANGE_RATE_CREATE = `
  mutation createAdminSettingsExchangeRate($targetCurrency: Int!, $rate: Float!) {
    createAdminSettingsExchangeRate(input: { targetCurrency: $targetCurrency, rate: $rate }) {
      adminSettingsExchangeRate { id _id targetCurrency rate }
    }
  }
`;

export const ADMIN_EXCHANGE_RATE_UPDATE = `
  mutation updateAdminSettingsExchangeRate($id: ID!, $rate: Float) {
    updateAdminSettingsExchangeRate(input: { id: $id, rate: $rate }) {
      adminSettingsExchangeRate { id _id rate }
    }
  }
`;

export const ADMIN_EXCHANGE_RATE_DELETE = `
  mutation deleteAdminSettingsExchangeRate($id: ID!) {
    deleteAdminSettingsExchangeRate(input: { id: $id }) { adminSettingsExchangeRate { id } }
  }
`;

export const ADMIN_EXCHANGE_RATE_MASS_DELETE = `
  mutation createAdminSettingsExchangeRateMassDelete($indices: Iterable!) {
    createAdminSettingsExchangeRateMassDelete(input: { indices: $indices }) {
      adminSettingsExchangeRateMassDelete { id }
    }
  }
`;
