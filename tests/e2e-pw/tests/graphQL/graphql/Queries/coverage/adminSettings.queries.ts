export const ADMIN_SETTINGS_EXCHANGE_RATE_UPDATE_RATES_MUTATION = `
  mutation updateRates($input: createAdminSettingsExchangeRateUpdateRatesInput!) {
    createAdminSettingsExchangeRateUpdateRates(input: $input) {
      adminSettingsExchangeRateUpdateRates {
        success
        updated
        message
      }
    }
  }
`;

export const ADMIN_SETTINGS_USER_DELETE_SELF_MUTATION = `
  mutation deleteSelf($input: createAdminSettingsUserDeleteSelfInput!) {
    createAdminSettingsUserDeleteSelf(input: $input) {
      adminSettingsUserDeleteSelf {
        _id
        success
        message
      }
    }
  }
`;

export const ADMIN_PROFILE_QUERY = `
  query readAdminProfile {
    readAdminProfile {
      id
      email
    }
  }
`;

export const ADMIN_SETTINGS_DATA_TRANSFER_IMPORT_CREATE_MUTATION = `
  mutation createImport($input: createAdminSettingsDataTransferImportInput!) {
    createAdminSettingsDataTransferImport(input: $input) {
      adminSettingsDataTransferImport {
        _id
      }
    }
  }
`;

const IMPORT_FIELDS = `
  _id
  code
  action
  state
  processedRowsCount
  invalidRowsCount
  errorsCount
  isValid
  success
  stats
`;

export const ADMIN_SETTINGS_DATA_TRANSFER_IMPORT_VALIDATE_MUTATION = `
  mutation validateImport($input: validateAdminSettingsDataTransferImportValidateInput!) {
    validateAdminSettingsDataTransferImportValidate(input: $input) {
      adminSettingsDataTransferImportValidate {
        ${IMPORT_FIELDS}
      }
    }
  }
`;

export const ADMIN_SETTINGS_DATA_TRANSFER_IMPORT_START_MUTATION = `
  mutation startImport($input: startAdminSettingsDataTransferImportStartInput!) {
    startAdminSettingsDataTransferImportStart(input: $input) {
      adminSettingsDataTransferImportStart {
        ${IMPORT_FIELDS}
      }
    }
  }
`;

export const ADMIN_SETTINGS_DATA_TRANSFER_IMPORT_LINK_MUTATION = `
  mutation linkImport($input: linkAdminSettingsDataTransferImportLinkInput!) {
    linkAdminSettingsDataTransferImportLink(input: $input) {
      adminSettingsDataTransferImportLink {
        ${IMPORT_FIELDS}
      }
    }
  }
`;

export const ADMIN_SETTINGS_DATA_TRANSFER_IMPORT_INDEX_MUTATION = `
  mutation indexImport($input: indexAdminSettingsDataTransferImportIndexInput!) {
    indexAdminSettingsDataTransferImportIndex(input: $input) {
      adminSettingsDataTransferImportIndex {
        ${IMPORT_FIELDS}
      }
    }
  }
`;

export const ADMIN_SETTINGS_DATA_TRANSFER_IMPORT_STATS_QUERY = `
  query importStats($id: ID!) {
    adminSettingsDataTransferImportStats(id: $id) {
      ${IMPORT_FIELDS}
    }
  }
`;
