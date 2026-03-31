function readEnv(name: string, required = false): string | undefined {
  const value = process.env[name]?.trim();

  if (required && !value) {
    throw new Error(`❌ ${name} is not defined`);
  }

  return value;
}

export const env = {
  baseUrl: readEnv('BAGISTO_URL', true)!,
  graphqlEndpoint: '/api/graphql',
  storefrontAccessKey: readEnv('STOREFRONT_ACCESS_KEY', true)!,
  customerEmail: readEnv('BAGISTO_CUSTOMER_EMAIL'),
  customerPassword: readEnv('BAGISTO_CUSTOMER_PASSWORD'),
  bookingProductId: readEnv('BAGISTO_BOOKING_PRODUCT_ID'),
  bookingDate: readEnv('BAGISTO_BOOKING_DATE') ?? '2026-03-26',
};
