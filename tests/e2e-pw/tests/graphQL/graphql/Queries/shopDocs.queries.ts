export const SHOP_DOCS_QUERIES = {
  getAttributes: `
    query getAllAttributes($first: Int, $after: String) {
      attributes(first: $first, after: $after) {
        edges {
          node {
            id
            _id
            code
            adminName
            type
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
  `,
  getAttribute: `
    query getAttributeByID($id: ID!) {
      attribute(id: $id) {
        id
        _id
        code
        adminName
        type
      }
    }
  `,
  getAttributeOptions: `
    query getAttributeOptions($first: Int, $after: String) {
      attributeOptions(first: $first, after: $after) {
        edges {
          node {
            id
            _id
            adminName
            sortOrder
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
  `,
  getCategories: `
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
  `,
  getCategory: `
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
  `,
  treeCategories: `
    query treeCategories {
      treeCategories(parentId: 1) {
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
  `,
  getChannels: `
    query getChannels($first: Int, $after: String) {
      channels(first: $first, after: $after) {
        edges {
          node {
            id
            _id
            code
            hostname
            timezone
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
  `,
  getChannel: `
    query getChannelByID($id: ID!) {
      channel(id: $id) {
        id
        _id
        code
        hostname
        timezone
      }
    }
  `,
  getCountries: `
    query countries($first: Int, $after: String) {
      countries(first: $first, after: $after) {
        edges {
          node {
            id
            _id
            code
            name
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
  `,
  getCountry: `
    query getSingleCountry($id: ID!) {
      country(id: $id) {
        id
        _id
        code
        name
      }
    }
  `,
  getCountryStates: `
    query getCountryStates($countryId: Int!) {
      countryStates(countryId: $countryId) {
        edges {
          node {
            id
            _id
            code
            defaultName
            countryId
            countryCode
          }
        }
        totalCount
      }
    }
  `,
  getCountryState: `
    query getCountryState($id: ID!) {
      countryState(id: $id) {
        id
        _id
        code
        defaultName
        countryId
        countryCode
      }
    }
  `,
  getCurrencies: `
    query allCurrency($first: Int, $after: String) {
      currencies(first: $first, after: $after) {
        edges {
          node {
            id
            _id
            code
            name
            symbol
          }
        }
        pageInfo {
          hasNextPage
          endCursor
        }
      }
    }
  `,
  getCurrency: `
    query getCurrencyByID($id: ID!) {
      currency(id: $id) {
        id
        _id
        code
        name
        symbol
      }
    }
  `,
  getLocales: `
    query getLocales($first: Int, $after: String) {
      locales(first: $first, after: $after) {
        edges {
          node {
            id
            _id
            code
            name
            direction
          }
        }
        pageInfo {
          hasNextPage
          endCursor
        }
      }
    }
  `,
  getLocale: `
    query getSingleLocale($id: ID!) {
      locale(id: $id) {
        id
        _id
        code
        name
        direction
      }
    }
  `,
  getPages: `
    query getCmsPagesDetails($first: Int, $after: String) {
      pages(first: $first, after: $after) {
        edges {
          node {
            id
            _id
            layout
            translation {
              pageTitle
              urlKey
            }
          }
          cursor
        }
        pageInfo {
          endCursor
          hasNextPage
        }
      }
    }
  `,
  getPage: `
    query getCmsPageDetail($id: ID!) {
      page(id: $id) {
        id
        _id
        layout
        translation {
          pageTitle
          urlKey
          locale
        }
      }
    }
  `,
  getThemeCustomizations: `
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
  `,
  getThemeCustomization: `
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
  `,
  getProducts: `
    query getProductsSorted($first: Int) {
      products(reverse: false, sortKey: "TITLE", first: $first) {
        edges {
          node {
            id
            name
            sku
            price
          }
          cursor
        }
        totalCount
      }
    }
  `,
  getProduct: `
    query getProduct($id: ID!) {
      product(id: $id) {
        id
        name
        sku
        urlKey
        price
      }
    }
  `,
  searchProducts: `
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
        totalCount
      }
    }
  `,
  getProductReviews: `
    query productReviews($first: Int, $after: String) {
      productReviews(first: $first, after: $after) {
        edges {
          node {
            id
            _id
            name
            title
            rating
            comment
            status
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
  `,
  createReadCart: `
    mutation readCart {
      createReadCart(input: {}) {
        readCart {
          id
          _id
          itemsCount
          itemsQty
          cartToken
          isGuest
          success
          message
        }
      }
    }
  `,
  getCheckoutAddresses: `
    query collectionGetCheckoutAddresses {
      collectionGetCheckoutAddresses {
        edges {
          node {
            id
            addressType
            firstName
            lastName
            city
            country
            postcode
          }
        }
      }
    }
  `,
  getPaymentMethods: `
    query paymentMethods {
      paymentMethods {
        method
        methodTitle
        description
      }
    }
  `,
  getShippingMethods: `
    query shippingMethods {
      shippingMethods {
        method
        methodTitle
        price
        formattedPrice
      }
    }
  `,
  getCompareItems: `
    query getCompareItems {
      compareItems {
        edges {
          node {
            id
            _id
          }
        }
        totalCount
      }
    }
  `,
  getCompareItem: `
    query getCompareItem($id: ID!) {
      compareItem(id: $id) {
        id
        _id
      }
    }
  `,
  getWishlists: `
    query getWishlists($first: Int, $after: String) {
      wishlists(first: $first, after: $after) {
        edges {
          node {
            id
            _id
          }
        }
        pageInfo {
          endCursor
          hasNextPage
        }
        totalCount
      }
    }
  `,
  getWishlist: `
    query getWishlist($id: ID!) {
      wishlist(id: $id) {
        id
        _id
      }
    }
  `,
  getCustomerProfile: `
    query getCustomerProfile {
      readCustomerProfile {
        id
        firstName
        lastName
        email
      }
    }
  `,
  getCustomerAddresses: `
    query getCustomerAddresses {
      customerAddresses {
        edges {
          node {
            id
            firstName
            lastName
            city
            country
          }
        }
        totalCount
      }
    }
  `,
  getCustomerOrders: `
    query GetCustomerOrders($first: Int, $after: String) {
      customerOrders(first: $first, after: $after) {
        edges {
          cursor
          node {
            id
            _id
            incrementId
            status
          }
        }
        pageInfo {
          endCursor
          hasNextPage
        }
        totalCount
      }
    }
  `,
  getCustomerOrder: `
    query GetCustomerOrder($id: ID!) {
      customerOrder(id: $id) {
        id
        _id
        incrementId
        status
      }
    }
  `,
  getCustomerOrderShipments: `
    query getOrderShipments($orderId: ID!) {
      customerOrderShipments(orderId: $orderId) {
        edges {
          node {
            id
            _id
            status
          }
        }
        totalCount
      }
    }
  `,
  getCustomerOrderShipment: `
    query getOrderShipment($id: ID!) {
      customerOrderShipment(id: $id) {
        id
        _id
        status
      }
    }
  `,
  getCustomerInvoices: `
    query GetCustomerInvoices($first: Int, $after: String) {
      customerInvoices(first: $first, after: $after) {
        edges {
          cursor
          node {
            id
            _id
            incrementId
            state
            downloadUrl
          }
        }
        pageInfo {
          endCursor
          hasNextPage
        }
        totalCount
      }
    }
  `,
  getCustomerInvoice: `
    query GetCustomerInvoice($id: ID!) {
      customerInvoice(id: $id) {
        id
        _id
        incrementId
        state
        downloadUrl
      }
    }
  `,
  getCustomerReviews: `
    query getCustomerReviews($first: Int, $after: String) {
      customerReviews(first: $first, after: $after) {
        edges {
          node {
            id
            _id
            title
            rating
          }
        }
        totalCount
      }
    }
  `,
  getCustomerReview: `
    query getCustomerReview($id: ID!) {
      customerReview(id: $id) {
        id
        _id
        title
        rating
      }
    }
  `,
  getCustomerDownloadableProducts: `
    query GetCustomerDownloadableProducts($first: Int, $after: String) {
      customerDownloadableProducts(first: $first, after: $after) {
        edges {
          cursor
          node {
            id
            _id
            productName
            name
            status
          }
        }
        pageInfo {
          endCursor
          hasNextPage
        }
        totalCount
      }
    }
  `,
  getCustomerDownloadableProduct: `
    query GetCustomerDownloadableProduct($id: ID!) {
      customerDownloadableProduct(id: $id) {
        id
        _id
        productName
        name
        status
      }
    }
  `,
  getBookingSlots: `
    query getBookingSlots($id: Int!, $date: String!) {
      bookingSlots(id: $id, date: $date) {
        slotId
        from
        to
        timestamp
        qty
        time
        slots
      }
    }
  `,
} as const;
