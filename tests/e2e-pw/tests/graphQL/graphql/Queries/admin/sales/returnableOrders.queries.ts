export const ADMIN_RETURNABLE_ORDERS_QUERY = `
  query adminReturnableOrders($first: Int, $after: String, $id: Int, $increment_id: String) {
    adminReturnableOrders(first: $first, after: $after, id: $id, increment_id: $increment_id) {
      totalCount
      edges {
        cursor
        node {
          id
          _id
          incrementId
          status
          customerName
          isGuest
          grandTotal
          formattedGrandTotal
          orderCurrencyCode
          methodTitle
          totalQtyOrdered
          totalRmaQty
          returnableQty
          createdAt
        }
      }
      pageInfo {
        endCursor
        hasNextPage
      }
    }
  }
`;
