export const SHOP_CMS_QUERIES = {
  pageByUrlKey: `
    query pageByUrlKeypages($urlKey: String!) {
      pageByUrlKeypages(urlKey: $urlKey) {
        id
        _id
        layout
        createdAt
        updatedAt
        translation {
          _id
          pageTitle
          urlKey
          htmlContent
          metaTitle
          metaKeywords
          metaDescription
          locale
          cmsPageId
        }
      }
    }
  `,

  pageByUrlKeyWithoutArgument: `
    query pageByUrlKeypages {
      pageByUrlKeypages {
        _id
      }
    }
  `,

  pageTranslations: `
    query pageTranslations($first: Int, $after: String) {
      pageTranslations(first: $first, after: $after) {
        totalCount
        pageInfo {
          hasNextPage
          endCursor
        }
        edges {
          cursor
          node {
            id
            _id
            cmsPageId
            pageTitle
            urlKey
            htmlContent
            metaTitle
            metaKeywords
            metaDescription
            locale
          }
        }
      }
    }
  `,

  pageTranslation: `
    query pageTranslation($id: ID!) {
      pageTranslation(id: $id) {
        id
        _id
        cmsPageId
        pageTitle
        urlKey
        htmlContent
        metaTitle
        metaKeywords
        metaDescription
        locale
      }
    }
  `,
};
