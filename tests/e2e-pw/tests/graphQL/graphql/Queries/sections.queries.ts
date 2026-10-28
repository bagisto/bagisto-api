export const GET_SECTIONS_BASIC = `
query sections($first: Int, $after: String) {
  sections(first: $first, after: $after) {
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

export const GET_SECTIONS_FILTERED = `
query sections($type: String) {
  sections(type: $type) {
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
          sectionId
          locale
          options
        }
        translations {
          edges {
            node {
              id
              _id
              sectionId
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

export const GET_SECTIONS_COMPLETE = `
query sections($first: Int, $after: String, $last: Int, $before: String, $type: String) {
  sections(first: $first, after: $after, last: $last, before: $before, type: $type) {
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
          sectionId
          locale
          options
        }
        translations {
          edges {
            cursor
            node {
              id
              _id
              sectionId
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

export const GET_SECTION_BY_ID = `
query getThemeCustomisation($id: ID!) {
  section(id: $id) {
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

export const GET_SECTION_BY_NUMERIC_ID = `
query getThemeCustomisation($id: ID!) {
  section(id: $id) {
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

export const GET_SECTION_BY_ID_COMPLETE_DETAILS = `
query getThemeCustomisation($id: ID!) {
  section(id: $id) {
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
      sectionId
      locale
      options
    }
    translations {
      edges {
        cursor
        node {
          id
          _id
          sectionId
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