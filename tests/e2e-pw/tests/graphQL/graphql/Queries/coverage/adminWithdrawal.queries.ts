const ADMIN_WITHDRAWAL_FIELDS = `
  _id
  uuid
  orderId
  orderIncrementId
  customerId
  customerEmail
  isGuest
  reasonText
  status
  receivedAt
  declinedAt
  declinedReason
  declinedByUserId
  refundedAt
  refundedByUserId
  refundNote
  message
`;

export const ADMIN_COVERAGE_EU_WITHDRAWALS = `
  query adminEuWithdrawals($customer_email: String, $status: String, $first: Int) {
    adminEuWithdrawals(customer_email: $customer_email, status: $status, first: $first) {
      totalCount
      edges {
        node {
          ${ADMIN_WITHDRAWAL_FIELDS}
        }
      }
    }
  }
`;

export const ADMIN_COVERAGE_EU_WITHDRAWAL = `
  query adminEuWithdrawal($id: ID!) {
    adminEuWithdrawal(id: $id) {
      ${ADMIN_WITHDRAWAL_FIELDS}
    }
  }
`;

export const ADMIN_COVERAGE_DECLINE_EU_WITHDRAWAL = `
  mutation declineAdminEuWithdrawal($input: declineAdminEuWithdrawalInput!) {
    declineAdminEuWithdrawal(input: $input) {
      adminEuWithdrawal {
        ${ADMIN_WITHDRAWAL_FIELDS}
      }
    }
  }
`;

export const ADMIN_COVERAGE_MARK_REFUNDED_EU_WITHDRAWAL = `
  mutation markRefundedAdminEuWithdrawal($input: markRefundedAdminEuWithdrawalInput!) {
    markRefundedAdminEuWithdrawal(input: $input) {
      adminEuWithdrawal {
        ${ADMIN_WITHDRAWAL_FIELDS}
      }
    }
  }
`;
