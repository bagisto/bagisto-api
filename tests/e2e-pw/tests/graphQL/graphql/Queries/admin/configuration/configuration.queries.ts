// Admin Configuration GraphQL operations.
// Note: query field names are `menuAdminConfigurationMenu` and
// `valuesAdminConfigurationValues` (auto-derived from the named resolver).

export const ADMIN_CONFIG_MENU = `
  query menuAdminConfigurationMenu($slug: String, $includeValues: Boolean, $channel: String, $locale: String) {
    menuAdminConfigurationMenu(slug: $slug, include_values: $includeValues, channel: $channel, locale: $locale) {
      id
      slug
      tree
    }
  }
`;

export const ADMIN_CONFIG_VALUES = `
  query valuesAdminConfigurationValues($slug: String!, $channel: String, $locale: String) {
    valuesAdminConfigurationValues(slug: $slug, channel: $channel, locale: $locale) {
      id slug channel locale values
    }
  }
`;

export const ADMIN_CONFIG_UPDATE = `
  mutation createAdminConfigurationUpdate($slug: String!, $values: Iterable!, $channel: String, $locale: String) {
    createAdminConfigurationUpdate(input: { slug: $slug, values: $values, channel: $channel, locale: $locale }) {
      adminConfigurationUpdate { id }
    }
  }
`;
