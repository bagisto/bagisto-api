// tests/graphQL/graphql/Queries/admin/sales/shipments.queries.ts
// AdminShipment GraphQL schema does NOT expose `orderIncrementId` —
// REST-only field. Reduced to the slim set known to resolve.

export const ADMIN_SHIPMENTS_LIST_QUERY = `
  query adminShipments($first: Int, $after: String) {
    adminShipments(first: $first, after: $after) {
      edges {
        node {
          id
          _id
          orderId
          totalQty
          inventorySourceName
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

export const ADMIN_SHIPMENT_DETAIL_QUERY = `
  query adminShipment($id: ID!) {
    adminShipment(id: $id) {
      id
      _id
      orderId
      totalQty
    }
  }
`;
