// Admin Appearance — Themes GraphQL operations. A theme is keyed by its `code`,
// not by an id, and activation is the only write.

export const ADMIN_APPEARANCE_THEMES_LIST = `
  query adminAppearanceThemes {
    adminAppearanceThemes {
      code
      name
      author
      version
      status
      isInstalled
      activeOn
    }
  }
`;

export const ADMIN_APPEARANCE_THEME_DETAIL = `
  query adminAppearanceTheme($code: String!) {
    adminAppearanceTheme(code: $code) {
      code
      name
      status
      isInstalled
      activeOn
    }
  }
`;

export const ADMIN_APPEARANCE_THEME_IMPACT = `
  query adminAppearanceThemeImpact($code: String!, $channelIds: [Int!]!) {
    adminAppearanceThemeImpact(code: $code, channelIds: $channelIds) {
      code
      impact
    }
  }
`;

export const ADMIN_APPEARANCE_THEME_ACTIVATE = `
  mutation createAdminAppearanceThemeActivate($code: String!, $channelIds: Iterable!) {
    createAdminAppearanceThemeActivate(input: { code: $code, channelIds: $channelIds }) {
      adminAppearanceThemeActivate {
        code
        message
      }
    }
  }
`;
