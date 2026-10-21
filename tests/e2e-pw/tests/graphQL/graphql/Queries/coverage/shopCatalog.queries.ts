export const SHOP_CATALOG_QUERIES = {
  attributeTranslations: `
    query attributeTranslations($first: Int, $after: String) {
      attributeTranslations(first: $first, after: $after) {
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
            attributeId
            locale
            name
          }
        }
      }
    }
  `,

  attributeTranslation: `
    query attributeTranslation($id: ID!) {
      attributeTranslation(id: $id) {
        id
        _id
        attributeId
        locale
        name
      }
    }
  `,

  categoryAttributeFilters: `
    query categoryAttributeFilters($categorySlug: String, $first: Int) {
      categoryAttributeFilters(categorySlug: $categorySlug, first: $first) {
        totalCount
        edges {
          node {
            id
            _id
            code
            type
            adminName
            isFilterable
            maxPrice
            minPrice
          }
        }
      }
    }
  `,

  categoryAttributeFiltersWithIntegerSlug: `
    query categoryAttributeFilters {
      categoryAttributeFilters(categorySlug: 123) {
        totalCount
      }
    }
  `,

  categoryTrees: `
    query categoryTrees {
      categoryTrees {
        id
      }
    }
  `,

  categoryTreesWithUnknownField: `
    query categoryTrees {
      categoryTrees {
        id
        notAField
      }
    }
  `,

  theme: `
    query theme {
      theme {
        code
        name
        sectionTypes
      }
    }
  `,

  themeWithArgument: `
    query theme {
      theme(id: "default") {
        code
      }
    }
  `,

  productAttributeFile: `
    query productAttributeFile($productId: Int!, $attributeId: Int!) {
      productAttributeFile(productId: $productId, attributeId: $attributeId) {
        productId
        attributeId
        fileName
        mimeType
        size
        content
      }
    }
  `,

  productBundleOptions: `
    query productBundleOptions($first: Int, $after: String) {
      productBundleOptions(first: $first, after: $after) {
        totalCount
        pageInfo {
          hasNextPage
          endCursor
        }
        edges {
          node {
            id
            _id
            type
            isRequired
            sortOrder
          }
        }
      }
    }
  `,

  productBundleOption: `
    query productBundleOption($id: ID!) {
      productBundleOption(id: $id) {
        id
        _id
        type
        isRequired
        sortOrder
        bundleOptionProducts {
          edges {
            node {
              _id
              qty
              isDefault
              sortOrder
            }
          }
        }
      }
    }
  `,

  productBundleOptionProducts: `
    query productBundleOptionProducts($first: Int, $after: String) {
      productBundleOptionProducts(first: $first, after: $after) {
        totalCount
        pageInfo {
          hasNextPage
          endCursor
        }
        edges {
          node {
            id
            _id
            qty
            isDefault
            sortOrder
          }
        }
      }
    }
  `,

  productBundleOptionProduct: `
    query productBundleOptionProduct($id: ID!) {
      productBundleOptionProduct(id: $id) {
        id
        _id
        qty
        isDefault
        isUserDefined
        sortOrder
      }
    }
  `,

  productGroupedProducts: `
    query productGroupedProducts($first: Int, $after: String) {
      productGroupedProducts(first: $first, after: $after) {
        totalCount
        pageInfo {
          hasNextPage
          endCursor
        }
        edges {
          node {
            id
            _id
            qty
            sortOrder
          }
        }
      }
    }
  `,

  productGroupedProduct: `
    query productGroupedProduct($id: ID!) {
      productGroupedProduct(id: $id) {
        id
        _id
        qty
        sortOrder
      }
    }
  `,

  createCustomizableOptionFile: `
    mutation createCustomizableOptionFile($input: createCustomizableOptionFileInput!) {
      createCustomizableOptionFile(input: $input) {
        customizableOptionFile {
          token
          fileName
          optionId
        }
      }
    }
  `,

  createImageSearch: `
    mutation createImageSearch($input: createImageSearchInput!) {
      createImageSearch(input: $input) {
        imageSearch {
          imageUrl
          keywords
          engine
        }
      }
    }
  `,
};
