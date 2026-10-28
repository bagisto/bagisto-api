export const SHOP_ACCOUNT_QUERIES = {
  registerCustomer: `
    mutation shopAccountRegister($input: createCustomerInput!) {
      createCustomer(input: $input) {
        customer {
          _id
          email
          token
        }
      }
    }
  `,

  loginCustomer: `
    mutation shopAccountLogin($email: String!, $password: String!) {
      createCustomerLogin(input: { email: $email, password: $password }) {
        customerLogin {
          _id
          token
          success
          message
        }
      }
    }
  `,

  createAddress: `
    mutation shopAccountCreateAddress($input: createAddUpdateCustomerAddressInput!) {
      createAddUpdateCustomerAddress(input: $input) {
        addUpdateCustomerAddress {
          addressId
          firstName
          city
        }
      }
    }
  `,

  readGetCustomerAddress: `
    query shopAccountReadAddress($id: ID!) {
      readGetCustomerAddress(id: $id) {
        _id
        addressId
        firstName
        lastName
        city
        postcode
        country
        defaultAddress
      }
    }
  `,

  socialLogin: `
    mutation shopAccountSocialLogin($input: createSocialLoginInput!) {
      createSocialLogin(input: $input) {
        socialLogin {
          success
          message
          code
          token
        }
      }
    }
  `,

  resetPassword: `
    mutation shopAccountResetPassword($input: createResetPasswordInput!) {
      createResetPassword(input: $input) {
        resetPassword {
          success
          message
        }
      }
    }
  `,

  verifyAccount: `
    mutation shopAccountVerifyAccount($token: String!) {
      createVerifyAccount(input: { token: $token }) {
        verifyAccount {
          success
          message
        }
      }
    }
  `,

  resendVerificationEmail: `
    mutation shopAccountResendVerification($email: String!) {
      createResendVerificationEmail(input: { email: $email }) {
        resendVerificationEmail {
          success
          message
        }
      }
    }
  `,

  newsletterUnsubscription: `
    mutation shopAccountNewsletterUnsubscribe($token: String) {
      createNewsletterUnsubscription(input: { token: $token }) {
        newsletterUnsubscription {
          success
          message
        }
      }
    }
  `,

  productReviewTranslation: `
    query shopAccountReviewTranslation($productId: Int!, $reviewId: Int!) {
      productReviewTranslation(productId: $productId, reviewId: $reviewId) {
        content
      }
    }
  `,

  shipmentItems: `
    query shopAccountShipmentItems($first: Int) {
      customerOrderShipmentItems(first: $first) {
        edges {
          node {
            _id
            sku
            name
            qty
          }
        }
        totalCount
      }
    }
  `,

  shipmentItem: `
    query shopAccountShipmentItem($id: ID!) {
      customerOrderShipmentItem(id: $id) {
        _id
        sku
        name
        qty
        shipment {
          _id
        }
      }
    }
  `,

  returnReasons: `
    query shopAccountReturnReasons($resolutionType: String!) {
      returnReasons(resolutionType: $resolutionType) {
        _id
        title
        position
      }
    }
  `,

  returnCustomFields: `
    query shopAccountReturnCustomFields {
      returnCustomFields {
        _id
        code
        label
        type
        isRequired
        position
        options
      }
    }
  `,

  returnableItems: `
    query shopAccountReturnableItems($orderId: Int!) {
      returnableItems(orderId: $orderId) {
        orderItemId
        sku
        name
        qtyOrdered
        currentQuantity
        forReturnQuantity
        forCancelQuantity
        rmaQuantity
      }
    }
  `,

  returnableOrders: `
    query shopAccountReturnableOrders($incrementId: String) {
      returnableOrders(incrementId: $incrementId) {
        _id
        incrementId
        status
        totalQtyOrdered
        totalReturnedQty
        returnableQty
      }
    }
  `,

  customerReturns: `
    query shopAccountReturns($first: Int, $status: Int) {
      customerReturns(first: $first, status: $status) {
        edges {
          node {
            _id
            orderId
            statusId
          }
        }
      }
    }
  `,

  customerReturn: `
    query shopAccountReturn($id: ID!) {
      customerReturn(id: $id) {
        _id
        orderId
        orderIncrementId
        statusId
        statusTitle
        information
        packageCondition
        canClose
        canReopen
        isExpired
        messagesCount
        item
      }
    }
  `,

  createCustomerReturn: `
    mutation shopAccountCreateReturn($input: createCustomerReturnInput!) {
      createCustomerReturn(input: $input) {
        customerReturn {
          _id
          orderId
          statusId
          information
          packageCondition
          canClose
          canReopen
          item
        }
      }
    }
  `,

  cancelCustomerReturn: `
    mutation shopAccountCancelReturn($id: ID!) {
      cancelCustomerReturn(input: { id: $id }) {
        customerReturn {
          _id
          statusId
          canClose
          canReopen
        }
      }
    }
  `,

  closeCustomerReturn: `
    mutation shopAccountCloseReturn($id: ID!) {
      closeCustomerReturn(input: { id: $id }) {
        customerReturn {
          _id
          statusId
          canClose
          canReopen
        }
      }
    }
  `,

  reopenCustomerReturn: `
    mutation shopAccountReopenReturn($id: ID!) {
      reopenCustomerReturn(input: { id: $id }) {
        customerReturn {
          _id
          statusId
          canClose
          canReopen
        }
      }
    }
  `,

  customerReturnMessages: `
    query shopAccountReturnMessages($returnId: Int!) {
      customerReturnMessages(returnId: $returnId) {
        _id
        rmaId
        message
        isAdmin
      }
    }
  `,

  createCustomerReturnMessage: `
    mutation shopAccountCreateReturnMessage($returnId: Int, $message: String) {
      createCustomerReturnMessage(input: { returnId: $returnId, message: $message }) {
        customerReturnMessage {
          _id
          rmaId
          message
          isAdmin
        }
      }
    }
  `,

  euWithdrawalStatus: `
    query shopAccountEuWithdrawalStatus {
      euWithdrawalStatus {
        channel
        enabled
      }
    }
  `,

  euWithdrawals: `
    query shopAccountEuWithdrawals($first: Int) {
      euWithdrawals(first: $first) {
        edges {
          node {
            _id
            uuid
            orderId
            status
          }
        }
      }
    }
  `,

  euWithdrawal: `
    query shopAccountEuWithdrawal($id: ID!) {
      euWithdrawal(id: $id) {
        _id
        uuid
        orderId
        orderIncrementId
        isGuest
        status
        receivedAt
      }
    }
  `,

  createEuWithdrawal: `
    mutation shopAccountCreateEuWithdrawal($orderId: Int, $reasonText: String) {
      createEuWithdrawal(input: { orderId: $orderId, reasonText: $reasonText }) {
        euWithdrawal {
          _id
          uuid
          orderId
          orderIncrementId
          isGuest
          status
        }
      }
    }
  `,

  createGuestEuWithdrawal: `
    mutation shopAccountCreateGuestEuWithdrawal($orderIncrementId: String, $email: String, $reasonText: String) {
      createGuestEuWithdrawal(input: { orderIncrementId: $orderIncrementId, email: $email, reasonText: $reasonText }) {
        guestEuWithdrawal {
          _id
          uuid
          status
        }
      }
    }
  `,

  guestEuWithdrawalConfirmation: `
    query shopAccountGuestEuWithdrawalConfirmation($uuid: String!, $email: String!) {
      guestEuWithdrawalConfirmation(uuid: $uuid, email: $email) {
        uuid
        orderIncrementId
        status
        receivedAt
      }
    }
  `,

  gdprStatus: `
    query shopAccountGdprStatus {
      gdprStatus {
        channel
        enabled
      }
    }
  `,

  gdprData: `
    query shopAccountGdprData {
      gdprData {
        customer
        addresses
        orders
      }
    }
  `,

  gdprDataPdf: `
    query shopAccountGdprDataPdf {
      gdprDataPdf {
        fileName
        mimeType
        size
        content
      }
    }
  `,

  gdprRequests: `
    query shopAccountGdprRequests($first: Int) {
      gdprRequests(first: $first) {
        edges {
          node {
            _id
            type
            status
            message
          }
        }
      }
    }
  `,

  gdprRequest: `
    query shopAccountGdprRequest($id: ID!) {
      gdprRequest(id: $id) {
        _id
        type
        status
        message
        email
      }
    }
  `,

  createGdprRequest: `
    mutation shopAccountCreateGdprRequest($type: String, $message: String) {
      createGdprRequest(input: { type: $type, message: $message }) {
        gdprRequest {
          _id
          type
          status
          message
          email
          successMessage
        }
      }
    }
  `,

  revokeGdprRequest: `
    mutation shopAccountRevokeGdprRequest($id: ID!) {
      revokeGdprRequest(input: { id: $id }) {
        gdprRequest {
          _id
          status
          revokedAt
          successMessage
        }
      }
    }
  `,

  deleteGdprRequest: `
    mutation shopAccountDeleteGdprRequest($id: ID!) {
      deleteGdprRequest(input: { id: $id }) {
        gdprRequest {
          _id
        }
      }
    }
  `,

  productDownloadableLinks: `
    query shopAccountDownloadableLinks($first: Int, $after: String) {
      productDownloadableLinks(first: $first, after: $after) {
        edges {
          node {
            _id
            type
            price
            sortOrder
          }
        }
        pageInfo {
          hasNextPage
          endCursor
        }
      }
    }
  `,

  productDownloadableLink: `
    query shopAccountDownloadableLink($id: ID!) {
      productDownloadableLink(id: $id) {
        _id
        type
        price
        sortOrder
        product {
          _id
        }
      }
    }
  `,

  productDownloadableSamples: `
    query shopAccountDownloadableSamples($first: Int, $after: String) {
      productDownloadableSamples(first: $first, after: $after) {
        edges {
          node {
            _id
            type
            sortOrder
          }
        }
        pageInfo {
          hasNextPage
          endCursor
        }
      }
    }
  `,

  productDownloadableSample: `
    query shopAccountDownloadableSample($id: ID!) {
      productDownloadableSample(id: $id) {
        _id
        type
        sortOrder
        product {
          _id
        }
      }
    }
  `,
};
