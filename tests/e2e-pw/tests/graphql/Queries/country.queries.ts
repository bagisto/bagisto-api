/* ===================================================
 * Country GraphQL Queries
 * Contains all query definitions for Countries API
 * =================================================== */

/* ===================================================
 * QUERY 1: Get Country by ID - Basic
 * Returns basic country information
 * =================================================== */
export const GET_COUNTRY_BASIC = `
  query getSingleCountry($id: ID!) {
    country(id: $id) {
      id
      _id
      code
      name
    }
  }
`;

/* ===================================================
 * QUERY 2: Get Country with States
 * Returns country with all states
 * =================================================== */
export const GET_COUNTRY_WITH_STATES = `
  query getSingleCountry($id: ID!) {
    country(id: $id) {
      id
      _id
      code
      name
      states {
        edges {
          node {
            id
            _id
            code
            defaultName
            countryId
            countryCode
            translations {
              edges {
                node {
                  id
                  locale
                  defaultName
                }
              }
            }
          }
        }
        pageInfo {
          hasNextPage
          endCursor
        }
        totalCount
      }
    }
  }
`;

/* ===================================================
 * QUERY 3: Get Country with Translations
 * Returns country with translations
 * =================================================== */
export const GET_COUNTRY_WITH_TRANSLATIONS = `
  query getSingleCountry($id: ID!) {
    country(id: $id) {
      id
      _id
      code
      name
      translations {
        edges {
          node {
            id
            _id
            locale
            name
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
  }
`;

/* ===================================================
 * QUERY 4: Get Country - Complete Details
 * Returns complete country information with states and translations
 * =================================================== */
export const GET_COUNTRY_COMPLETE = `
  query getSingleCountry($id: ID!) {
    country(id: $id) {
      id
      _id
      code
      name
      states {
        edges {
          node {
            id
            _id
            code
            defaultName
            countryId
            countryCode
            translations {
              edges {
                node {
                  id
                  locale
                  defaultName
                }
              }
            }
          }
        }
        pageInfo {
          hasNextPage
          endCursor
        }
        totalCount
      }
      translations {
        edges {
          node {
            id
            _id
            locale
            name
          }
        }
        pageInfo {
          hasNextPage
          endCursor
        }
        totalCount
      }
    }
  }
`;

/* ===================================================
 * QUERY 5: Get All Countries - Basic
 * Returns basic country information for all countries
 * =================================================== */
export const GET_COUNTRIES_BASIC = `
  query countries {
    countries {
      edges {
        node {
          id
          _id
          code
          name
        }
      }
      pageInfo {
        hasNextPage
        endCursor
      }
      totalCount
    }
  }
`;

/* ===================================================
 * QUERY 6: Get All Countries with States and Translations
 * Returns complete country information with states and translations
 * =================================================== */
export const GET_COUNTRIES_WITH_STATES = `
  query countries {
    countries {
      edges {
        node {
          id
          _id
          code
          name
          states {
            edges {
              node {
                id
                _id
                code
                defaultName
                countryId
                countryCode
                translations {
                  edges {
                    node {
                      id
                      locale
                      defaultName
                    }
                  }
                }
              }
            }
            pageInfo {
              hasNextPage
              endCursor
            }
            totalCount
          }
          translations {
            edges {
              node {
                id
                locale
                name
              }
            }
            totalCount
          }
        }
      }
      pageInfo {
        hasNextPage
        endCursor
      }
      totalCount
    }
  }
`;

/* ===================================================
 * QUERY 7: Get Countries with Pagination
 * Returns countries with pagination variables
 * =================================================== */
export const GET_COUNTRIES_WITH_PAGINATION = `
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
        startCursor
        hasNextPage
        hasPreviousPage
      }
      totalCount
    }
  }
`;

/* ===================================================
 * QUERY 8: Get Countries with All Translations
 * Returns all countries with complete translation data
 * =================================================== */
export const GET_COUNTRIES_WITH_ALL_TRANSLATIONS = `
  query countries {
    countries {
      edges {
        node {
          id
          _id
          code
          translations {
            edges {
              node {
                id
                locale
                name
              }
            }
            totalCount
          }
        }
      }
      totalCount
    }
  }
`;

/* ===================================================
 * QUERY 9: Get Countries for Address Form
 * Returns countries with states for address form dropdowns
 * =================================================== */
export const GET_COUNTRIES_FOR_ADDRESS_FORM = `
  query countries {
    countries {
      edges {
        node {
          id
          _id
          code
          name
          states {
            edges {
              node {
                id
                _id
                code
                defaultName
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

/* ===================================================
 * COUNTRY STATES QUERIES
 * =================================================== */

/* ===================================================
 * QUERY 10: Get Country States - Basic
 * Returns basic state information for a country
 * =================================================== */
export const GET_COUNTRY_STATES_BASIC = `
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
`;

/* ===================================================
 * QUERY 11: Get Country States with Translations
 * Returns states with translation data
 * =================================================== */
export const GET_COUNTRY_STATES_WITH_TRANSLATIONS = `
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
          translations {
            edges {
              node {
                id
                locale
                defaultName
              }
            }
            totalCount
          }
        }
      }
      totalCount
    }
  }
