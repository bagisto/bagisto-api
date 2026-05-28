// rest/endpoints/admin.settings.endpoints.ts
//
// W4b — Admin Settings + CMS Pages + Configuration endpoint registry.
// Self-contained (not added to the central ENDPOINTS const) so the file can
// land without merge conflicts against parallel waves.

export const ADMIN_SETTINGS = {
  // ── Currencies ──────────────────────────────────────────────
  CURRENCIES: '/api/admin/settings/currencies',
  CURRENCY: (id: number | string) => `/api/admin/settings/currencies/${id}`,
  CURRENCIES_MASS_DELETE: '/api/admin/settings/currencies/mass-delete',

  // ── Channels ────────────────────────────────────────────────
  CHANNELS: '/api/admin/settings/channels',
  CHANNEL: (id: number | string) => `/api/admin/settings/channels/${id}`,

  // ── Exchange Rates ──────────────────────────────────────────
  EXCHANGE_RATES: '/api/admin/settings/exchange-rates',
  EXCHANGE_RATE: (id: number | string) => `/api/admin/settings/exchange-rates/${id}`,
  EXCHANGE_RATES_MASS_DELETE: '/api/admin/settings/exchange-rates/mass-delete',

  // ── Locales ─────────────────────────────────────────────────
  LOCALES: '/api/admin/settings/locales',
  LOCALE: (id: number | string) => `/api/admin/settings/locales/${id}`,
  LOCALES_MASS_DELETE: '/api/admin/settings/locales/mass-delete',

  // ── Inventory Sources ───────────────────────────────────────
  INVENTORY_SOURCES: '/api/admin/settings/inventory-sources',
  INVENTORY_SOURCE: (id: number | string) => `/api/admin/settings/inventory-sources/${id}`,
  INVENTORY_SOURCES_MASS_DELETE: '/api/admin/settings/inventory-sources/mass-delete',

  // ── Tax Rates ───────────────────────────────────────────────
  TAX_RATES: '/api/admin/settings/tax-rates',
  TAX_RATE: (id: number | string) => `/api/admin/settings/tax-rates/${id}`,

  // ── Tax Categories ──────────────────────────────────────────
  TAX_CATEGORIES: '/api/admin/settings/tax-categories',
  TAX_CATEGORY: (id: number | string) => `/api/admin/settings/tax-categories/${id}`,

  // ── Roles ───────────────────────────────────────────────────
  ROLES: '/api/admin/settings/roles',
  ROLE: (id: number | string) => `/api/admin/settings/roles/${id}`,

  // ── Users (admins) ──────────────────────────────────────────
  USERS: '/api/admin/settings/users',
  USER: (id: number | string) => `/api/admin/settings/users/${id}`,

  // ── Themes ──────────────────────────────────────────────────
  THEMES: '/api/admin/settings/themes',
  THEME: (id: number | string) => `/api/admin/settings/themes/${id}`,
  THEMES_MASS_DELETE: '/api/admin/settings/themes/mass-delete',
  THEMES_MASS_UPDATE_STATUS: '/api/admin/settings/themes/mass-update-status',

  // ── Data Transfer Imports ───────────────────────────────────
  DATA_TRANSFER_IMPORTS: '/api/admin/settings/data-transfer/imports',
  DATA_TRANSFER_IMPORT: (id: number | string) =>
    `/api/admin/settings/data-transfer/imports/${id}`,
  DATA_TRANSFER_IMPORT_CANCEL: (id: number | string) =>
    `/api/admin/settings/data-transfer/imports/${id}/cancel`,

  // ── CMS Pages ───────────────────────────────────────────────
  CMS_PAGES: '/api/admin/cms/pages',
  CMS_PAGE: (id: number | string) => `/api/admin/cms/pages/${id}`,
  CMS_PAGES_MASS_DELETE: '/api/admin/cms/pages/mass-delete',

  // ── Configuration ───────────────────────────────────────────
  CONFIG_MENU: '/api/admin/configuration/menu',
  CONFIG_VALUES: '/api/admin/configuration',
  CONFIG_UPDATE: '/api/admin/configuration',
};
