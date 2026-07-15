// Admin Marketing — Newsletter Subscribers GraphQL operations.
// No create — subscriptions originate from the storefront.

export const ADMIN_SUBSCRIBERS_QUERY = `
  query adminMarketingSubscribers($first: Int, $email: String, $is_subscribed: Int) {
    adminMarketingSubscribers(first: $first, email: $email, is_subscribed: $is_subscribed) {
      edges { node { id _id email isSubscribed channel { _id code name } customerId customerName } }
    }
  }
`;

export const ADMIN_SUBSCRIBER_QUERY = `
  query adminMarketingSubscriber($id: ID!) {
    adminMarketingSubscriber(id: $id) {
      id _id email isSubscribed
    }
  }
`;

export const ADMIN_SUBSCRIBER_UPDATE_MUTATION = `
  mutation updateAdminMarketingSubscriber($input: updateAdminMarketingSubscriberInput!) {
    updateAdminMarketingSubscriber(input: $input) {
      adminMarketingSubscriber { id _id isSubscribed }
    }
  }
`;

export const ADMIN_SUBSCRIBER_DELETE_MUTATION = `
  mutation deleteAdminMarketingSubscriber($input: deleteAdminMarketingSubscriberInput!) {
    deleteAdminMarketingSubscriber(input: $input) {
      adminMarketingSubscriber { id }
    }
  }
`;
