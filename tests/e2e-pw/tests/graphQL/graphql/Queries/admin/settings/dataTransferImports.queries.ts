// Admin Settings — Data Transfer Imports GraphQL operations.
// Create is deferred (multipart). Only list + detail + cancel + delete.

export const ADMIN_IMPORTS_LIST = `
  query adminSettingsDataTransferImports($first: Int, $after: String) {
    adminSettingsDataTransferImports(first: $first, after: $after) {
      edges { node { id _id code action state } }
    }
  }
`;

export const ADMIN_IMPORT_DETAIL = `
  query adminSettingsDataTransferImport($id: ID!) {
    adminSettingsDataTransferImport(id: $id) { id _id code action state }
  }
`;

export const ADMIN_IMPORT_CANCEL = `
  mutation cancelAdminSettingsDataTransferImportCancel($id: ID!) {
    cancelAdminSettingsDataTransferImportCancel(input: { id: $id }) {
      adminSettingsDataTransferImportCancel { id }
    }
  }
`;

export const ADMIN_IMPORT_DELETE = `
  mutation deleteAdminSettingsDataTransferImport($id: ID!) {
    deleteAdminSettingsDataTransferImport(input: { id: $id }) {
      adminSettingsDataTransferImport { id }
    }
  }
`;
