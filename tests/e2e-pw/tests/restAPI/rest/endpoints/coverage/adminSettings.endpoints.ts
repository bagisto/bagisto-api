export const ADMIN_SETTINGS_COVERAGE = {
  EXCHANGE_RATES: '/api/admin/settings/exchange-rates',
  EXCHANGE_RATE: (id: number | string) => `/api/admin/settings/exchange-rates/${id}`,
  EXCHANGE_RATES_UPDATE_RATES: '/api/admin/settings/exchange-rates/update-rates',
  TAX_RATES: '/api/admin/settings/tax-rates',
  TAX_RATE: (id: number | string) => `/api/admin/settings/tax-rates/${id}`,
  TAX_RATES_EXPORT: '/api/admin/settings/tax-rates/export',
  USERS_DELETE_SELF: '/api/admin/settings/users/delete-self',
  ADMIN_PROFILE: '/api/admin/get',
  DATA_TRANSFER_IMPORTS: '/api/admin/settings/data-transfer/imports',
  DATA_TRANSFER_IMPORT: (id: number | string) => `/api/admin/settings/data-transfer/imports/${id}`,
  CATALOG_PRODUCTS: '/api/admin/catalog/products',
  CATALOG_PRODUCT: (id: number | string) => `/api/admin/catalog/products/${id}`,
};

export const ADMIN_SETTINGS_IMPORT_IRI = (id: number | string, action: string) =>
  `/api/admin/settings/data-transfer/imports/${id}/${action}`;
