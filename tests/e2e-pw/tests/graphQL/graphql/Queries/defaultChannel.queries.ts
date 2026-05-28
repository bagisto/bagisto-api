// graphql/Queries/defaultChannel.queries.ts
//
// The DefaultChannel resource declares `QueryCollection(name: 'collection')`,
// which API Platform's auto-naming convention exposes in the schema as
// `collectionDefaultChannels` (NOT `defaultChannels`).

export const GET_DEFAULT_CHANNEL = `
  query getDefaultChannel {
    collectionDefaultChannels {
      edges {
        node {
          id
          _id
          code
          name
          description
          theme
          hostname
          logoUrl
          faviconUrl
          timezone
          isMaintenanceOn
          rootCategoryId
          defaultLocaleId
          baseCurrencyId
          createdAt
          updatedAt
        }
      }
      totalCount
    }
  }
`;

export const GET_DEFAULT_CHANNEL_MINIMAL = `
  query getDefaultChannelMinimal {
    collectionDefaultChannels {
      edges {
        node {
          id
          code
          name
        }
      }
      totalCount
    }
  }
`;
