// Admin Settings — Channels GraphQL operations.

export const ADMIN_CHANNELS_LIST = `
  query adminSettingsChannels($first: Int, $after: String) {
    adminSettingsChannels(first: $first, after: $after) {
      edges { node { id _id code hostname name } }
    }
  }
`;

export const ADMIN_CHANNEL_DETAIL = `
  query adminSettingsChannel($id: ID!) {
    adminSettingsChannel(id: $id) { id _id code hostname name description }
  }
`;

export const ADMIN_CHANNEL_CREATE = `
  mutation createAdminSettingsChannel(
    $code: String!, $name: String!, $hostname: String,
    $locales: Iterable!, $currencies: Iterable!, $inventorySources: Iterable!,
    $defaultLocaleId: Int!, $baseCurrencyId: Int!, $rootCategoryId: Int!
  ) {
    createAdminSettingsChannel(input: {
      code: $code, name: $name, hostname: $hostname,
      locales: $locales, currencies: $currencies, inventorySources: $inventorySources,
      defaultLocaleId: $defaultLocaleId, baseCurrencyId: $baseCurrencyId, rootCategoryId: $rootCategoryId
    }) {
      adminSettingsChannel { id _id code }
    }
  }
`;

export const ADMIN_CHANNEL_UPDATE = `
  mutation updateAdminSettingsChannel($id: ID!, $name: String) {
    updateAdminSettingsChannel(input: { id: $id, name: $name }) {
      adminSettingsChannel { id _id name }
    }
  }
`;

export const ADMIN_CHANNEL_DELETE = `
  mutation deleteAdminSettingsChannel($id: ID!) {
    deleteAdminSettingsChannel(input: { id: $id }) { adminSettingsChannel { id } }
  }
`;
