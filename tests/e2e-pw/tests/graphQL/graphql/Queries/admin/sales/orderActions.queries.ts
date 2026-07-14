// tests/graphQL/graphql/Queries/admin/sales/orderActions.queries.ts
//
// Per-order action mutations + comments query.
// Quirk (per CLAUDE.md): createAdminReorder input field is `orderId`, NOT `id`
// (`id` is reserved as IRI on mutation inputs in API Platform GraphQL).
//
// Cancel / Invoice / Shipment / Refund / RefundPreview use `orderId` similarly
// — the underlying processor reads from URL in REST but from input arg in
// GraphQL. We pass orderId scalar to avoid IRI-vs-int collisions.

export const ADMIN_REORDER_MUTATION = `
  mutation adminReorder($orderId: Int!) {
    createAdminReorder(input: { orderId: $orderId }) {
      adminReorder {
        success
        message
        cartId
      }
    }
  }
`;

export const ADMIN_CANCEL_ORDER_MUTATION = `
  mutation adminCancelOrder($orderId: Int!) {
    createAdminCancelOrder(input: { orderId: $orderId }) {
      adminCancelOrder {
        id
        _id
        status
      }
    }
  }
`;

export const ADMIN_ORDER_COMMENTS_QUERY = `
  query adminOrderComments($first: Int, $after: String, $orderId: Int!) {
    adminOrderComments(first: $first, after: $after, orderId: $orderId) {
      edges {
        node {
          id
          _id
          comment
          customerNotified
          createdAt
        }
        cursor
      }
      pageInfo {
        endCursor
        hasNextPage
      }
    }
  }
`;

export const ADMIN_ORDER_COMMENT_CREATE_MUTATION = `
  mutation adminOrderCommentCreate($orderId: Int!, $comment: String!, $customerNotified: Boolean) {
    createAdminOrderComment(
      input: { orderId: $orderId, comment: $comment, customerNotified: $customerNotified }
    ) {
      adminOrderComment {
        id
        _id
        comment
        customerNotified
      }
    }
  }
`;

export const ADMIN_INVOICE_CREATE_MUTATION = `
  mutation adminInvoiceCreate($orderId: Int!, $items: Iterable) {
    createAdminInvoice(input: { orderId: $orderId, items: $items }) {
      adminInvoice {
        id
        _id
        state
      }
    }
  }
`;

export const ADMIN_SHIPMENT_CREATE_MUTATION = `
  mutation adminShipmentCreate($orderId: Int!, $source: Int, $items: Iterable) {
    createAdminShipment(input: { orderId: $orderId, source: $source, items: $items }) {
      adminShipment {
        id
        _id
      }
    }
  }
`;

export const ADMIN_REFUND_CREATE_MUTATION = `
  mutation adminRefundCreate(
    $orderId: Int!
    $items: Iterable
    $shipping: Float
    $adjustmentRefund: Float
    $adjustmentFee: Float
  ) {
    createAdminRefund(
      input: {
        orderId: $orderId
        items: $items
        shipping: $shipping
        adjustmentRefund: $adjustmentRefund
        adjustmentFee: $adjustmentFee
      }
    ) {
      adminRefund {
        id
        _id
        state
      }
    }
  }
`;

export const ADMIN_REFUND_PREVIEW_MUTATION = `
  mutation adminRefundPreview(
    $orderId: Int!
    $items: Iterable
    $shipping: Float
    $adjustmentRefund: Float
    $adjustmentFee: Float
  ) {
    previewAdminRefund(
      input: {
        orderId: $orderId
        items: $items
        shipping: $shipping
        adjustmentRefund: $adjustmentRefund
        adjustmentFee: $adjustmentFee
      }
    ) {
      adminRefund {
        grandTotal
        subtotal
        formattedGrandTotal
      }
    }
  }
`;
