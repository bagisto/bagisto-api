export const GET_CUSTOMER_INVOICES = `
  query GetCustomerInvoices($first: Int, $after: String, $last: Int, $before: String, $orderId: Int, $state: String) {
    customerInvoices(first: $first, after: $after, last: $last, before: $before, orderId: $orderId, state: $state) {
      edges {
        cursor
        node {
          _id
          incrementId
          state
          totalQty
          grandTotal
          baseGrandTotal
          subTotal
          baseSubTotal
          shippingAmount
          baseShippingAmount
          taxAmount
          baseTaxAmount
          discountAmount
          baseDiscountAmount
          baseCurrencyCode
          orderCurrencyCode
          downloadUrl
          createdAt
          updatedAt
        }
      }
      pageInfo {
        endCursor
        startCursor
        hasNextPage
        hasPreviousPage
      }
      totalCount
    }
  }
`;

export const GET_CUSTOMER_INVOICES_BY_ORDER = `
  query GetInvoicesByOrder($orderId: Int, $first: Int) {
    customerInvoices(first: $first, orderId: $orderId) {
      edges {
        node {
          _id
          incrementId
          state
          grandTotal
          downloadUrl
          createdAt
        }
      }
      totalCount
    }
  }
`;

export const GET_CUSTOMER_INVOICES_BY_STATE = `
  query GetInvoicesByState($state: String, $first: Int) {
    customerInvoices(first: $first, state: $state) {
      edges {
        node {
          _id
          incrementId
          state
          grandTotal
          createdAt
        }
      }
      totalCount
    }
  }
`;

export const GET_CUSTOMER_INVOICES_BY_ORDER_WITH_ITEMS = `
  query GetCustomerInvoiceByOrderWithItems($first: Int, $orderId: Int) {
    customerInvoices(first: $first, orderId: $orderId) {
      edges {
        node {
          orderCurrencyCode
          grandTotal
          downloadUrl
          items {
            edges {
              node {
                id
                _id
                sku
                parentId
                name
                price
                qty
                total
                basePrice
                description
                baseTotal
                taxAmount
                baseTaxAmount
                discountPercent
                discountAmount
                baseDiscountAmount
                priceInclTax
                basePriceInclTax
                totalInclTax
                baseTotalInclTax
                productId
                productType
                orderItemId
                invoiceId
                createdAt
                updatedAt
              }
            }
          }
        }
      }
    }
  }
`;

export const GET_CUSTOMER_INVOICES_PAGINATED = `
  query GetCustomerInvoicesNextPage($first: Int, $after: String) {
    customerInvoices(first: $first, after: $after) {
      edges {
        cursor
        node {
          _id
          incrementId
          state
          grandTotal
          downloadUrl
        }
      }
      pageInfo {
        endCursor
        hasNextPage
      }
      totalCount
    }
  }
`;

export const GET_CUSTOMER_INVOICE = `
  query GetCustomerInvoice($id: ID!) {
    customerInvoice(id: $id) {
      _id
      incrementId
      state
      totalQty
      grandTotal
      baseGrandTotal
      subTotal
      baseSubTotal
      shippingAmount
      baseShippingAmount
      taxAmount
      baseTaxAmount
      discountAmount
      baseDiscountAmount
      shippingTaxAmount
      subTotalInclTax
      shippingAmountInclTax
      baseCurrencyCode
      channelCurrencyCode
      orderCurrencyCode
      transactionId
      emailSent
      reminders
      createdAt
      updatedAt
      downloadUrl
      items {
        edges {
          node {
            id
            sku
            name
            qty
            price
            total
          }
        }
      }
    }
  }
`;

export const GET_CUSTOMER_INVOICE_DOWNLOAD_URL = `
  query GetInvoiceDownloadUrl($id: ID!) {
    customerInvoice(id: $id) {
      _id
      incrementId
      downloadUrl
    }
  }
`;
