// graphql/queries/product.queries.ts
export const GET_PRODUCT_BY_ID = `
  query getProduct($id: ID!) {
    product(id: $id) {
      id
      name
      sku
      urlKey
      price
    }
  }
`;

// Get Product by SKU
export const GET_PRODUCT_BY_SKU = `
  query getProduct($sku: String!) {
    product(sku: $sku) {
      id
      name
      sku
      urlKey
      price
    }
  }
`;

// Get Product by ID with Variants
export const GET_PRODUCT_WITH_VARIANTS = `
  query getProduct($id: ID!) {
    product(id: $id) {
      id
      name
      sku
      urlKey
      price
      variants {
        edges {
          node {
            id
            name
            sku
            price
            attributeValues {
              edges {
                node {
                  value
                  attribute {
                    code
                    adminName
                  }
                }
              }
            }
          }
        }
      }
    }
  }
`;

export const GET_PRODUCT_BY_URLKEY =`
query getProducts($urlKey: String!) {
  product(urlKey: $urlKey) {
    id
    sku
    name
    urlKey
    type
    price
  }
}
`;

export const GET_FULL_PRODUCT_DETAILS = `
query getProduct($id: ID!) {
  product(id: $id) {
    id
    name
    sku
    urlKey
    description
    shortDescription
    price
    specialPrice
    images {
      edges {
        node {
          id
          publicPath
          position
        }
      }
    }
    attributeValues {
      edges {
        node {
          value
          attribute {
            code
            adminName
          }
        }
      }
    }
    variants {
      edges {
        node {
          id
          name
          sku
          price
          attributeValues {
            edges {
              node {
                value
                attribute {
                  code
                  adminName
                }
              }
            }
          }
        }
      }
    }
    categories {
      edges {
        node {
          id
          translation {
            name
          }
        }
      }
    }
  }
}
`;

// Get Products Sorted (A-Z, Z-A, or by Created Date)
export const GET_PRODUCTS_SORTED = `
  query getProductsSorted($reverse: Boolean, $sortKey: String, $first: Int) {
    products(reverse: $reverse, sortKey: $sortKey, first: $first) {
      edges {
        node {
          id
          name
          sku
          price
          createdAt
        }
      }
    }
  }
`;

// Get Products by Search and Filter
export const GET_PRODUCTS_SEARCH_FILTER = `
  query getProductsSearchFilter($query: String, $sortKey: String, $reverse: Boolean, $first: Int) {
    products(query: $query, sortKey: $sortKey, reverse: $reverse, first: $first) {
      edges {
        node {
          id
          name
          sku
          price
        }
      }
    }
  }
`;

// Get Products by Category ID with Pagination
export const GET_PRODUCTS_BY_CATEGORY = `
  query getProductsByCategory($filter: String, $first: Int, $after: String) {
    products(filter: $filter, first: $first, after: $after) {
      edges {
        node {
          id
          sku
          price
          name
          urlKey
          baseImageUrl
          description
          shortDescription
          specialPrice
        }
      }
      pageInfo {
        hasNextPage
        hasPreviousPage
        startCursor
        endCursor
      }
      totalCount
    }
  }
`;

// Get Products by Type
export const GET_PRODUCTS_BY_TYPE = `
  query getProductsByType($filter: String) {
    products(filter: $filter) {
      edges {
        node {
          id
          sku
        }
      }
      totalCount
    }
  }
`;

// Get Products by Attribute (e.g., Color)
export const GET_PRODUCTS_BY_ATTRIBUTE = `
  query getProductsByAttribute($filter: String) {
    products(filter: $filter) {
      edges {
        node {
          id
          sku
        }
      }
      totalCount
    }
  }
`;

// Get Products by Multiple Attributes
export const GET_PRODUCTS_BY_MULTIPLE_ATTRIBUTES = `
  query getProductsByMultipleAttributes($filter: String, $first: Int) {
    products(filter: $filter, first: $first) {
      edges {
        node {
          id
          sku
          name
          price
        }
      }
      totalCount
    }
  }
`;

export const GET_PRODUCTS_FILTERED_AND_SORTED = `
  query getProductsFilteredAndSorted($filter: String, $sortKey: String, $reverse: Boolean, $first: Int) {
    products(filter: $filter, sortKey: $sortKey, reverse: $reverse, first: $first) {
      edges {
        node {
          id
          sku
          name
          price
          urlKey
          baseImageUrl
        }
      }
      totalCount
    }
  }
`;

