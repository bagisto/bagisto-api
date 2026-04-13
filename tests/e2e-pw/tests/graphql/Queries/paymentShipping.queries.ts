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

export const SET_CHECKOUT_ADDRESS = `
  mutation createCheckoutAddress($input: createCheckoutAddressInput!) {
    createCheckoutAddress(input: $input) {
      checkoutAddress {
        success
        message
        _id
        billingAddress
        billingCity
        billingCountry
        billingState
        billingPostcode
        useForShipping
      }
    }
  }
`;

export const SET_SHIPPING_METHOD = `
  mutation createCheckoutShippingMethod($shippingMethod: String!) {
    createCheckoutShippingMethod(input: { shippingMethod: $shippingMethod }) {
      checkoutShippingMethod {
        success
        id
        message
      }
    }
  }
`;

export const SET_PAYMENT_METHOD = `
  mutation createCheckoutPaymentMethod(
    $paymentMethod: String!
    $successUrl: String
    $failureUrl: String
    $cancelUrl: String
  ) {
    createCheckoutPaymentMethod(
      input: {
        paymentMethod: $paymentMethod
        paymentSuccessUrl: $successUrl
        paymentFailureUrl: $failureUrl
        paymentCancelUrl: $cancelUrl
      }
    ) {
      checkoutPaymentMethod {
        success
        message
        paymentGatewayUrl
        paymentData
      }
    }
  }
`;

export const PLACE_ORDER = `
  mutation createCheckoutOrder {
    createCheckoutOrder(input: {}) {
      checkoutOrder {
        id
        orderId
      }
    }
  }
`;
