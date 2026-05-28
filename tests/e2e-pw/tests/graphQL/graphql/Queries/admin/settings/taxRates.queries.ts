// Admin Settings — Tax Rates GraphQL operations. No mass-delete.

export const ADMIN_TAX_RATES_LIST = `
  query adminSettingsTaxRates($first: Int, $after: String) {
    adminSettingsTaxRates(first: $first, after: $after) {
      edges { node { id _id identifier country state taxRate isZip zipCode } }
    }
  }
`;

export const ADMIN_TAX_RATE_DETAIL = `
  query adminSettingsTaxRate($id: ID!) {
    adminSettingsTaxRate(id: $id) { id _id identifier country state taxRate isZip zipCode zipFrom zipTo }
  }
`;

export const ADMIN_TAX_RATE_CREATE = `
  mutation createAdminSettingsTaxRate(
    $identifier: String!, $country: String!, $state: String, $taxRate: Float!,
    $isZip: Boolean!, $zipCode: String, $zipFrom: String, $zipTo: String
  ) {
    createAdminSettingsTaxRate(input: {
      identifier: $identifier, country: $country, state: $state, taxRate: $taxRate,
      isZip: $isZip, zipCode: $zipCode, zipFrom: $zipFrom, zipTo: $zipTo
    }) {
      adminSettingsTaxRate { id _id identifier }
    }
  }
`;

export const ADMIN_TAX_RATE_UPDATE = `
  mutation updateAdminSettingsTaxRate($id: ID!, $taxRate: Float) {
    updateAdminSettingsTaxRate(input: { id: $id, taxRate: $taxRate }) {
      adminSettingsTaxRate { id _id taxRate }
    }
  }
`;

export const ADMIN_TAX_RATE_DELETE = `
  mutation deleteAdminSettingsTaxRate($id: ID!) {
    deleteAdminSettingsTaxRate(input: { id: $id }) { adminSettingsTaxRate { id } }
  }
`;
