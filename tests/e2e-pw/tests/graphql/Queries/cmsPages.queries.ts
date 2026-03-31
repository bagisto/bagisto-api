// graphql/queries/cmsPages.queries.ts

// Get All CMS Pages
export const GET_ALL_PAGES = `
  query GetAllPages($first: Int, $after: String) {
    cmsPages(first: $first, after: $after) {
      edges {
        cursor
        node {
          id
          _id
          urlKey
          title
          heading
          channel
          locale
          htmlContent
          metaTitle
          metaDescription
          metaKeywords
          status
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

// Get Single CMS Page
export const GET_CMS_PAGE = `
  query GetCmsPage($id: ID) {
    cmsPage(id: $id) {
      id
      _id
      urlKey
      title
      heading
      channel
      locale
      htmlContent
      metaTitle
      metaDescription
      metaKeywords
      status
      createdAt
      updatedAt
    }
  }
`;

// Get CMS Page by URL Key
export const GET_CMS_PAGE_BY_URLKEY = `
  query GetCmsPageByUrlKey($urlKey: String!) {
    cmsPage(urlKey: $urlKey) {
      id
      _id
      urlKey
      title
      heading
      channel
      locale
      htmlContent
      metaTitle
      metaDescription
      metaKeywords
      status
    }
  }
`;
