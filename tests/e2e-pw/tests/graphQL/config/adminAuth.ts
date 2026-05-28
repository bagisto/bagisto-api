// tests/graphQL/config/adminAuth.ts
//
// Admin GraphQL authentication helper (post 2026-05-27 refactor).
//
// Admin endpoints authenticate via a pre-issued AdminPersonalAccessToken
// (Webkul\BagistoApi\Admin\Auth\AdminApiGuard). The plaintext token lives in
// the `ADMIN_INTEGRATION_TOKEN` env var. No login mutation, no token cache.

import { env } from './env';

export function getAdminToken(): string {
  return env.adminIntegrationToken;
}

export function adminGraphQLHeaders(token?: string): Record<string, string> {
  return {
    'Authorization': `Bearer ${token ?? getAdminToken()}`,
  };
}
