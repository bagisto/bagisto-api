function readEnv(name: string, required = false): string | undefined {
  const value = process.env[name]?.trim();

  if (required && !value) {
    throw new Error(`❌ ${name} is not defined`);
  }

  return value;
}

// Admin API auth model (post 2026-05-27 refactor):
//   - Single pre-issued AdminPersonalAccessToken in ADMIN_INTEGRATION_TOKEN.
//   - No X-Admin-Key, no login round-trip, no token regeneration mid-run.
//   - ADMIN_EMAIL is kept only so the profile-read test can assert on it.
//
// The old ADMIN_PASSWORD / ADMIN_ACCESS_KEY env vars are no longer consumed.
// They may still appear in the shell environment for legacy tooling, but are
// not exported on this object.
export const env = {
  baseUrl: readEnv('BAGISTO_URL', true)!,
  storefrontAccessKey: readEnv('STOREFRONT_ACCESS_KEY', true)!,
  adminIntegrationToken: readEnv('ADMIN_INTEGRATION_TOKEN', true)!,
  adminEmail: readEnv('ADMIN_EMAIL', true)!,
};
