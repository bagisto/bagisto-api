export const ADMIN_COVERAGE_INVOICE_MASS_UPDATE_STATUS = `
  mutation createAdminInvoiceMassUpdateStatus($input: createAdminInvoiceMassUpdateStatusInput!) {
    createAdminInvoiceMassUpdateStatus(input: $input) {
      adminInvoiceMassUpdateStatus {
        updated
        message
      }
    }
  }
`;

export const ADMIN_COVERAGE_INVOICE_SEND_DUPLICATE = `
  mutation createAdminInvoiceSendDuplicate($input: createAdminInvoiceSendDuplicateInput!) {
    createAdminInvoiceSendDuplicate(input: $input) {
      adminInvoiceSendDuplicate {
        _id
        email
        success
        message
      }
    }
  }
`;

export const ADMIN_COVERAGE_CREATE_TRANSACTION = `
  mutation createAdminTransaction($input: createAdminTransactionInput!) {
    createAdminTransaction(input: $input) {
      adminTransaction {
        _id
        transactionId
        invoiceId
        orderId
        orderIncrementId
        amount
        formattedAmount
        status
        type
        paymentMethod
      }
    }
  }
`;

export const ADMIN_COVERAGE_REPORTING_CUSTOMERS_VIEW = `
  query viewStatsAdminReportingCustomers($type: String, $start: String, $end: String, $channel: String) {
    viewStatsAdminReportingCustomers(type: $type, start: $start, end: $end, channel: $channel) {
      entity
      type
      dateRange
      statistics
    }
  }
`;
