// graphql/queries/paymentShipping.queries.ts

// Get All Payment Methods
export const GET_PAYMENT_METHODS = `
  query GetPaymentMethods {
    paymentMethods {
      code
      title
      method
      description
      sortOrder
    }
  }
`;

// Get All Shipping Methods
export const GET_SHIPPING_METHODS = `
  query GetShippingMethods {
    shippingMethods {
      code
      title
      method
      description
      sortOrder
      price
      formattedPrice
    }
  }
`;