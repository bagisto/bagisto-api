export const ADMIN_MENU_COVERAGE_QUERY = `
  query getAdminMenu {
    getAdminMenu {
      tree
    }
  }
`;

export const ADMIN_PERMISSIONS_COVERAGE_QUERY = `
  query getAdminPermissions {
    getAdminPermissions {
      permissionType
      permissions
    }
  }
`;

export const ADMIN_CONFIGURATION_SLUGS_COVERAGE_QUERY = `
  query listAdminConfigurationSlug {
    listAdminConfigurationSlug {
      slugs
    }
  }
`;

export const ADMIN_CONFIGURATION_SLUGS_UNKNOWN_FIELD_QUERY = `
  query listAdminConfigurationSlug {
    listAdminConfigurationSlug {
      slugs
      sections
    }
  }
`;
