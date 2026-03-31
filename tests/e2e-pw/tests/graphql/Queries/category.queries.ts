// Category Queries
export const GET_ALL_CATEGORY = `
    query treeCategories {
  treeCategories(parentId: 0) {
    id
    _id
    position
    status
    translation {
      name
      slug
      urlPath
    }
    children(first: 100) {
      edges {
        node {
          id
          _id
          position
          status
          translation {
            name
            slug
            urlPath
          }
          children(first: 100) {
            edges {
              node {
                id
                _id
                position
                status
                translation {
                  name
                  slug
                  urlPath
                }
              }
            }
          }
        }
      }
    }
  }
}`;

export const GET_TREE_CATEGORY_BASIC = `
  query treeCategories($parentId: Int!) {
    treeCategories(parentId: $parentId) {
      id
      _id
      position
      status
      translation {
        name
        slug
        urlPath
      }
      children(first: 100) {
        edges {
          node {
            id
            _id
            position
            status
            translation {
              name
              slug
              urlPath
            }
          }
        }
      }
    }
  }
`;

export const GET_TREE_CATEGORY_COMPLETE = `
  query treeCategories {
    treeCategories(parentId: 1) {
    id
    _id
    position
    status
    logoPath
    displayMode
    _lft
    _rgt
    additional
    bannerPath
    createdAt
    updatedAt
    url
    logoUrl
    bannerUrl
    translation {
      name
      slug
      urlPath
    }
    children(first: 100) {
      edges {
        node {
          id
          _id
          position
          status
          translation {
            name
            slug
            urlPath
          }
        }
      }
    }
    translations(first: 1) {
      edges {
        node {
          id
          _id
          categoryId
          name
          slug
          urlPath
          description
          metaTitle
          metaDescription
          metaKeywords
          localeId
          locale
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

export const GET_TREE_CATEGORY_BY_PARENT_FILTER = `
  query treeCategories($parentId: Int!) {
    treeCategories(parentId: $parentId) {
      id
      _id
      position
      status
      logoPath
      displayMode
      translation {
        name
        slug
        urlPath
      }
      children(first: 50) {
        edges {
          node {
            id
            _id
            position
            status
            translation {
              name
              slug
              urlPath
            }
          }
        }
      }
    }
  }
`;

export const GET_CATEGORIES_BASIC = `
query getCategories($first: Int, $after: String) {
  categories(first: $first, after: $after) {
    edges {
      node {
        id
        _id
        position
        status
        translation {
          name
          slug
          urlPath
        }
      }
    }
    pageInfo {
      hasNextPage
      endCursor
    }
  }
}
`;

export const GET_CATEGORIES_COMPLETE = `
query getCategories($first: Int, $after: String) {
  categories(first: $first, after: $after) {
    edges {
      node {
        id
        _id
        position
        status
        logoPath
        displayMode
        _lft
        _rgt
        additional
        bannerPath
        createdAt
        updatedAt
        url
        logoUrl
        bannerUrl
        translation {
          name
          slug
          urlPath
        }
        children(first: 100) {
          edges {
            node {
              id
              _id
              position
              status
              translation {
                name
                slug
                urlPath
              }
            }
          }
        }
        translations(first: 1) {
          edges {
            node {
              id
              _id
              categoryId
              name
              slug
              urlPath
              description
              metaTitle
              metaDescription
              metaKeywords
              localeId
              locale
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

export const GET_CATEGORIES_CURSOR = `
query getCategories($first: Int, $after: String, $last: Int, $before: String) {
  categories(first: $first, after: $after, last: $last, before: $before) {
    edges {
      node {
        id
        _id
        position
        translation {
          name
          slug
        }
        status
        children {
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

export const GET_CATEGORIES_WITH_CHILDREN = `
query getCategories($first: Int) {
  categories(first: $first) {
    edges {
      node {
        id
        _id
        position
        translation {
          name
          slug
        }
        children(first: 50) {
          edges {
            node {
              id
              _id
              position
              translation {
                name
                slug
              }
            }
          }
          totalCount
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

export const GET_CATEGORY_BY_ID = `
query getCategoryByID($id: ID!) {
  category(id: $id) {
    id
    _id
    position
    status
    translation {
      name
      slug
      urlPath
      description
    }
  }
}
`;

export const GET_CATEGORY_BY_ID_COMPLETE = `
query getCategoryByID($id: ID!) {
  category(id: $id) {
    id
    _id
    position
    logoPath
    logoUrl
    status
    displayMode
    _lft
    _rgt
    additional
    bannerPath
    bannerUrl
    translation {
      id
      _id
      categoryId
      name
      slug
      urlPath
      description
      metaTitle
      metaDescription
      metaKeywords
      localeId
      locale
    }
    translations {
      edges {
        node {
          id
          _id
          categoryId
          name
          slug
          urlPath
          description
          metaTitle
          metaDescription
          metaKeywords
          localeId
          locale
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
    createdAt
    updatedAt
    url
    children {
      edges {
        node {
          id
          _id
          position
          logoUrl
          status
          translation {
            name
            slug
          }
        }
      }
      pageInfo {
        hasNextPage
        endCursor
        startCursor
        hasPreviousPage
      }
      totalCount
    }
  }
}
`;

export const GET_CATEGORY_BY_ID_WITH_CHILDREN = `
query getCategoryWithChildren($id: ID!) {
  category(id: $id) {
    id
    _id
    position
    status
    translation {
      name
      slug
      urlPath
      description
      metaTitle
      metaDescription
      metaKeywords
    }
    children {
      edges {
        node {
          id
          _id
          position
          logoUrl
          status
          translation {
            name
            slug
          }
        }
      }
      pageInfo {
        hasNextPage
        endCursor
        startCursor
        hasPreviousPage
      }
      totalCount
    }
  }
}
`;
