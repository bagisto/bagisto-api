// Admin Marketing — Email Templates GraphQL operations.

export const ADMIN_TEMPLATES_QUERY = `
  query adminMarketingTemplates($first: Int, $name: String, $status: String) {
    adminMarketingTemplates(first: $first, name: $name, status: $status) {
      edges { node { id _id name status } }
    }
  }
`;

export const ADMIN_TEMPLATE_QUERY = `
  query adminMarketingTemplate($id: ID!) {
    adminMarketingTemplate(id: $id) {
      id _id name status content
    }
  }
`;

export const ADMIN_TEMPLATE_CREATE_MUTATION = `
  mutation createAdminMarketingTemplate($input: createAdminMarketingTemplateInput!) {
    createAdminMarketingTemplate(input: $input) {
      adminMarketingTemplate { id _id name status }
    }
  }
`;

export const ADMIN_TEMPLATE_UPDATE_MUTATION = `
  mutation updateAdminMarketingTemplate($input: updateAdminMarketingTemplateInput!) {
    updateAdminMarketingTemplate(input: $input) {
      adminMarketingTemplate { id _id name }
    }
  }
`;

export const ADMIN_TEMPLATE_DELETE_MUTATION = `
  mutation deleteAdminMarketingTemplate($input: deleteAdminMarketingTemplateInput!) {
    deleteAdminMarketingTemplate(input: $input) {
      adminMarketingTemplate { id }
    }
  }
`;
