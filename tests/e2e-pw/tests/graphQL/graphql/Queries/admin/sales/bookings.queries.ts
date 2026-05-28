// tests/graphQL/graphql/Queries/admin/sales/bookings.queries.ts

export const ADMIN_BOOKINGS_LIST_QUERY = `
  query adminBookings($first: Int, $after: String) {
    adminBookings(first: $first, after: $after) {
      edges {
        node {
          id
          _id
          orderId
          productId
          productSku
          qty
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

export const ADMIN_BOOKING_DETAIL_QUERY = `
  query adminBooking($id: ID!) {
    adminBooking(id: $id) {
      id
      _id
      orderId
      productId
      qty
    }
  }
`;
