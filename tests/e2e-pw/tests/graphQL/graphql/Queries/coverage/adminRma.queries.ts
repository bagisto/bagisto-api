export const ADMIN_RMA_STATUSES_QUERY = `
  query adminRmaStatuses($title: String, $status: Int, $first: Int) {
    adminRmaStatuses(title: $title, status: $status, first: $first) {
      totalCount
      edges {
        node {
          id
          _id
          title
          status
          color
          default
          message
          createdAt
          updatedAt
        }
      }
    }
  }
`;

export const ADMIN_RMA_STATUS_QUERY = `
  query adminRmaStatus($id: ID!) {
    adminRmaStatus(id: $id) {
      id
      _id
      title
      status
      color
      default
      message
      createdAt
      updatedAt
    }
  }
`;

export const CREATE_ADMIN_RMA_STATUS = `
  mutation createAdminRmaStatus($title: String, $status: Int, $color: String) {
    createAdminRmaStatus(input: { title: $title, status: $status, color: $color }) {
      adminRmaStatus {
        id
        _id
        title
        status
        color
        default
        message
        createdAt
        updatedAt
      }
    }
  }
`;

export const UPDATE_ADMIN_RMA_STATUS = `
  mutation updateAdminRmaStatus($id: ID!, $title: String, $status: Int, $color: String) {
    updateAdminRmaStatus(input: { id: $id, title: $title, status: $status, color: $color }) {
      adminRmaStatus {
        id
        _id
        title
        status
        color
        default
        message
        createdAt
        updatedAt
      }
    }
  }
`;

export const DELETE_ADMIN_RMA_STATUS = `
  mutation deleteAdminRmaStatus($id: ID!) {
    deleteAdminRmaStatus(input: { id: $id }) {
      adminRmaStatus {
        _id
        title
        message
      }
    }
  }
`;

export const ADMIN_RMA_STATUS_MASS_DELETE = `
  mutation createAdminRmaStatusMassDelete($indices: Iterable) {
    createAdminRmaStatusMassDelete(input: { indices: $indices }) {
      adminRmaStatusMassDelete {
        deleted
        message
      }
    }
  }
`;

export const ADMIN_RMA_STATUS_MASS_UPDATE_STATUS = `
  mutation createAdminRmaStatusMassUpdateStatus($indices: Iterable, $value: Int) {
    createAdminRmaStatusMassUpdateStatus(input: { indices: $indices, value: $value }) {
      adminRmaStatusMassUpdateStatus {
        updated
        message
      }
    }
  }
`;

export const ADMIN_RMA_REASONS_QUERY = `
  query adminRmaReasons($title: String, $status: Int, $first: Int) {
    adminRmaReasons(title: $title, status: $status, first: $first) {
      totalCount
      edges {
        node {
          id
          _id
          title
          status
          position
          isAdmin
          resolutionType
          message
          createdAt
        }
      }
    }
  }
`;

export const ADMIN_RMA_REASON_QUERY = `
  query adminRmaReason($id: ID!) {
    adminRmaReason(id: $id) {
      id
      _id
      title
      status
      position
      isAdmin
      resolutionType
      message
      createdAt
    }
  }
`;

export const CREATE_ADMIN_RMA_REASON = `
  mutation createAdminRmaReason($title: String, $status: Int, $position: Int, $resolutionType: Iterable) {
    createAdminRmaReason(input: { title: $title, status: $status, position: $position, resolutionType: $resolutionType }) {
      adminRmaReason {
        id
        _id
        title
        status
        position
        isAdmin
        resolutionType
        message
        createdAt
      }
    }
  }
`;

export const UPDATE_ADMIN_RMA_REASON = `
  mutation updateAdminRmaReason($id: ID!, $title: String, $status: Int, $position: Int, $resolutionType: Iterable) {
    updateAdminRmaReason(input: { id: $id, title: $title, status: $status, position: $position, resolutionType: $resolutionType }) {
      adminRmaReason {
        id
        _id
        title
        status
        position
        isAdmin
        resolutionType
        message
        createdAt
      }
    }
  }
`;

