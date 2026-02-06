export const GET_ALL_LOCALES = `
  query GetAllLocales {
    locales {
      edges {
        node {
          id
          code
          name
          direction
        }
      }
    }
  }
`;
