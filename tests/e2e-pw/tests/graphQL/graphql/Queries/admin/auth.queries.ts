// tests/graphQL/graphql/Queries/admin/auth.queries.ts
//
// Admin authentication GraphQL operations — post 2026-05-27 refactor.
// Only the profile-read query survives; the auth.spec.ts file now inlines
// the query string directly. This file is kept for callers that want a
// reusable constant.

export const ADMIN_PROFILE_QUERY = `
  query readAdminProfile {
    readAdminProfile {
      id
      _id
      name
      email
      success
    }
  }
`;
