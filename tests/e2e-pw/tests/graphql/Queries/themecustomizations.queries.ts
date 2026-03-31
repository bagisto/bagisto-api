export const GET_THEME_CUSTOMIZATIONS_BASIC = `
query themeCustomizations($first: Int, $after: String) {
  themeCustomizations(first: $first, after: $after) {
    edges {
      node {
        id
        _id
        type
        name
        status
        themeCode
        sortOrder
        translation {
          locale
          options
        }
      }
      cursor
    }
    pageInfo {
      hasNextPage
      endCursor
    }
    totalCount
  }
}
`;

export const GET_THEME_CUSTOMIZATIONS_FILTERED = `
query themeCustomizations($type: String) {
  themeCustomizations(type: $type) {
    edges {
      node {
        id
        _id
        type
        name
        status
        themeCode
        sortOrder
        translation {
          id
          _id
          themeCustomizationId
          locale
          options
        }
        translations {
          edges {
            node {
              id
              _id
              themeCustomizationId
              locale
              options
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
`;

export const GET_THEME_CUSTOMIZATIONS_COMPLETE = `
query themeCustomizations($first: Int, $after: String, $last: Int, $before: String, $type: String) {
  themeCustomizations(first: $first, after: $after, last: $last, before: $before, type: $type) {
    edges {
      node {
        id
        _id
        themeCode
        type
        name
        sortOrder
        status
        channelId
        createdAt
        updatedAt
        translation {
          id
          _id
          themeCustomizationId
          locale
          options
        }
        translations {
          edges {
            cursor
            node {
              id
              _id
              themeCustomizationId
              locale
              options
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
`;

export const GET_THEME_CUSTOMIZATION_BY_ID = `
query getThemeCustomisation($id: ID!) {
  themeCustomization(id: $id) {
    id
    _id
    type
    name
    status
    themeCode
    translation {
      locale
      options
    }
  }
}
`;

export const GET_THEME_CUSTOMIZATION_BY_NUMERIC_ID = `
query getThemeCustomisation($id: ID!) {
  themeCustomization(id: $id) {
    id
    _id
    type
    name
    status
    themeCode
    sortOrder
    translation {
      locale
      options
    }
  }
}
`;

export const GET_THEME_CUSTOMIZATION_BY_ID_COMPLETE_DETAILS = `
query getThemeCustomisation($id: ID!) {
  themeCustomization(id: $id) {
    id
    _id
    themeCode
    type
    name
    sortOrder
    status
    channelId
    createdAt
    updatedAt
    translation {
      id
      _id
      themeCustomizationId
      locale
      options
    }
    translations {
      edges {
        cursor
        node {
          id
          _id
          themeCustomizationId
          locale
          options
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
}
`;