// Sort Products A-Z by Title (ascending order)
export const GET_PRODUCTS_SORTED_BY_TITLE_AZ = `
  query getProducts {
    products(sortKey: "TITLE", reverse: false, first: 5) {
      edges {
        node {
          id
          name
          sku
          price
        }
      }
      totalCount
    }
  }
`;

// Sort Products Z-A by Title (descending order)
export const GET_PRODUCTS_SORTED_BY_TITLE_ZA = `
  query getProducts {
    products(sortKey: "TITLE", reverse: true, first: 5) {
      edges {
        node {
          id
          name
          sku
          price
        }
      }
      totalCount
    }
  }
`;

export const GET_PRODUCTS_WITH_FORMATTED_PRICES = `
  query getProductsWithFormattedPrices($first: Int) {
    products(first: $first) {
      edges {
        node {
          id
          name
          sku
          price
          formattedPrice
          specialPrice
          formattedSpecialPrice
          minimumPrice
          formattedMinimumPrice
          maximumPrice
          formattedMaximumPrice
          regularMinimumPrice
          formattedRegularMinimumPrice
          regularMaximumPrice
          formattedRegularMaximumPrice
        }
      }
      totalCount
    }
  }
`;

export const GET_PRODUCTS_BY_TYPE_WITH_DETAILS = `
  query getProductsByTypeWithDetails($filter: String!, $first: Int) {
    products(filter: $filter, first: $first) {
      edges {
        node {
          id
          name
          sku
          type
          urlKey
          description
          shortDescription
          price
          specialPrice
          minimumPrice
          images(first: 5) {
            edges {
              node {
                id
                publicPath
                position
              }
            }
          }
          attributeValues {
            edges {
              node {
                value
                attribute {
                  code
                  adminName
                }
              }
            }
          }
          categories {
            edges {
              node {
                id
                translation {
                  name
                }
              }
            }
          }
        }
      }
      totalCount
    }
  }
`;

export const GET_SIMPLE_PRODUCTS = `
  query getAllSimpleProducts {
    products(filter: "{\\"type\\": \\"simple\\"}") {
      edges {
        node {
          id
          name
          sku
          urlKey
          description
          shortDescription
          price
          specialPrice
          images(first: 5) {
            edges {
              node {
                id
                publicPath
                position
              }
            }
          }
          attributeValues {
            edges {
              node {
                value
                attribute {
                  code
                  adminName
                }
              }
            }
          }
          categories {
            edges {
              node {
                id
                translation {
                  name
                }
              }
            }
          }
        }
      }
      totalCount
    }
  }
`;

export const GET_CONFIGURABLE_PRODUCTS = `
  query getAllConfigurableProducts {
    products(filter: "{\\"type\\": \\"configurable\\"}") {
      edges {
        node {
          id
          name
          sku
          type
          urlKey
          description
          shortDescription
          minimumPrice
          images(first: 5) {
            edges {
              node {
                id
                publicPath
                position
              }
            }
          }
          attributeValues {
            edges {
              node {
                value
                attribute {
                  code
                  adminName
                }
              }
            }
          }
          categories {
            edges {
              node {
                id
                translation {
                  name
                }
              }
            }
          }
        }
      }
      totalCount
    }
  }
`;

export const GET_BOOKING_PRODUCTS = `
  query getAllBookingProducts {
    products(filter: "{\\"type\\": \\"booking\\"}") {
      edges {
        node {
          id
          name
          sku
          type
          urlKey
          description
          shortDescription
          price
          specialPrice
          bookingProducts {
            edges {
              node {
                id
                _id
                type
                qty
                location
                showLocation
                availableEveryWeek
                availableFrom
                availableTo
                createdAt
                updatedAt
              }
            }
          }
          images(first: 5) {
            edges {
              node {
                id
                publicPath
                position
              }
            }
          }
          categories {
            edges {
              node {
                id
                translation {
                  name
                }
              }
            }
          }
        }
      }
      totalCount
    }
  }
`;