export const DELETE_ADMIN_RMA_REASON = `
  mutation deleteAdminRmaReason($id: ID!) {
    deleteAdminRmaReason(input: { id: $id }) {
      adminRmaReason {
        _id
        title
        message
      }
    }
  }
`;

export const ADMIN_RMA_REASON_MASS_DELETE = `
  mutation createAdminRmaReasonMassDelete($indices: Iterable) {
    createAdminRmaReasonMassDelete(input: { indices: $indices }) {
      adminRmaReasonMassDelete {
        deleted
        message
      }
    }
  }
`;

export const ADMIN_RMA_REASON_MASS_UPDATE_STATUS = `
  mutation createAdminRmaReasonMassUpdateStatus($indices: Iterable, $value: Int) {
    createAdminRmaReasonMassUpdateStatus(input: { indices: $indices, value: $value }) {
      adminRmaReasonMassUpdateStatus {
        updated
        message
      }
    }
  }
`;

export const ADMIN_RMA_RULES_QUERY = `
  query adminRmaRules($name: String, $status: Int, $first: Int) {
    adminRmaRules(name: $name, status: $status, first: $first) {
      totalCount
      edges {
        node {
          id
          _id
          name
          description
          status
          returnPeriod
          default
          message
          createdAt
        }
      }
    }
  }
`;

export const ADMIN_RMA_RULE_QUERY = `
  query adminRmaRule($id: ID!) {
    adminRmaRule(id: $id) {
      id
      _id
      name
      description
      status
      returnPeriod
      default
      message
      createdAt
    }
  }
`;

export const CREATE_ADMIN_RMA_RULE = `
  mutation createAdminRmaRule($name: String, $description: String, $status: Int, $returnPeriod: Int) {
    createAdminRmaRule(input: { name: $name, description: $description, status: $status, returnPeriod: $returnPeriod }) {
      adminRmaRule {
        id
        _id
        name
        description
        status
        returnPeriod
        default
        message
        createdAt
      }
    }
  }
`;

export const UPDATE_ADMIN_RMA_RULE = `
  mutation updateAdminRmaRule($id: ID!, $name: String, $description: String, $status: Int, $returnPeriod: Int) {
    updateAdminRmaRule(input: { id: $id, name: $name, description: $description, status: $status, returnPeriod: $returnPeriod }) {
      adminRmaRule {
        id
        _id
        name
        description
        status
        returnPeriod
        default
        message
        createdAt
      }
    }
  }
`;

export const DELETE_ADMIN_RMA_RULE = `
  mutation deleteAdminRmaRule($id: ID!) {
    deleteAdminRmaRule(input: { id: $id }) {
      adminRmaRule {
        _id
        name
        message
      }
    }
  }
`;

export const ADMIN_RMA_RULE_MASS_DELETE = `
  mutation createAdminRmaRuleMassDelete($indices: Iterable) {
    createAdminRmaRuleMassDelete(input: { indices: $indices }) {
      adminRmaRuleMassDelete {
        deleted
        message
      }
    }
  }
`;

export const ADMIN_RMA_RULE_MASS_UPDATE_STATUS = `
  mutation createAdminRmaRuleMassUpdateStatus($indices: Iterable, $value: Int) {
    createAdminRmaRuleMassUpdateStatus(input: { indices: $indices, value: $value }) {
      adminRmaRuleMassUpdateStatus {
        updated
        message
      }
    }
  }
`;

export const ADMIN_RMA_CUSTOM_FIELDS_QUERY = `
  query adminRmaCustomFields($code: String, $label: String, $type: String, $status: Int, $first: Int) {
    adminRmaCustomFields(code: $code, label: $label, type: $type, status: $status, first: $first) {
      totalCount
      edges {
        node {
          id
          _id
          code
          label
          type
          isRequired
          position
          inputValidation
          status
          options
          message
          createdAt
        }
      }
    }
  }
`;

