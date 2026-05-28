// rest/helpers/adminAuth.ts
//
// Admin API authentication helper (post 2026-05-27 refactor).
//
// Admin endpoints authenticate via a pre-issued AdminPersonalAccessToken
// (Webkul\BagistoApi\Admin\Auth\AdminApiGuard). The plaintext token lives in
// the `ADMIN_INTEGRATION_TOKEN` environment variable. There is no login
// round-trip — the token never changes during a test run, so we don't cache
// or rotate anything. X-Admin-Key is no longer a thing for /api/admin/*.

import { env } from '../../config/env';

/**
 * Return the static admin Bearer token from the environment.
 */
export function getAdminToken(): string {
  return env.adminIntegrationToken;
}

/**
 * Bearer auth headers for admin requests.
 */
export function adminHeaders(token?: string): Record<string, string> {
  return {
    'Authorization': `Bearer ${token ?? getAdminToken()}`,
  };
}
