// Admin CMS Pages GraphQL operations.

export const ADMIN_CMS_PAGES_LIST = `
  query adminCmsPages($first: Int, $after: String, $page_title: String) {
    adminCmsPages(first: $first, after: $after, page_title: $page_title) {
      edges { node { id _id urlKey pageTitle } }
    }
  }
`;

export const ADMIN_CMS_PAGE_DETAIL = `
  query adminCmsPage($id: ID!) {
    adminCmsPage(id: $id) { id _id urlKey pageTitle }
  }
`;

export const ADMIN_CMS_PAGE_CREATE = `
  mutation createAdminCmsPage(
    $urlKey: String!, $pageTitle: String!, $htmlContent: String!, $channels: Iterable!
  ) {
    createAdminCmsPage(input: {
      urlKey: $urlKey, pageTitle: $pageTitle, htmlContent: $htmlContent, channels: $channels
    }) {
      adminCmsPage { id _id urlKey pageTitle }
    }
  }
`;

export const ADMIN_CMS_PAGE_UPDATE = `
  mutation updateAdminCmsPage($id: ID!, $en: Iterable, $channels: Iterable, $locale: String) {
    updateAdminCmsPage(input: { id: $id, en: $en, channels: $channels, locale: $locale }) {
      adminCmsPage { id _id }
    }
  }
`;

export const ADMIN_CMS_PAGE_DELETE = `
  mutation deleteAdminCmsPage($id: ID!) {
    deleteAdminCmsPage(input: { id: $id }) { adminCmsPage { id } }
  }
`;

export const ADMIN_CMS_PAGE_MASS_DELETE = `
  mutation createAdminCmsPageMassDelete($indices: Iterable!) {
    createAdminCmsPageMassDelete(input: { indices: $indices }) {
      adminCmsPageMassDelete { id }
    }
  }
`;