export const ADMIN_RMA_CUSTOM_FIELD_QUERY = `
  query adminRmaCustomField($id: ID!) {
    adminRmaCustomField(id: $id) {
      id
      _id
      code
      label
      type
      isRequired
      position
      inputValidation
      status
      options
      message
      createdAt
    }
  }
`;

export const CREATE_ADMIN_RMA_CUSTOM_FIELD = `
  mutation createAdminRmaCustomField(
    $code: String
    $label: String
    $type: String
    $isRequired: Int
    $position: Int
    $inputValidation: String
    $status: Int
    $options: Iterable
  ) {
    createAdminRmaCustomField(input: {
      code: $code
      label: $label
      type: $type
      isRequired: $isRequired
      position: $position
      inputValidation: $inputValidation
      status: $status
      options: $options
    }) {
      adminRmaCustomField {
        id
        _id
        code
        label
        type
        isRequired
        position
        inputValidation
        status
        options
        message
        createdAt
      }
    }
  }
`;

export const UPDATE_ADMIN_RMA_CUSTOM_FIELD = `
  mutation updateAdminRmaCustomField(
    $id: ID!
    $code: String
    $label: String
    $type: String
    $isRequired: Int
    $position: Int
    $inputValidation: String
    $status: Int
    $options: Iterable
  ) {
    updateAdminRmaCustomField(input: {
      id: $id
      code: $code
      label: $label
      type: $type
      isRequired: $isRequired
      position: $position
      inputValidation: $inputValidation
      status: $status
      options: $options
    }) {
      adminRmaCustomField {
        id
        _id
        code
        label
        type
        isRequired
        position
        inputValidation
        status
        options
        message
        createdAt
      }
    }
  }
`;

export const DELETE_ADMIN_RMA_CUSTOM_FIELD = `
  mutation deleteAdminRmaCustomField($id: ID!) {
    deleteAdminRmaCustomField(input: { id: $id }) {
      adminRmaCustomField {
        _id
        code
        message
      }
    }
  }
`;

export const ADMIN_RMA_CUSTOM_FIELD_MASS_DELETE = `
  mutation createAdminRmaCustomFieldMassDelete($indices: Iterable) {
    createAdminRmaCustomFieldMassDelete(input: { indices: $indices }) {
      adminRmaCustomFieldMassDelete {
        deleted
        message
      }
    }
  }
`;

export const ADMIN_RMA_CUSTOM_FIELD_MASS_UPDATE_STATUS = `
  mutation createAdminRmaCustomFieldMassUpdateStatus($indices: Iterable, $value: Int) {
    createAdminRmaCustomFieldMassUpdateStatus(input: { indices: $indices, value: $value }) {
      adminRmaCustomFieldMassUpdateStatus {
        updated
        message
      }
    }
  }
`;

export const ADMIN_RETURNS_QUERY = `
  query adminReturns($id: Int, $order_id: Int, $status: String, $first: Int) {
    adminReturns(id: $id, order_id: $order_id, status: $status, first: $first) {
      totalCount
      edges {
        node {
          id
          _id
          orderId
          orderIncrementId
          statusId
          statusTitle
          customerEmail
          createdAt
        }
      }
    }
  }
`;

export const ADMIN_RETURN_QUERY = `
  query adminReturn($id: ID!) {
    adminReturn(id: $id) {
      id
      _id
      orderId
      orderIncrementId
      orderStatus
      customerName
      customerEmail
      isGuest
      statusId
      statusTitle
      statusColor
      packageCondition
      information
      canReopen
      item
      images
      availableStatuses
      messagesCount
      createdAt
      updatedAt
    }
  }
`;

