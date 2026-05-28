// Admin Marketing — Events GraphQL operations.

export const ADMIN_EVENTS_QUERY = `
  query adminMarketingEvents($first: Int, $name: String) {
    adminMarketingEvents(first: $first, name: $name) {
      edges { node { id _id name date } }
    }
  }
`;

export const ADMIN_EVENT_QUERY = `
  query adminMarketingEvent($id: ID!) {
    adminMarketingEvent(id: $id) {
      id _id name description date
    }
  }
`;

export const ADMIN_EVENT_CREATE_MUTATION = `
  mutation createAdminMarketingEvent($input: createAdminMarketingEventInput!) {
    createAdminMarketingEvent(input: $input) {
      adminMarketingEvent { id _id name }
    }
  }
`;

export const ADMIN_EVENT_UPDATE_MUTATION = `
  mutation updateAdminMarketingEvent($input: updateAdminMarketingEventInput!) {
    updateAdminMarketingEvent(input: $input) {
      adminMarketingEvent { id _id name }
    }
  }
`;

export const ADMIN_EVENT_DELETE_MUTATION = `
  mutation deleteAdminMarketingEvent($input: deleteAdminMarketingEventInput!) {
    deleteAdminMarketingEvent(input: $input) {
      adminMarketingEvent { id }
    }
  }
`;
