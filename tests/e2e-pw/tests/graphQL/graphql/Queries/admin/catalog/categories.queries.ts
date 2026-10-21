export const ADMIN_CATEGORIES_LIST_QUERY = `
  query adminCategories($first: Int, $after: String) {
    adminCategories(first: $first, after: $after) {
      edges {
        node {
          id
          _id
          name
          position
          status
          parentId
          displayMode
        }
      }
      pageInfo {
        endCursor
        hasNextPage
      }
    }
  }
`;

export const ADMIN_CATEGORY_DETAIL_QUERY = `
  query adminCategory($id: ID!) {
    adminCategory(id: $id) {
      id
      _id
      name
      position
      status
      parentId
      displayMode
      description
    }
  }
`;

export const ADMIN_CATEGORY_TREES_QUERY = `
  query adminCategoryTrees($first: Int) {
    adminCategoryTrees(first: $first) {
      edges {
        node {
          id
          _id
          name
        }
      }
    }
  }
`;

export const ADMIN_CATEGORY_CREATE_MUTATION = `
  mutation createAdminCategory(
    $slug: String
    $name: String
    $description: String
    $position: Int
    $attributes: Iterable
    $parentId: Int
    $displayMode: String
    $locale: String
    $status: Int
  ) {
    createAdminCategory(
      input: {
        slug: $slug
        name: $name
        description: $description
        position: $position
        attributes: $attributes
        parentId: $parentId
        displayMode: $displayMode
        locale: $locale
        status: $status
      }
    ) {
      adminCategory {
        id
        _id
        name
        position
        status
      }
    }
  }
`;

export const ADMIN_CATEGORY_UPDATE_MUTATION = `
  mutation updateAdminCategory(
    $id: ID!
    $position: Int
    $status: Int
    $en: Iterable
    $locale: String
    $attributes: Iterable
    $parentId: Int
  ) {
    updateAdminCategory(
      input: {
        id: $id
        position: $position
        status: $status
        en: $en
        locale: $locale
        attributes: $attributes
        parentId: $parentId
      }
    ) {
      adminCategory {
        id
        _id
        name
        position
        status
      }
    }
  }
`;

export const ADMIN_CATEGORY_DELETE_MUTATION = `
  mutation deleteAdminCategory($id: ID!) {
    deleteAdminCategory(input: { id: $id }) {
      adminCategory {
        id
        _id
      }
    }
  }
`;

export const ADMIN_CATEGORY_MASS_DELETE_MUTATION = `
  mutation createAdminCategoryMassDelete($indices: Iterable!) {
    createAdminCategoryMassDelete(input: { indices: $indices }) {
      adminCategoryMassDelete {
        id
        _id
      }
    }
  }
`;

export const ADMIN_CATEGORY_MASS_UPDATE_STATUS_MUTATION = `
  mutation createAdminCategoryMassUpdateStatus($indices: Iterable!, $value: Int!) {
    createAdminCategoryMassUpdateStatus(input: { indices: $indices, value: $value }) {
      adminCategoryMassUpdateStatus {
        id
        _id
      }
    }
  }
`;
