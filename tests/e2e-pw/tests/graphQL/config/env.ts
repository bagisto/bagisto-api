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
export const env = {
  baseUrl: readEnv('BAGISTO_URL', true)!,
  graphqlEndpoint: '/api/graphql',
  storefrontAccessKey: readEnv('STOREFRONT_ACCESS_KEY', true)!,
  adminIntegrationToken: readEnv('ADMIN_INTEGRATION_TOKEN', true)!,
  adminEmail: readEnv('ADMIN_EMAIL', true)!,
  bookingProductId: readEnv('BAGISTO_BOOKING_PRODUCT_ID'),
  bookingDate: readEnv('BAGISTO_BOOKING_DATE') ?? '2026-03-26',
};
