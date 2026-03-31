// tests/graphql/Queries/customer.queries.ts

// Get Customer Profile
export const GET_CUSTOMER_PROFILE = `
  query getCustomerProfile {
    readCustomerProfile {
      id
      firstName
      lastName
      email
      dateOfBirth
      gender
      phone
      status
      subscribedToNewsLetter
      isVerified
      image
    }
  }
`;

// Get All Customer Orders
export const GET_CUSTOMER_ORDERS = `
  query GetCustomerOrders($first: Int, $after: String) {
    customerOrders(first: $first, after: $after) {
      edges {
        cursor
        node {
          _id
          incrementId
          status
          channelName
          customerEmail
          customerFirstName
          customerLastName
          shippingMethod
          shippingTitle
          couponCode
          totalItemCount
          totalQtyOrdered
          grandTotal
          baseGrandTotal
          subTotal
          baseSubTotal
          taxAmount
          shippingAmount
          discountAmount
          baseCurrencyCode
          orderCurrencyCode
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

// Get Customer Orders - Filter by Status
export const GET_CUSTOMER_ORDERS_BY_STATUS = `
  query GetPendingOrders($first: Int, $status: String) {
    customerOrders(first: $first, status: $status) {
      edges {
        cursor
        node {
          _id
          incrementId
          status
          grandTotal
          createdAt
        }
      }
      totalCount
    }
  }
`;

// Get Customer Orders - Forward Pagination
export const GET_CUSTOMER_ORDERS_PAGINATED = `
  query GetNextPage($first: Int, $after: String) {
    customerOrders(first: $first, after: $after) {
      edges {
        cursor
        node {
          _id
          incrementId
          status
          grandTotal
          createdAt
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

// Get Single Customer Order
export const GET_CUSTOMER_ORDER_BY_ID = `
  query GetCustomerOrder($id: ID!) {
    customerOrder(id: $id) {
      incrementId
      status
      channelName
      customerEmail
      customerFirstName
      customerLastName
      shippingMethod
      shippingTitle
      couponCode
      totalItemCount
      totalQtyOrdered
      grandTotal
      baseGrandTotal
      grandTotalInvoiced
      grandTotalRefunded
      subTotal
      baseSubTotal
      taxAmount
      baseTaxAmount
      discountAmount
      baseDiscountAmount
      shippingAmount
      baseShippingAmount
      baseCurrencyCode
      channelCurrencyCode
      orderCurrencyCode
      items {
        edges {
          node {
            id
            sku
            name
            qtyOrdered
            qtyShipped
            qtyInvoiced
            qtyCanceled
            qtyRefunded
          }
        }
      }
      addresses {
        edges {
          node {
            id
            _id
            addressType
            parentAddressId
            customerId
            cartId
            orderId
            name
            firstName
            lastName
            companyName
            address
            city
            state
            country
            postcode
            useForShipping
            email
            phone
            gender
            vatId
            defaultAddress
            createdAt
            updatedAt
          }
        }
      }
      createdAt
      updatedAt
    }
  }
`;

// Get Customer Order with Shipments
export const GET_CUSTOMER_ORDER_WITH_SHIPMENTS = `
  query getCustomerOrder($id: ID!) {
    customerOrder(id: $id) {
      _id
      incrementId
      status
      shipments {
        edges {
          node {
            _id
            status
            totalQty
            totalWeight
            carrierCode
            carrierTitle
            trackNumber
            emailSent
            shippingNumber
            createdAt
            items {
              edges {
                node {
                  _id
                  sku
                  name
                  qty
                  weight
                }
              }
            }
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
  }
`;

// Get Customer Order Shipments by Order ID
export const GET_ORDER_SHIPMENTS_BY_ORDER_ID = `
  query getOrderShipments($orderId: ID!) {
    customerOrderShipments(orderId: $orderId) {
      edges {
        node {
          id
          _id
          status
          trackNumber
          carrierTitle
          totalQty
          createdAt
          items {
            edges {
              node {
                id
                name
                sku
                qty
              }
            }
          }
          shippingNumber
        }
      }
      totalCount
    }
  }
`;

// Get Single Customer Order Shipment
export const GET_SINGLE_ORDER_SHIPMENT = `
  query getOrderShipment($id: ID!) {
    customerOrderShipment(id: $id) {
      id
      _id
      status
      trackNumber
      carrierTitle
      totalQty
      createdAt
      items {
        edges {
          node {
            id
            name
            sku
            qty
          }
        }
      }
      shippingNumber
    }
  }
`;
