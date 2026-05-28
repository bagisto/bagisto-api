// Admin Marketing — Sitemaps GraphQL operations.

export const ADMIN_SITEMAPS_QUERY = `
  query adminMarketingSitemaps($first: Int, $file_name: String) {
    adminMarketingSitemaps(first: $first, file_name: $file_name) {
      edges { node { id _id fileName path generatedAt } }
    }
  }
`;

export const ADMIN_SITEMAP_QUERY = `
  query adminMarketingSitemap($id: ID!) {
    adminMarketingSitemap(id: $id) {
      id _id fileName path generatedAt
    }
  }
`;

export const ADMIN_SITEMAP_CREATE_MUTATION = `
  mutation createAdminMarketingSitemap($input: createAdminMarketingSitemapInput!) {
    createAdminMarketingSitemap(input: $input) {
      adminMarketingSitemap { id _id fileName path }
    }
  }
`;

export const ADMIN_SITEMAP_UPDATE_MUTATION = `
  mutation updateAdminMarketingSitemap($input: updateAdminMarketingSitemapInput!) {
    updateAdminMarketingSitemap(input: $input) {
      adminMarketingSitemap { id _id fileName }
    }
  }
`;

export const ADMIN_SITEMAP_DELETE_MUTATION = `
  mutation deleteAdminMarketingSitemap($input: deleteAdminMarketingSitemapInput!) {
    deleteAdminMarketingSitemap(input: $input) {
      adminMarketingSitemap { id }
    }
  }
`;

export const ADMIN_SITEMAP_GENERATE_MUTATION = `
  mutation createAdminMarketingSitemapGenerate($input: createAdminMarketingSitemapGenerateInput!) {
    createAdminMarketingSitemapGenerate(input: $input) {
      adminMarketingSitemapGenerate { id generatedAt }
    }
  }
`;
