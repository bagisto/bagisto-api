// graphql/queries/cart.queries.ts

// Get Cart Details
export const GET_CART = `
  query getCart {
    cart {
      id
      _id
      grandTotal
      discountAmount
      cartToken
      customerId
      channelId
      subtotal
      baseSubtotal
      discountAmount
      baseDiscountAmount
      taxAmount
      baseTaxAmount
      shippingAmount
      baseShippingAmount
      grandTotal
      baseGrandTotal
      formattedSubtotal
      formattedDiscountAmount
      formattedTaxAmount
      formattedShippingAmount
      formattedGrandTotal
      items {
        id
        _id
        quantity
        type
        itemId
        productId
        sku
        name
        price
        formattedPrice
        basePrice
        formattedBasePrice
        totalWeight
        sumOf
        discountAmount
        formattedDiscountAmount
        discountPercent
        baseDiscountAmount
        formattedBaseDiscountAmount
        taxAmount
        formattedTaxAmount
        baseTaxAmount
        formattedBaseTaxAmount
        rowTotal
        formattedRowTotal
        baseRowTotal
        formattedBaseRowTotal
        additional {
          color
          size
          material
        }
        product {
          id
          name
          sku
          urlKey
          price
          images {
            url
          }
        }
      }
      shippingAddress {
        id
        firstName
        lastName
        name
        companyName
        address1
        address2
        country
        state
        city
        postcode
        phone
        email
      }
      billingAddress {
        id
        firstName
        lastName
        name
        companyName
        address1
        address2
        country
        state
        city
        postcode
        phone
        email
      }
      payment {
        method
        methodTitle
      }
      shipping {
        method
        methodTitle
        price
        formattedPrice
      }
      couponCode
      paymentCode
      shippingCode
    }
  }
`;

// Create Cart (Mutation - but querying created cart)
export const CREATE_CART = `
  mutation createCart($customerId: ID, $channelId: ID) {
    createCart(input: { customer_id: $customerId, channel_id: $channelId }) {
      cart {
        id
        _id
        cartToken
      }
    }
  }
`;