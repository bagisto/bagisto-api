// graphql/queries/currency.queries.ts

// Get All Currencies
export const GET_CURRENCIES = `
  query GetCurrencies {
    currencies {
      id
      code
      name
      symbol
    }
  }
`;

// Get Single Currency
export const GET_CURRENCY = `
  query GetCurrency($id: ID!) {
    currency(id: $id) {
      id
      _id
      code
      name
      symbol
    }
  }
`;

export const GET_CURRENCIES_COMPLETE = `
  query GetCurrenciesComplete($first: Int, $after: String) {
    currencies(first: $first, after: $after) {
      edges {
        cursor
        node {
          id
          _id
          code
          name
          symbol
          decimal
          groupSeparator
          decimalSeparator
          currencyPosition
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

export const GET_CURRENCY_COMPLETE = `
  query GetCurrencyComplete($id: ID!) {
    currency(id: $id) {
      id
      _id
      code
      name
      symbol
      decimal
      groupSeparator
      decimalSeparator
      currencyPosition
    }
  }
`;
