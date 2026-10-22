export const ADMIN_TOKEN_PAIR_FIELDS = 'tokenType accessToken expiresIn accessTokenExpiresAt refreshToken refreshTokenExpiresAt session admin permissions';

export const ADMIN_LOGIN = `
  mutation createAdminLogin($input: createAdminLoginInput!) {
    createAdminLogin(input: $input) {
      adminLogin { ${ADMIN_TOKEN_PAIR_FIELDS} twoFactorRequired challengeToken }
    }
  }
`;

export const ADMIN_TOKEN_REFRESH = `
  mutation createAdminTokenRefresh($input: createAdminTokenRefreshInput!) {
    createAdminTokenRefresh(input: $input) {
      adminTokenRefresh { ${ADMIN_TOKEN_PAIR_FIELDS} }
    }
  }
`;

export const ADMIN_LOGOUT = `
  mutation createAdminLogout {
    createAdminLogout(input: {}) { adminLogout { success message } }
  }
`;

export const ADMIN_LOGOUT_ALL = `
  mutation createAdminLogoutAll {
    createAdminLogoutAll(input: {}) { adminLogoutAll { success revoked } }
  }
`;

export const ADMIN_SESSIONS = `
  query adminSessions {
    adminSessions { edges { node { _id name clientType isCurrent lastChannel status } } }
  }
`;

export const ADMIN_SESSION_DELETE = `
  mutation deleteAdminSession($input: deleteAdminSessionInput!) {
    deleteAdminSession(input: $input) { adminSession { _id status } }
  }
`;

export const ADMIN_USER_SESSIONS = `
  query adminSettingsUserSessions($adminId: Int!) {
    adminSettingsUserSessions(admin_id: $adminId) { edges { node { _id clientType } } }
  }
`;

export const ADMIN_USER_REVOKE_SESSIONS = `
  mutation revokeSessionsAdminSettingsUser($input: revokeSessionsAdminSettingsUserInput!) {
    revokeSessionsAdminSettingsUser(input: $input) { adminSettingsUser { _id message } }
  }
`;

export const ADMIN_API_SESSIONS = `
  query adminApiSessions {
    adminApiSessions(first: 5) { edges { node { _id } } }
  }
`;

export const ADMIN_PROFILE = `
  query readAdminProfile {
    readAdminProfile { id email roleId tokenKind tokenId tokenExpiresAt }
  }
`;

export const ADMIN_API_TOKENS = `
  query adminApiTokens($domain: String, $status: String) {
    adminApiTokens(first: 50, domain: $domain, status: $status) {
      edges { node { _id name domain tokenPreview status lastChannel } }
    }
  }
`;

export const ADMIN_API_TOKEN = `
  query adminApiToken($id: ID!) {
    adminApiToken(id: $id) { _id name domain tokenPreview status resolvedIps domainMismatchCount }
  }
`;

export const ADMIN_API_TOKEN_DELETE = `
  mutation deleteAdminApiToken($input: deleteAdminApiTokenInput!) {
    deleteAdminApiToken(input: $input) { adminApiToken { _id status revokedReason } }
  }
`;
