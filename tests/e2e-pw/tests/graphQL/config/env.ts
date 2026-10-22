function readEnv(name: string, required = false): string | undefined {
  const value = process.env[name]?.trim();

  if (required && !value) {
    throw new Error(`❌ ${name} is not defined`);
  }

  return value || undefined;
}

export const env = {
  baseUrl: readEnv('BAGISTO_URL', true)!,
  graphqlEndpoint: '/api/graphql',
  adminGraphqlEndpoint: '/api/admin/graphql',
  storefrontAccessKey: readEnv('STOREFRONT_ACCESS_KEY', true)!,
  adminEmail: readEnv('ADMIN_EMAIL', true)!,
  adminPassword: readEnv('ADMIN_PASSWORD', true)!,
  adminApiToken: readEnv('ADMIN_API_TOKEN'),
  adminApiTokenOtherDomain: readEnv('ADMIN_API_TOKEN_OTHER_DOMAIN'),
  adminApiTokenRevocable: readEnv('ADMIN_API_TOKEN_REVOCABLE_GRAPHQL'),
  bookingProductId: readEnv('BAGISTO_BOOKING_PRODUCT_ID'),
  bookingDate: readEnv('BAGISTO_BOOKING_DATE') ?? '2026-03-26',
};