export const ADMIN_RETURN_MESSAGES_QUERY = `
  query adminReturnMessages($returnId: Int!) {
    adminReturnMessages(returnId: $returnId) {
      _id
      rmaId
      message
      isAdmin
      attachment
      attachmentUrl
      createdAt
    }
  }
`;

export const ADMIN_RETURN_REASONS_QUERY = `
  query adminReturnReasons($resolutionType: String!) {
    adminReturnReasons(resolutionType: $resolutionType) {
      _id
      title
      position
    }
  }
`;

export const ADMIN_RETURNABLE_ITEMS_QUERY = `
  query adminReturnableItems($orderId: Int!) {
    adminReturnableItems(orderId: $orderId) {
      orderItemId
      productId
      sku
      name
      type
      price
      qtyOrdered
      currentQuantity
      forReturnQuantity
      forCancelQuantity
      rmaQuantity
      rmaReturnPeriod
    }
  }
`;

export const CREATE_ADMIN_RETURN = `
  mutation createAdminReturn(
    $orderId: Int
    $orderItemId: Int
    $rmaQty: Int
    $resolutionType: String
    $rmaReasonId: Int
    $information: String
    $packageCondition: String
  ) {
    createAdminReturn(input: {
      orderId: $orderId
      orderItemId: $orderItemId
      rmaQty: $rmaQty
      resolutionType: $resolutionType
      rmaReasonId: $rmaReasonId
      information: $information
      packageCondition: $packageCondition
    }) {
      adminReturn {
        id
        _id
        orderId
        orderIncrementId
        orderStatus
        customerName
        customerEmail
        isGuest
        statusId
        statusTitle
        statusColor
        packageCondition
        information
        canReopen
        item
        images
        availableStatuses
        messagesCount
        createdAt
        updatedAt
      }
    }
  }
`;

export const CREATE_ADMIN_RETURN_MESSAGE = `
  mutation createAdminReturnMessage($returnId: Int, $message: String) {
    createAdminReturnMessage(input: { returnId: $returnId, message: $message }) {
      adminReturnMessage {
        _id
        rmaId
        message
        isAdmin
        attachment
        attachmentUrl
        createdAt
      }
    }
  }
`;

export const UPDATE_STATUS_ADMIN_RETURN = `
  mutation updateStatusAdminReturn($id: ID!, $rmaStatusId: Int, $shipping: Float) {
    updateStatusAdminReturn(input: { id: $id, rmaStatusId: $rmaStatusId, shipping: $shipping }) {
      adminReturn {
        id
        _id
        orderId
        orderIncrementId
        orderStatus
        customerName
        customerEmail
        isGuest
        statusId
        statusTitle
        statusColor
        packageCondition
        information
        canReopen
        item
        images
        availableStatuses
        messagesCount
        createdAt
        updatedAt
      }
    }
  }
`;

export const REOPEN_ADMIN_RETURN = `
  mutation reopenAdminReturn($id: ID!) {
    reopenAdminReturn(input: { id: $id }) {
      adminReturn {
        id
        _id
        orderId
        orderIncrementId
        orderStatus
        customerName
        customerEmail
        isGuest
        statusId
        statusTitle
        statusColor
        packageCondition
        information
        canReopen
        item
        images
        availableStatuses
        messagesCount
        createdAt
        updatedAt
      }
    }
  }
`;

export const RESEND_CONFIRMATION_ADMIN_EU_WITHDRAWAL = `
  mutation resendConfirmationAdminEuWithdrawal($id: ID!) {
    resendConfirmationAdminEuWithdrawal(input: { id: $id }) {
      adminEuWithdrawal {
        _id
        orderId
        status
        confirmationSentAt
        finalConfirmationSentAt
        confirmationError
        message
      }
    }
  }
`;

export const ADMIN_EU_WITHDRAWAL_QUERY = `
  query adminEuWithdrawal($id: ID!) {
    adminEuWithdrawal(id: $id) {
      _id
      confirmationError
      confirmationSentAt
      finalConfirmationSentAt
    }
  }
`;