`;

/* ===================================================
 * QUERY 12: Get Country States with Pagination
 * Returns states with pagination support
 * =================================================== */
export const GET_COUNTRY_STATES_WITH_PAGINATION = `
  query getCountryStates($countryId: Int!, $first: Int, $after: String) {
    countryStates(countryId: $countryId, first: $first, after: $after) {
      edges {
        node {
          id
          _id
          code
          defaultName
          countryId
          countryCode
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

/* ===================================================
 * QUERY 12B: Get Country States with Last Pagination
 * Returns states using last/before pagination
 * =================================================== */
export const GET_COUNTRY_STATES_WITH_LAST_PAGINATION = `
  query getCountryStates($countryId: Int!, $last: Int, $before: String) {
    countryStates(countryId: $countryId, last: $last, before: $before) {
      edges {
        node {
          id
          _id
          code
          defaultName
          countryId
          countryCode
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

/* ===================================================
 * QUERY 12C: Get Country States with First/Last
 * Returns states with both first and last parameters
 * =================================================== */
export const GET_COUNTRY_STATES_FIRST_LAST = `
  query getCountryStates($countryId: Int!, $first: Int, $last: Int) {
    countryStates(countryId: $countryId, first: $first, last: $last) {
      edges {
        node {
          id
          _id
          code
          defaultName
          countryId
          countryCode
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

/* ===================================================
 * QUERY 7B: Get Countries with Last Pagination
 * Returns countries using last/before pagination
 * =================================================== */
export const GET_COUNTRIES_WITH_LAST_PAGINATION = `
  query countries($last: Int, $before: String) {
    countries(last: $last, before: $before) {
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
        startCursor
        hasNextPage
        hasPreviousPage
      }
      totalCount
    }
  }
`;

/* ===================================================
 * QUERY 7C: Get Countries with First and Last
 * Returns countries with both first and last parameters
 * =================================================== */
export const GET_COUNTRIES_WITH_FIRST_LAST = `
  query countries($first: Int, $last: Int) {
    countries(first: $first, last: $last) {
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
        startCursor
        hasNextPage
        hasPreviousPage
      }
      totalCount
    }
  }
`;

/* ===================================================
 * QUERY 7D: Get Countries with All Pagination Params
 * Returns countries with all available pagination parameters
 * =================================================== */
export const GET_COUNTRIES_FULL_PAGINATION = `
  query countries($first: Int, $after: String, $last: Int, $before: String) {
    countries(first: $first, after: $after, last: $last, before: $before) {
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
        startCursor
        hasNextPage
        hasPreviousPage
      }
      totalCount
    }
  }
`;

/* ===================================================
 * QUERY 12D: Get Country States with All Pagination
 * Returns states with all pagination parameters
 * =================================================== */
export const GET_COUNTRY_STATES_FULL_PAGINATION = `
  query getCountryStates($countryId: Int!, $first: Int, $after: String, $last: Int, $before: String) {
    countryStates(countryId: $countryId, first: $first, after: $after, last: $last, before: $before) {
      edges {
        node {
          id
          _id
          code
          defaultName
          countryId
          countryCode
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

/* ===================================================
 * QUERY 13: Get Country States for Dropdown Form
 * Returns minimal state information for form dropdowns
 * =================================================== */
export const GET_COUNTRY_STATES_FOR_DROPDOWN = `
  query getCountryStates($countryId: Int!) {
    countryStates(countryId: $countryId) {
      edges {
        node {
          id
          _id
          code
          defaultName
        }
      }
      totalCount
    }
  }
`;

/* ===================================================
 * QUERY 14: Get Country State - Basic
 * Returns basic country state information
 * =================================================== */
export const GET_COUNTRY_STATE_BASIC = `
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
`;

/* ===================================================
 * QUERY 15: Get Country State with Translations
 * Returns state with translation data
 * =================================================== */
export const GET_COUNTRY_STATE_WITH_TRANSLATIONS = `
  query getCountryState($id: ID!) {
    countryState(id: $id) {
      id
      _id
      code
      defaultName
      countryId
      countryCode
      translations {
        edges {
          node {
            id
            locale
            defaultName
          }
        }
        totalCount
      }
    }
  }
`;

/* ===================================================
 * QUERY 16: Get Country State for Address Validation
 * Returns state details for address validation purposes
 * =================================================== */
export const GET_COUNTRY_STATE_FOR_ADDRESS_VALIDATION = `
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
`;

/* ===================================================
 * QUERY 17: Get Country State - Complete Details
 * Returns complete state information with all details
 * =================================================== */
export const GET_COUNTRY_STATE_COMPLETE = `
  query getCountryState($id: ID!) {
    countryState(id: $id) {
      id
      _id
      code
      defaultName
      countryId
      countryCode
      translations {
        edges {
          node {
            id
            locale
            defaultName
          }
        }
        totalCount
      }
    }
  }
`;

/* ===================================================
 * NEGATIVE TEST QUERIES
 * =================================================== */

/* Invalid ID format */
export const GET_COUNTRY_INVALID_FORMAT = `
  query getSingleCountry($id: ID!) {
    country(id: $id) {
      id
      invalidField
    }
  }
`;
