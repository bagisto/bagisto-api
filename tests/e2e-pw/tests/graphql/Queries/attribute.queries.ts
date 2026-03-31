// graphql/queries/attribute.queries.ts

/**
 * Get All Attributes Query - Basic
 * Fetches attributes with pagination support
 */
export const GET_ALL_ATTRIBUTES = `
  query getAllAttributes($first: Int, $after: String) {
    attributes(first: $first, after: $after) {
      edges {
        node {
          id
          _id
          code
          adminName
          type
          swatchType
          position
          isRequired
          isConfigurable
          options {
            edges {
              node {
                id
                adminName
                swatchValue
              }
            }
            totalCount
          }
        }
        cursor
      }
      pageInfo {
        endCursor
        hasNextPage
      }
      totalCount
    }
  }
`;

/**
 * Get All Attributes Query - Full
 * Fetches attributes with all fields including options with translations and attribute translations
 */
export const GET_ALL_ATTRIBUTES_FULL = `
  query getAllAttributes($first: Int) {
    attributes(first: $first) {
      edges {
        node {
          id
          _id
          code
          adminName
          type
          swatchType
          validation
          regex
          position
          isRequired
          isUnique
          isFilterable
          isComparable
          isConfigurable
          isUserDefined
          isVisibleOnFront
          valuePerLocale
          valuePerChannel
          defaultValue
          enableWysiwyg
          createdAt
          updatedAt
          columnName
          validations
          options {
            edges {
              node {
                id
                _id
                adminName
                sortOrder
                swatchValue
                swatchValueUrl
                translation {
                  id
                  _id
                  attributeOptionId
                  locale
                  label
                }
                translations {
                  edges {
                    node {
                      id
                      _id
                      attributeOptionId
                      locale
                      label
                    }
                  }
                  pageInfo {
                    endCursor
                    hasNextPage
                  }
                  totalCount
                }
              }
              cursor
            }
            pageInfo {
              endCursor
              hasNextPage
            }
            totalCount
          }
          translations {
            edges {
              node {
                id
                _id
                attributeId
                locale
                name
              }
            }
            pageInfo {
              endCursor
              hasNextPage
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

/**
 * Get Attribute By ID Query
 * Fetches a single attribute by its ID with all fields
 */
export const GET_ATTRIBUTE_BY_ID = `
  query getAttributeByID($id: ID!) {
    attribute(id: $id) {
      id
      _id
      code
      adminName
      type
      swatchType
      validation
      regex
      position
      isRequired
      isUnique
      isFilterable
      isComparable
      isConfigurable
      isUserDefined
      isVisibleOnFront
      valuePerLocale
      valuePerChannel
      defaultValue
      enableWysiwyg
      createdAt
      updatedAt
      columnName
      validations
      options {
        edges {
          node {
            id
            _id
            adminName
            sortOrder
            swatchValue
            swatchValueUrl
            translation {
              id
              _id
              attributeOptionId
              locale
              label
            }
            translations {
              edges {
                node {
                  id
                  _id
                  attributeOptionId
                  locale
                  label
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
      translations {
        edges {
          node {
            id
            _id
            attributeId
            locale
            name
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

/**
 * Get Attribute Options Query - Basic
 * Fetches attribute options with basic fields
 */
export const GET_ATTRIBUTE_OPTIONS = `
  query getAttributeOptions($first: Int) {
    attributeOptions(first: $first) {
      edges {
        node {
          id
          _id
          adminName
          sortOrder
          swatchValue
        }
      }
      pageInfo {
        hasNextPage
        endCursor
      }
    }
  }
`;

/**
 * Get Attribute Options with Translations
 * Fetches attribute options with translations
 */
export const GET_ATTRIBUTE_OPTIONS_WITH_TRANSLATIONS = `
  query getAttributeOptionsWithTranslations($first: Int) {
    attributeOptions(first: $first) {
      edges {
        node {
          id
          adminName
          sortOrder
          translations(first: 10) {
            edges {
              node {
                locale
                label
              }
            }
          }
        }
      }
    }
  }
`;

/**
 * Get Attribute Options with Swatches
 * Fetches attribute options with swatch values
 */
export const GET_SWATCH_OPTIONS = `
  query getSwatchOptions($first: Int) {
    attributeOptions(first: $first) {
      edges {
        node {
          id
          adminName
          swatchValue
          swatchValueUrl
          translation {
            locale
            label
          }
        }
      }
    }
  }
`;

/**
 * Get Single Attribute Option by ID
 * Fetches a single attribute option by its ID
 */
export const GET_ATTRIBUTE_OPTION_BY_ID = `
  query getAttributeOptionByID($id: ID!) {
    attributeOption(id: $id) {
      id
      _id
      adminName
      sortOrder
      swatchValue
      swatchValueUrl
      translation {
        id
        _id
        attributeOptionId
        locale
        label
      }
      translations {
        edges {
          node {
            id
            _id
            attributeOptionId
            locale
            label
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

/**
 * Get Attribute Options Paginated
 * Fetches attribute options with full pagination
 */
export const GET_ATTRIBUTE_OPTIONS_PAGINATED = `
  query getAttributeOptionsPaginated($first: Int, $after: String) {
    attributeOptions(first: $first, after: $after) {
      edges {
        node {
          id
          adminName
          sortOrder
        }
        cursor
      }
      pageInfo {
        hasNextPage
        endCursor
        hasPreviousPage
        startCursor
      }
    }
  }
`;

/**
 * Get Attribute with Options
 * Fetches an attribute with its options
 */
export const GET_ATTRIBUTE_WITH_OPTIONS = `
  query getAttribute($id: ID!, $first: Int) {
    attribute(id: $id) {
      id
      code
      adminName
      options(first: $first) {
        edges {
          node {
            id
            adminName
            sortOrder
            swatchValue
            translation {
              locale
              label
            }
          }
          cursor
        }
        pageInfo {
          hasNextPage
          endCursor
        }
      }
    }
  }
`;

/**
 * Get Color Options for Display
 * Fetches color attribute options for display
 */
export const GET_COLOR_OPTIONS = `
  query getColorOptions {
    attributeOptions(first: 50) {
      edges {
        node {
          adminName
          swatchValue
          translation {
            label
          }
        }
      }
    }
  }
`;
