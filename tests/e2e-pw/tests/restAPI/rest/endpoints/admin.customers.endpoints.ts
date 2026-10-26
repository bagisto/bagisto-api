// rest/endpoints/admin.customers.endpoints.ts
//
// W2 — Admin Customers menu group endpoints. Imported directly by the
// /admin/customers/*.spec.ts files. Not merged into the central ENDPOINTS
// registry so parallel waves don't collide.

export const ADMIN_CUSTOMERS = {
  // ── Customers (CRUD + mass actions) ────────────────────────────
  CUSTOMERS: '/api/admin/customers',
  CUSTOMER: (id: number | string) => `/api/admin/customers/${id}`,
  CUSTOMERS_MASS_DELETE: '/api/admin/customers/mass-delete',
  CUSTOMERS_MASS_UPDATE_STATUS: '/api/admin/customers/mass-update-status',

  // ── Customer addresses (parent-scoped CRUD) ────────────────────
  CUSTOMER_ADDRESSES: (customerId: number | string) =>
    `/api/admin/customers/${customerId}/addresses`,
  CUSTOMER_ADDRESS: (customerId: number | string, id: number | string) =>
    `/api/admin/customers/${customerId}/addresses/${id}`,

  // ── Customer notes (append-only) ───────────────────────────────
  CUSTOMER_NOTES: (customerId: number | string) =>
    `/api/admin/customers/${customerId}/notes`,

  // ── Customer API sessions (view and revoke) ────────────────────
  API_SESSIONS: '/api/admin/customers/api-sessions',
  API_SESSION: (id: number | string) => `/api/admin/customers/api-sessions/${id}`,
  CUSTOMER_API_SESSIONS: (customerId: number | string) =>
    `/api/admin/customers/${customerId}/api-sessions`,
  CUSTOMER_REVOKE_API_SESSIONS: (customerId: number | string) =>
    `/api/admin/customers/${customerId}/revoke-api-sessions`,

  // ── Customer groups ────────────────────────────────────────────
  GROUPS: '/api/admin/customers/groups',
  GROUP: (id: number | string) => `/api/admin/customers/groups/${id}`,
  GROUPS_MASS_DELETE: '/api/admin/customers/groups/mass-delete',

  // ── Customer reviews (moderation-only) ─────────────────────────
  REVIEWS: '/api/admin/customers/reviews',
  REVIEW: (id: number | string) => `/api/admin/customers/reviews/${id}`,
  REVIEWS_MASS_DELETE: '/api/admin/customers/reviews/mass-delete',
  REVIEWS_MASS_UPDATE_STATUS: '/api/admin/customers/reviews/mass-update-status',

  // ── Customer GDPR requests ─────────────────────────────────────
  GDPR_REQUESTS: '/api/admin/customers/gdpr-requests',
  GDPR_REQUEST: (id: number | string) =>
    `/api/admin/customers/gdpr-requests/${id}`,
  GDPR_PROCESS: (id: number | string) =>
    `/api/admin/customers/gdpr-requests/${id}/process`,
  GDPR_DOWNLOAD_DATA: (customerId: number | string) =>
    `/api/admin/customers/${customerId}/gdpr-download-data`,
};
