// Admin Marketing — Campaigns GraphQL operations.

export const ADMIN_CAMPAIGNS_QUERY = `
  query adminMarketingCampaigns($first: Int, $name: String) {
    adminMarketingCampaigns(first: $first, name: $name) {
      edges { node { id _id name subject status } }
    }
  }
`;

export const ADMIN_CAMPAIGN_QUERY = `
  query adminMarketingCampaign($id: ID!) {
    adminMarketingCampaign(id: $id) {
      id _id name subject status marketingTemplateId channelId customerGroupId
    }
  }
`;

export const ADMIN_CAMPAIGN_CREATE_MUTATION = `
  mutation createAdminMarketingCampaign($input: createAdminMarketingCampaignInput!) {
    createAdminMarketingCampaign(input: $input) {
      adminMarketingCampaign { id _id name }
    }
  }
`;

export const ADMIN_CAMPAIGN_UPDATE_MUTATION = `
  mutation updateAdminMarketingCampaign($input: updateAdminMarketingCampaignInput!) {
    updateAdminMarketingCampaign(input: $input) {
      adminMarketingCampaign { id _id name }
    }
  }
`;

export const ADMIN_CAMPAIGN_DELETE_MUTATION = `
  mutation deleteAdminMarketingCampaign($input: deleteAdminMarketingCampaignInput!) {
    deleteAdminMarketingCampaign(input: $input) {
      adminMarketingCampaign { id }
    }
  }
`;

export const ADMIN_CAMPAIGN_SEND_MUTATION = `
  mutation createAdminMarketingCampaignSend($input: createAdminMarketingCampaignSendInput!) {
    createAdminMarketingCampaignSend(input: $input) {
      adminMarketingCampaignSend { id }
    }
  }
`;

// Template helpers (reused for campaign fixtures)
export const ADMIN_TEMPLATE_CREATE_FOR_CAMPAIGN = `
  mutation createAdminMarketingTemplate($input: createAdminMarketingTemplateInput!) {
    createAdminMarketingTemplate(input: $input) {
      adminMarketingTemplate { id _id }
    }
  }
`;

export const ADMIN_TEMPLATE_DELETE_FOR_CAMPAIGN = `
  mutation deleteAdminMarketingTemplate($input: deleteAdminMarketingTemplateInput!) {
    deleteAdminMarketingTemplate(input: $input) {
      adminMarketingTemplate { id }
    }
  }
`;