export const GET_VIRTUAL_PRODUCTS = `
  query getAllVirtualProducts {
    products(filter: "{\\"type\\": \\"virtual\\"}") {
      edges {
        node {
          id
          name
          sku
          type
          urlKey
          description
          shortDescription
          price
          specialPrice
          images(first: 5) {
            edges {
              node {
                id
                publicPath
                position
              }
            }
          }
          attributeValues {
            edges {
              node {
                value
                attribute {
                  code
                  adminName
                }
              }
            }
          }
          categories {
            edges {
              node {
                id
                translation {
                  name
                }
              }
            }
          }
        }
      }
      totalCount
    }
  }
`;

export const GET_GROUPED_PRODUCTS = `
  query getAllGroupedProducts {
    products(filter: "{\\"type\\": \\"grouped\\"}") {
      edges {
        node {
          id
          name
          sku
          type
          urlKey
          description
          shortDescription
          price
          specialPrice
          images(first: 5) {
            edges {
              node {
                id
                publicPath
                position
              }
            }
          }
          categories {
            edges {
              node {
                id
                translation {
                  name
                }
              }
            }
          }
        }
      }
      totalCount
    }
  }
`;

export const GET_DOWNLOADABLE_PRODUCTS = `
  query getAllDownloadableProducts {
    products(filter: "{\\"type\\": \\"downloadable\\"}") {
      edges {
        node {
          id
          name
          sku
          type
          urlKey
          description
          shortDescription
          price
          specialPrice
          images(first: 5) {
            edges {
              node {
                id
                publicPath
                position
              }
            }
          }
          categories {
            edges {
              node {
                id
                translation {
                  name
                }
              }
            }
          }
        }
      }
      totalCount
    }
  }
`;

export const GET_BUNDLE_PRODUCTS = `
  query getAllBundleProducts {
    products(filter: "{\\"type\\": \\"bundle\\"}") {
      edges {
        node {
          id
          name
          sku
          type
          urlKey
          description
          shortDescription
          price
          specialPrice
          minimumPrice
          maximumPrice
          images(first: 5) {
            edges {
              node {
                id
                publicPath
                position
              }
            }
          }
          categories {
            edges {
              node {
                id
                translation {
                  name
                }
              }
            }
          }
        }
      }
      totalCount
    }
  }
`;

export const GET_PRODUCT_BOOKING_APPOINTMENT = `
  query getAppointmentBookingProduct($id: ID!) {
    product(id: $id) {
      id
      name
      sku
      urlKey
      price
      bookingProducts {
        edges {
          node {
            _id
            type
            appointmentSlot {
              id
              _id
              bookingProductId
              duration
              breakTime
              sameSlotAllDays
              slots
            }
          }
        }
      }
    }
  }
`;

export const GET_PRODUCT_BOOKING_RENTAL = `
  query getRentalBookingProduct($id: ID!) {
    product(id: $id) {
      id
      name
      sku
      urlKey
      price
      bookingProducts {
        edges {
          node {
            _id
            type
            rentalSlot {
              id
              _id
              bookingProductId
              rentingType
              dailyPrice
              hourlyPrice
              sameSlotAllDays
              slots
            }
          }
        }
      }
    }
  }
`;

export const GET_PRODUCT_BOOKING_DEFAULT = `
  query getDefaultBookingProduct($id: ID!) {
    product(id: $id) {
      id
      name
      sku
      urlKey
      price
      bookingProducts {
        edges {
          node {
            _id
            type
            defaultSlot {
              id
              _id
              bookingType
              duration
              breakTime
              slots
            }
          }
        }
      }
    }
  }
`;

export const GET_PRODUCT_BOOKING_TABLE = `
  query getTableBookingProduct($id: ID!) {
    product(id: $id) {
      id
      name
      sku
      urlKey
      price
      bookingProducts {
        edges {
          node {
            _id
            type
            tableSlot {
              id
              _id
              bookingProductId
              priceType
              guestLimit
              duration
              breakTime
              preventSchedulingBefore
              sameSlotAllDays
              slots
            }
          }
        }
      }
    }
  }
`;

export const GET_PRODUCT_BOOKING_EVENT = `
  query getEventBookingProduct($id: ID!) {
    product(id: $id) {
      id
      name
      sku
      urlKey
      price
      bookingProducts {
        edges {
          node {
            _id
            type
            eventTickets {
              edges {
                node {
                  id
                  _id
                  bookingProductId
                  price
                  qty
                  specialPrice
                  specialPriceFrom
                  specialPriceTo
                }
              }
            }
          }
        }
      }
    }
  }
`;
