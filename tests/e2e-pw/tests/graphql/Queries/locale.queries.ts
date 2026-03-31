// graphql/queries/locale.queries.ts

// Get All Locales
export const GET_LOCALES = `
  query GetLocales {
    locales {
      id
      code
      name
      direction
    }
  }
`;

// Get Single Locale
export const GET_LOCALE = `
  query GetLocale($id: ID!) {
    locale(id: $id) {
      id
      _id
      code
      name
      direction
    }
  }
`;

export const GET_LOCALES_COMPLETE = `
  query GetLocalesComplete($first: Int, $after: String) {
    locales(first: $first, after: $after) {
      edges {
        cursor
        node {
          id
          _id
          code
          name
          direction
          logoPath
          createdAt
          updatedAt
          logoUrl
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

export const GET_LOCALE_COMPLETE = `
  query GetLocaleComplete($id: ID!) {
    locale(id: $id) {
      id
      _id
      code
      name
      direction
      logoPath
      createdAt
      updatedAt
    }
  }
`;
