
export const ADMIN_SALES = {
  ORDERS: '/api/admin/orders',
  ORDER: (id: number | string) => `/api/admin/orders/${id}`,
  ORDER_CANCEL: (id: number | string) => `/api/admin/orders/${id}/cancel`,
  ORDER_REORDER: (id: number | string) => `/api/admin/orders/${id}/reorder`,
  ORDER_COMMENTS: (id: number | string) => `/api/admin/orders/${id}/comments`,
  ORDER_INVOICES: (id: number | string) => `/api/admin/orders/${id}/invoices`,
  ORDER_SHIPMENTS: (id: number | string) => `/api/admin/orders/${id}/shipments`,
  ORDER_REFUNDS: (id: number | string) => `/api/admin/orders/${id}/refunds`,
  ORDER_REFUND_PREVIEW: (id: number | string) =>
    `/api/admin/orders/${id}/refunds/preview`,
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

  RMA_REQUESTS: '/api/admin/rma/requests',
  RMA_REQUEST: (id: number | string) => `/api/admin/rma/requests/${id}`,
  RMA_RETURNABLE_ORDERS: '/api/admin/rma/requests/orders',
  RMA_REQUEST_IMAGE: (id: number | string) => `/api/admin/rma/requests/images/${id}`,
  RMA_MESSAGES: '/api/admin/rma/messages',
  RMA_MESSAGE_ATTACHMENT: (id: number | string) => `/api/admin/rma/messages/${id}/attachment`,
};
