const CART_FIELDS = `
  id
  _id
  customerId
  isGuest
  itemsCount
  itemsQty
  subtotal
  grandTotal
  success
  message
  items {
    edges {
      node {
        id
        _id
        productId
        quantity
      }
    }
  }
`;

export const SHOP_CART_QUERIES = {
  readGetCartToken: `
    mutation readGetCartToken {
      readGetCartToken(input: { id: "current" }) {
        getCartToken {
          ${CART_FIELDS}
        }
      }
    }
  `,

  createMergeCart: `
    mutation createMergeCart($guestCartToken: String, $cartId: Int) {
      createMergeCart(input: { guestCartToken: $guestCartToken, cartId: $cartId }) {
        mergeCart {
          ${CART_FIELDS}
        }
      }
    }
  `,

  createRemoveCartItems: `
    mutation createRemoveCartItems($itemIds: Iterable!) {
      createRemoveCartItems(input: { itemIds: $itemIds }) {
        removeCartItems {
          ${CART_FIELDS}
        }
      }
    }
  `,

  createMoveToWishlist: `
    mutation createMoveToWishlist($itemIds: Iterable!, $quantities: Iterable) {
      createMoveToWishlist(input: { itemIds: $itemIds, quantities: $quantities }) {
        moveToWishlist {
          ${CART_FIELDS}
        }
      }
    }
  `,

  createEstimateShipping: `
    mutation createEstimateShipping($country: String, $state: String, $postcode: String, $shippingMethod: String) {
      createEstimateShipping(
        input: { country: $country, state: $state, postcode: $postcode, shippingMethod: $shippingMethod }
      ) {
        estimateShipping {
          ${CART_FIELDS}
        }
      }
    }
  `,

  cartCrossSells: `
    query cartCrossSells($first: Int) {
      cartCrossSells(first: $first) {
        totalCount
        edges {
          node {
            id
            _id
            sku
            name
            price
            formattedPrice
          }
        }
      }
    }
  `,

  checkoutMessage: `
    query checkoutMessage($orderId: Int!) {
      checkoutMessage(orderId: $orderId) {
        message
      }
    }
  `,

  collectionPaymentMethods: `
    query collectionPaymentMethods {
      collectionPaymentMethods {
        id
        method
        title
        description
        isAllowed
      }
    }
  `,

  collectionShippingRates: `
    query collectionShippingRates {
      collectionShippingRates {
        id
        code
        method
        methodTitle
        price
        formattedPrice
        carrier
        carrierTitle
      }
    }
  `,

  createCheckoutPaymentSession: `
    mutation createCheckoutPaymentSession {
      createCheckoutPaymentSession(input: {}) {
        checkoutPaymentSession {
          success
          cartId
          sessionId
          redirectUrl
          message
        }
      }
    }
  `,

  createCheckoutPaymentConfirmation: `
    mutation createCheckoutPaymentConfirmation($sessionId: String) {
      createCheckoutPaymentConfirmation(input: { sessionId: $sessionId }) {
        checkoutPaymentConfirmation {
          success
          cartId
          orderId
          message
        }
      }
    }
  `,

  createMutationCheckoutAddress: `
    mutation createMutationCheckoutAddress($input: createMutationCheckoutAddressInput!) {
      createMutationCheckoutAddress(input: $input) {
        mutationCheckoutAddress {
          id
        }
      }
    }
  `,

  readCheckoutAddress: `
    query readCheckoutAddress($id: ID!) {
      readCheckoutAddress(id: $id) {
        customerId
        billingFirstName
        billingLastName
        billingEmail
        billingAddress
        billingCity
        billingState
        billingCountry
        billingPostcode
        billingPhoneNumber
        shippingFirstName
        shippingCity
        success
      }
    }
  `,

  createCustomerAddressToken: `
    mutation createCustomerAddressToken($input: createCustomerAddressTokenInput!) {
      createCustomerAddressToken(input: $input) {
        customerAddressToken {
          addressId
          firstName
          lastName
          address1
          city
          state
          country
          postcode
          phone
          defaultAddress
        }
      }
    }
  `,

  readCustomerAddressToken: `
    query readCustomerAddressToken($id: ID!) {
      readCustomerAddressToken(id: $id) {
        id
        addressId
        firstName
        lastName
        address1
        city
        postcode
      }
    }
  `,

  createDeleteCustomerAddressToken: `
    mutation createDeleteCustomerAddressToken($id: ID!, $addressId: Int) {
      createDeleteCustomerAddressToken(input: { id: $id, addressId: $addressId }) {
        customerAddressToken {
          addressId
          firstName
          city
        }
      }
    }
  `,
};
