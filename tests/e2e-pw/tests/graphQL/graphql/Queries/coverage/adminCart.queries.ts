const ADMIN_CART_FIELDS = `
  id
  _id
  customerId
  isActive
  itemsCount
  itemsQty
  subTotal
  grandTotal
  formattedGrandTotal
  discountAmount
  couponCode
  shippingMethod
  paymentMethod
  paymentMethodTitle
  haveStockableItems
  items
  billingAddress
  shippingAddress
  success
  message
`;

export const ADMIN_COVERAGE_CREATE_DRAFT_CART = `
  mutation createAdminDraftCart($input: createAdminDraftCartInput!) {
    createAdminDraftCart(input: $input) {
      adminDraftCart {
        id
        cartId
        customerId
        success
        message
      }
    }
  }
`;

export const ADMIN_COVERAGE_CART = `
  query adminCart($id: ID!) {
    adminCart(id: $id) {
      ${ADMIN_CART_FIELDS}
    }
  }
`;

export const ADMIN_COVERAGE_CART_ADD_ITEM = `
  mutation addItemAdminCart($input: addItemAdminCartInput!) {
    addItemAdminCart(input: $input) {
      adminCart {
        ${ADMIN_CART_FIELDS}
      }
    }
  }
`;

export const ADMIN_COVERAGE_CART_UPDATE_ITEMS = `
  mutation updateItemsAdminCart($input: updateItemsAdminCartInput!) {
    updateItemsAdminCart(input: $input) {
      adminCart {
        ${ADMIN_CART_FIELDS}
      }
    }
  }
`;

export const ADMIN_COVERAGE_CART_REMOVE_ITEM = `
  mutation removeItemAdminCart($input: removeItemAdminCartInput!) {
    removeItemAdminCart(input: $input) {
      adminCart {
        ${ADMIN_CART_FIELDS}
      }
    }
  }
`;

export const ADMIN_COVERAGE_CART_SAVE_ADDRESS = `
  mutation saveAddressAdminCart($input: saveAddressAdminCartInput!) {
    saveAddressAdminCart(input: $input) {
      adminCart {
        ${ADMIN_CART_FIELDS}
      }
    }
  }
`;

export const ADMIN_COVERAGE_CART_APPLY_COUPON = `
  mutation applyCouponAdminCart($input: applyCouponAdminCartInput!) {
    applyCouponAdminCart(input: $input) {
      adminCart {
        ${ADMIN_CART_FIELDS}
      }
    }
  }
`;

export const ADMIN_COVERAGE_CART_REMOVE_COUPON = `
  mutation removeCouponAdminCart($input: removeCouponAdminCartInput!) {
    removeCouponAdminCart(input: $input) {
      adminCart {
        ${ADMIN_CART_FIELDS}
      }
    }
  }
`;

export const ADMIN_COVERAGE_CART_SET_SHIPPING_METHOD = `
  mutation setShippingMethodAdminCart($input: setShippingMethodAdminCartInput!) {
    setShippingMethodAdminCart(input: $input) {
      adminCart {
        ${ADMIN_CART_FIELDS}
      }
    }
  }
`;

export const ADMIN_COVERAGE_CART_SET_PAYMENT_METHOD = `
  mutation setPaymentMethodAdminCart($input: setPaymentMethodAdminCartInput!) {
    setPaymentMethodAdminCart(input: $input) {
      adminCart {
        ${ADMIN_CART_FIELDS}
      }
    }
  }
`;

export const ADMIN_COVERAGE_CART_SHIPPING_RATES = `
  query adminCartShippingRates($cartId: Int!) {
    adminCartShippingRates(cartId: $cartId) {
      totalCount
      edges {
        node {
          method
          carrierCode
          carrierTitle
          methodTitle
          price
          formattedPrice
          baseTotal
          formattedBaseTotal
        }
      }
    }
  }
`;

export const ADMIN_COVERAGE_CART_PAYMENT_METHODS = `
  query adminCartPaymentMethods($cartId: Int!) {
    adminCartPaymentMethods(cartId: $cartId) {
      totalCount
      edges {
        node {
          method
          methodTitle
          description
          sort
        }
      }
    }
  }
`;

export const ADMIN_COVERAGE_PLACE_ORDER = `
  mutation createAdminPlaceOrder($input: createAdminPlaceOrderInput!) {
    createAdminPlaceOrder(input: $input) {
      adminPlaceOrder {
        orderId
        incrementId
        customerId
        grandTotal
        success
        message
      }
    }
  }
`;

export const ADMIN_COVERAGE_COPY_CART_RULE = `
  mutation copyAdminMarketingCartRule($input: copyAdminMarketingCartRuleInput!) {
    copyAdminMarketingCartRule(input: $input) {
      adminMarketingCartRule {
        _id
        name
        status
      }
    }
  }
`;
