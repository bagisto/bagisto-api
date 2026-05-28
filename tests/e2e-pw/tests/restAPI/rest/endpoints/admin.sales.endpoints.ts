// rest/endpoints/admin.sales.endpoints.ts
//
// W3 — Admin Sales endpoint registry. Self-contained (not added to the central
// ENDPOINTS const) so this file can land without conflicting with parallel
// admin waves touching endpoints.ts.

export const ADMIN_SALES = {
  // ── Orders ──────────────────────────────────────────────────
  ORDERS: '/api/admin/orders',
  ORDER: (id: number | string) => `/api/admin/orders/${id}`,

  // ── Per-order actions ───────────────────────────────────────
  ORDER_CANCEL: (id: number | string) => `/api/admin/orders/${id}/cancel`,
  ORDER_REORDER: (id: number | string) => `/api/admin/orders/${id}/reorder`,
  ORDER_COMMENTS: (id: number | string) => `/api/admin/orders/${id}/comments`,
  ORDER_INVOICES: (id: number | string) => `/api/admin/orders/${id}/invoices`,
  ORDER_SHIPMENTS: (id: number | string) => `/api/admin/orders/${id}/shipments`,
  ORDER_REFUNDS: (id: number | string) => `/api/admin/orders/${id}/refunds`,
  ORDER_REFUND_PREVIEW: (id: number | string) =>
    `/api/admin/orders/${id}/refunds/preview`,

  // ── Standalone listings + details ───────────────────────────
  INVOICES: '/api/admin/invoices',
  INVOICE: (id: number | string) => `/api/admin/invoices/${id}`,
  INVOICE_PRINT: (id: number | string) => `/api/admin/invoices/${id}/print`,

  SHIPMENTS: '/api/admin/shipments',
  SHIPMENT: (id: number | string) => `/api/admin/shipments/${id}`,

  REFUNDS: '/api/admin/refunds',
  REFUND: (id: number | string) => `/api/admin/refunds/${id}`,

  TRANSACTIONS: '/api/admin/transactions',
  TRANSACTION: (id: number | string) => `/api/admin/transactions/${id}`,

  BOOKINGS: '/api/admin/bookings',
  BOOKING: (id: number | string) => `/api/admin/bookings/${id}`,
};
