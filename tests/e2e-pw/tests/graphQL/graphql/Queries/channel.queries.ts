// graphql/queries/channel.queries.ts

// Get All Channels
export const GET_ALL_CHANNELS = `
  query GetAllChannels {
    channels {
      id
      code
      name
      description
      timezone
      defaultLocale
      baseCurrency
      rootCategoryId
      logoUrl
      faviconUrl
      bannerUrl
      locales {
        id
        code
        name
        direction
      }
      currencies {
        id
        code
        name
        symbol
      }
    }
  }
`;

// Get Single Channel
export const GET_CHANNEL = `
  query GetChannel($id: ID) {
    channel(id: $id) {
      id
      code
      name
      description
      timezone
      defaultLocale
      baseCurrency
      rootCategoryId
      logoUrl
      faviconUrl
      bannerUrl
      locales {
        id
        code
        name
        direction
      }
      currencies {
        id
        code
        name
        symbol
      }
    }
  }
`;

export const GET_ALL_CHANNELS_COMPLETE = `
  query GetAllChannelsComplete($first: Int, $after: String) {
    channels(first: $first, after: $after) {
      edges {
        cursor
        node {
          id
          _id
          code
          timezone
          theme
          hostname
          logo
          favicon
          isMaintenanceOn
          allowedIps
          createdAt
          updatedAt
          logoUrl
          faviconUrl
          translation {
            id
            _id
            channelId
            locale
            name
            description
            maintenanceModeText
          }
          translations {
            edges {
              node {
                id
                _id
                channelId
                locale
                name
                description
                maintenanceModeText
              }
              cursor
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

export const GET_CHANNEL_COMPLETE = `
  query GetChannelComplete($id: ID!) {
    channel(id: $id) {
      id
      _id
      code
      timezone
      theme
      hostname
      logo
      favicon
      isMaintenanceOn
      allowedIps
      createdAt
      updatedAt
      logoUrl
      faviconUrl
      translation {
        id
        _id
        channelId
        locale
        name
        description
        maintenanceModeText
      }
      translations {
        edges {
          node {
            id
            _id
            channelId
            locale
            name
            description
            maintenanceModeText
          }
          cursor
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
  }
`;
