export const ADMIN_RMA = {
  STATUSES: '/api/admin/rma/statuses',
  STATUS: (id: number | string) => `/api/admin/rma/statuses/${id}`,
  STATUSES_MASS_DELETE: '/api/admin/rma/statuses/mass-delete',
  STATUSES_MASS_UPDATE_STATUS: '/api/admin/rma/statuses/mass-update-status',

  REASONS: '/api/admin/rma/reasons',
  REASON: (id: number | string) => `/api/admin/rma/reasons/${id}`,
  REASONS_MASS_DELETE: '/api/admin/rma/reasons/mass-delete',
  REASONS_MASS_UPDATE_STATUS: '/api/admin/rma/reasons/mass-update-status',

  RULES: '/api/admin/rma/rules',
  RULE: (id: number | string) => `/api/admin/rma/rules/${id}`,
  RULES_MASS_DELETE: '/api/admin/rma/rules/mass-delete',
  RULES_MASS_UPDATE_STATUS: '/api/admin/rma/rules/mass-update-status',

  CUSTOM_FIELDS: '/api/admin/rma/custom-fields',
  CUSTOM_FIELD: (id: number | string) => `/api/admin/rma/custom-fields/${id}`,
  CUSTOM_FIELDS_MASS_DELETE: '/api/admin/rma/custom-fields/mass-delete',
  CUSTOM_FIELDS_MASS_UPDATE_STATUS: '/api/admin/rma/custom-fields/mass-update-status',

  REQUEST_ORDER_ITEMS: '/api/admin/rma/requests/order-items',
  REQUEST_RESOLUTION_REASONS: '/api/admin/rma/requests/resolution-reasons',

  EU_WITHDRAWAL: (id: number | string) => `/api/admin/eu-withdrawals/${id}`,
  EU_WITHDRAWAL_RESEND_CONFIRMATION: (id: number | string) => `/api/admin/eu-withdrawals/${id}/resend-confirmation`,
  SHOP_EU_WITHDRAWALS: '/api/shop/eu-withdrawals',

  IMPORT_SAMPLE: (type: string, format: string) => `/api/admin/settings/data-transfer/imports/sample/${type}/${format}`,
};
